<?php
require_once 'db.php';
require_once 'email.php';
require_once 'notification.php';
/**
 * ===========================
 *      ADMIN SECTION
 * ===========================
 */

function loginAdmin($email, $password) {
    $db = (new Database())->connect();

    // Stricter validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }

    $stmt = $db->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        // Use consistent error messages to prevent email enumeration
        return ['success' => false, 'message' => 'Invalid credentials'];
    }

    // Check if account is locked
    if ($admin['locked_until'] && strtotime($admin['locked_until']) > time()) {
        $lockTime = date('H:i', strtotime($admin['locked_until']));
        return ['success' => false, 'message' => "Account is locked until {$lockTime}"];
    }

    // Always verify password (even if account is inactive) to prevent timing attacks
    $passwordValid = password_verify($password, $admin['password']);

    if (!$passwordValid) {
        // Increment login attempts
        $stmt = $db->prepare("UPDATE admins SET login_attempts = login_attempts + 1 WHERE email = ?");
        $stmt->execute([$email]);

        // Lock account after 5 failed attempts
        if ($admin['login_attempts'] + 1 >= 5) {
            $lockUntil = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            $stmt = $db->prepare("UPDATE admins SET locked_until = ? WHERE email = ?");
            $stmt->execute([$lockUntil, $email]);
            return ['success' => false, 'message' => 'Account locked due to too many failed attempts'];
        }

        return ['success' => false, 'message' => 'Invalid credentials'];
    }

    // Check if account is active
    if (!$admin['is_active']) {
        return ['success' => false, 'message' => 'Account deactivated'];
    }

    // Check if password needs to be changed (e.g., every 90 days)
    if ($admin['password_changed_at'] && strtotime($admin['password_changed_at']) < strtotime('-90 days')) {
        return [
            'success' => false,
            'message' => 'Password expired',
            'requires_password_change' => true,
            'admin_id' => $admin['id']
        ];
    }

    // Login successful - Reset attempts and update last login
    $stmt = $db->prepare("UPDATE admins SET login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = ?");
    $stmt->execute([$admin['id']]);

    // Check if 2FA is enabled
    if ($admin['two_factor_enabled']) {
        // Generate and send OTP
        $otpResult = generateAndSendAdminOTP($admin['id']);
        
        if (!$otpResult['success']) {
            return ['success' => false, 'message' => 'Failed to send OTP. Please try again.'];
        }

        // Store temporary session for OTP verification
        $_SESSION['admin_temp_id'] = $admin['id'];
        $_SESSION['admin_temp_email'] = $admin['email'];
        $_SESSION['admin_temp_name'] = $admin['name'];
        $_SESSION['admin_temp_role'] = $admin['role'];
        $_SESSION['admin_temp_permissions'] = json_decode($admin['permissions'], true);
        $_SESSION['admin_otp_verified'] = false;

        return [
            'success' => true, 
            'admin_id' => $admin['id'], 
            'role' => $admin['role'],
            'requires_2fa' => true,
            'otp_method' => $admin['two_factor_method']
        ];
    }

    // No 2FA required - set full session
    setAdminSession($admin);

    return [
        'success' => true, 
        'admin_id' => $admin['id'], 
        'role' => $admin['role'],
        'requires_2fa' => false
    ];
}


/**
 * Generate and send OTP to admin
 */
function generateAndSendAdminOTP($adminId) {
    $db = (new Database())->connect();
    
    // Get admin details
    $stmt = $db->prepare("SELECT email, phone_number, two_factor_method FROM admins WHERE id = ?");
    $stmt->execute([$adminId]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        return ['success' => false, 'message' => 'Admin not found'];
    }
    
    // Generate 6-digit OTP
    $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    // Delete any existing OTPs for this admin
    $stmt = $db->prepare("DELETE FROM admin_otp_tokens WHERE admin_id = ? AND token_type = 'login'");
    $stmt->execute([$adminId]);
    
    // Store the OTP
    $stmt = $db->prepare("
        INSERT INTO admin_otp_tokens (admin_id, token, token_type, expires_at, ip_address, user_agent)
        VALUES (?, ?, 'login', ?, ?, ?)
    ");
    $stmt->execute([
        $adminId,
        password_hash($otp, PASSWORD_DEFAULT),
        $expiresAt,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);
    
    // Send OTP based on method
    switch ($admin['two_factor_method']) {
        case 'email':
            return Notification::sendAdminOTPnotification($admin['email'], $otp);
        case 'sms':
            if (empty($admin['phone_number'])) {
                return ['success' => false, 'message' => 'No phone number registered'];
            }
            return Notification::sendAdminOTPnotification($admin['phone_number'], $otp);
        case 'authenticator':
            return ['success' => true];
        default:
            return ['success' => false, 'message' => 'Invalid 2FA method'];
    }
}

/**
 * Verify admin OTP
 */
function verifyAdminOTP($adminId, $otp) {
    $db = (new Database())->connect();
    
    // Check OTP attempts to prevent brute force
    $attempts = checkOTPAttempts($adminId);
    if ($attempts['locked']) {
        return ['success' => false, 'message' => 'Too many OTP attempts. Please try again later.'];
    }
    
    // Get the most recent valid OTP token
    $stmt = $db->prepare("
        SELECT id, token 
        FROM admin_otp_tokens 
        WHERE admin_id = ? 
        AND token_type = 'login' 
        AND expires_at > NOW() 
        AND is_used = 0
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$adminId]);
    $otpToken = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$otpToken) {
        // Log failed attempt
        logOTPAttempt($adminId, false);
        return ['success' => false, 'message' => 'Invalid or expired OTP'];
    }
    
    // Verify OTP
    if (!password_verify($otp, $otpToken['token'])) {
        // Log failed attempt
        logOTPAttempt($adminId, false);
        return ['success' => false, 'message' => 'Invalid OTP'];
    }
    
    // Mark OTP as used
    $stmt = $db->prepare("UPDATE admin_otp_tokens SET is_used = 1 WHERE id = ?");
    $stmt->execute([$otpToken['id']]);
    
    // Log successful attempt
    logOTPAttempt($adminId, true);
    
    // Get admin details
    $stmt = $db->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$adminId]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Set full admin session
    setAdminSession($admin);
    
    // Mark OTP as verified in session
    $_SESSION['admin_otp_verified'] = true;
    
    return ['success' => true];
}

/**
 * Set admin session after successful authentication
 */
function setAdminSession($admin) {
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_name'] = $admin['name'];
    $_SESSION['admin_permissions'] = json_decode($admin['permissions'], true);
    $_SESSION['is_admin_logged_in'] = true;
    $_SESSION['admin_last_activity'] = time();
    
    // Store additional security info
    $_SESSION['login_ip'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    
    // Clear temporary session if exists
    unset($_SESSION['admin_temp_id']);
    unset($_SESSION['admin_temp_email']);
    unset($_SESSION['admin_temp_name']);
    unset($_SESSION['admin_temp_role']);
    unset($_SESSION['admin_temp_permissions']);
}

/**
 * Check OTP attempts to prevent brute force
 */
function checkOTPAttempts($adminId = null) {
    $db = (new Database())->connect();
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Get attempts for this IP
    $stmt = $db->prepare("SELECT * FROM admin_otp_attempts WHERE ip_address = ?");
    $stmt->execute([$ip]);
    $attempts = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($attempts) {
        // Check if locked
        if ($attempts['locked_until'] && strtotime($attempts['locked_until']) > time()) {
            return ['locked' => true, 'until' => $attempts['locked_until']];
        }
        
        // Check if too many attempts
        if ($attempts['attempts'] >= 5) {
            // Lock for 30 minutes
            $lockedUntil = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            $stmt = $db->prepare("UPDATE admin_otp_attempts SET locked_until = ? WHERE ip_address = ?");
            $stmt->execute([$lockedUntil, $ip]);
            return ['locked' => true, 'until' => $lockedUntil];
        }
    }
    
    return ['locked' => false];
}

/**
 * Log OTP attempt
 */
function logOTPAttempt($adminId, $success) {
    $db = (new Database())->connect();
    $ip = $_SERVER['REMOTE_ADDR'];
    
    try {
        $db->beginTransaction();
        
        // Get current attempt record
        $stmt = $db->prepare("SELECT * FROM admin_otp_attempts WHERE ip_address = ?");
        $stmt->execute([$ip]);
        $attempt = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($attempt) {
            if ($success) {
                // Reset on successful attempt
                $stmt = $db->prepare("
                    UPDATE admin_otp_attempts 
                    SET attempts = 0, 
                        last_attempt = NOW(), 
                        locked_until = NULL 
                    WHERE ip_address = ?
                ");
                $stmt->execute([$ip]);
            } else {
                // Increment failed attempt
                $stmt = $db->prepare("
                    UPDATE admin_otp_attempts 
                    SET attempts = attempts + 1, 
                        last_attempt = NOW() 
                    WHERE ip_address = ?
                ");
                $stmt->execute([$ip]);
            }
        } else {
            // Create new attempt record
            $stmt = $db->prepare("
                INSERT INTO admin_otp_attempts 
                (admin_id, ip_address, attempts, last_attempt) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$adminId, $ip, $success ? 0 : 1]);
        }
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Failed to log OTP attempt: " . $e->getMessage());
        return false;
    }
}


/**
 * Check if admin session is valid (with OTP verification check)
 */
function validateAdminSession() {
    if (!isAdminLoggedIn()) {
        return false;
    }

    // Check if OTP verification is required and completed
    if (isset($_SESSION['admin_temp_id']) && !isset($_SESSION['admin_otp_verified'])) {
        return false;
    }

    // Basic session timeout check
    if (!checkAdminSessionTimeout()) {
        return false;
    }

    // Additional security checks
    $expectedParams = [
        'admin_id',
        'admin_role',
        'admin_email',
        'admin_name',
        'admin_permissions',
        'is_admin_logged_in'
    ];

    foreach ($expectedParams as $param) {
        if (!isset($_SESSION[$param])) {
            return false;
        }
    }

    return true;
}

/**
 * Force password change for admin
 */
function forceAdminPasswordChange($adminId, $newPassword) {
    $db = (new Database())->connect();
    
    // Validate password strength
    $strengthCheck = validateAdminPasswordStrength($newPassword);
    if (!$strengthCheck['valid']) {
        return [
            'success' => false,
            'errors' => $strengthCheck['errors']
        ];
    }

    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    try {
        $db->beginTransaction();
        
        // Update password and reset change timestamp
        $stmt = $db->prepare("UPDATE admins SET password = ?, password_changed_at = NOW() WHERE id = ?");
        $success = $stmt->execute([$hashedPassword, $adminId]);
        
        if (!$success) {
            throw new Exception('Failed to update password');
        }
        
        // Log the password change
        logAdminActivity($adminId, 'password_change_forced', 'admins', $adminId);
        
        $db->commit();
        return ['success' => true];
        
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Admin forced password change failed: " . $e->getMessage());
        return ['success' => false, 'message' => 'Password change failed'];
    }
}

/**
 * Two-Factor Authentication Setup
 */
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\QRServerProvider;

function setupAdminTwoFactorAuth($adminId) {
    $db = (new Database())->connect();

    // Generate secret - use QRServerProvider for QR code generation
    $qrProvider = new QRServerProvider();
    $tfa = new TwoFactorAuth($qrProvider, 'StepCashier'); 
    $secret = $tfa->createSecret();

    // Store secret in database
    $stmt = $db->prepare("UPDATE admins SET two_factor_secret = ?, two_factor_enabled = 0 WHERE id = ?");
    $stmt->execute([$secret, $adminId]);

    return $secret;
}

/**
 * Verify Two-Factor Authentication Code
 */
function verifyAdminTwoFactorCode($adminId, $code) {
    $db = (new Database())->connect();

    // Get admin's secret
    $stmt = $db->prepare("SELECT two_factor_secret FROM admins WHERE id = ?");
    $stmt->execute([$adminId]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin || !$admin['two_factor_secret']) {
        return false;
    }

    // Verify the code using RobThree\TwoFactorAuth
    $qrProvider = new QRServerProvider();
    $tfa = new TwoFactorAuth($qrProvider, 'StepCashier');
    $isValid = $tfa->verifyCode($admin['two_factor_secret'], $code);

    if ($isValid) {
        // Mark 2FA as complete in session
        $_SESSION['admin_2fa_verified'] = true;

        // Enable 2FA if this was setup phase
        if (isset($_SESSION['admin_2fa_setup_mode'])) {
            $stmt = $db->prepare("UPDATE admins SET two_factor_enabled = 1 WHERE id = ?");
            $stmt->execute([$adminId]);
            unset($_SESSION['admin_2fa_setup_mode']);
        }

        return true;
    }

    return false;
}

/**
 * Check if admin is logged in (including OTP verification)
 */
function isAdminLoggedIn() {
    // If in OTP verification stage
    if (isset($_SESSION['admin_temp_id'])) {
        return false;
    }
    
    return isset($_SESSION['is_admin_logged_in']) && $_SESSION['is_admin_logged_in'] === true;
}



/**
 * Check if current admin has specific permission
 */
function hasAdminPermission($permission) {
    if (!isAdminLoggedIn()) {
        return false;
    }
    
    // Super admin has all permissions
    if ($_SESSION['admin_role'] === 'super_admin') {
        return true;
    }
    
    // Check if admin has "all" permissions
    if (in_array('all', $_SESSION['admin_permissions'])) {
        return true;
    }
    
    // Check specific permission
    return in_array($permission, $_SESSION['admin_permissions']);
}


/**
 * Get current admin info (handles OTP verification state)
 */
function getCurrentAdmin() {
    // If in OTP verification stage
    if (isset($_SESSION['admin_temp_id'])) {
        return [
            'id' => $_SESSION['admin_temp_id'],
            'name' => $_SESSION['admin_temp_name'],
            'email' => $_SESSION['admin_temp_email'],
            'role' => $_SESSION['admin_temp_role'],
            'permissions' => $_SESSION['admin_temp_permissions'],
            'otp_required' => true
        ];
    }
    
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['admin_id'],
        'name' => $_SESSION['admin_name'],
        'email' => $_SESSION['admin_email'],
        'role' => $_SESSION['admin_role'],
        'permissions' => $_SESSION['admin_permissions'],
        'otp_required' => false
    ];
}

/**
 * Resend OTP to admin
 */
function resendAdminOTP() {
    if (!isset($_SESSION['admin_temp_id'])) {
        return ['success' => false, 'message' => 'No OTP verification in progress'];
    }
    
    $adminId = $_SESSION['admin_temp_id'];
    return generateAndSendAdminOTP($adminId);
}

/**
 * Log admin activity
 */
function logAdminActivity($admin_id, $action, $table_name = null, $record_id = null, $old_values = null, $new_values = null, $ip_address = null, $user_agent = null) {
    try {
        $db = (new Database())->connect();
        
        $stmt = $db->prepare("
            INSERT INTO admin_logs (admin_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $admin_id,
            $action,
            $table_name,
            $record_id,
            $old_values ? json_encode($old_values) : null,
            $new_values ? json_encode($new_values) : null,
            $ip_address ?: $_SERVER['REMOTE_ADDR'],
            $user_agent ?: $_SERVER['HTTP_USER_AGENT']
        ]);
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to log admin activity: " . $e->getMessage());
        return false;
    }
}

/**
 * Logout admin
 */
function logoutAdmin() {
    if (isAdminLoggedIn()) {
        // Log logout activity
        logAdminActivity($_SESSION['admin_id'], 'logout');
        
        // Clear admin session variables
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_role']);
        unset($_SESSION['admin_email']);
        unset($_SESSION['admin_name']);
        unset($_SESSION['admin_permissions']);
        unset($_SESSION['is_admin_logged_in']);
    }
    
    return true;
}

/**
 * Require admin login - redirect if not logged in
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . BASE_URL . '/management/login');
        exit;
    }
}

/**
 * Require specific admin permission
 */
function requireAdminPermission($permission) {
    requireAdminLogin();
    
    if (!hasAdminPermission($permission)) {
        header('HTTP/1.1 403 Forbidden');
        die('Access denied. You do not have permission to access this resource.');
    }
}

/**
 * Get admin role hierarchy level (higher number = more privileges)
 */
function getAdminRoleLevel($role) {
    switch ($role) {
        case 'super_admin':
            return 3;
        case 'admin':
            return 2;
        case 'moderator':
            return 1;
        default:
            return 0;
    }
}

/**
 * Check if current admin can manage another admin
 */
function canManageAdmin($target_admin_role) {
    if (!isAdminLoggedIn()) {
        return false;
    }
    
    $current_level = getAdminRoleLevel($_SESSION['admin_role']);
    $target_level = getAdminRoleLevel($target_admin_role);
    
    // Can only manage admins with lower or equal level
    return $current_level >= $target_level;
}

/**
 * Get all available admin permissions
 */
function getAdminPermissions() {
    return [
        'users_view' => 'View Users',
        'users_edit' => 'Edit Users',
        'users_delete' => 'Delete Users',
        'investments_view' => 'View Investments',
        'investments_edit' => 'Edit Investments',
        'investments_delete' => 'Delete Investments',
        'transactions_view' => 'View Transactions',
        'transactions_edit' => 'Edit Transactions',
        'withdrawals_view' => 'View Withdrawals',
        'withdrawals_approve' => 'Approve Withdrawals',
        'deposits_view' => 'View Deposits',
        'deposits_edit' => 'Edit Deposits',
        'settings_view' => 'View Settings',
        'settings_edit' => 'Edit Settings',
        'admins_view' => 'View Admins',
        'admins_create' => 'Create Admins',
        'admins_edit' => 'Edit Admins',
        'admins_delete' => 'Delete Admins',
        'logs_view' => 'View Logs',
        'reports_view' => 'View Reports',
        'all' => 'All Permissions'
    ];
}

/**
 * Check admin session timeout (optional security feature)
 */
function checkAdminSessionTimeout($timeout_minutes = 60) {
    if (!isAdminLoggedIn()) {
        return false;
    }
    
    if (!isset($_SESSION['admin_last_activity'])) {
        $_SESSION['admin_last_activity'] = time();
        return true;
    }
    
    if (time() - $_SESSION['admin_last_activity'] > ($timeout_minutes * 60)) {
        logoutAdmin();
        return false;
    }
    
    $_SESSION['admin_last_activity'] = time();
    return true;
}


/**
 * Generates a password reset token for an admin
 */
function generateAdminPasswordResetToken($email) {
    $db = (new Database())->connect();
    
    // Set timezone for this connection
    $db->exec("SET time_zone = '+03:00'"); 
    
    // Check if admin exists and is active
    $stmt = $db->prepare("SELECT id, is_active FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        return false; // Admin doesn't exist
    }
    
    if (!$admin['is_active']) {
        return false; // Admin account is not active
    }
    
    // Generate secure token
    $token = bin2hex(random_bytes(32));
    
    // Delete any existing tokens for this admin
    $stmt = $db->prepare("DELETE FROM admin_password_resets WHERE admin_id = ?");
    $stmt->execute([$admin['id']]);
    
    // Insert new token with 10 minute expiration (shorter than user reset for security)
    $stmt = $db->prepare("
        INSERT INTO admin_password_resets (admin_id, token, expires_at, created_at) 
        VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE), NOW())
    ");
    $stmt->execute([$admin['id'], $token]);
    
    return $token;
}

/**
 * Validates an admin password reset token
 */
function validateAdminPasswordResetToken($token) {
    $db = (new Database())->connect();
    
    // Check if token exists and is not expired
    $stmt = $db->prepare("
        SELECT apr.admin_id, a.email, a.name, a.is_active
        FROM admin_password_resets apr 
        JOIN admins a ON apr.admin_id = a.id 
        WHERE apr.token = ? AND apr.expires_at > NOW() AND a.is_active = 1
    ");
    $stmt->execute([$token]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? $result : false;
}

/**
 * Resets an admin's password
 */
function resetAdminPassword($adminId, $newPassword) {
    $db = (new Database())->connect();
    
    try {
        $db->beginTransaction();
        
        // Get admin info for logging
        $stmt = $db->prepare("SELECT email, name FROM admins WHERE id = ?");
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$admin) {
            throw new Exception('Admin not found');
        }
        
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE admins SET password = ?, login_attempts = 0, locked_until = NULL WHERE id = ?");
        $success = $stmt->execute([$hashedPassword, $adminId]);
        
        if (!$success) {
            throw new Exception('Failed to update password');
        }
        
        // Delete all reset tokens for this admin
        $stmt = $db->prepare("DELETE FROM admin_password_resets WHERE admin_id = ?");
        $stmt->execute([$adminId]);
        
        // Log the password reset
        logAdminActivity($adminId, 'password_reset_completed', 'admins', $adminId, null, null, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
        
        $db->commit();
        return true;
        
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Admin password reset failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Get admin password reset token info (for display purposes)
 */
function getAdminPasswordResetInfo($token) {
    $db = (new Database())->connect();
    
    $stmt = $db->prepare("
        SELECT apr.*, a.email, a.name 
        FROM admin_password_resets apr 
        JOIN admins a ON apr.admin_id = a.id 
        WHERE apr.token = ?
    ");
    $stmt->execute([$token]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Clean up expired admin password reset tokens
 */
function cleanupExpiredAdminResetTokens() {
    $db = (new Database())->connect();
    
    $stmt = $db->prepare("DELETE FROM admin_password_resets WHERE expires_at < NOW()");
    $stmt->execute();
    
    return $stmt->rowCount();
}

/**
 * Check if admin has pending password reset tokens
 */
function hasAdminPendingPasswordReset($adminId) {
    $db = (new Database())->connect();
    
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM admin_password_resets WHERE admin_id = ? AND expires_at > NOW()");
    $stmt->execute([$adminId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['count'] > 0;
}

/**
 * Validate admin password strength
 */
function validateAdminPasswordStrength($password) {
    $errors = [];
    
    // Minimum length
    if (strlen($password) < 10) {
        $errors[] = 'Password must be at least 10 characters long';
    }
    
    // Must contain uppercase
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    
    // Must contain lowercase
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    
    // Must contain number
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    
    // Must contain special character
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character';
    }
    
    
    // Check for common passwords
    $commonPasswords = include 'common_passwords.php';
    if (in_array(strtolower($password), $commonPasswords)) {
        return ['success' => false, 'message' => 'Password is too common. Please choose a more secure password'];
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}
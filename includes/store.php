<?php
require_once 'db.php';
require_once 'functions.php';


/**
 * Registers a new user with name, email, phone, password and optional referral code
 * Returns an array with success status and user ID if successful
*/
function registerUser($name, $email, $phone, $password, $referralCode = null) {
    // First validate the password strength
    $validation = validateUserPasswordStrength($password);
    
    if (!$validation['valid']) {
        return [
            'success' => false, 
            'message' => 'Password does not meet security requirements',
            'errors' => $validation['errors']
        ];
    }
    
    $db = (new Database())->connect();
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
    $stmt->execute([$email, $phone]);
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Email or phone already registered'];
    }
    
    // Generate referral code for new user
    $userReferralCode = generateReferralCode();
    
    // Hash password securely using bcrypt
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert user
    $stmt = $db->prepare("INSERT INTO users (name, email, phone, password, referral_code, is_active) VALUES (?, ?, ?, ?, ?, 0)");
    $stmt->execute([$name, $email, $phone, $hashedPassword, $userReferralCode]);
    $userId = $db->lastInsertId();
    
    // Process referral if exists
    if ($referralCode) {
        $stmt = $db->prepare("SELECT id FROM users WHERE referral_code = ?");
        $stmt->execute([$referralCode]);
        if ($referrer = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stmt = $db->prepare("INSERT INTO referrals (referrer_id, referred_id) VALUES (?, ?)");
            $stmt->execute([$referrer['id'], $userId]);
        }
    }
    
    return ['success' => true, 'user_id' => $userId];
}


/**
 * Logs in a user with email and password
 * Returns an array with success status and user ID if successful
 */

function loginUser($email, $password) {
    $db = (new Database())->connect();

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }

    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }

    // Check if account is active
    if (!$user['is_active']) {
        return ['success' => false, 'message' => 'Your account is not active. Please contact support to activate your account.'];
    }

    // Login successful
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['is_admin'] ? 'admin' : 'user';
    $_SESSION['user_email'] = $user['email']; 
    
    return ['success' => true, 'user_id' => $user['id']];
}


/**
 * Checks if the CSRF token in the session matches the provided token
 * Returns true if valid, false otherwise
 */
function generateCSRFToken() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    return isset($_SESSION['csrf_token']) && 
           isset($token) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Rate limits actions to prevent abuse
 * Returns true if action is allowed, false if rate limit exceeded
*/

function rateLimit($action, $limit = 5, $timeout = 60) {
    $key = "rate_limit_{$action}_" . $_SERVER['REMOTE_ADDR'];
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'attempts' => 0,
            'last_attempt' => time()
        ];
    }
    
    $now = time();
    $data = $_SESSION[$key];
    
    if ($data['last_attempt'] + $timeout < $now) {
        // Reset if timeout has passed
        $_SESSION[$key] = [
            'attempts' => 1,
            'last_attempt' => $now
        ];
        return true;
    }
    
    if ($data['attempts'] >= $limit) {
        return false;
    }
    
    $_SESSION[$key]['attempts']++;
    $_SESSION[$key]['last_attempt'] = $now;
    return true;
}




function logoutUser() {
    session_unset();
    session_destroy();
}


/**
 * Generates a password reset token for the user
*/
function generatePasswordResetToken($email) {
    $db = (new Database())->connect();
    
    // Set timezone for this connection
    $db->exec("SET time_zone = '+03:00'"); 
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        return false;
    }
    
    // Generate token
    $token = bin2hex(random_bytes(32));
    
    // Delete any existing tokens for this user
    $stmt = $db->prepare("DELETE FROM password_resets WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    
    // Insert new token with 15 minute expiration
    $stmt = $db->prepare("
        INSERT INTO password_resets (user_id, token, expires_at, created_at) 
        VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE), NOW())
    ");
    $stmt->execute([$user['id'], $token]);
    
    return $token;
}

/**
 * Validates a password reset token
 */
function validatePasswordResetToken($token) {
    $db = (new Database())->connect();
    
    // Check if token exists and is not expired
    $stmt = $db->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? $result['user_id'] : false;
}


/**
 * Validate user password strength 
 */
function validateUserPasswordStrength($password) {
    $errors = [];
    
    // Minimum length (8 characters for users vs 10 for admins)
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
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
    
    // Check for common passwords
    $commonPasswords = [
        'password', 'admin123', '123456789', 'qwerty123', 'admin@123',
        'password123', 'administrator', 'welcome123', 'stepcashier', 
        '123456', '12345678', '12345', '1234567', 'qwerty', 'abc123',
        '111111', '123123', 'letmein', 'monkey', 'dragon', 'baseball',
        'iloveyou', 'trustno1', 'sunshine', 'master', 'hello', 'freedom',
        'whatever', 'qazwsx', '654321', 'superman', '1qaz2wsx', 'password1',
        'welcome', 'login', 'admin', 'passw0rd', 'starwars', 'football',
        'michael', 'shadow', 'hannah', 'jessica', 'ashley', 'bailey',
        'charlie', 'daniel', 'matthew', 'andrew', 'michelle', 'tigger',
        'princess', 'joshua', 'jessica1', 'jordan1', 'jennifer1',
        'hunter1', 'fuckyou1', '2000', 'test123', 'batman123', 'trustno12',
        'thomas1', 'robert1', 'access123', 'love123', 'buster1', 'soccer1',
        'hockey1', 'killer1', 'george1', 'sexy123', 'andrew1', 'charlie1',
        'jordan', 'jennifer', 'hunter', 'fuckyou', '2000', 'test', 'batman',
        'trustno1', 'thomas', 'robert', 'access', 'love', 'buster', 'soccer',
        'hockey', 'killer', 'george', 'sexy', 'andrew', 'charlie', 'superman',
        'asshole', 'fuckyou', 'dallas', 'jessica', 'panties', 'pepper',
        'ginger', 'hammer', 'summer', 'corvette', 'taylor', 'fucker',
        'austin', '1234', 'a1b2c3', 'abc123', 'definition', '123qwe',
        'zaq12wsx', 'qwertyuiop', 'zxcvbnm', 'asdfgh', 'poiuyt',
        'lkjhgf', 'mnbvcx', '987654321', '1111111', '0000000',
        'password!', 'Password', 'PASSWORD', 'Admin', 'ADMIN', 'root',
        'user', 'guest', 'demo', 'test123', 'temp', 'default', 'public',
        'private', 'secret', 'secure', 'super', 'system', 'windows',
        'linux', 'ubuntu', 'oracle', 'mysql', 'postgres', 'database',
        'server', 'computer', 'laptop', 'desktop', 'iphone', 'android',
        'google', 'facebook', 'twitter', 'instagram', 'youtube', 'amazon',
        'apple', 'microsoft', 'adobe', 'netflix', 'spotify', 'paypal',
        'ebay', 'walmart', 'target', 'costco', 'bestbuy', 'homedepot',
        'lowes', 'mcdonalds', 'starbucks', 'subway', 'pizza', 'burger',
        'chicken', 'coffee', 'beer', 'wine', 'vodka', 'whiskey',
        'money', 'dollar', 'bitcoin', 'crypto', 'gold', 'silver',
        'diamond', 'ruby', 'emerald', 'sapphire', 'pearl', 'crystal',
        'rainbow', 'unicorn', 'dragon', 'phoenix', 'eagle', 'lion',
        'tiger', 'bear', 'wolf', 'shark', 'dolphin', 'whale',
        'elephant', 'giraffe', 'zebra', 'hippo', 'rhino', 'kangaroo',
        'penguin', 'flamingo', 'peacock', 'butterfly', 'spider', 'snake',
        'turtle', 'frog', 'fish', 'bird', 'cat', 'dog', 'horse',
        'cow', 'pig', 'sheep', 'goat', 'rabbit', 'mouse', 'rat',
        'flower', 'rose', 'lily', 'daisy', 'tulip', 'sunflower',
        'cherry', 'apple', 'banana', 'orange', 'grape', 'strawberry',
        'blueberry', 'raspberry', 'blackberry', 'pineapple', 'mango',
        'peach', 'pear', 'plum', 'kiwi', 'coconut', 'lemon', 'lime',
        'red', 'blue', 'green', 'yellow', 'orange', 'purple', 'pink',
        'black', 'white', 'gray', 'brown', 'silver', 'gold',
        'monday', 'tuesday', 'wednesday', 'thursday', 'friday',
        'saturday', 'sunday', 'january', 'february', 'march', 'april',
        'may', 'june', 'july', 'august', 'september', 'october',
        'november', 'december', 'spring', 'summer', 'autumn', 'winter',
        'morning', 'afternoon', 'evening', 'night', 'midnight', 'noon',
        'home', 'house', 'apartment', 'office', 'school', 'college',
        'university', 'hospital', 'church', 'store', 'mall', 'park',
        'beach', 'mountain', 'forest', 'desert', 'ocean', 'lake',
        'river', 'bridge', 'road', 'street', 'avenue', 'boulevard',
        'america', 'canada', 'mexico', 'england', 'france', 'germany',
        'italy', 'spain', 'russia', 'china', 'japan', 'india',
        'australia', 'brazil', 'argentina', 'egypt', 'africa',
        'newyork', 'losangeles', 'chicago', 'houston', 'phoenix',
        'philadelphia', 'sanantonio', 'sandiego', 'dallas', 'sanjose',
        'austin', 'jacksonville', 'fortworth', 'columbus', 'charlotte',
        'seattle', 'denver', 'boston', 'detroit', 'nashville',
        'portland', 'oklahoma', 'lasvegas', 'louisville', 'milwaukee',
        'albuquerque', 'tucson', 'fresno', 'sacramento', 'mesa',
        'kansas', 'atlanta', 'omaha', 'colorado', 'raleigh',
        'virginia', 'miami', 'oakland', 'minneapolis', 'tulsa',
        'cleveland', 'wichita', 'orleans', 'tampa', 'honolulu',
        '000000', '1234567890', 'qwertyui', 'asdfghjk', 'zxcvbnm123',
        'q1w2e3r4', 'a1s2d3f4', 'z1x2c3v4', '1q2w3e4r', '1a2s3d4f',
        '1z2x3c4v', 'qwer1234', 'asdf1234', 'zxcv1234', '4321rewq',
        '4321fdsa', '4321vcxz', 'qazwsxedc', 'rfvtgbyhn', 'plokijuh'
    ];
    
    if (in_array(strtolower($password), $commonPasswords)) {
        $errors[] = 'Password is too common. Please choose a more secure password';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * resetUserPassword with password validation
 */
function resetUserPassword($userId, $newPassword) {
    $validation = validateUserPasswordStrength($newPassword);
    
    if (!$validation['valid']) {
        return [
            'success' => false, 
            'message' => 'Password does not meet security requirements',
            'errors' => $validation['errors']
        ];
    }
    
    $db = (new Database())->connect();
    
    try {
        $db->beginTransaction();
        
        // Get user info for logging 
        $stmt = $db->prepare("SELECT email, name FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new Exception('User not found');
        }
        
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $success = $stmt->execute([$hashedPassword, $userId]);
        
        if (!$success) {
            throw new Exception('Failed to update password');
        }
        
        // Delete all reset tokens for this user
        $stmt = $db->prepare("DELETE FROM password_resets WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        $db->commit();
        
        return [
            'success' => true,
            'message' => 'Password reset successfully'
        ];
        
    } catch (Exception $e) {
        $db->rollBack();
        error_log("User password reset failed: " . $e->getMessage());
        
        return [
            'success' => false,
            'message' => 'Failed to reset password. Please try again.',
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Clears expired password reset tokens
 */
function clearExpiredPasswordResetTokens() {
    $db = (new Database())->connect();
    $stmt = $db->prepare("DELETE FROM password_resets WHERE expires_at <= NOW()");
    return $stmt->execute();
}

/**
 * ===========================
 *      ADMIN SECTION
 * ===========================
 */

/**
 * Logs in an admin with email and password
 * Returns an array with success status and admin ID if successful
 */
function loginAdmin($email, $password) {
    $db = (new Database())->connect();

    $stmt = $db->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }

    // Check if account is locked
    if ($admin['locked_until'] && strtotime($admin['locked_until']) > time()) {
        $lockTime = date('H:i', strtotime($admin['locked_until']));
        return ['success' => false, 'message' => "Account is locked until {$lockTime}. Please try again later."];
    }

    if (!password_verify($password, $admin['password'])) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }

    // Check if account is active
    if (!$admin['is_active']) {
        return ['success' => false, 'message' => 'Your admin account is not active. Please contact the super admin.'];
    }

    // Login successful - Set admin session
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_name'] = $admin['name'];
    $_SESSION['admin_permissions'] = json_decode($admin['permissions'], true);
    $_SESSION['is_admin_logged_in'] = true;
    
    return ['success' => true, 'admin_id' => $admin['id'], 'role' => $admin['role']];
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
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
 * Get current admin info
 */
function getCurrentAdmin() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['admin_id'],
        'name' => $_SESSION['admin_name'],
        'email' => $_SESSION['admin_email'],
        'role' => $_SESSION['admin_role'],
        'permissions' => $_SESSION['admin_permissions']
    ];
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
    $commonPasswords = [
        'password', 'admin123', '123456789', 'qwerty123', 'admin@123',
        'password123', 'administrator', 'welcome123', 'stepcashier', 
        '123456', '12345678', '12345', '1234567', 'qwerty', 'abc123',
        '111111', '123123', 'letmein', 'monkey', 'dragon', 'baseball',
        'iloveyou', 'trustno1', 'sunshine', 'master', 'hello', 'freedom',
        'whatever', 'qazwsx', '654321', 'superman', '1qaz2wsx', 'password1',
        'welcome', 'login', 'admin', 'passw0rd', 'starwars', 'football',
        'michael', 'shadow', 'hannah', 'jessica', 'ashley', 'bailey',
        'charlie', 'daniel', 'matthew', 'andrew', 'michelle', 'tigger',
        'princess', 'joshua', 'jessica1', 'jordan1', 'jennifer1',
        'hunter1', 'fuckyou1', '2000', 'test123', 'batman123', 'trustno12',
        'thomas1', 'robert1', 'access123', 'love123', 'buster1', 'soccer1',
        'hockey1', 'killer1', 'george1', 'sexy123', 'andrew1', 'charlie1',
        'jordan', 'jennifer', 'hunter', 'fuckyou', '2000', 'test', 'batman',
        'trustno1', 'thomas', 'robert', 'access', 'love', 'buster', 'soccer',
        'hockey', 'killer', 'george', 'sexy', 'andrew', 'charlie', 'superman',
        'asshole', 'fuckyou', 'dallas', 'jessica', 'panties', 'pepper',
        'ginger', 'hammer', 'summer', 'corvette', 'taylor', 'fucker',
        'austin', '1234', 'a1b2c3', 'abc123', 'definition', '123qwe',
        'zaq12wsx', 'qwertyuiop', 'zxcvbnm', 'asdfgh', 'poiuyt',
        'lkjhgf', 'mnbvcx', '987654321', '1111111', '0000000',
        'password!', 'Password', 'PASSWORD', 'Admin', 'ADMIN', 'root',
        'user', 'guest', 'demo', 'test123', 'temp', 'default', 'public',
        'private', 'secret', 'secure', 'super', 'system', 'windows',
        'linux', 'ubuntu', 'oracle', 'mysql', 'postgres', 'database',
        'server', 'computer', 'laptop', 'desktop', 'iphone', 'android',
        'google', 'facebook', 'twitter', 'instagram', 'youtube', 'amazon',
        'apple', 'microsoft', 'adobe', 'netflix', 'spotify', 'paypal',
        'ebay', 'walmart', 'target', 'costco', 'bestbuy', 'homedepot',
        'lowes', 'mcdonalds', 'starbucks', 'subway', 'pizza', 'burger',
        'chicken', 'coffee', 'beer', 'wine', 'vodka', 'whiskey',
        'money', 'dollar', 'bitcoin', 'crypto', 'gold', 'silver',
        'diamond', 'ruby', 'emerald', 'sapphire', 'pearl', 'crystal',
        'rainbow', 'unicorn', 'dragon', 'phoenix', 'eagle', 'lion',
        'tiger', 'bear', 'wolf', 'shark', 'dolphin', 'whale',
        'elephant', 'giraffe', 'zebra', 'hippo', 'rhino', 'kangaroo',
        'penguin', 'flamingo', 'peacock', 'butterfly', 'spider', 'snake',
        'turtle', 'frog', 'fish', 'bird', 'cat', 'dog', 'horse',
        'cow', 'pig', 'sheep', 'goat', 'rabbit', 'mouse', 'rat',
        'flower', 'rose', 'lily', 'daisy', 'tulip', 'sunflower',
        'cherry', 'apple', 'banana', 'orange', 'grape', 'strawberry',
        'blueberry', 'raspberry', 'blackberry', 'pineapple', 'mango',
        'peach', 'pear', 'plum', 'kiwi', 'coconut', 'lemon', 'lime',
        'red', 'blue', 'green', 'yellow', 'orange', 'purple', 'pink',
        'black', 'white', 'gray', 'brown', 'silver', 'gold',
        'monday', 'tuesday', 'wednesday', 'thursday', 'friday',
        'saturday', 'sunday', 'january', 'february', 'march', 'april',
        'may', 'june', 'july', 'august', 'september', 'october',
        'november', 'december', 'spring', 'summer', 'autumn', 'winter',
        'morning', 'afternoon', 'evening', 'night', 'midnight', 'noon',
        'home', 'house', 'apartment', 'office', 'school', 'college',
        'university', 'hospital', 'church', 'store', 'mall', 'park',
        'beach', 'mountain', 'forest', 'desert', 'ocean', 'lake',
        'river', 'bridge', 'road', 'street', 'avenue', 'boulevard',
        'america', 'canada', 'mexico', 'england', 'france', 'germany',
        'italy', 'spain', 'russia', 'china', 'japan', 'india',
        'australia', 'brazil', 'argentina', 'egypt', 'africa',
        'newyork', 'losangeles', 'chicago', 'houston', 'phoenix',
        'philadelphia', 'sanantonio', 'sandiego', 'dallas', 'sanjose',
        'austin', 'jacksonville', 'fortworth', 'columbus', 'charlotte',
        'seattle', 'denver', 'boston', 'detroit', 'nashville',
        'portland', 'oklahoma', 'lasvegas', 'louisville', 'milwaukee',
        'albuquerque', 'tucson', 'fresno', 'sacramento', 'mesa',
        'kansas', 'atlanta', 'omaha', 'colorado', 'raleigh',
        'virginia', 'miami', 'oakland', 'minneapolis', 'tulsa',
        'cleveland', 'wichita', 'orleans', 'tampa', 'honolulu',
        '000000', '1234567890', 'qwertyui', 'asdfghjk', 'zxcvbnm123',
        'q1w2e3r4', 'a1s2d3f4', 'z1x2c3v4', '1q2w3e4r', '1a2s3d4f',
        '1z2x3c4v', 'qwer1234', 'asdf1234', 'zxcv1234', '4321rewq',
        '4321fdsa', '4321vcxz', 'qazwsxedc', 'rfvtgbyhn', 'plokijuh'
    ];
    
    if (in_array(strtolower($password), $commonPasswords)) {
        $errors[] = 'Password is too common. Please choose a more secure password';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

?>
<?php
require_once 'db.php';
require_once 'functions.php';

function registerUser($name, $email, $phone, $password, $referralCode = null) {
    $db = (new Database())->connect();

    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Email is already registered'];
    }

    // Check if phone exists
    $stmt = $db->prepare("SELECT id FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Phone number is already registered'];
    }

    $userReferralCode = generateReferralCode();

    
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    
    $commonPasswords = require_once 'commonpassords.php';
    if (in_array(strtolower($password), $commonPasswords)) {
        return ['success' => false, 'message' => 'Password was found in a data breach. Please choose a more secure password'];
    }
    
    $stmt = $db->prepare("INSERT INTO users (name, email, phone, password, referral_code, is_active, email_verified, phone_verified) VALUES (?, ?, ?, ?, ?, 0, 1, 1)");
    $stmt->execute([$name, $email, $phone, $hashedPassword, $userReferralCode]);
    $userId = $db->lastInsertId();

    
    $referrerId = null;
    if ($referralCode) {
        $stmt = $db->prepare("SELECT id FROM users WHERE referral_code = ?");
        $stmt->execute([$referralCode]);

        if ($referrer = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $referrerId = $referrer['id'];
            $stmt = $db->prepare("INSERT INTO referrals (referrer_id, referred_id) VALUES (?, ?)");
            $stmt->execute([$referrerId, $userId]);
        }
    }

    return [
        'success' => true,
        'user_id' => $userId,
        'referrer_id' => $referrerId 
    ];
}

function loginUser($emailOrPhone, $password) {
    $db = (new Database())->connect();

    // Check if input is email or phone number
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
    $stmt->execute([$emailOrPhone, $emailOrPhone]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return ['success' => false, 'message' => 'Invalid email/phone or password'];
    }

    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email/phone or password'];
    }

    // If account is not active, generate payment token
    if (!$user['is_active']) {
        // Generate a secure one-time token for payment
        $paymentToken = bin2hex(random_bytes(32));
        
        // Store token in database with expiration (15 minutes)
        $stmt = $db->prepare("INSERT INTO payment_tokens 
                            (user_id, token, expires_at) 
                            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))");
        $stmt->execute([$user['id'], $paymentToken]);
        
        // Return response indicating payment is needed
        return [
            'success' => false, 
            'needs_payment' => true,
            'payment_url' => BASE_URL . '/complete_payment.php?token=' . $paymentToken
        ];
    }

    // Login successful - set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['is_admin'] ? 'admin' : 'user';
    $_SESSION['user_email'] = $user['email']; 
    
    return ['success' => true, 'user_id' => $user['id']];
}



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


function generatePasswordResetToken($email) {
    $db = (new Database())->connect();
    
    $db->exec("SET time_zone = '+03:00'"); 
    
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        return false;
    }
    
    $token = bin2hex(random_bytes(32));
    
    $stmt = $db->prepare("DELETE FROM password_resets WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $stmt = $db->prepare("
        INSERT INTO password_resets (user_id, token, expires_at, created_at) 
        VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE), NOW())
    ");
    $stmt->execute([$user['id'], $token]);
    
    return $token;
}

function validatePasswordResetToken($token) {
    $db = (new Database())->connect();
    // Check if token exists and is not expired
    $stmt = $db->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? $result['user_id'] : false;
}


function resetUserPassword($userId, $newPassword) {
    $db = (new Database())->connect();
    
    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $success = $stmt->execute([$hashedPassword, $userId]);
    
    if ($success) {
        // Delete all reset tokens for this user
        $stmt = $db->prepare("DELETE FROM password_resets WHERE user_id = ?");
        $stmt->execute([$userId]);
    }
    
    return $success;
}

/**
 * Clears expired password reset tokens
 */
function clearExpiredPasswordResetTokens() {
    $db = (new Database())->connect();
    $stmt = $db->prepare("DELETE FROM password_resets WHERE expires_at <= NOW()");
    return $stmt->execute();
}


?>
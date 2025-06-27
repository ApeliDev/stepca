<?php
require_once 'db.php';

function generateReferralCode($length = 6) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserById($id) {
    $db = (new Database())->connect();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getReferrals($userId) {
    $db = (new Database())->connect();
    $stmt = $db->prepare("SELECT * FROM referrals WHERE referrer_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTotalEarnings($userId) {
    $db = (new Database())->connect();
    $stmt = $db->prepare("SELECT SUM(amount) as total FROM referral_earnings WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ?? 0;
}

function getSystemSetting($key) {
    $db = (new Database())->connect();
    $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['setting_value'] : null;
}

function isRegistrationAllowed() {
    return getSystemSetting('registration_open') === '1';
}

function redirectIfRegistrationClosed() {
    if (getSystemSetting('registration_open') !== '1') {
        header('Location: index.php');
        exit;
    }
}

function getReferralBonus() {
    return (float)getSystemSetting('referral_bonus');
}
function getRegistrationFee() {
    return (float)getSystemSetting('registration_fee');
}
function getMpesaShortcode() {
    return getSystemSetting('mpesa_shortcode');
}


?>
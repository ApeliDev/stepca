<?php
function get_user_with_wallet($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT u.*, 
               COALESCE(wb.available_balance, 0) as available_balance,
               COALESCE(wb.locked_balance, 0) as locked_balance
        FROM users u
        LEFT JOIN wallet_balances wb ON u.id = wb.user_id AND wb.currency = 'KES'
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function calculate_total_balance($user) {
    return ($user['available_balance'] ?? 0) + ($user['referral_bonus_balance'] ?? 0);
}

function calculate_max_withdrawal($user) {
    $total_balance = calculate_total_balance($user);
    return min($total_balance, $user['withdrawal_limit'] ?? 50000.00);
}

function get_wallet_transactions($conn, $user_id, $limit = 10, $offset = 0) {
    $stmt = $conn->prepare("
        SELECT *, 
               CASE 
                   WHEN transaction_type = 'withdrawal' THEN 'withdrawal'
                   WHEN transaction_type = 'transfer' THEN 'transfer'
                   WHEN transaction_type = 'deposit' THEN 'deposit'
                   ELSE transaction_type
               END as display_type
        FROM wallet_transactions
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$user_id, $limit, $offset]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function count_wallet_transactions($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM wallet_transactions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function get_referral_stats($conn, $user_id) {
    $stats = [
        'total_referrals' => 0,
        'active_referrals' => 0,
        'total_earned' => 0
    ];
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM referrals WHERE referrer_id = ?");
    $stmt->execute([$user_id]);
    $stats['total_referrals'] = $stmt->fetchColumn();
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM referrals r 
        JOIN users u ON r.referred_id = u.id 
        WHERE r.referrer_id = ? AND u.is_active = 1
    ");
    $stmt->execute([$user_id]);
    $stats['active_referrals'] = $stmt->fetchColumn();
    
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(amount), 0) 
        FROM wallet_transactions 
        WHERE user_id = ? AND transaction_type = 'referral'
    ");
    $stmt->execute([$user_id]);
    $stats['total_earned'] = $stmt->fetchColumn();
    
    return $stats;
}

function get_wallet_stats($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN transaction_type = 'deposit' THEN amount ELSE 0 END), 0) as total_deposits,
            COUNT(CASE WHEN transaction_type = 'deposit' THEN 1 END) as deposit_count,
            COALESCE(SUM(CASE WHEN transaction_type = 'withdrawal' THEN amount ELSE 0 END), 0) as total_withdrawals,
            COALESCE(SUM(CASE WHEN transaction_type = 'transfer' AND amount < 0 THEN ABS(amount) ELSE 0 END), 0) as total_transfers_sent
        FROM wallet_transactions
        WHERE user_id = ? AND status = 'completed'
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_txn_icon($type) {
    $icons = [
        'deposit' => 'fa-arrow-down',
        'withdrawal' => 'fa-arrow-up',
        'transfer' => 'fa-exchange-alt',
        'referral' => 'fa-users',
        'investment' => 'fa-chart-line',
        'fee' => 'fa-file-invoice-dollar',
        'default' => 'fa-wallet'
    ];
    return 'fas ' . ($icons[$type] ?? $icons['default']);
}

function get_txn_icon_class($type) {
    $classes = [
        'deposit' => 'bg-green-500/10 text-green-500',
        'withdrawal' => 'bg-red-500/10 text-red-500',
        'transfer' => 'bg-blue-500/10 text-blue-500',
        'referral' => 'bg-purple-500/10 text-purple-500',
        'investment' => 'bg-yellow-500/10 text-yellow-500',
        'fee' => 'bg-gray-500/10 text-gray-500',
        'default' => 'bg-primary/10 text-primary'
    ];
    return $classes[$type] ?? $classes['default'];
}

function get_status_class($status) {
    $classes = [
        'completed' => 'bg-green-500/20 text-green-400',
        'failed' => 'bg-red-500/20 text-red-400',
        'pending' => 'bg-yellow-500/20 text-yellow-400',
        'reversed' => 'bg-gray-500/20 text-gray-400'
    ];
    return $classes[$status] ?? 'bg-gray-500/20 text-gray-400';
}

function secure_session_start() {
    $session_name = 'secure_session';
    $secure = true;
    $httponly = true;
    
    ini_set('session.use_only_cookies', 1);
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => $cookieParams["lifetime"],
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => 'Strict'
    ]);
    
    session_name($session_name);
    session_start();
    session_regenerate_id(true);
}
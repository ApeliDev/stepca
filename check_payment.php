<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/mpesa.php';
require_once 'includes/notifications.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

// Validate user ID
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($userId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid user ID']);
    exit;
}

try {
    $db = (new Database())->connect();
    
    // Check if user is already active
    $stmt = $db->prepare("SELECT is_active FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['is_active']) {
        echo json_encode(['status' => 'completed']);
        exit;
    }

    // Check for recent successful payment
    $stmt = $db->prepare("SELECT status, mpesa_code, created_at FROM payments 
                         WHERE user_id = ? 
                         AND created_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                         ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$userId]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($payment) {
        if ($payment['status'] === 'completed') {
            echo json_encode(['status' => 'completed']);
        } else if ($payment['status'] === 'failed') {
            echo json_encode(['status' => 'failed', 'message' => 'Payment failed']);
        } else {
            // Check if payment is expired (older than 15 minutes)
            $createdTime = new DateTime($payment['created_at']);
            $currentTime = new DateTime();
            $interval = $currentTime->diff($createdTime);
            
            if ($interval->i >= 15) {
                // Mark as expired if not already
                if ($payment['status'] !== 'expired') {
                    $stmt = $db->prepare("UPDATE payments SET status = 'expired' WHERE user_id = ? AND status = 'pending'");
                    $stmt->execute([$userId]);
                }
                echo json_encode(['status' => 'failed', 'message' => 'Payment request expired']);
            } else {
                echo json_encode(['status' => 'pending']);
            }
        }
    } else {
        echo json_encode(['status' => 'pending']);
    }
} catch (Exception $e) {
    error_log("Payment check error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'System error']);
}
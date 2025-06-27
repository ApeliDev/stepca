<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/mpesa.php';
require_once 'includes/notifications.php';

// Set content type to json
header('Content-Type: application/json');

// Get the callback data
$callbackData = file_get_contents('php://input');

// Log raw callback data
file_put_contents('logs/mpesa_raw_withdraw_callback_'.date('Y-m-d').'.log', date('Y-m-d H:i:s')." - RAW WITHDRAWAL CALLBACK: ".$callbackData."\n", FILE_APPEND);

// Process the callback
$mpesa = new MpesaPayment();
$result = $mpesa->processWithdrawalCallback($callbackData);

if ($result) {
    // Success response
    echo json_encode([
        'ResultCode' => 0,
        'ResultDesc' => 'Withdrawal callback processed successfully'
    ]);
} else {
    // Error response
    echo json_encode([
        'ResultCode' => 1,
        'ResultDesc' => 'Failed to process withdrawal callback'
    ]);
}
?>
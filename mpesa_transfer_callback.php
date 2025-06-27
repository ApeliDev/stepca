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
file_put_contents('logs/mpesa_raw_transfer_callback_'.date('Y-m-d').'.log', date('Y-m-d H:i:s')." - RAW TRANSFER CALLBACK: ".$callbackData."\n", FILE_APPEND);

// Process the callback
$mpesa = new MpesaPayment();
$result = $mpesa->processTransferCallback($callbackData);

if ($result) {
    // Success response
    echo json_encode([
        'ResultCode' => 0,
        'ResultDesc' => 'Transfer callback processed successfully'
    ]);
} else {
    // Error response
    echo json_encode([
        'ResultCode' => 1,
        'ResultDesc' => 'Failed to process transfer callback'
    ]);
}
?>
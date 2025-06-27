<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/mpesa.php';
require_once 'includes/notifications.php';

header('Content-Type: application/json');

// Create logs directory if it doesn't exist
if (!is_dir('logs')) {
    mkdir('logs', 0755, true);
}

// Get callback data
$callbackData = file_get_contents('php://input');

// Validate callback data
if (empty($callbackData)) {
    logCallbackError('Empty callback data received');
    echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Empty callback data']);
    exit;
}

try {
    // Parse JSON data
    $data = json_decode($callbackData, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }
    
    // Validate callback structure
    if (!isset($data['Body']['stkCallback'])) {
        throw new Exception('Invalid callback structure - missing stkCallback');
    }
    
    $callback = $data['Body']['stkCallback'];
    $merchantRequestId = $callback['MerchantRequestID'] ?? '';
    $checkoutRequestId = $callback['CheckoutRequestID'] ?? '';
    $resultCode = $callback['ResultCode'] ?? '';
    $resultDesc = $callback['ResultDesc'] ?? '';
    
    // Log callback details
    logCallbackReceived($merchantRequestId, $checkoutRequestId, $resultCode, $resultDesc);
    
    // Check for duplicate callback
    $db = (new Database())->connect();
    $stmt = $db->prepare("SELECT id FROM payments 
                         WHERE merchant_request_id = ? 
                         AND checkout_request_id = ? 
                         AND status != 'pending'");
    $stmt->execute([$merchantRequestId, $checkoutRequestId]);
    
    if ($stmt->rowCount() > 0) {
        logCallbackDuplicate($merchantRequestId, $checkoutRequestId);
        echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Duplicate callback ignored']);
        exit;
    }
    
    // Initialize M-Pesa handler
    $mpesa = new MpesaPayment();
    
    // Process the callback
    $result = $mpesa->processCallback($callbackData);

    if ($result) {
        // Success response to M-Pesa
        echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Callback processed successfully']);
        
        // Log success with transaction details
        logCallbackSuccess($callback);
    } else {
        // Error response to M-Pesa
        echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Failed to process callback']);
        
        // Log failure
        logCallbackFailure($merchantRequestId, $resultCode, $resultDesc);
    }
    
} catch (Exception $e) {
    // Log the error with full details
    logCallbackException($e, $callbackData);
    
    // Return error response to M-Pesa
    echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'System error processing callback']);
}

// Helper functions for logging
function logCallbackError($message) {
    $logEntry = date('Y-m-d H:i:s') . " - ERROR: $message\n";
    file_put_contents('logs/mpesa_errors_'.date('Y-m-d').'.log', $logEntry, FILE_APPEND);
}

function logCallbackReceived($merchantRequestId, $checkoutRequestId, $resultCode, $resultDesc) {
    $logEntry = date('Y-m-d H:i:s') . " - CALLBACK: " . 
        "MerchantRequestID: $merchantRequestId, " .
        "CheckoutRequestID: $checkoutRequestId, " .
        "ResultCode: $resultCode, " .
        "ResultDesc: $resultDesc\n";
    file_put_contents('logs/mpesa_callbacks_'.date('Y-m-d').'.log', $logEntry, FILE_APPEND);
}

function logCallbackDuplicate($merchantRequestId, $checkoutRequestId) {
    $logEntry = date('Y-m-d H:i:s') . " - DUPLICATE: " . 
        "MerchantRequestID: $merchantRequestId, " .
        "CheckoutRequestID: $checkoutRequestId - Already processed\n";
    file_put_contents('logs/mpesa_callbacks_'.date('Y-m-d').'.log', $logEntry, FILE_APPEND);
}

function logCallbackSuccess($callback) {
    $logEntry = date('Y-m-d H:i:s') . " - SUCCESS: Callback processed successfully\n";
    
    if (isset($callback['CallbackMetadata']['Item'])) {
        foreach ($callback['CallbackMetadata']['Item'] as $item) {
            $logEntry .= "  - " . $item['Name'] . ": " . ($item['Value'] ?? '') . "\n";
        }
    }
    
    file_put_contents('logs/mpesa_callbacks_'.date('Y-m-d').'.log', $logEntry, FILE_APPEND);
}

function logCallbackFailure($merchantRequestId, $resultCode, $resultDesc) {
    $logEntry = date('Y-m-d H:i:s') . " - FAILURE: " . 
        "MerchantRequestID: $merchantRequestId, " .
        "ResultCode: $resultCode, " .
        "ResultDesc: $resultDesc\n";
    file_put_contents('logs/mpesa_errors_'.date('Y-m-d').'.log', $logEntry, FILE_APPEND);
}

function logCallbackException($e, $callbackData) {
    $errorDetails = [
        'timestamp' => date('Y-m-d H:i:s'),
        'error_message' => $e->getMessage(),
        'error_line' => $e->getLine(),
        'error_file' => $e->getFile(),
        'callback_data' => $callbackData,
        'trace' => $e->getTraceAsString()
    ];
    
    $logEntry = date('Y-m-d H:i:s') . " - EXCEPTION: " . json_encode($errorDetails, JSON_PRETTY_PRINT) . "\n";
    file_put_contents('logs/mpesa_errors_'.date('Y-m-d').'.log', $logEntry, FILE_APPEND);
}
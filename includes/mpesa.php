<?php
require_once 'config.php';
require_once 'db.php';
require_once 'notifications.php';

class MpesaPayment {
    private $consumerKey;
    private $consumerSecret;
    private $shortCode;
    private $passKey;
    private $callbackUrl;
    private $initiatorName;
    private $initiatorPassword;
    private $withdrawCallbackUrl;
    private $securityCredential;

    public function __construct() {
        $this->consumerKey = $_ENV['MPESA_CONSUMER_KEY'];
        $this->consumerSecret = $_ENV['MPESA_CONSUMER_SECRET'];
        $this->shortCode = $_ENV['MPESA_SHORTCODE'];
        $this->passKey = $_ENV['MPESA_PASSKEY'];
        $this->callbackUrl = $_ENV['MPESA_CALLBACK_URL'];
        $this->initiatorName = $_ENV['MPESA_INITIATOR_NAME'];
        $this->initiatorPassword = $_ENV['MPESA_INITIATOR_PASSWORD'];
        $this->withdrawCallbackUrl = $_ENV['MPESA_WITHDRAW_CALLBACK_URL'];
        $this->securityCredential = $this->getSecurityCredential();
    }

    
    public function initiateSTKPush($phone, $amount, $accountReference, $transactionDesc) {
        $phone = $this->formatPhone($phone);
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortCode . $this->passKey . $timestamp);
        
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return ['status' => 'error', 'message' => 'Failed to get access token'];
        }
        
        /** Determine the correct URL based on the environment
         * Use sandbox for development and production for live transactions
         */
        if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production') {
            $url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        } else {
            $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        }

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];
        
        $payload = [
            'BusinessShortCode' => $this->shortCode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => $this->shortCode,
            'PhoneNumber' => $phone,
            'CallBackURL' => $this->callbackUrl,
            'AccountReference' => $accountReference,
            'TransactionDesc' => $transactionDesc
        ];
        
        $response = $this->makeCurlRequest($url, $headers, $payload);
        
        // Log STK Push request
        $this->logTransaction('STK_PUSH_REQUEST', [
            'phone' => $phone,
            'amount' => $amount,
            'response' => $response
        ]);
        
        return $response;
    }
    
    
    public function processCallback($callbackData) {
        $data = json_decode($callbackData, true);
        
        if (!isset($data['Body']['stkCallback'])) {
            $this->logTransaction('CALLBACK_ERROR', ['error' => 'Invalid callback structure', 'data' => $data]);
            return false;
        }
        
        $callback = $data['Body']['stkCallback'];
        $merchantRequestId = $callback['MerchantRequestID'];
        $checkoutRequestId = $callback['CheckoutRequestID'];
        $resultCode = $callback['ResultCode'];
        $resultDesc = $callback['ResultDesc'];
        
        $db = (new Database())->connect();
        
        // Log callback received
        $this->logTransaction('CALLBACK_RECEIVED', [
            'merchant_request_id' => $merchantRequestId,
            'checkout_request_id' => $checkoutRequestId,
            'result_code' => $resultCode,
            'result_desc' => $resultDesc
        ]);
        
        if ($resultCode == 0) {
            // Successful payment
            $callbackMetadata = $callback['CallbackMetadata']['Item'];
            $amount = null;
            $mpesaReceiptNumber = null;
            $transactionDate = null;
            $phoneNumber = null;
            
            // Parse callback metadata
            foreach ($callbackMetadata as $item) {
                switch ($item['Name']) {
                    case 'Amount':
                        $amount = $item['Value'];
                        break;
                    case 'MpesaReceiptNumber':
                        $mpesaReceiptNumber = $item['Value'];
                        break;
                    case 'TransactionDate':
                        $transactionDate = $item['Value'];
                        break;
                    case 'PhoneNumber':
                        $phoneNumber = $item['Value'];
                        break;
                }
            }
            
            // Get user by phone
            $stmt = $db->prepare("SELECT id, referrer_id FROM users WHERE phone = ?");
            $stmt->execute([$phoneNumber]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                try {
                    $db->beginTransaction();
                    
                    // Record payment
                    $stmt = $db->prepare("INSERT INTO payments (user_id, amount, mpesa_code, transaction_date, merchant_request_id, checkout_request_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'completed', NOW())");
                    $stmt->execute([$user['id'], $amount, $mpesaReceiptNumber, $transactionDate, $merchantRequestId, $checkoutRequestId]);
                    $paymentId = $db->lastInsertId();
                    
                    // Activate user
                    $stmt = $db->prepare("UPDATE users SET is_active = 1, activated_at = NOW() WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    
                    // Update user balance
                    $stmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                    $stmt->execute([$amount, $user['id']]);
                    
                    // Process referrals
                    $this->processReferrals($user['id'], $amount, $db);
                    
                    $db->commit();
                    
                    // Send payment notification
                    Notification::sendPaymentNotification($user['id'], $amount, $mpesaReceiptNumber);
                    
                    $this->logTransaction('PAYMENT_SUCCESS', [
                        'user_id' => $user['id'],
                        'amount' => $amount,
                        'mpesa_code' => $mpesaReceiptNumber,
                        'payment_id' => $paymentId
                    ]);
                    
                    return true;
                    
                } catch (Exception $e) {
                    $db->rollBack();
                    $this->logTransaction('PAYMENT_ERROR', [
                        'error' => $e->getMessage(),
                        'phone' => $phoneNumber,
                        'amount' => $amount
                    ]);
                    return false;
                }
            } else {
                $this->logTransaction('USER_NOT_FOUND', [
                    'phone' => $phoneNumber,
                    'amount' => $amount
                ]);
                return false;
            }
        } else {
            // Payment failed - log the error
            $this->logTransaction('PAYMENT_FAILED', [
                'result_code' => $resultCode,
                'result_desc' => $resultDesc,
                'merchant_request_id' => $merchantRequestId
            ]);
            return false;
        }
    }

    

    /**
     * Process withdrawal request
     * 
     * @param int $userId User ID initiating the withdrawal
     * @param string $phone User's phone number
     * @param float $amount Amount to withdraw
     * @param string $remarks Remarks for the transaction
     * @return array Response indicating success or failure
     */
    public function processWithdrawal($userId, $phone, $amount, $remarks = 'Withdrawal') {
        $db = (new Database())->connect();
        
        try {
            $db->beginTransaction();
            
            // Calculate transaction cost
            $transactionCost = $this->transactionCost($amount);
            $totalDeduction = $amount + $transactionCost;
            
            // Verify user has sufficient balance (including fees)
            $stmt = $db->prepare("SELECT balance, phone as user_phone FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || $user['balance'] < $totalDeduction) {
                $db->rollBack();
                return [
                    'status' => 'error', 
                    'message' => "Insufficient balance. You need KES {$totalDeduction} (Amount: KES {$amount} + Fee: KES {$transactionCost})"
                ];
            }
            
            // Create withdrawal record with fee information
            $stmt = $db->prepare("INSERT INTO withdrawals (user_id, amount, transaction_fee, phone, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
            $stmt->execute([$userId, $amount, $transactionCost, $phone]);
            $withdrawalId = $db->lastInsertId();
            
            // Deduct total amount (including fee) from user balance immediately
            $stmt = $db->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$totalDeduction, $userId]);
            
            $db->commit();
            
            // Send withdrawal request notification with fee breakdown
            Notification::sendWithdrawalRequestNotification($userId, $amount, $withdrawalId, $transactionCost);
            
            // Initiate M-Pesa B2C payment (only the withdrawal amount, not the fee)
            $response = $this->processBusinessPayment($phone, $amount, $remarks, "WD{$withdrawalId}");
            
            $this->logTransaction('WITHDRAWAL_INITIATED', [
                'withdrawal_id' => $withdrawalId,
                'user_id' => $userId,
                'amount' => $amount,
                'transaction_fee' => $transactionCost,
                'total_deducted' => $totalDeduction,
                'phone' => $phone,
                'response' => $response
            ]);
            
            if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
                // Update withdrawal status to processing
                $stmt = $db->prepare("UPDATE withdrawals SET status = 'processing', conversation_id = ?, originator_conversation_id = ? WHERE id = ?");
                $stmt->execute([
                    $response['ConversationID'] ?? null,
                    $response['OriginatorConversationID'] ?? null,
                    $withdrawalId
                ]);
                
                // Send processing notification
                Notification::sendWithdrawalProcessingNotification($userId, $amount, $withdrawalId);
                
                return [
                    'status' => 'success', 
                    'message' => "Withdrawal initiated successfully. Fee: KES {$transactionCost}", 
                    'withdrawal_id' => $withdrawalId,
                    'conversation_id' => $response['ConversationID'] ?? null,
                    'amount' => $amount,
                    'fee' => $transactionCost,
                    'total_deducted' => $totalDeduction
                ];
            } else {
                // Mark withdrawal as failed and return full amount (including fee)
                $stmt = $db->prepare("UPDATE withdrawals SET status = 'failed', failure_reason = ? WHERE id = ?");
                $stmt->execute([$response['errorMessage'] ?? 'MPesa API error', $withdrawalId]);
                
                // Return full amount to user
                $stmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $stmt->execute([$totalDeduction, $userId]);
                
                // Send failure notification
                Notification::sendWithdrawalFailedNotification($userId, $amount, $withdrawalId, $response['errorMessage'] ?? 'MPesa API error');
                
                return [
                    'status' => 'error', 
                    'message' => $response['errorMessage'] ?? 'Failed to initiate withdrawal'
                ];
            }
            
        } catch (Exception $e) {
            $db->rollBack();
            $this->logTransaction('WITHDRAWAL_ERROR', [
                'user_id' => $userId,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            return ['status' => 'error', 'message' => 'System error: ' . $e->getMessage()];
        }
    }

    /**
     * Process withdrawal callback from M-Pesa
     * 
     * @param string $callbackData JSON data from M-Pesa callback
     * @return bool True if processed successfully, false otherwise
     */
    public function processWithdrawalCallback($callbackData) {
        $data = json_decode($callbackData, true);
        
        if (!isset($data['Result'])) {
            $this->logTransaction('WITHDRAWAL_CALLBACK_ERROR', ['error' => 'Invalid callback structure', 'data' => $data]);
            return false;
        }
        
        $result = $data['Result'];
        $resultCode = $result['ResultCode'];
        $resultDesc = $result['ResultDesc'];
        $conversationId = $result['ConversationID'] ?? null;
        $originatorConversationId = $result['OriginatorConversationID'] ?? null;
        
        $db = (new Database())->connect();
        
        try {
            $db->beginTransaction();
            
            // Find withdrawal by conversation ID
            $stmt = $db->prepare("SELECT * FROM withdrawals WHERE conversation_id = ? OR originator_conversation_id = ?");
            $stmt->execute([$conversationId, $originatorConversationId]);
            $withdrawal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$withdrawal) {
                $this->logTransaction('WITHDRAWAL_NOT_FOUND', [
                    'conversation_id' => $conversationId,
                    'originator_conversation_id' => $originatorConversationId
                ]);
                $db->rollBack();
                return false;
            }
            
            $withdrawalId = $withdrawal['id'];
            $userId = $withdrawal['user_id'];
            $amount = $withdrawal['amount'];
            $transactionFee = $withdrawal['transaction_fee'];
            $totalDeduction = $amount + $transactionFee;
            
            $this->logTransaction('WITHDRAWAL_CALLBACK_RECEIVED', [
                'withdrawal_id' => $withdrawalId,
                'result_code' => $resultCode,
                'result_desc' => $resultDesc,
                'conversation_id' => $conversationId
            ]);
            
            if ($resultCode == 0) {
                // Successful withdrawal
                $resultParameters = $result['ResultParameters']['ResultParameter'] ?? [];
                
                $params = [];
                foreach ($resultParameters as $param) {
                    $params[$param['Key']] = $param['Value'] ?? null;
                }
                
                $transactionAmount = $params['TransactionAmount'] ?? $amount;
                $mpesaReceiptNumber = $params['TransactionReceipt'] ?? null;
                $receiverPhone = $params['ReceiverPartyPublicName'] ?? null;
                $b2CUtilityAccountAvailableFunds = $params['B2CUtilityAccountAvailableFunds'] ?? null;
                $b2CWorkingAccountAvailableFunds = $params['B2CWorkingAccountAvailableFunds'] ?? null;
                $transactionCompletedDateTime = $params['TransactionCompletedDateTime'] ?? null;
                
                // Parse and format the transaction completed date
                $formattedCompletedAt = null;
                if ($transactionCompletedDateTime) {
                    // M-Pesa format is typically YYYYMMDDHHMMSS
                    $parsedDate = DateTime::createFromFormat('YmdHis', $transactionCompletedDateTime);
                    if ($parsedDate) {
                        $formattedCompletedAt = $parsedDate->format('Y-m-d H:i:s');
                    }
                }
                
                // Update withdrawal record
                $stmt = $db->prepare("UPDATE withdrawals SET 
                    status = 'completed',
                    mpesa_code = ?,
                    processed_at = NOW(),
                    result_desc = ?,
                    receiver_phone = ?,
                    transaction_completed_at = ?
                    WHERE id = ?");
                $stmt->execute([
                    $mpesaReceiptNumber,
                    $resultDesc,
                    $receiverPhone,
                    $formattedCompletedAt,
                    $withdrawalId
                ]);
                
                $db->commit();
                
                // Send completion notification
                Notification::sendWithdrawalCompletedNotification(
                    $userId, 
                    $amount, 
                    $withdrawalId,
                    $mpesaReceiptNumber
                );
                
                $this->logTransaction('WITHDRAWAL_SUCCESS', [
                    'withdrawal_id' => $withdrawalId,
                    'amount' => $transactionAmount,
                    'mpesa_code' => $mpesaReceiptNumber,
                    'receiver_phone' => $receiverPhone,
                    'transaction_fee' => $transactionFee
                ]);
                
                return true;
                
            } else {
                // Failed withdrawal - return funds to user's balance
                $stmt = $db->prepare("UPDATE withdrawals SET 
                    status = 'failed',
                    failure_reason = ?,
                    processed_at = NOW(),
                    result_desc = ?
                    WHERE id = ?");
                $stmt->execute([$resultDesc, $resultDesc, $withdrawalId]);
                
                // Return full amount (including fee) to user's balance
                $stmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $stmt->execute([$totalDeduction, $userId]);
                
                $db->commit();
                
                // Send failure notification
                Notification::sendWithdrawalFailedNotification(
                    $userId, 
                    $amount, 
                    $withdrawalId,
                    $resultDesc
                );
                
                $this->logTransaction('WITHDRAWAL_FAILED', [
                    'withdrawal_id' => $withdrawalId,
                    'amount' => $amount,
                    'transaction_fee' => $transactionFee,
                    'total_returned' => $totalDeduction,
                    'reason' => $resultDesc
                ]);
                
                return false;
            }
            
        } catch (Exception $e) {
            $db->rollBack();
            $this->logTransaction('WITHDRAWAL_CALLBACK_ERROR', [
                'error' => $e->getMessage(),
                'withdrawal_id' => $withdrawalId ?? null,
                'conversation_id' => $conversationId
            ]);
            return false;
        }
    }


    /**
     * Initiate a money transfer from one user to another
     * 
     * @param int $senderId ID of the user sending money
     * @param string $receiverPhone Phone number of the recipient
     * @param float $amount Amount to transfer
     * @param string $remarks Remarks for the transaction
     * @return array Response indicating success or failure
     */
    public function initiateTransfer($senderId, $receiverPhone, $amount, $remarks = 'Money Transfer') {
        $db = (new Database())->connect();
        
        try {
            $db->beginTransaction();
            
            // Format phone number first
            $receiverPhone = $this->formatPhone($receiverPhone);
            
            // Calculate transfer cost
            $transferCost = $this->transferCost($amount);
            $totalDeduction = $amount + $transferCost;
            
            // Get sender details
            $stmt = $db->prepare("SELECT id, phone, balance FROM users WHERE id = ?");
            $stmt->execute([$senderId]);
            $sender = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$sender || $sender['balance'] < $totalDeduction) {
                $db->rollBack();
                return [
                    'status' => 'error', 
                    'message' => "Insufficient funds. You need KES {$totalDeduction} (Amount: KES {$amount} + Fee: KES {$transferCost})"
                ];
            }
            
            // Get receiver details - now using formatted phone
            $stmt = $db->prepare("SELECT id, phone FROM users WHERE phone = ? OR phone LIKE ?");
            $stmt->execute([$receiverPhone, '%'.substr($receiverPhone, -9)]);
            $receiver = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$receiver) {
                $db->rollBack();
                return ['status' => 'error', 'message' => 'Recipient not found. Please ensure they are registered with Stepcashier'];
            }
                
            // Deduct total amount (including fee) from sender's balance immediately
            $stmt = $db->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$totalDeduction, $senderId]);
            
            // Create transfer record with fee information
            $stmt = $db->prepare("INSERT INTO transfers (sender_id, receiver_id, amount, transfer_fee, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
            $stmt->execute([$senderId, $receiver['id'], $amount, $transferCost]);
            $transferId = $db->lastInsertId();
            
            $db->commit();
            
            // Send initial notifications with fee information
            Notification::sendTransferSentNotification($senderId, $receiver['id'], $amount, $transferId, $transferCost);
            
            // Initiate M-Pesa B2C payment to receiver (only the transfer amount, not the fee)
            $response = $this->processBusinessPayment($receiverPhone, $amount, $remarks, "TR{$transferId}");
            
            $this->logTransaction('TRANSFER_INITIATED', [
                'transfer_id' => $transferId,
                'sender_id' => $senderId,
                'receiver_id' => $receiver['id'],
                'amount' => $amount,
                'transfer_fee' => $transferCost,
                'total_deducted' => $totalDeduction,
                'response' => $response
            ]);
            
            if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
                // Update transfer status to processing
                $stmt = $db->prepare("UPDATE transfers SET 
                    status = 'processing', 
                    conversation_id = ?, 
                    originator_conversation_id = ? 
                    WHERE id = ?");
                $stmt->execute([
                    $response['ConversationID'] ?? null,
                    $response['OriginatorConversationID'] ?? null,
                    $transferId
                ]);
                
                return [
                    'status' => 'success', 
                    'message' => "Transfer initiated successfully. Fee: KES {$transferCost}", 
                    'transfer_id' => $transferId,
                    'conversation_id' => $response['ConversationID'] ?? null,
                    'amount' => $amount,
                    'fee' => $transferCost,
                    'total_deducted' => $totalDeduction
                ];
            } else {
                // Mark transfer as failed and return full amount (including fee)
                $stmt = $db->prepare("UPDATE transfers SET status = 'failed', failure_reason = ? WHERE id = ?");
                $stmt->execute([$response['errorMessage'] ?? 'MPesa API error', $transferId]);
                
                // Return full amount to sender
                $stmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $stmt->execute([$totalDeduction, $senderId]);
                
                // Send failure notification
                Notification::sendTransferFailedNotification($senderId, $receiver['id'], $amount, $transferId, $response['errorMessage'] ?? 'MPesa API error');
                
                return [
                    'status' => 'error', 
                    'message' => $response['errorMessage'] ?? 'Failed to initiate transfer'
                ];
            }
            
        } catch (Exception $e) {
            $db->rollBack();
            $this->logTransaction('TRANSFER_ERROR', [
                'sender_id' => $senderId,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            return ['status' => 'error', 'message' => 'System error: ' . $e->getMessage()];
        }
    }

    /**
     * Process transfer callback from M-Pesa
     * 
     * @param string $callbackData JSON data from M-Pesa callback
     * @return bool True if processed successfully, false otherwise
     */
    public function processTransferCallback($callbackData) {
        $data = json_decode($callbackData, true);
        
        if (!isset($data['Result'])) {
            $this->logTransaction('TRANSFER_CALLBACK_ERROR', ['error' => 'Invalid callback structure', 'data' => $data]);
            return false;
        }
        
        $result = $data['Result'];
        $resultCode = $result['ResultCode'];
        $resultDesc = $result['ResultDesc'];
        $conversationId = $result['ConversationID'] ?? null;
        $originatorConversationId = $result['OriginatorConversationID'] ?? null;
        
        $db = (new Database())->connect();
        
        // Find transfer by conversation ID (we'll need to store this when initiating the transfer)
        $stmt = $db->prepare("SELECT * FROM transfers WHERE conversation_id = ? OR originator_conversation_id = ?");
        $stmt->execute([$conversationId, $originatorConversationId]);
        $transfer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$transfer) {
            $this->logTransaction('TRANSFER_NOT_FOUND', [
                'conversation_id' => $conversationId,
                'originator_conversation_id' => $originatorConversationId
            ]);
            return false;
        }
        
        $transferId = $transfer['id'];
        $senderId = $transfer['sender_id'];
        $receiverId = $transfer['receiver_id'];
        $amount = $transfer['amount'];
        
        $this->logTransaction('TRANSFER_CALLBACK_RECEIVED', [
            'transfer_id' => $transferId,
            'result_code' => $resultCode,
            'result_desc' => $resultDesc,
            'conversation_id' => $conversationId
        ]);
        
        if ($resultCode == 0) {
            // Successful transfer
            $resultParameters = $result['ResultParameters']['ResultParameter'] ?? [];
            
            $params = [];
            foreach ($resultParameters as $param) {
                $params[$param['Key']] = $param['Value'] ?? null;
            }
            
            $transactionAmount = $params['TransactionAmount'] ?? $amount;
            $mpesaReceiptNumber = $params['TransactionReceipt'] ?? null;
            $receiverPhone = $params['ReceiverPartyPublicName'] ?? null;
            $transactionCompletedDateTime = $params['TransactionCompletedDateTime'] ?? null;
            
            try {
                $db->beginTransaction();
                
                // Update transfer record
                $stmt = $db->prepare("UPDATE transfers SET 
                    status = 'completed',
                    mpesa_code = ?,
                    processed_at = NOW(),
                    result_desc = ?,
                    receiver_phone = ?,
                    transaction_completed_at = ?
                    WHERE id = ?");
                $stmt->execute([
                    $mpesaReceiptNumber,
                    $resultDesc,
                    $receiverPhone,
                    $transactionCompletedDateTime,
                    $transferId
                ]);
                
                // Update receiver's balance (if not already done)
                if ($transfer['status'] != 'completed') {
                    $stmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                    $stmt->execute([$amount, $receiverId]);
                }
                
                $db->commit();
                
                // Send notifications
                Notification::sendTransferReceivedNotification($senderId, $receiverId, $amount, $transferId);
                Notification::sendTransferSentNotification($senderId, $receiverId, $amount, $transferId);
                
                $this->logTransaction('TRANSFER_SUCCESS', [
                    'transfer_id' => $transferId,
                    'amount' => $transactionAmount,
                    'mpesa_code' => $mpesaReceiptNumber,
                    'receiver_phone' => $receiverPhone
                ]);
                
                return true;
                
            } catch (Exception $e) {
                $db->rollBack();
                $this->logTransaction('TRANSFER_UPDATE_ERROR', [
                    'transfer_id' => $transferId,
                    'error' => $e->getMessage()
                ]);
                return false;
            }
            
        } else {
            // Failed transfer
            try {
                $db->beginTransaction();
                
                // Update transfer record
                $stmt = $db->prepare("UPDATE transfers SET 
                    status = 'failed',
                    failure_reason = ?,
                    processed_at = NOW(),
                    result_desc = ?
                    WHERE id = ?");
                $stmt->execute([$resultDesc, $resultDesc, $transferId]);
                
                // Return funds to sender's balance
                $stmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $stmt->execute([$amount, $senderId]);
                
                $db->commit();
                
                // Send failure notification
                Notification::sendTransferFailedNotification($senderId, $receiverId, $amount, $transferId, $resultDesc);
                
                $this->logTransaction('TRANSFER_FAILED', [
                    'transfer_id' => $transferId,
                    'amount' => $amount,
                    'reason' => $resultDesc
                ]);
                
                return false;
                
            } catch (Exception $e) {
                $db->rollBack();
                $this->logTransaction('TRANSFER_FAILURE_UPDATE_ERROR', [
                    'transfer_id' => $transferId,
                    'error' => $e->getMessage()
                ]);
                return false;
            }
        }
    }

    private function processBusinessPayment($phone, $amount, $remarks, $reference) {
        $phone = $this->formatPhone($phone);
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return ['ResponseCode' => '1', 'errorMessage' => 'Failed to get access token'];
        }
        
        // Determine the correct URL based on the environment
        if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production') {
            $url = 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';
        } else {
            $url = 'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';
        }
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];
        
        $payload = [
            'InitiatorName' => $this->initiatorName,
            'SecurityCredential' => $this->securityCredential,
            'CommandID' => 'BusinessPayment',
            'Amount' => $amount,
            'PartyA' => $this->shortCode,
            'PartyB' => $phone,
            'Remarks' => $remarks,
            'QueueTimeOutURL' => $this->withdrawCallbackUrl,
            'ResultURL' => $this->withdrawCallbackUrl,
            'Occasion' => 'Withdrawal',
            'OriginatorConversationID' => $reference
        ];
        
        return $this->makeCurlRequest($url, $headers, $payload);
    }

    
    private function processReferrals($userId, $amount, $db) {
    // Check if this user was referred by someone
    $stmt = $db->prepare("SELECT id, referrer_id FROM referrals WHERE referred_id = ? AND bonus_paid = 0");
    $stmt->execute([$userId]);
    $referral = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($referral && $amount >= $_ENV['REGISTRATION_FEE']) {
        $referrerId = $referral['referrer_id'];
        $bonusAmount = $_ENV['REFERRAL_BONUS']; 
        $groupBonusPool = $_ENV['GROUP_SHARE_AMOUNT']; 
        $groupSharePerUser = $groupBonusPool / 5;

        // ✅ Step 1: Pay direct referrer
        $stmt = $db->prepare("INSERT INTO referral_earnings (user_id, referral_id, amount, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$referrerId, $referral['id'], $bonusAmount]);

        $stmt = $db->prepare("UPDATE users SET referral_bonus_balance = referral_bonus_balance + ? WHERE id = ?");
        $stmt->execute([$bonusAmount, $referrerId]);

        $stmt = $db->prepare("UPDATE referrals SET bonus_paid = 1, bonus_paid_at = NOW() WHERE id = ?");
        $stmt->execute([$referral['id']]);

        Notification::sendReferralBonusNotification($referrerId, $userId, $bonusAmount);

        $this->logTransaction('REFERRAL_BONUS', [
            'referrer_id' => $referrerId,
            'referred_id' => $userId,
            'bonus_amount' => $bonusAmount
        ]);

        // ✅ Step 2: Pay 5 uplines above the referrer (excluding the referrer)
        $uplineUserIds = [];
        $currentReferrer = $referrerId;

        for ($i = 0; $i < 5; $i++) {
            $stmt = $db->prepare("SELECT referrer_id FROM referrals WHERE referred_id = ?");
            $stmt->execute([$currentReferrer]);
            $upline = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$upline || !$upline['referrer_id']) {
                break; // no more uplines
            }

            $currentReferrer = $upline['referrer_id'];
            $uplineUserIds[] = $currentReferrer;
        }

        foreach ($uplineUserIds as $uplineId) {
            // Pay each of the 5 uplines equally
            $stmt = $db->prepare("INSERT INTO referral_earnings (user_id, referral_id, amount, created_at) VALUES (?, NULL, ?, NOW())");
            $stmt->execute([$uplineId, $groupSharePerUser]);

            $stmt = $db->prepare("UPDATE users SET referral_bonus_balance = referral_bonus_balance + ? WHERE id = ?");
            $stmt->execute([$groupSharePerUser, $uplineId]);

            Notification::sendUplineBonusNotification($uplineId, $userId, $groupSharePerUser);

            $this->logTransaction('UPLINE_SHARE', [
                'upline_id' => $uplineId,
                'from_user_id' => $userId,
                'bonus_amount' => $groupSharePerUser
            ]);
        }
    }
}


    private function getAccessToken() {
        // Determine the correct URL based on environment
        if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production') {
            $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        } else {
            $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        }
        $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);
        
        $headers = [
            'Authorization: Basic ' . $credentials,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200) {
            $data = json_decode($response, true);
            return $data['access_token'] ?? null;
        }
        
        $this->logTransaction('ACCESS_TOKEN_ERROR', [
            'http_code' => $httpCode,
            'response' => $response
        ]);
        
        return null;
    }

    /**
     * Get security credential for M-Pesa API
     * 
     * @return string Base64 encoded security credential
     */

    private function getSecurityCredential() {
        // Check if we're in production environment
        if ($_ENV['APP_ENV'] === 'production') {
            $certPath = 'certificates/ProductionCertificate.cer';
            
            // Check if certificate file exists
            if (!file_exists($certPath)) {
                $this->logTransaction('CERT_ERROR', [
                    'error' => 'Production certificate not found',
                    'path' => $certPath
                ]);
                throw new Exception('M-Pesa production certificate not found at: ' . $certPath);
            }
            
            // Load the M-Pesa production public certificate
            $publicKey = file_get_contents($certPath);
            
            if (!$publicKey) {
                $this->logTransaction('CERT_ERROR', [
                    'error' => 'Failed to read production certificate',
                    'path' => $certPath
                ]);
                throw new Exception('Failed to read M-Pesa production certificate');
            }
            
            // Get the certificate resource
            $cert = openssl_x509_read($publicKey);
            if (!$cert) {
                $this->logTransaction('CERT_ERROR', [
                    'error' => 'Invalid certificate format',
                    'path' => $certPath
                ]);
                throw new Exception('Invalid M-Pesa certificate format');
            }
            
            // Extract public key from certificate
            $pubKey = openssl_pkey_get_public($cert);
            if (!$pubKey) {
                $this->logTransaction('CERT_ERROR', [
                    'error' => 'Failed to extract public key from certificate'
                ]);
                throw new Exception('Failed to extract public key from M-Pesa certificate');
            }
            
            // Encrypt the initiator password
            $encrypted = '';
            $result = openssl_public_encrypt($this->initiatorPassword, $encrypted, $pubKey, OPENSSL_PKCS1_PADDING);
            
            // Clean up resources
            openssl_pkey_free($pubKey);
            openssl_x509_free($cert);
            
            if (!$result) {
                $this->logTransaction('ENCRYPTION_ERROR', [
                    'error' => 'Failed to encrypt initiator password',
                    'openssl_error' => openssl_error_string()
                ]);
                throw new Exception('Failed to encrypt initiator password: ' . openssl_error_string());
            }
            
            // Return base64 encoded encrypted password
            $securityCredential = base64_encode($encrypted);
            
            $this->logTransaction('SECURITY_CREDENTIAL_GENERATED', [
                'environment' => 'production',
                'credential_length' => strlen($securityCredential)
            ]);
            
            return $securityCredential;
        }
        
        // For sandbox environment
        $securityCredential = base64_encode($this->initiatorPassword);
        
        $this->logTransaction('SECURITY_CREDENTIAL_GENERATED', [
            'environment' => 'sandbox',
            'credential_length' => strlen($securityCredential)
        ]);
        
        return $securityCredential;
    }


    /**
     * Format phone number to M-Pesa standard
     * 
     * @param string $phone Phone number to format
     * @return string Formatted phone number
     */
    private function formatPhone($phone) {
        // Remove all non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Handle different formats:
        if (strpos($phone, '0') === 0 && strlen($phone) === 10) {
            // Format like 0703416091 → 254703416091
            return '254' . substr($phone, 1);
        } elseif (strpos($phone, '254') === 0 && strlen($phone) === 12) {
            // Already in 254 format
            return $phone;
        } elseif (strpos($phone, '+254') === 0 && strlen($phone) === 13) {
            // Format like +254740640525 → 254740640525
            return substr($phone, 1);
        } elseif (strlen($phone) === 9) {
            // Format like 703416091 → 254703416091
            return '254' . $phone;
        }
        
        // Default - return as is (will fail validation)
        return $phone;
    }

    /**
     * Calculate transaction cost based on amount
     * 
     * @param float $amount Amount to calculate cost for
     * @return float Transaction cost
    */
    public function transactionCost($amount) {
        if ($amount < 100) {
            return 10; 
        } elseif ($amount < 500) {
            return 20; 
        } elseif ($amount < 1000) {
            return 30; 
        } elseif ($amount < 2500) {
            return 50; 
        } elseif ($amount < 5000) {
            return 100;
        } elseif ($amount < 10000) {
            return 150; 
        } elseif ($amount < 20000) {
            return 200; 
        } elseif ($amount < 35000) {
            return 250; 
        } elseif ($amount < 50000) {
            return 300; 
        } else {
            return 400; 
        }
    }

    /**
     * Calculate net amount after deducting transaction cost
     * 
     * @param float $amount Amount to calculate net amount for
     * @return float Net amount after cost
     */
    public function netAmountAfterCost($amount) {
        $cost = $this->transactionCost($amount);
        return max(0, $amount - $cost);
    }

    
    /**
     * Get transaction cost breakdown for a given amount
     * 
     * @param float $amount Amount to calculate cost for
     * @return array Transaction cost breakdown
     */
    public function getTransactionCostBreakdown($amount) {
        $cost = $this->transactionCost($amount);
        return [
            'amount' => $amount,
            'transaction_cost' => $cost,
            'net_amount' => $this->netAmountAfterCost($amount)
        ];
    }

    /**
     * Get transfer fee breakdown for a given amount
     * 
     * @param float $amount Amount to transfer
     * @return array Transfer fee breakdown
     */
    public function getWithdrawalFeeBreakdown($amount) {
        $cost = $this->transactionCost($amount);
        return [
            'amount' => $amount,
            'transaction_fee' => $cost,
            'total_deduction' => $amount + $cost,
            'you_will_receive' => $amount
        ];
    }

    /**
     * Get transfer fee breakdown for a given amount
     * 
     * @param float $amount Amount to calculate transfer fee for
     * @return array Transfer fee breakdown
     */
    public function getTransferFeeBreakdown($amount) {
        $cost = $this->transferCost($amount);
        return [
            'amount' => $amount,
            'transfer_fee' => $cost,
            'total_deduction' => $amount + $cost,
            'recipient_will_receive' => $amount
        ];
    }

    /**
     * Calculate transfer cost based on amount
     * 
     * @param float $amount Amount to calculate cost for
     * @return float Transfer cost
     */
    public function transferCost($amount) {
        if ($amount < 100) {
            return 5; 
        } elseif ($amount < 500) {
            return 10; 
        } elseif ($amount < 1000) {
            return 15; 
        } elseif ($amount < 5000) {
            return 50; 
        } elseif ($amount < 10000) {
            return 100; 
        } elseif ($amount < 20000) {
            return 150; 
        } elseif ($amount < 50000) {
            return 250; 
        } else {
            return 400; 
        }
    }

    /**
     * Initiate a withdrawal request to M-Pesa
     * 
     * @param int $userId ID of the user requesting withdrawal
     * @param float $amount Amount to withdraw
     * @return array Response indicating success or failure
     */
    private function makeCurlRequest($url, $headers, $payload = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        if ($payload) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $decodedResponse = json_decode($response, true);
        
        if ($httpCode != 200) {
            $this->logTransaction('API_ERROR', [
                'url' => $url,
                'http_code' => $httpCode,
                'response' => $response
            ]);
        }
        
        return $decodedResponse ?: ['ResponseCode' => '1', 'errorMessage' => 'Invalid response'];
    }

    private function logTransaction($type, $data) {
        $logEntry = date('Y-m-d H:i:s') . " - {$type}: " . json_encode($data) . "\n";
        file_put_contents('logs/mpesa_transactions_'.date('Y-m-d').'.log', $logEntry, FILE_APPEND);
    }

    /**
     * Check the status of a withdrawal by its ID
     * 
     * @param int $withdrawalId Withdrawal ID to check
     * @return array Withdrawal details or null if not found
     */
    public function checkWithdrawalStatus($withdrawalId) {
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT * FROM withdrawals WHERE id = ?");
        $stmt->execute([$withdrawalId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all transactions for a user, including payments and withdrawals
     * 
     * @param int $userId User ID to fetch transactions for
     * @param string $type 'all', 'payment', or 'withdrawal' to filter results
     * @return array List of transactions
     */
    public function getUserTransactions($userId, $type = 'all') {
        $db = (new Database())->connect();
        
        $query = "SELECT 'payment' as type, amount, mpesa_code, status, created_at FROM payments WHERE user_id = ?";
        if ($type !== 'all') {
            $query .= " AND 'payment' = '{$type}'";
        }
        
        $query .= " UNION ALL SELECT 'withdrawal' as type, amount, mpesa_code, status, created_at FROM withdrawals WHERE user_id = ?";
        if ($type !== 'all') {
            $query .= " AND 'withdrawal' = '{$type}'";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$userId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns a human-readable description from the M-PESA API response.
     * @param array $response
     * @return string
     */
    public function getResponseDescription($response) {
        if (isset($response['errorMessage'])) {
            return $response['errorMessage'];
        }
        if (isset($response['ResponseDescription'])) {
            return $response['ResponseDescription'];
        }
        return 'Payment request failed. Please try again.';
    }
}
?>
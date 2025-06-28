<?php
require_once 'includes/db.php';
require_once 'includes/mpesa.php';

class Deposit {
    private $db;
    private $conn;
    private $mpesa;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
        $this->mpesa = new MpesaPayment(); 
    }

    /**
     * Initiate a deposit request
     */
    public function initiateDeposit($userId, $amount, $phone) {
        try {
            // Validate amount
            if ($amount < 100) {
                return ['success' => false, 'message' => 'Minimum deposit amount is KES 100'];
            }

            // Format phone number
            $phone = $this->mpesa->formatPhone($phone);
            
            // Generate a unique reference
            $reference = 'DP' . time() . rand(100, 999);
            
            // Initiate STK push
            $response = $this->mpesa->initiateSTKPush(
                $phone, 
                $amount, 
                $reference,
                'Deposit to ' . SITE_NAME
            );
            
            if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
                // Record the deposit attempt
                $stmt = $this->conn->prepare("
                    INSERT INTO deposits (user_id, amount, phone, merchant_request_id, checkout_request_id, status, created_at)
                    VALUES (?, ?, ?, ?, ?, 'pending', NOW())
                ");
                $stmt->execute([
                    $userId,
                    $amount,
                    $phone,
                    $response['MerchantRequestID'] ?? null,
                    $response['CheckoutRequestID'] ?? null
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Deposit request initiated. Please complete the payment on your phone.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $this->mpesa->getResponseDescription($response)
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'System error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get deposit history for a user
     */
    public function getDepositHistory($userId, $limit = 10) {
        $stmt = $this->conn->prepare("
            SELECT * FROM deposits 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Process deposit callback from M-Pesa
     */
    public function processDepositCallback($callbackData) {
        return $this->mpesa->processCallback($callbackData);
    }

    /**
     * Get formatted status badge
     */
    public function getStatusBadge($status, $mpesaCode = null) {
        $statusClasses = [
            'completed' => 'bg-green-500/20 text-green-400',
            'failed' => 'bg-red-500/20 text-red-400',
            'pending' => 'bg-yellow-500/20 text-yellow-400',
            'processing' => 'bg-blue-500/20 text-blue-400'
        ];
        
        $statusIcons = [
            'completed' => 'fas fa-check-circle',
            'failed' => 'fas fa-times-circle',
            'pending' => 'fas fa-clock',
            'processing' => 'fas fa-spinner fa-pulse'
        ];
        
        $statusClass = $statusClasses[$status] ?? 'bg-gray-500/20 text-gray-400';
        $statusIcon = $statusIcons[$status] ?? 'fas fa-question-circle';
        
        $badge = '<span class="px-3 py-1 text-xs font-semibold rounded-full ' . $statusClass . '">';
        $badge .= '<i class="' . $statusIcon . ' mr-1"></i>';
        $badge .= ucfirst($status);
        
        if ($mpesaCode) {
            $badge .= '<span class="ml-1 text-xs">(' . $mpesaCode . ')</span>';
        }
        
        $badge .= '</span>';
        
        return $badge;
    }
}
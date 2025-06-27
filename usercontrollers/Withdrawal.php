<?php
require_once 'includes/db.php';
require_once 'includes/mpesa.php';

class Withdrawal {
    private $db;
    private $conn;
    private $mpesa;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
        $this->mpesa = new MpesaPayment(); 
    }


    /**
     * Get the total balance available for withdrawal
     */
    public function getTotalBalance($user) {
        return $user['balance'] + $user['referral_bonus_balance'];
    }

    /**
     * Get the withdrawal limits for a user
     */
    public function getWithdrawalLimits($user) {
        return [
            'min' => 100,
            'max' => $this->getMaxWithdrawalAmount($user)
        ];
    }

    /**
     * Get the maximum amount that can be withdrawn
     * This is the minimum of total balance and user's withdrawal limit
     */
    public function getMaxWithdrawalAmount($user) {
        $total_balance = $this->getTotalBalance($user);
        return min($total_balance, $user['withdrawal_limit']);
    }



    /**
     * Check if user has sufficient balance for withdrawal
     */
    public function hasSufficientBalance($user) {
        return $this->getTotalBalance($user) >= 100;
    }



    /**
     * Get withdrawal history for a user
     */
    public function getWithdrawalHistory($user_id, $limit = 10) {
        $stmt = $this->conn->prepare("SELECT * FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Process withdrawal request by delegating to MpesaPayment class
     */
    public function processWithdrawal($userId, $amount, $phone) {
        try {
            $result = $this->mpesa->processWithdrawal($userId, $phone, $amount);

            // Convert the response format to match what page expects
            return [
                'success' => $result['status'] === 'success',
                'message' => $result['message'],
                'withdrawal_id' => $result['withdrawal_id'] ?? null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }


    /**
     * Process withdrawal callback by delegating to MpesaPayment class
     */
    public function processWithdrawalCallback($callbackData) {
        return $this->mpesa->processWithdrawalCallback($callbackData);
    }



    /**
     * Get transaction cost by delegating to MpesaPayment class
     */
    public function getTransactionCost($amount) {
        return $this->mpesa->transactionCost($amount);
    }



    /**
     * Get formatted status badge
     */
    public function getStatusBadge($status, $mpesa_code = null) {
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
        
        if ($mpesa_code) {
            $badge .= '<span class="ml-1 text-xs">(' . $mpesa_code . ')</span>';
        }
        
        $badge .= '</span>';
        
        return $badge;
    }
}
<?php
require_once 'includes/db.php';

class WalletController {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    /**
     * Get wallet balance for a user
     */
    public function getWalletBalance($user_id) {
        try {
            $stmt = $this->conn->prepare("...");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                throw new Exception("User wallet not found");
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get transaction history
     */
    public function getTransactionHistory($user_id, $limit = 10) {
        $stmt = $this->conn->prepare("
            (SELECT 
                'withdrawal' as type,
                amount,
                transaction_fee,
                phone as destination,
                status,
                created_at
            FROM withdrawals 
            WHERE user_id = ?)
            
            UNION ALL
            
            (SELECT 
                'transfer' as type,
                amount,
                transfer_fee as transaction_fee,
                receiver_phone as destination,
                status,
                created_at
            FROM transfers 
            WHERE sender_id = ?)
            
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$user_id, $user_id, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get recent transactions
     */
    public function getRecentTransactions($user_id, $limit = 5) {
        return $this->getTransactionHistory($user_id, $limit);
    }

    /**
     * Format transaction type
     */
    public function formatTransactionType($type) {
        $types = [
            'withdrawal' => 'Withdrawal',
            'transfer' => 'Transfer',
            'deposit' => 'Deposit',
            'investment' => 'Investment'
        ];
        return $types[$type] ?? ucfirst($type);
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge($status) {
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
        
        return '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $statusClass . '">' .
               '<i class="' . $statusIcon . ' mr-1"></i>' .
               ucfirst($status) .
               '</span>';
    }
}
?>
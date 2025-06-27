<?php
require_once 'includes/db.php';
require_once 'includes/mpesa.php';


class Transfer {
    private $db;
    private $conn;
    private $mpesa;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
        $this->mpesa = new MpesaPayment();
    }

    /**
     * Get the total balance available for transfer
     */
    public function getAvailableBalance($user_id) {
        $stmt = $this->conn->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? $user['balance'] : 0;
    }

    /**
     * Get the maximum amount that can be transferred
     */
    public function hasSufficientBalance($user_id, $amount) {
        $balance = $this->getAvailableBalance($user_id);
        return $balance >= $amount;
    }

    /**
     * Get transfer history for a user
     */
    public function getTransferHistory($user_id, $limit = 10) {
        $stmt = $this->conn->prepare("
            SELECT t.*, u.name as receiver_name 
            FROM transfers t 
            LEFT JOIN users u ON t.receiver_id = u.id 
            WHERE t.sender_id = ? 
            ORDER BY t.created_at DESC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * Process transfer request by delegating to MpesaPayment class
     */

    public function processTransfer($sender_id, $phone, $amount, $remarks = 'Transfer') {
        try {
            // Process transfer through MpesaPayment class
            $result = $this->mpesa->initiateTransfer($sender_id, $phone, $amount, $remarks);
            
            // Convert the response format to match what page expects
            return [
                'success' => $result['status'] === 'success',
                'message' => $result['message'],
                'transfer_id' => $result['transfer_id'] ?? null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Process transfer callback by delegating to MpesaPayment class
     */ 
    public function processTransferCallback($callbackData) {
        return $this->mpesa->processTransferCallback($callbackData);
    }


    /**
     * Get status badge for transfer status
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

    /**
     * Get transfer cost for a given amount
     */
    public function getTransferCost($amount) {
        return $this->mpesa->transferCost($amount);
    }
}
?>
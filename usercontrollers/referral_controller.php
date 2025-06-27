<?php
class Referral {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    /**
     * Get total referrals for a user
     */
    public function getTotalReferrals($user_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM referrals WHERE referrer_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }

    /**
     * Get active referrals for a user
     */
    public function getActiveReferrals($user_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM referrals r JOIN users u ON r.referred_id = u.id WHERE r.referrer_id = ? AND u.is_active = 1");
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }


    /**
     * Get total earnings from referrals for a user
     */
    public function getTotalEarned($user_id) {
        $stmt = $this->conn->prepare("SELECT COALESCE(SUM(amount), 0) FROM referral_earnings WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }

    /**
     * Get referral network for a user
     */
    public function getReferralNetwork($user_id) {
        $stmt = $this->conn->prepare("
            SELECT u.name, u.email, u.phone, u.is_active, u.created_at, 
                (SELECT COUNT(*) FROM referrals r2 JOIN users u2 ON r2.referred_id = u2.id WHERE r2.referrer_id = u.id) as sub_referrals,
                (SELECT COALESCE(SUM(re.amount), 0) FROM referral_earnings re WHERE re.referral_id IN 
                    (SELECT r.id FROM referrals r WHERE r.referrer_id = u.id)) as earned_from_sub
            FROM referrals r
            JOIN users u ON r.referred_id = u.id
            WHERE r.referrer_id = ?
            ORDER BY u.created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Get earnings history for a user
     */
    public function getEarningsHistory($user_id) {
        $stmt = $this->conn->prepare("
            SELECT re.*, u.name as referral_name 
            FROM referral_earnings re 
            JOIN referrals r ON re.referral_id = r.id 
            JOIN users u ON r.referred_id = u.id 
            WHERE re.user_id = ? 
            ORDER BY re.created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate a referral link for a user
     */
    public function generateReferralLink($user) {
        return BASE_URL . "/register?ref=" . $user['referral_code'];
    }
}
?>
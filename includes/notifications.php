<?php
require_once 'db.php';
require_once 'sms.php';
require_once 'email.php';
require_once 'functions.php';

class Notification {


    public static function sendRegistrationNotification($userId) {
        $db = (new Database())->connect();
        $user = getUserById($userId);
        
        if (!$user) return false;
        
        // Send email
        $email = new Email();
        $email->sendEmail(
            $user['email'],
            "Welcome to {$_ENV['SITE_NAME']}",
            'registration',
            ['name' => $user['name']]
        );
        
        // Send SMS
        if ($user['phone_verified']) {
            $sms = new SMS();
            $sms->sendSMS(
                $user['phone'],
                "Welcome to {$_ENV['SITE_NAME']}, {$user['name']}! Your account has been created. Login to start earning."
            );
        }
        
        // Add to notifications center
        self::addNotification($userId, "Welcome to {$_ENV['SITE_NAME']}", 'Your account has been successfully created. Complete your registration by making the KES 500 payment.');
    }

    
    public static function sendPaymentNotification($userId, $amount) {
        $db = (new Database())->connect();
        $user = getUserById($userId);
        
        if (!$user) return false;
        
        // Send email
        $email = new Email();
        $email->sendEmail(
            $user['email'],
            'Payment Received',
            'payment_received',
            ['name' => $user['name'], 'amount' => $amount]
        );
        
        // Send SMS
        if ($user['phone_verified']) {
            $sms = new SMS();
            $sms->sendSMS(
                $user['phone'],
                "Your payment of KES {$amount} has been received. Your account is now active."
            );
        }
        
        // Add to notifications center
        self::addNotification($userId, 'Payment Received', "Your payment of KES {$amount} has been received and your account is now active.");
        
        // Notify admin
        self::notifyAdmin("New Payment Received", "User {$user['name']} has made a payment of KES {$amount}.");
    }

   
    public static function sendPasswordResetEmail($userId, $resetToken) {
        $db = (new Database())->connect();
        $user = getUserById($userId);
        
        if (!$user) return false;
        
        // Send email
        $email = new Email();
        $email->sendEmail(
            $user['email'],
            'Password Reset Request',
            'password_reset',
            ['name' => $user['name'], 'reset_link' => BASE_URL . "/resetpassword?token={$resetToken}"]
        );


        
        // Add to notifications center
        self::addNotification($userId, 'Password Reset Requested', 'A password reset has been requested for your account. If this was not you, please ignore this message.');
    }

    
    public static function sendAdminPasswordResetEmail($adminId, $resetToken) {
        $db = (new Database())->connect();
        // Get admin details
        $stmt = $db->prepare("SELECT id, name, email FROM admins WHERE id = ?");
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$admin) return false;
        
        // Send email
        $email = new Email();
        $emailSent = $email->sendEmail(
            $admin['email'],
            'Admin Password Reset Request',
            'admin_password_reset',
            [
                'name' => $admin['name'],
                'reset_link' => BASE_URL . "/management/resetpassword?token={$resetToken}",
                'admin_email' => $admin['email']
            ]
        );
        
        
        if ($emailSent) {
            self::logAdminNotification($adminId, 'Admin Password Reset Requested', 'A password reset has been requested for your admin account.');
        }
        
        return $emailSent;
    }

    
    public static function sendAdminOTPnotification ($adminId, $otp) {
        $db = (new Database())->connect();
        
        // Get admin details
        $stmt = $db->prepare("SELECT id, name, email FROM admins WHERE id = ?");
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$admin) return false;
        
        // Send email
        $email = new Email();
        $email->sendEmail(
            $admin['email'],
            'Admin OTP Code',
            'admin_otp',
            [
                'name' => $admin['name'],
                'otp' => $otp
            ]
        );

        // Send SMS 
        if ($admin['phone_verified']) {
            $sms = new SMS();
            $sms->sendSMS(
                $admin['phone'],
                "Your one-time password (OTP) code is: {$otp}. Use this to verify your account."
            );
        }
        
        // Log admin notification
        self::logAdminNotification($adminId, 'Admin OTP Code Sent', 'Your one-time password (OTP) code has been sent to your email.');
    } 


    public static function sendAdminPasswordChangedNotification($adminId, $newPassword) {
        $db = (new Database())->connect();
        
        // Get admin details
        $stmt = $db->prepare("SELECT id, name, email FROM admins WHERE id = ?");
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$admin) return false;
        
        // Send email
        $email = new Email();
        $email->sendEmail(
            $admin['email'],
            'Admin Password Changed Successfully',
            'admin_password_changed',
            [
                'name' => $admin['name'],
                'new_password' => $newPassword
            ]
        );
        
        // Log admin notification
        self::logAdminNotification($adminId, 'Admin Password Changed', 'Your password has been changed successfully.');
    }

    

    public static function sendPasswordChangedNotification($userId) {
        $db = (new Database())->connect();
        $user = getUserById($userId);
        
        if (!$user) return false;
        
        // Send email
        $email = new Email();
        $email->sendEmail(
            $user['email'],
            'Password Changed Successfully',
            'password_changed',
            ['name' => $user['name']]
        );
        
        // Add to notifications center
        self::addNotification($userId, 'Password Changed', 'Your password has been changed successfully.');
    }


    public static function sendReferralBonusNotification($referrerId, $referredId, $amount) {
        $db = (new Database())->connect();
        $referrer = getUserById($referrerId);
        $referred = getUserById($referredId);
        
        if (!$referrer || !$referred) return false;
        
        // Send email to referrer
        $email = new Email();
        $email->sendEmail(
            $referrer['email'],
            'Referral Bonus Earned',
            'referral_bonus',
            [
                'name' => $referrer['name'],
                'referred_name' => $referred['name'],
                'amount' => $amount
            ]
        );
        
        // Send SMS to referrer
        if ($referrer['phone_verified']) {
            $sms = new SMS();
            $sms->sendSMS(
                $referrer['phone'],
                "You've earned KES {$amount} for referring {$referred['name']}. Login to view your earnings."
            );
        }
        
        self::addNotification($referrerId, 'Referral Bonus Earned', "You've earned KES {$amount} for referring {$referred['name']}.");
    }

   
    public static function sendUplineBonusNotification($uplineId, $fromUserId, $amount) {
        $db = (new Database())->connect();
        $upline = getUserById($uplineId);
        $fromUser = getUserById($fromUserId);
        
        if (!$upline || !$fromUser) return false;
        
        // Send email to upline
        $email = new Email();
        $email->sendEmail(
            $upline['email'],
            'Upline Bonus Earned',
            'upline_bonus',
            [
                'name' => $upline['name'],
                'from_user_name' => $fromUser['name'],
                'amount' => $amount
            ]
        );
        
        // Send SMS to upline
        if ($upline['phone_verified']) {
            $sms = new SMS();
            $sms->sendSMS(
                $upline['phone'],
                "You've earned KES {$amount} as an upline bonus from {$fromUser['name']}. Login to view your earnings."
            );
        }
        
        // Add to notifications center
        self::addNotification($uplineId, 'Upline Bonus Earned', "You've earned KES {$amount} as an upline bonus from {$fromUser['name']}.");
    }


    public static function sendWithdrawalRequestNotification($userId, $amount, $withdrawalId) {
        $db = (new Database())->connect();
        $user = getUserById($userId);
        
        if (!$user) return false;
        
        // Send email
        $email = new Email();
        $email->sendEmail(
            $user['email'],
            'Withdrawal Request Submitted',
            'withdrawal_request',
            [
                'name' => $user['name'],
                'amount' => $amount,
                'withdrawal_id' => $withdrawalId
            ]
        );
        
        // Send SMS
        if ($user['phone_verified']) {
            $sms = new SMS();
            $sms->sendSMS(
                $user['phone'],
                "Your withdrawal request of KES {$amount} has been submitted. Reference: WD{$withdrawalId}. You will be notified once processed."
            );
        }
        
        // Add to notifications center
        self::addNotification($userId, 'Withdrawal Request Submitted', "Your withdrawal request of KES {$amount} has been submitted and is being processed. Reference: WD{$withdrawalId}");
        
        // Notify admin
        self::notifyAdmin("New Withdrawal Request", "User {$user['name']} has requested a withdrawal of KES {$amount}. Reference: WD{$withdrawalId}");
    }


    public static function sendWithdrawalProcessingNotification($userId, $amount, $withdrawalId) {
        $db = (new Database())->connect();
        $user = getUserById($userId);
        
        if (!$user) return false;
        
        // Send email
        $email = new Email();
        $email->sendEmail(
            $user['email'],
            'Withdrawal Being Processed',
            'withdrawal_processing',
            [
                'name' => $user['name'],
                'amount' => $amount,
                'withdrawal_id' => $withdrawalId
            ]
        );
        
        // Send SMS
        if ($user['phone_verified']) {
            $sms = new SMS();
            $sms->sendSMS(
                $user['phone'],
                "Your withdrawal of KES {$amount} (Ref: WD{$withdrawalId}) is now being processed. You'll receive the money shortly."
            );
        }
        
        // Add to notifications center
        self::addNotification($userId, 'Withdrawal Processing', "Your withdrawal of KES {$amount} is being processed. Reference: WD{$withdrawalId}");
    }

    
    public static function sendWithdrawalCompletedNotification($userId, $amount, $withdrawalId, $mpesaReceipt = null) {
        $db = (new Database())->connect();
        $user = getUserById($userId);
        
        if (!$user) return false;
        
        // Send email
        $email = new Email();
        $email->sendEmail(
            $user['email'],
            'Withdrawal Completed Successfully',
            'withdrawal_completed',
            [
                'name' => $user['name'],
                'amount' => $amount,
                'withdrawal_id' => $withdrawalId,
                'mpesa_receipt' => $mpesaReceipt
            ]
        );
        
        // Send SMS
        if ($user['phone_verified']) {
            $sms = new SMS();
            $message = "Your withdrawal of KES {$amount} has been completed successfully. Reference: WD{$withdrawalId}";
            if ($mpesaReceipt) {
                $message .= ". M-Pesa Code: {$mpesaReceipt}";
            }
            $sms->sendSMS($user['phone'], $message);
        }
        
        // Add to notifications center
        $notificationMessage = "Your withdrawal of KES {$amount} has been completed successfully. Reference: WD{$withdrawalId}";
        if ($mpesaReceipt) {
            $notificationMessage .= ". M-Pesa Receipt: {$mpesaReceipt}";
        }
        self::addNotification($userId, 'Withdrawal Completed', $notificationMessage);
    }

    public static function sendWithdrawalFailedNotification($userId, $amount, $withdrawalId, $reason = null) {
        $db = (new Database())->connect();
        $user = getUserById($userId);
        
        if (!$user) return false;
        
        // Send email
        $email = new Email();
        $email->sendEmail(
            $user['email'],
            'Withdrawal Failed',
            'withdrawal_failed',
            [
                'name' => $user['name'],
                'amount' => $amount,
                'withdrawal_id' => $withdrawalId,
                'reason' => $reason
            ]
        );
        
        // Send SMS
        if ($user['phone_verified']) {
            $sms = new SMS();
            $message = "Your withdrawal of KES {$amount} (Ref: WD{$withdrawalId}) has failed";
            if ($reason) {
                $message .= ": {$reason}";
            }
            $message .= ". Your balance has been restored. Contact support for assistance.";
            $sms->sendSMS($user['phone'], $message);
        }
        
        // Add to notifications center
        $notificationMessage = "Your withdrawal of KES {$amount} has failed";
        if ($reason) {
            $notificationMessage .= ": {$reason}";
        }
        $notificationMessage .= ". Your balance has been restored. Reference: WD{$withdrawalId}";
        self::addNotification($userId, 'Withdrawal Failed', $notificationMessage);
    }

   
    public static function sendTransferSentNotification($senderId, $receiverId, $amount, $transferId) {
        $db = (new Database())->connect();
        $sender = getUserById($senderId);
        $receiver = getUserById($receiverId);
        
        if (!$sender || !$receiver) return false;
        
        // Notify sender
        $email = new Email();
        $email->sendEmail(
            $sender['email'],
            'Money Transfer Sent',
            'transfer_sent',
            [
                'name' => $sender['name'],
                'receiver_name' => $receiver['name'],
                'amount' => $amount,
                'transfer_id' => $transferId
            ]
        );
        
        if ($sender['phone_verified']) {
            $sms = new SMS();
            $sms->sendSMS(
                $sender['phone'],
                "You have successfully sent KES {$amount} to {$receiver['name']}. Reference: TR{$transferId}"
            );
        }
        
        self::addNotification($senderId, 'Transfer Sent', "You have sent KES {$amount} to {$receiver['name']}. Reference: TR{$transferId}");
    }

    
    public static function sendTransferReceivedNotification($senderId, $receiverId, $amount, $transferId) {
        $db = (new Database())->connect();
        $sender = getUserById($senderId);
        $receiver = getUserById($receiverId);
        
        if (!$sender || !$receiver) return false;
        
        // Notify receiver
        $email = new Email();
        $email->sendEmail(
            $receiver['email'],
            'Money Received',
            'transfer_received',
            [
                'name' => $receiver['name'],
                'sender_name' => $sender['name'],
                'amount' => $amount,
                'transfer_id' => $transferId
            ]
        );
        
        if ($receiver['phone_verified']) {
            $sms = new SMS();
            $sms->sendSMS(
                $receiver['phone'],
                "You have received KES {$amount} from {$sender['name']}. Reference: TR{$transferId}"
            );
        }
        
        self::addNotification($receiverId, 'Money Received', "You have received KES {$amount} from {$sender['name']}. Reference: TR{$transferId}");
    }

    
    public static function sendTransferFailedNotification($senderId, $receiverId, $amount, $transferId, $reason = null) {
        $db = (new Database())->connect();
        $sender = getUserById($senderId);
        $receiver = getUserById($receiverId);
        
        if (!$sender) return false;
        
        $email = new Email();
        $email->sendEmail(
            $sender['email'],
            'Transfer Failed',
            'transfer_failed',
            [
                'name' => $sender['name'],
                'receiver_name' => $receiver ? $receiver['name'] : 'Unknown User',
                'amount' => $amount,
                'transfer_id' => $transferId,
                'reason' => $reason
            ]
        );
        
        if ($sender['phone_verified']) {
            $sms = new SMS();
            $message = "Your transfer of KES {$amount} has failed";
            if ($reason) {
                $message .= ": {$reason}";
            }
            $message .= ". Your balance has been restored. Reference: TR{$transferId}";
            $sms->sendSMS($sender['phone'], $message);
        }
        
        $notificationMessage = "Your transfer of KES {$amount} has failed";
        if ($reason) {
            $notificationMessage .= ": {$reason}";
        }
        $notificationMessage .= ". Your balance has been restored. Reference: TR{$transferId}";
        self::addNotification($senderId, 'Transfer Failed', $notificationMessage);
    }

    
    public static function sendLowBalanceNotification($userId, $currentBalance, $threshold = 100) {
        $db = (new Database())->connect();
        $user = getUserById($userId);
        
        if (!$user || $currentBalance > $threshold) return false;
        
        // Check if we already sent this notification recently (within 24 hours)
        $stmt = $db->prepare("SELECT id FROM notifications WHERE user_id = ? AND title = 'Low Balance Alert' AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $stmt->execute([$userId]);
        if ($stmt->fetch()) return false; // Already notified recently
        
        // Send email
        $email = new Email();
        $email->sendEmail(
            $user['email'],
            'Low Balance Alert',
            'low_balance',
            [
                'name' => $user['name'],
                'balance' => $currentBalance
            ]
        );
        
        // Add to notifications center
        self::addNotification($userId, 'Low Balance Alert', "Your account balance is running low (KES {$currentBalance}). Consider adding more funds or earning through referrals.");
    }

    
    public static function notifyAdmin($subject, $message) {
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT email FROM admins WHERE status = 'active'");
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $email = new Email();
        foreach ($admins as $admin) {
            $email->sendEmail(
                $admin['email'],
                $subject,
                'admin_alert',
                ['message' => $message]
            );
        }
    }

    
    public static function addNotification($userId, $title, $message) {
        $db = (new Database())->connect();
        $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
        return $stmt->execute([$userId, $title, $message]);
    }

    
    public static function logAdminNotification($adminId, $title, $message) {
        $db = (new Database())->connect();
        
        try {
            $stmt = $db->prepare("INSERT INTO admin_notifications (admin_id, title, message, created_at) VALUES (?, ?, ?, NOW())");
            return $stmt->execute([$adminId, $title, $message]);
        } catch (PDOException $e) {
            
            return true;
        }
    }

    
    public static function getUserNotifications($userId, $limit = 10) {
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public static function markAsRead($notificationId, $userId) {
        $db = (new Database())->connect();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        return $stmt->execute([$notificationId, $userId]);
    }

    
    public static function getUnreadCount($userId) {
        $db = (new Database())->connect();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
}

?>
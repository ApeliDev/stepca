<?php

/**
 * Notification Management Class
 * Handles all notification-related operations
 */
class NotificationManager {
    private $conn;
    private $user_id;
    
    public function __construct($database_connection, $user_id = null) {
        $this->conn = $database_connection;
        $this->user_id = $user_id;
    }
    
    /**
     * Get all notifications for a specific user
     */
    public function getUserNotifications($user_id = null, $limit = null, $unread_only = false) {
        $user_id = $user_id ?? $this->user_id;
        
        $sql = "SELECT * FROM notifications WHERE user_id = ?";
        $params = [$user_id];
        
        if ($unread_only) {
            $sql .= " AND is_read = 0";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get notification by ID
     */
    public function getNotificationById($notification_id, $user_id = null) {
        $user_id = $user_id ?? $this->user_id;
        
        $stmt = $this->conn->prepare("SELECT * FROM notifications WHERE id = ? AND user_id = ?");
        $stmt->execute([$notification_id, $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Mark a single notification as read
     */
    public function markAsRead($notification_id, $user_id = null) {
        $user_id = $user_id ?? $this->user_id;
        
        try {
            $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
            $result = $stmt->execute([$notification_id, $user_id]);
            
            return [
                'success' => $result,
                'message' => $result ? 'Notification marked as read' : 'Failed to mark notification as read'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($user_id = null) {
        $user_id = $user_id ?? $this->user_id;
        
        try {
            $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
            $result = $stmt->execute([$user_id]);
            
            return [
                'success' => $result,
                'message' => $result ? 'All notifications marked as read' : 'No unread notifications found'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Create a new notification
     */
    public function createNotification($user_id, $title, $message, $type = 'info') {
        try {
            $stmt = $this->conn->prepare("INSERT INTO notifications (user_id, title, message, type, created_at) VALUES (?, ?, ?, ?, NOW())");
            $result = $stmt->execute([$user_id, $title, $message, $type]);
            
            return [
                'success' => $result,
                'message' => $result ? 'Notification created successfully' : 'Failed to create notification',
                'id' => $result ? $this->conn->lastInsertId() : null
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete a notification
     */
    public function deleteNotification($notification_id, $user_id = null) {
        $user_id = $user_id ?? $this->user_id;
        
        try {
            $stmt = $this->conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
            $result = $stmt->execute([$notification_id, $user_id]);
            
            return [
                'success' => $result,
                'message' => $result ? 'Notification deleted successfully' : 'Failed to delete notification'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get unread notification count
     */
    public function getUnreadCount($user_id = null) {
        $user_id = $user_id ?? $this->user_id;
        
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int) $result['count'];
    }
    
    /**
     * Get notification icon based on type/title
     */
    public function getNotificationIcon($notification) {
        $title = strtolower($notification['title']);
        $type = isset($notification['type']) ? strtolower($notification['type']) : '';
        
        if (strpos($title, 'payment') !== false || strpos($title, 'withdrawal') !== false || $type === 'payment') {
            return 'fas fa-money-bill-wave';
        } elseif (strpos($title, 'referral') !== false || $type === 'referral') {
            return 'fas fa-users';
        } elseif (strpos($title, 'welcome') !== false || $type === 'welcome') {
            return 'fas fa-hand-wave';
        } elseif ($type === 'warning') {
            return 'fas fa-exclamation-triangle';
        } elseif ($type === 'success') {
            return 'fas fa-check-circle';
        } elseif ($type === 'error') {
            return 'fas fa-times-circle';
        } else {
            return 'fas fa-info-circle';
        }
    }
    
    /**
     * Format notification date for display
     */
    public function formatNotificationDate($datetime) {
        return date('M j, Y H:i', strtotime($datetime));
    }
    
    /**
     * Check if notification has pending status
     */
    public function isPendingNotification($notification) {
        $message = strtolower($notification['message']);
        return (strpos($message, 'withdrawal') !== false && strpos($message, 'completed') === false);
    }
    
    /**
     * Handle AJAX requests (combined handler functionality)
     */
    public function handleRequest() {
        // Check if this is a POST request and user is logged in
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->user_id) {
            return [
                'success' => false,
                'message' => 'Invalid request'
            ];
        }
        
        // Get the action from POST data
        $action = $_POST['action'] ?? '';
        
        try {
            switch ($action) {
                case 'mark_as_read':
                    $notification_id = $_POST['notification_id'] ?? null;
                    
                    if (!$notification_id) {
                        return ['success' => false, 'message' => 'Notification ID is required'];
                    }
                    
                    return $this->markAsRead($notification_id);
                    
                case 'mark_all_as_read':
                    return $this->markAllAsRead();
                    
                case 'get_notification':
                    $notification_id = $_POST['notification_id'] ?? null;
                    
                    if (!$notification_id) {
                        return ['success' => false, 'message' => 'Notification ID is required'];
                    }
                    
                    $notification = $this->getNotificationById($notification_id);
                    
                    if ($notification) {
                        // Mark as read when viewed
                        $this->markAsRead($notification_id);
                        
                        return [
                            'success' => true,
                            'notification' => [
                                'id' => $notification['id'],
                                'title' => $notification['title'],
                                'message' => $notification['message'],
                                'created_at' => $notification['created_at'],
                                'formatted_date' => $this->formatNotificationDate($notification['created_at']),
                                'is_read' => true, // Now marked as read
                                'icon' => $this->getNotificationIcon($notification),
                                'is_pending' => $this->isPendingNotification($notification)
                            ]
                        ];
                    } else {
                        return ['success' => false, 'message' => 'Notification not found'];
                    }
                    
                case 'delete_notification':
                    $notification_id = $_POST['notification_id'] ?? null;
                    
                    if (!$notification_id) {
                        return ['success' => false, 'message' => 'Notification ID is required'];
                    }
                    
                    return $this->deleteNotification($notification_id);
                    
                case 'get_unread_count':
                    $count = $this->getUnreadCount();
                    return ['success' => true, 'count' => $count];
                    
                default:
                    return ['success' => false, 'message' => 'Invalid action'];
            }
            
        } catch (Exception $e) {
            error_log('Notification Handler Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while processing your request'];
        }
    }
}

<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'usercontrollers/NotificationManager.php';

$db = new Database();
$conn = $db->connect();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user has paid registration fee
if ($user['is_active'] == 0 && basename($_SERVER['PHP_SELF']) != 'payment.php') {
    header('Location: payment.php');
    exit;
}

// Initialize notification manager
$notificationManager = new NotificationManager($conn, $user_id);

// Handle POST requests for notification actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => 'Invalid action'];
    
    switch ($action) {
        case 'mark_all_as_read':
            $response = $notificationManager->markAllAsRead();
            break;
        case 'mark_as_read':
            $notification_id = $_POST['notification_id'] ?? null;
            if ($notification_id) {
                $response = $notificationManager->markAsRead($notification_id);
            }
            break;
    }
    
    // Redirect with message to prevent form resubmission
    $_SESSION['notification_message'] = $response['message'];
    $_SESSION['notification_type'] = $response['success'] ? 'success' : 'error';
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Get all notifications for the user
$notifications = $notificationManager->getUserNotifications();
$unreadCount = $notificationManager->getUnreadCount();

// Get any flash messages
$flash_message = $_SESSION['notification_message'] ?? null;
$flash_type = $_SESSION['notification_type'] ?? null;
unset($_SESSION['notification_message'], $_SESSION['notification_type']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Notifications</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4CAF50',
                        primaryDark: '#45a049',
                        dark: '#0f0f23',
                        darker: '#1a1a2e',
                        darkest: '#16213e',
                        lightGray: '#9CA3AF',
                        lighterGray: '#D1D5DB',
                    },
                    animation: {
                        float: 'float 6s ease-in-out infinite',
                        slideIn: 'slideIn 0.3s ease-out',
                        pulse: 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        fadeIn: 'fadeIn 0.5s ease-out',
                        scaleIn: 'scaleIn 0.3s ease-out',
                        modalIn: 'modalIn 0.3s ease-out',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px) rotate(0deg)' },
                            '33%': { transform: 'translateY(-20px) rotate(5deg)' },
                            '66%': { transform: 'translateY(10px) rotate(-3deg)' },
                        },
                        slideIn: {
                            'from': { opacity: '0', transform: 'translateY(-10px)' },
                            'to': { opacity: '1', transform: 'translateY(0)' },
                        },
                        fadeIn: {
                            'from': { opacity: '0' },
                            'to': { opacity: '1' },
                        },
                        scaleIn: {
                            'from': { opacity: '0', transform: 'scale(0.9)' },
                            'to': { opacity: '1', transform: 'scale(1)' },
                        },
                        modalIn: {
                            'from': { opacity: '0', transform: 'scale(0.9) translateY(-10px)' },
                            'to': { opacity: '1', transform: 'scale(1) translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-dark via-darker to-darkest min-h-screen text-white">
    <!-- Animated Background -->
    <div class="fixed top-0 left-0 w-full h-full pointer-events-none z-0 overflow-hidden">
        <div class="absolute top-[10%] left-[10%] text-primary opacity-5 text-2xl animate-float"><i class="fas fa-coins"></i></div>
        <div class="absolute top-[20%] right-[10%] text-primary opacity-5 text-2xl animate-float" style="animation-delay: 1s;"><i class="fas fa-chart-line"></i></div>
        <div class="absolute bottom-[30%] left-[15%] text-primary opacity-5 text-2xl animate-float" style="animation-delay: 2s;"><i class="fas fa-dollar-sign"></i></div>
        <div class="absolute bottom-[10%] right-[20%] text-primary opacity-5 text-2xl animate-float" style="animation-delay: 3s;"><i class="fas fa-piggy-bank"></i></div>
    </div>

    <div class="flex h-screen relative z-10">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-screen">
            <!-- Top Navigation -->
            <?php include 'includes/header.php'; ?>

            <!-- Main Content Area -->
            <main class="flex-1 p-4 lg:p-6 overflow-y-auto">
                
                <!-- Flash Message -->
                <?php if ($flash_message): ?>
                <div id="flash-message" class="mb-6 p-4 rounded-lg border animate-slideIn <?php echo $flash_type === 'success' ? 'bg-green-500/20 border-green-500/30 text-green-400' : 'bg-red-500/20 border-red-500/30 text-red-400'; ?>">
                    <div class="flex items-center">
                        <i class="<?php echo $flash_type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'; ?> mr-2"></i>
                        <span><?php echo htmlspecialchars($flash_message); ?></span>
                        <button onclick="this.parentElement.parentElement.style.display='none'" class="ml-auto text-current hover:opacity-75">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <?php endif; ?>
               
                <!-- Notifications Section -->
                <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 overflow-hidden animate-fadeIn">
                    <div class="p-6 border-b border-primary/20">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <h3 class="text-xl font-semibold text-white flex items-center text-base sm:text-xl">
                                <i class="fas fa-bell mr-2 sm:mr-3 text-primary text-base sm:text-xl"></i>
                                <span class="truncate">Notifications</span>
                                <?php if ($unreadCount > 0): ?>
                                <span class="ml-2 bg-primary text-white text-xs px-2 py-0.5 sm:px-2 sm:py-1 rounded-full"><?php echo $unreadCount; ?></span>
                                <?php endif; ?>
                            </h3>
                            
                            <?php if ($unreadCount > 0): ?>
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="mark_all_as_read">
                                <button type="submit" class="inline-flex items-center px-2 py-1 sm:px-4 sm:py-2 bg-gradient-to-r from-primary to-primaryDark text-white text-xs sm:text-sm font-medium rounded-lg hover:shadow-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary/50">
                                    <i class="fas fa-check-double mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                                    <span class="hidden xs:inline">Mark all as read</span>
                                    <span class="inline xs:hidden">Mark all</span>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="overflow-hidden">
                        <div class="divide-y divide-primary/10">
                            <?php if (count($notifications) > 0): ?>
                                <?php foreach ($notifications as $notification): ?>
                                <div class="<?php echo $notification['is_read'] ? 'bg-transparent' : 'bg-primary/5'; ?> hover:bg-primary/10 transition-all duration-300 cursor-pointer animate-slideIn group notification-item" 
                                     data-notification-id="<?php echo $notification['id']; ?>">
                                    <div class="px-6 py-4">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between mb-2">
                                                    <p class="text-sm font-semibold text-white truncate flex items-center">
                                                        <i class="<?php echo $notificationManager->getNotificationIcon($notification); ?> text-primary mr-2 text-xs"></i>
                                                        <?php echo htmlspecialchars($notification['title']); ?>
                                                    </p>
                                                    <div class="flex items-center ml-4 flex-shrink-0">
                                                        <p class="text-xs text-lightGray">
                                                            <time datetime="<?php echo $notification['created_at']; ?>">
                                                                <?php echo $notificationManager->formatNotificationDate($notification['created_at']); ?>
                                                            </time>
                                                        </p>
                                                        <?php if (!$notification['is_read']): ?>
                                                        <span class="ml-3 inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-primary to-primaryDark text-white animate-pulse">
                                                            <i class="fas fa-circle text-xs mr-1"></i>
                                                            New
                                                        </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="mt-1">
                                                    <p class="text-sm text-lightGray leading-relaxed line-clamp-2">
                                                        <?php echo htmlspecialchars($notification['message']); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Click indicator and actions -->
                                        <div class="mt-2 flex items-center justify-between">
                                            <p class="text-xs text-lightGray/60 group-hover:text-primary transition-colors">
                                                <i class="fas fa-mouse-pointer mr-1"></i>
                                                Click to view details
                                            </p>
                                            <div class="flex items-center space-x-2">
                                                <?php if ($notificationManager->isPendingNotification($notification)): ?>
                                                <span class="inline-flex items-center px-2 py-1 bg-yellow-500/20 text-yellow-400 text-xs font-medium rounded-lg">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    Pending
                                                </span>
                                                <?php endif; ?>
                                                
                                                <?php if (!$notification['is_read']): ?>
                                                <form method="POST" class="inline" onclick="event.stopPropagation();">
                                                    <input type="hidden" name="action" value="mark_as_read">
                                                    <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                    <button type="submit" class="text-xs text-primary hover:text-primaryDark transition-colors">
                                                        <i class="fas fa-check mr-1"></i>
                                                        Mark as read
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <div class="px-6 py-12 text-center animate-fadeIn">
                                <div class="flex flex-col items-center">
                                    <div class="bg-primary/10 p-4 rounded-full mb-4">
                                        <i class="fas fa-bell-slash text-primary text-2xl"></i>
                                    </div>
                                    <p class="text-lightGray text-sm">You don't have any notifications yet.</p>
                                    <p class="text-lightGray/60 text-xs mt-1">We'll notify you about important updates here.</p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Notification Modal -->
    <div id="notification-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="modal-content bg-gradient-to-br from-darker to-darkest rounded-2xl border border-primary/20 shadow-2xl max-w-md w-full mx-4 transform scale-90 opacity-0 transition-all duration-300 ease-out">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-primary/20">
                <div class="flex items-center">
                    <div class="bg-primary/10 p-2 rounded-full mr-3">
                        <i id="modal-icon" class="fas fa-info-circle text-primary text-lg"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Notification Details</h3>
                </div>
                <button type="button" class="close-modal-btn text-lightGray hover:text-white transition-colors duration-200 p-1">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6">
                <div class="mb-4">
                    <h4 id="modal-title" class="text-white font-semibold mb-2 flex items-center">
                        <!-- Title will be inserted here -->
                    </h4>
                    <p id="modal-date" class="text-sm text-lightGray mb-3">
                        <!-- Date will be inserted here -->
                    </p>
                    <span id="modal-new-badge" class="hidden inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-primary to-primaryDark text-white mb-3">
                        <i class="fas fa-circle text-xs mr-1"></i>New
                    </span>
                </div>
                
                <div class="bg-primary/5 rounded-xl p-4 border border-primary/10">
                    <p id="modal-message" class="text-sm text-white leading-relaxed">
                        <!-- Message will be inserted here -->
                    </p>
                </div>
                
                <div id="modal-actions" class="hidden mt-4 flex space-x-2">
                    <!-- Action buttons will be inserted here for pending notifications -->
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="flex justify-end p-6 border-t border-primary/20">
                <button type="button" class="close-modal-btn inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary to-primaryDark text-white text-sm font-medium rounded-lg hover:shadow-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary/50">
                    <i class="fas fa-check mr-2"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script src="assets/js/notifications.js"></script>

</body>
</html>
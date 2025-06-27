<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
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

// Check if user has paid registration fee if not redirect to payment page
if ($user['is_active'] == 0 && basename($_SERVER['PHP_SELF']) != 'payment.php') {
    header('Location: payment.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Dashboard</title>
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
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-dark via-darker to-darkest min-h-screen text-white">
    <div class="flex h-screen relative z-10">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-screen">
            <!-- Top Navigation -->
            <?php include 'includes/header.php'; ?>

            <!-- Main Content Area -->
            <main class="flex-1 p-4 lg:p-6 overflow-y-auto">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Balance Card -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 hover:border-primary/40 transition-all duration-300 animate-scaleIn">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-lightGray text-sm font-medium">Available Balance</p>
                                <p class="text-3xl font-bold text-white mt-2">KES <?php echo number_format($user['balance'], 2); ?></p>
                                <p class="text-primary text-sm mt-1 flex items-center">
                                    <i class="fas fa-wallet mr-1"></i>
                                    Ready to withdraw
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-primary to-primaryDark p-4 rounded-xl">
                                <i class="fas fa-wallet text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Referral Bonus Card -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 hover:border-primary/40 transition-all duration-300 animate-scaleIn" style="animation-delay: 0.1s;">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-lightGray text-sm font-medium">Referral Bonus</p>
                                <p class="text-3xl font-bold text-white mt-2">KES <?php echo number_format($user['referral_bonus_balance'], 2); ?></p>
                                <p class="text-primary text-sm mt-1 flex items-center">
                                    <i class="fas fa-hand-holding-usd mr-1"></i>
                                    From referrals
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-green-500 to-green-600 p-4 rounded-xl">
                                <i class="fas fa-hand-holding-usd text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Total Withdrawals Card -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 hover:border-primary/40 transition-all duration-300 animate-scaleIn" style="animation-delay: 0.2s;">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-lightGray text-sm font-medium">Total Withdrawn</p>
                                <?php
                                $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM withdrawals WHERE user_id = ? AND status = 'completed'");
                                $stmt->execute([$user_id]);
                                $total_withdrawn = $stmt->fetchColumn();
                                ?>
                                <p class="text-3xl font-bold text-white mt-2">KES <?php echo number_format($total_withdrawn, 2); ?></p>
                                <p class="text-blue-400 text-sm mt-1 flex items-center">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Successfully withdrawn
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-4 rounded-xl">
                                <i class="fas fa-money-check-alt text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions & Referral Link -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Recent Transactions -->
                    <div class="lg:col-span-2 bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 overflow-hidden animate-fadeIn">
                        <div class="p-6 border-b border-primary/20">
                            <h3 class="text-xl font-semibold text-white flex items-center">
                                <i class="fas fa-history mr-3 text-primary"></i>
                                Recent Transactions
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-primary/5">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-primary/10">
                                    <?php
                                    // Get recent transactions (withdrawals and transfers)
                                    $stmt = $conn->prepare("
                                        (SELECT 'withdrawal' as type, amount, status, created_at, NULL as receiver_phone 
                                        FROM withdrawals 
                                        WHERE user_id = ? 
                                        ORDER BY created_at DESC 
                                        LIMIT 5)
                                        
                                        UNION ALL
                                        
                                        (SELECT 'transfer' as type, amount, status, created_at, receiver_phone 
                                        FROM transfers 
                                        WHERE sender_id = ? 
                                        ORDER BY created_at DESC 
                                        LIMIT 5)
                                        
                                        ORDER BY created_at DESC 
                                        LIMIT 5
                                    ");
                                    $stmt->execute([$user_id, $user_id]);
                                    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    if (count($transactions) > 0):
                                        foreach ($transactions as $txn):
                                    ?>
                                    <tr class="hover:bg-primary/5 transition-colors">
                                        <td class="px-6 py-4 text-sm text-lightGray"><?php echo date('M j, Y H:i', strtotime($txn['created_at'])); ?></td>
                                        <td class="px-6 py-4 text-sm text-white font-medium">
                                            <?php echo ucfirst($txn['type']); ?>
                                            <?php if ($txn['type'] == 'transfer' && $txn['receiver_phone']): ?>
                                            <br><span class="text-xs text-lightGray">to <?php echo htmlspecialchars($txn['receiver_phone']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-white">KES <?php echo number_format($txn['amount'], 2); ?></td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                                <?php echo $txn['status'] == 'completed' ? 'bg-green-500/20 text-green-400' : 
                                                    ($txn['status'] == 'failed' ? 'bg-red-500/20 text-red-400' : 'bg-yellow-500/20 text-yellow-400'); ?>">
                                                <?php echo ucfirst($txn['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php
                                        endforeach;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-lightGray">
                                            <i class="fas fa-receipt text-4xl mb-4 opacity-50"></i>
                                            <p>No transactions yet</p>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="space-y-6">
                        <!-- Referral Link Card -->
                        <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 animate-fadeIn" style="animation-delay: 0.1s;">
                            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                                <i class="fas fa-link mr-3 text-primary"></i>
                                Referral Link
                            </h3>
                            <div class="space-y-4">
                                <div class="flex">
                                    <input type="text" id="referral-link" class="flex-1 px-4 py-3 bg-gray-800/80 border border-gray-600/50 rounded-l-lg text-white text-sm focus:outline-none focus:border-primary" value="<?php echo BASE_URL; ?>/register?ref=<?php echo $user['referral_code']; ?>" readonly>
                                    <button onclick="copyReferralLink()" class="px-4 py-3 bg-gradient-to-r from-primary to-primaryDark text-white rounded-r-lg hover:shadow-lg transition-all">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                <p class="text-xs text-lightGray">Earn KES <?php echo REFERRAL_BONUS; ?> for each successful referral</p>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 animate-fadeIn" style="animation-delay: 0.2s;">
                            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                                <i class="fas fa-chart-pie mr-3 text-primary"></i>
                                Quick Stats
                            </h3>
                            <div class="space-y-4">
                                <?php
                                $stmt = $conn->prepare("SELECT COUNT(*) FROM referrals WHERE referrer_id = ?");
                                $stmt->execute([$user_id]);
                                $total_referrals = $stmt->fetchColumn();
                                
                                $stmt = $conn->prepare("SELECT COUNT(*) FROM referrals r JOIN users u ON r.referred_id = u.id WHERE r.referrer_id = ? AND u.is_active = 1");
                                $stmt->execute([$user_id]);
                                $active_referrals = $stmt->fetchColumn();
                                
                                $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) FROM referral_earnings WHERE user_id = ?");
                                $stmt->execute([$user_id]);
                                $total_earned = $stmt->fetchColumn();
                                ?>
                                <div class="flex justify-between items-center">
                                    <span class="text-lightGray text-sm">Total Referrals</span>
                                    <span class="text-white font-semibold"><?php echo $total_referrals; ?></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-lightGray text-sm">Active Referrals</span>
                                    <span class="text-primary font-semibold"><?php echo $active_referrals; ?></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-lightGray text-sm">Total Earned</span>
                                    <span class="text-green-400 font-semibold">KES <?php echo number_format($total_earned, 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Referral Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 text-center animate-scaleIn" style="animation-delay: 0.3s;">
                        <div class="bg-gradient-to-br from-blue-500 to-blue-600 w-16 h-16 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-users text-white text-2xl"></i>
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2"><?php echo $total_referrals; ?></h4>
                        <p class="text-lightGray text-sm">Total Referrals</p>
                    </div>

                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 text-center animate-scaleIn" style="animation-delay: 0.4s;">
                        <div class="bg-gradient-to-br from-green-500 to-green-600 w-16 h-16 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-hand-holding-usd text-white text-2xl"></i>
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2"><?php echo $active_referrals; ?></h4>
                        <p class="text-lightGray text-sm">Active Referrals</p>
                    </div>

                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 text-center animate-scaleIn" style="animation-delay: 0.5s;">
                        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 w-16 h-16 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-coins text-white text-2xl"></i>
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2">KES <?php echo number_format($total_earned, 2); ?></h4>
                        <p class="text-lightGray text-sm">Total Earned</p>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="assets/js/refarral-link.js"></script>

    <script>
        // Mobile menu toggle
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        mobileMenuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            sidebarOverlay.classList.toggle('hidden');
        });
        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        });

        document.addEventListener('DOMContentLoaded', function() {
            const profileButton = document.getElementById('profile-dropdown');
            const profileMenu = document.getElementById('profile-menu');
            
            profileButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (profileMenu.classList.contains('hidden')) {
                    // Calculate position
                    const rect = profileButton.getBoundingClientRect();
                    profileMenu.style.position = 'fixed';
                    profileMenu.style.top = (rect.bottom + 8) + 'px';
                    profileMenu.style.right = (window.innerWidth - rect.right) + 'px';
                    profileMenu.style.zIndex = '99999';
                    
                    // Append to body
                    document.body.appendChild(profileMenu);
                    profileMenu.classList.remove('hidden');
                } else {
                    profileMenu.classList.add('hidden');
                }
            });
            
            // Close when clicking outside
            document.addEventListener('click', function(e) {
                if (!profileButton.contains(e.target) && !profileMenu.contains(e.target)) {
                    profileMenu.classList.add('hidden');
                }
            });
        });
        
        // Add animation classes to elements
        document.querySelectorAll('.animate-fadeIn').forEach(el => {
            el.classList.add('animate-fadeIn');
        });
        document.querySelectorAll('.animate-scaleIn').forEach(el => {
            el.classList.add('animate-scaleIn');
        });
        document.querySelectorAll('.animate-float').forEach(el => {
            el.classList.add('animate-float');
        });
        document.querySelectorAll('.animate-slideIn').forEach(el => {
            el.classList.add('animate-slideIn');
        });
        document.querySelectorAll('.animate-pulse').forEach(el => {
            el.classList.add('animate-pulse');
        });
        
    </script>
</body>


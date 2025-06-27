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

// Get wallet balances
$total_balance = $user['balance'] + $user['referral_bonus_balance'];
$max_withdrawal = min($total_balance, $user['withdrawal_limit']);

// Get recent transactions
$stmt = $conn->prepare("
    (SELECT 'withdrawal' as type, amount, status, created_at, phone as destination 
    FROM withdrawals 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5)
    
    UNION ALL
    
    (SELECT 'transfer' as type, amount, status, created_at, receiver_phone as destination 
    FROM transfers 
    WHERE sender_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5)
    
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id, $user_id]);
$recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get full transaction history
$stmt = $conn->prepare("
    (SELECT 'withdrawal' as type, amount, status, created_at, phone as destination 
    FROM withdrawals 
    WHERE user_id = ?)
    
    UNION ALL
    
    (SELECT 'transfer' as type, amount, status, created_at, receiver_phone as destination 
    FROM transfers 
    WHERE sender_id = ?)
    
    ORDER BY created_at DESC 
    LIMIT 20
");
$stmt->execute([$user_id, $user_id]);
$fullHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total withdrawn
$stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM withdrawals WHERE user_id = ? AND status = 'completed'");
$stmt->execute([$user_id]);
$total_withdrawn = $stmt->fetchColumn();

// Get referral stats
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Wallet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/wallet.css">
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
                <!-- Wallet Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- Main Balance -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 animate-scaleIn">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-lightGray mb-1">Total Balance</p>
                                <h3 class="text-2xl font-bold">KES <?php echo number_format($total_balance, 2); ?></h3>
                            </div>
                            <div class="bg-primary/10 p-3 rounded-xl">
                                <i class="fas fa-wallet text-primary text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Available Balance -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 animate-scaleIn" style="animation-delay: 0.1s;">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-lightGray mb-1">Available Balance</p>
                                <h3 class="text-2xl font-bold">KES <?php echo number_format($user['balance'], 2); ?></h3>
                            </div>
                            <div class="bg-green-500/10 p-3 rounded-xl">
                                <i class="fas fa-coins text-green-500 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Referral Balance -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 animate-scaleIn" style="animation-delay: 0.2s;">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-lightGray mb-1">Referral Bonus</p>
                                <h3 class="text-2xl font-bold">KES <?php echo number_format($user['referral_bonus_balance'], 2); ?></h3>
                            </div>
                            <div class="bg-blue-500/10 p-3 rounded-xl">
                                <i class="fas fa-users text-blue-500 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- Withdraw Card -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 animate-slideIn">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-lg flex items-center">
                                <i class="fas fa-money-bill-wave mr-2 text-primary"></i>
                                Withdraw Funds
                            </h4>
                            <span class="text-xs bg-primary/10 text-primary px-2 py-1 rounded">MPesa</span>
                        </div>
                        <p class="text-sm text-lightGray mb-4">Withdraw to your M-Pesa account instantly</p>
                        <div class="flex justify-between items-center">
                            <span class="text-sm">Max: KES <?php echo number_format($max_withdrawal, 2); ?></span>
                            <a href="withdraw.php" class="px-4 py-2 bg-primary/10 text-primary rounded-lg hover:bg-primary/20 transition-colors text-sm font-medium">
                                Withdraw Now
                            </a>
                        </div>
                    </div>
                    
                    <!-- Transfer Card -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 animate-slideIn" style="animation-delay: 0.1s;">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-lg flex items-center">
                                <i class="fas fa-exchange-alt mr-2 text-primary"></i>
                                Transfer Funds
                            </h4>
                            <span class="text-xs bg-blue-500/10 text-blue-400 px-2 py-1 rounded">Users</span>
                        </div>
                        <p class="text-sm text-lightGray mb-4">Send money to other Stepcashier users</p>
                        <div class="flex justify-between items-center">
                            <span class="text-sm">Max: KES <?php echo number_format($user['balance'], 2); ?></span>
                            <a href="transfer.php" class="px-4 py-2 bg-blue-500/10 text-blue-400 rounded-lg hover:bg-blue-500/20 transition-colors text-sm font-medium">
                                Transfer Now
                            </a>
                        </div>
                    </div>
                    
                    <!-- Deposit Card -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 animate-slideIn" style="animation-delay: 0.2s;">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-lg flex items-center">
                                <i class="fas fa-plus-circle mr-2 text-primary"></i>
                                Add Funds
                            </h4>
                            <span class="text-xs bg-green-500/10 text-green-400 px-2 py-1 rounded">MPesa</span>
                        </div>
                        <p class="text-sm text-lightGray mb-4">Deposit money to your wallet via M-Pesa</p>
                        <div class="flex justify-between items-center">
                            <span class="text-sm">Min: KES 100</span>
                            <a href="deposit.php" class="px-4 py-2 bg-green-500/10 text-green-400 rounded-lg hover:bg-green-500/20 transition-colors text-sm font-medium">
                                Deposit Now
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 overflow-hidden animate-fadeIn">
                    <div class="p-6 border-b border-primary/20">
                        <h3 class="text-xl font-semibold text-white flex items-center">
                            <i class="fas fa-history mr-3 text-primary"></i>
                            Recent Transactions
                        </h3>
                    </div>
                    
                    <div class="p-6">
                        <?php if (count($recentTransactions) > 0): ?>
                            <div class="space-y-4">
                                <?php foreach ($recentTransactions as $transaction): ?>
                                <div class="flex items-center justify-between p-4 bg-darker/40 rounded-xl border border-primary/10 animate-slideIn">
                                    <div class="flex items-center">
                                        <div class="p-3 rounded-lg mr-4 
                                            <?php echo $transaction['type'] === 'withdrawal' ? 'bg-red-500/10 text-red-400' : 
                                               ($transaction['type'] === 'transfer' ? 'bg-blue-500/10 text-blue-400' : 
                                               'bg-primary/10 text-primary'); ?>">
                                            <i class="fas 
                                                <?php echo $transaction['type'] === 'withdrawal' ? 'fa-money-bill-wave' : 
                                                   ($transaction['type'] === 'transfer' ? 'fa-exchange-alt' : 
                                                   'fa-wallet'); ?>"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-medium"><?php echo ucfirst($transaction['type']); ?></h4>
                                            <p class="text-xs text-lightGray">
                                                <?php echo date('M j, Y', strtotime($transaction['created_at'])); ?>
                                                <?php if ($transaction['destination']): ?>
                                                    • To: <?php echo htmlspecialchars($transaction['destination']); ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold <?php echo $transaction['type'] === 'withdrawal' ? 'text-red-400' : 'text-white'; ?>">
                                            <?php echo $transaction['type'] === 'withdrawal' ? '-' : ''; ?>KES <?php echo number_format($transaction['amount'], 2); ?>
                                        </p>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                            <?php echo $transaction['status'] == 'completed' ? 'bg-green-500/20 text-green-400' : 
                                               ($transaction['status'] == 'failed' ? 'bg-red-500/20 text-red-400' : 
                                               'bg-yellow-500/20 text-yellow-400'); ?>">
                                            <?php echo ucfirst($transaction['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="mt-6 text-center">
                                <a href="transactions.php" class="inline-flex items-center text-primary hover:text-primaryDark text-sm font-medium">
                                    View Full Transaction History
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="py-8 text-center animate-fadeIn">
                                <div class="bg-primary/10 p-4 rounded-full inline-block mb-4">
                                    <i class="fas fa-exchange-alt text-primary text-2xl"></i>
                                </div>
                                <p class="text-lightGray">No transactions yet</p>
                                <p class="text-lightGray/60 text-sm mt-1">Your transaction history will appear here</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Referral Stats -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
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

    <script src="assets/js/wallet.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'usercontrollers/Withdrawal.php';

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

$withdrawalService = new Withdrawal();
$total_balance = $withdrawalService->getTotalBalance($user);
$max_withdrawal = $withdrawalService->getMaxWithdrawalAmount($user);
$withdrawals = $withdrawalService->getWithdrawalHistory($user_id);

// Process withdrawal if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
    $amount = floatval($_POST['amount']);
    $phone = $user['phone'];
    
    // Process withdrawal through the Withdrawal class
    $result = $withdrawalService->processWithdrawal($user_id, $amount, $phone);
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        header('Location: withdraw.php');
        exit;
    } else {
        $error_message = $result['message'];
    }
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
    <link rel="stylesheet" href="assets/css/withdraw.css">
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
                <!-- Withdraw Section -->
                <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 overflow-hidden animate-fadeIn">
                    <div class="p-6 border-b border-primary/20">
                        <h3 class="text-xl font-semibold text-white flex items-center">
                            <i class="fas fa-wallet mr-3 text-primary"></i>
                            Withdraw Funds
                        </h3>
                        <p class="mt-2 text-sm text-lightGray">Withdraw your earnings directly to your M-Pesa account</p>
                    </div>
                    
                    <div class="p-6">
                       <?php if (!$withdrawalService->hasSufficientBalance($user)): ?>

                        <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-4 mb-6 animate-slideIn">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-yellow-500/20 p-2 rounded-lg">
                                        <i class="fas fa-exclamation-circle text-yellow-400 text-lg"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-sm font-semibold text-yellow-400">Insufficient Balance</h3>
                                    <div class="mt-2 text-sm text-yellow-300/80">
                                        <p>You need at least KES 100 to make a withdrawal. Your current balance is <span class="font-semibold text-yellow-300">KES <?php echo number_format($total_balance, 2); ?></span>.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        
                        <!-- Balance Display -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mb-8">
                            <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 hover:border-primary/40 transition-all duration-300 animate-scaleIn">
                                <div class="flex items-center">
                                    <div class="bg-gradient-to-br from-green-500 to-green-600 p-4 rounded-xl">
                                        <i class="fas fa-coins text-white text-xl"></i>
                                    </div>
                                    <div class="ml-5 flex-1">
                                        <dt class="text-sm font-medium text-lightGray">Available Balance</dt>
                                        <dd class="flex items-baseline mt-1">
                                            <div class="text-2xl font-bold text-white">KES <?php echo number_format($total_balance, 2); ?></div>
                                        </dd>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 hover:border-primary/40 transition-all duration-300 animate-scaleIn" style="animation-delay: 0.1s;">
                                <div class="flex items-center">
                                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-4 rounded-xl">
                                        <i class="fas fa-hand-holding-usd text-white text-xl"></i>
                                    </div>
                                    <div class="ml-5 flex-1">
                                        <dt class="text-sm font-medium text-lightGray">Max Withdrawal</dt>
                                        <dd class="flex items-baseline mt-1">
                                            <div class="text-2xl font-bold text-white">KES <?php echo number_format($max_withdrawal, 2); ?></div>
                                        </dd>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Withdrawal Form -->
                        <div class="animate-slideIn" style="animation-delay: 0.2s;">
                            <form id="withdraw-form" method="POST" action="withdraw.php" class="space-y-6">
                                <?php if (isset($error_message)): ?>
                                <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 mb-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-red-500/20 p-2 rounded-lg">
                                                <i class="fas fa-exclamation-circle text-red-400 text-lg"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm text-red-300"><?php echo htmlspecialchars($error_message); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div>
                                    <label for="amount" class="block text-sm font-medium text-lightGray mb-3">Amount (KES)</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <span class="text-lightGray text-sm">KES</span>
                                        </div>
                                        <input type="number" name="amount" id="amount" 
                                            min="10" max="<?php echo $max_withdrawal; ?>" step="1" 
                                            class="w-full pl-16 pr-16 py-3 bg-gray-800/80 border border-gray-600/50 rounded-xl text-white text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all" 
                                            placeholder="100" required>
                                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                            <span class="text-lightGray text-sm">.00</span>
                                        </div>
                                    </div>
                                    <p class="mt-2 text-xs text-lightGray/70">Minimum: KES 50, Maximum: KES <?php echo number_format($max_withdrawal, 2); ?></p>
                                </div>
                                
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-lightGray mb-3">M-Pesa Phone Number</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <i class="fas fa-mobile-alt text-primary text-sm"></i>
                                        </div>
                                        <input type="tel" name="phone" id="phone" 
                                            class="w-full pl-12 pr-4 py-3 bg-gray-800/80 border border-gray-600/50 rounded-xl text-white text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all" 
                                            value="<?php echo htmlspecialchars($user['phone']); ?>" readonly required>
                                    </div>
                                    <p class="mt-2 text-xs text-lightGray/70">
                                        If you need to change your M-Pesa phone number, please update it on your <a href="profile.php" class="text-primary underline hover:text-primaryDark">profile</a>.
                                    </p>
                                </div>
                                
                                <div class="flex justify-end pt-4">
                                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-primary to-primaryDark text-white font-semibold rounded-xl hover:shadow-lg hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary/50">
                                        <i class="fas fa-paper-plane mr-2"></i>
                                        Withdraw Now
                                    </button>
                                </div>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Withdrawal History -->
                <div class="mt-6 bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 overflow-hidden animate-fadeIn" style="animation-delay: 0.3s;">
                    <div class="p-6 border-b border-primary/20">
                        <h3 class="text-xl font-semibold text-white flex items-center">
                            <i class="fas fa-history mr-3 text-primary"></i>
                            Withdrawal History
                        </h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-primary/5">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Phone</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-primary/10">
                                <?php if (count($withdrawals) > 0): ?>
                                    <?php foreach ($withdrawals as $index => $withdrawalRecord): ?>
                                    <tr class="hover:bg-primary/5 transition-colors animate-slideIn" style="animation-delay: <?php echo (0.4 + $index * 0.1); ?>s;">
                                        <td class="px-6 py-4 text-sm text-lightGray"><?php echo date('M j, Y H:i', strtotime($withdrawalRecord['created_at'])); ?></td>
                                        <td class="px-6 py-4 text-sm text-white font-semibold">KES <?php echo number_format($withdrawalRecord['amount'], 2); ?></td>
                                        <td class="px-6 py-4 text-sm text-lightGray"><?php echo htmlspecialchars($withdrawalRecord['phone']); ?></td>
                                        <td class="px-6 py-4">
                                            <?php echo $withdrawalService->getStatusBadge($withdrawalRecord['status'], $withdrawalRecord['mpesa_code']); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center">
                                            <div class="flex flex-col items-center animate-fadeIn">
                                                <div class="bg-primary/10 p-4 rounded-full mb-4">
                                                    <i class="fas fa-receipt text-primary text-2xl"></i>
                                                </div>
                                                <p class="text-lightGray text-sm">No withdrawals yet</p>
                                                <p class="text-lightGray/60 text-xs mt-1">Your withdrawal history will appear here</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="assets/js/withdraw.js"></script>
    <script src="assets/js/main.js"></script>

</body>


<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'usercontrollers/referral_controller.php';
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

// Initialize Referral class
$referral = new Referral();
$total_referrals = $referral->getTotalReferrals($user_id);
$active_referrals = $referral->getActiveReferrals($user_id);
$total_earned = $referral->getTotalEarned($user_id);
$referrals = $referral->getReferralNetwork($user_id);
$earnings = $referral->getEarningsHistory($user_id);
$referral_link = $referral->generateReferralLink($user);
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
                <!-- Referrals Section -->
                <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 overflow-hidden animate-fadeIn">
                    <div class="p-6 border-b border-primary/20">
                        <h3 class="text-xl font-semibold text-white flex items-center">
                            <i class="fas fa-users mr-3 text-primary"></i>
                            Your Referrals
                        </h3>
                        <p class="mt-2 text-sm text-lightGray">Earn KES <?php echo REFERRAL_BONUS; ?> for each active referral</p>
                    </div>
                    
                    <div class="p-6">
                        <!-- Referral Link -->
                        <div class="mb-8 animate-slideIn">
                            <label for="referral-link" class="block text-sm font-medium text-lightGray mb-3">Your Referral Link</label>
                            <div class="flex">
                                <input type="text" id="referral-link" 
                                    class="flex-1 px-4 py-3 bg-gray-800/80 border border-gray-600/50 rounded-l-xl text-white text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all" 
                                    value="<?php echo $referral_link; ?>" readonly>
                                <button onclick="copyReferralLink()" 
                                        class="px-6 py-3 bg-gradient-to-r from-primary to-primaryDark text-white font-medium rounded-r-xl hover:shadow-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary/50">
                                    <i class="fas fa-copy mr-2"></i>
                                    Copy
                                </button>
                            </div>
                        </div>
                        
                        <!-- Referral Stats -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3 mb-8">
                            <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 hover:border-primary/40 transition-all duration-300 animate-scaleIn">
                                <div class="flex items-center">
                                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-4 rounded-xl">
                                        <i class="fas fa-users text-white text-xl"></i>
                                    </div>
                                    <div class="ml-5 flex-1">
                                        <dt class="text-sm font-medium text-lightGray">Total Referrals</dt>
                                        <dd class="flex items-baseline mt-1">
                                            <div class="text-2xl font-bold text-white"><?php echo $total_referrals; ?></div>
                                        </dd>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 hover:border-primary/40 transition-all duration-300 animate-scaleIn" style="animation-delay: 0.1s;">
                                <div class="flex items-center">
                                    <div class="bg-gradient-to-br from-green-500 to-green-600 p-4 rounded-xl">
                                        <i class="fas fa-hand-holding-usd text-white text-xl"></i>
                                    </div>
                                    <div class="ml-5 flex-1">
                                        <dt class="text-sm font-medium text-lightGray">Active Referrals</dt>
                                        <dd class="flex items-baseline mt-1">
                                            <div class="text-2xl font-bold text-white"><?php echo $active_referrals; ?></div>
                                        </dd>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 hover:border-primary/40 transition-all duration-300 animate-scaleIn" style="animation-delay: 0.2s;">
                                <div class="flex items-center">
                                    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 p-4 rounded-xl">
                                        <i class="fas fa-coins text-white text-xl"></i>
                                    </div>
                                    <div class="ml-5 flex-1">
                                        <dt class="text-sm font-medium text-lightGray">Total Earned</dt>
                                        <dd class="flex items-baseline mt-1">
                                            <div class="text-2xl font-bold text-white">KES <?php echo number_format($total_earned, 2); ?></div>
                                        </dd>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Referral Network -->
                        <div class="animate-fadeIn" style="animation-delay: 0.3s;">
                            <h4 class="text-lg font-semibold text-white mb-4 flex items-center">
                                <i class="fas fa-network-wired mr-2 text-primary"></i>
                                Your Referral Network
                            </h4>
                            <div class="bg-darker/40 backdrop-blur-sm rounded-xl border border-primary/20 overflow-hidden">
                                <div class="divide-y divide-primary/10">
                                    <?php if (count($referrals) > 0): ?>
                                        <?php foreach ($referrals as $index => $ref): ?>
                                        <div class="p-6 hover:bg-primary/5 transition-all duration-300 animate-slideIn" style="animation-delay: <?php echo ($index * 0.1); ?>s;">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-12 w-12">
                                                        <img class="h-12 w-12 rounded-full border-2 border-primary/30" 
                                                            src="<?php echo $ref['profile_pic'] ? '../assets/images/profile/'.$ref['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($ref['name']).'&background=4CAF50&color=fff'; ?>" 
                                                            alt="<?php echo htmlspecialchars($ref['name']); ?>">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-semibold text-white"><?php echo htmlspecialchars($ref['name']); ?></div>
                                                        <div class="text-sm text-lightGray"><?php echo htmlspecialchars($ref['email']); ?></div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center space-x-3">
                                                    <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $ref['is_active'] ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400'; ?>">
                                                        <?php echo $ref['is_active'] ? 'Active' : 'Pending Payment'; ?>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                                                <div class="flex items-center text-lightGray">
                                                    <i class="fas fa-phone-alt mr-2 text-primary text-xs"></i>
                                                    <?php echo htmlspecialchars($ref['phone']); ?>
                                                </div>
                                                <div class="flex items-center text-lightGray">
                                                    <i class="fas fa-calendar-alt mr-2 text-primary text-xs"></i>
                                                    Joined <?php echo date('M j, Y', strtotime($ref['created_at'])); ?>
                                                </div>
                                                <div class="flex items-center text-lightGray">
                                                    <i class="fas fa-users mr-2 text-primary text-xs"></i>
                                                    <?php echo $ref['sub_referrals']; ?> sub-referrals (KES <?php echo number_format($ref['earned_from_sub'], 2); ?>)
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="p-8 text-center animate-fadeIn">
                                            <div class="flex flex-col items-center">
                                                <div class="bg-primary/10 p-4 rounded-full mb-4">
                                                    <i class="fas fa-user-plus text-primary text-2xl"></i>
                                                </div>
                                                <p class="text-lightGray text-sm">You haven't referred anyone yet.</p>
                                                <p class="text-lightGray/60 text-xs mt-1">Share your referral link to start earning!</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Referral Earnings History -->
                <div class="mt-6 bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 overflow-hidden animate-fadeIn" style="animation-delay: 0.4s;">
                    <div class="p-6 border-b border-primary/20">
                        <h3 class="text-xl font-semibold text-white flex items-center">
                            <i class="fas fa-chart-line mr-3 text-primary"></i>
                            Referral Earnings History
                        </h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-primary/5">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">From Referral</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-primary/10">
                                <?php if (count($earnings) > 0): ?>
                                    <?php foreach ($earnings as $earning): ?>
                                    <tr class="hover:bg-primary/5 transition-colors animate-slideIn">
                                        <td class="px-6 py-4 text-sm text-lightGray"><?php echo date('M j, Y H:i', strtotime($earning['created_at'])); ?></td>
                                        <td class="px-6 py-4 text-sm text-white font-semibold">KES <?php echo number_format($earning['amount'], 2); ?></td>
                                        <td class="px-6 py-4 text-sm text-lightGray"><?php echo htmlspecialchars($earning['referral_name']); ?></td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-500/20 text-green-400">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Credited
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center">
                                            <div class="flex flex-col items-center">
                                                <div class="bg-primary/10 p-4 rounded-full mb-4">
                                                    <i class="fas fa-coins text-primary text-2xl"></i>
                                                </div>
                                                <p class="text-lightGray text-sm">No referral earnings yet</p>
                                                <p class="text-lightGray/60 text-xs mt-1">Start referring friends to see your earnings here</p>
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

    <script src="assets/js/refarral.js"></script>
    <script src="assets/js/refarral-link.js"></script>
    <script src="assets/js/main.js"></script>
</body>


<?php 
include '../includes/admin_header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$user_id = intval($_GET['id']);

// Get user details
$stmt = $conn->prepare("
    SELECT u.*, 
           (SELECT COUNT(*) FROM referrals WHERE referrer_id = u.id) as referral_count,
           (SELECT COALESCE(SUM(amount), 0) FROM referral_earnings WHERE user_id = u.id) as referral_earnings,
           (SELECT COALESCE(SUM(amount), 0) FROM withdrawals WHERE user_id = u.id AND status = 'completed') as total_withdrawn
    FROM users u 
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: users.php');
    exit;
}
?>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">User Details</h3>
            <div class="flex space-x-2">
                <a href="credit-account.php?user_id=<?php echo $user['id']; ?>" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-primary hover:bg-primaryDark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    <i class="fas fa-plus-circle mr-1"></i> Credit
                </a>
                <a href="suspend-user.php?user_id=<?php echo $user['id']; ?>" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <i class="fas fa-user-slash mr-1"></i> <?php echo $user['is_active'] ? 'Suspend' : 'Unsuspend'; ?>
                </a>
            </div>
        </div>
    </div>
    <div class="px-4 py-5 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- User Profile -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-16 w-16">
                        <img class="h-16 w-16 rounded-full" src="<?php echo $user['profile_pic'] ? '../assets/images/profile/'.$user['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($user['name']).'&background=4CAF50&color=fff'; ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></h4>
                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($user['phone']); ?></p>
                    </div>
                </div>
                
                <div class="mt-4 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="text-sm font-medium">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $user['is_active'] ? 'Active' : 'Suspended'; ?>
                            </span>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Joined</p>
                        <p class="text-sm font-medium"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Referral Code</p>
                        <p class="text-sm font-medium"><?php echo htmlspecialchars($user['referral_code']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Last Login</p>
                        <p class="text-sm font-medium"><?php echo $user['last_login'] ? date('M j, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Account Balances -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Account Balances</h4>
                
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Main Balance</p>
                        <p class="text-xl font-bold">KES <?php echo number_format($user['balance'], 2); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Referral Bonus</p>
                        <p class="text-xl font-bold">KES <?php echo number_format($user['referral_bonus_balance'], 2); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Withdrawn</p>
                        <p class="text-xl font-bold">KES <?php echo number_format($user['total_withdrawn'], 2); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Withdrawal Limit</p>
                        <p class="text-xl font-bold">KES <?php echo number_format($user['withdrawal_limit'], 2); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Referral Stats -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Referral Stats</h4>
                
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Total Referrals</p>
                        <p class="text-xl font-bold"><?php echo $user['referral_count']; ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Earned from Referrals</p>
                        <p class="text-xl font-bold">KES <?php echo number_format($user['referral_earnings'], 2); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Referral Link</p>
                        <div class="flex">
                            <input type="text" id="referral-link" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-md border border-gray-300 shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" value="<?php echo SITE_URL; ?>/register.php?ref=<?php echo $user['referral_code']; ?>" readonly>
                            <button onclick="copyToClipboard('referral-link')" class="ml-2 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                Copy
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- User Activity Tabs -->
        <div class="mt-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button id="withdrawals-tab" class="border-primary text-primary whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Withdrawals
                    </button>
                    <button id="transfers-tab" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Transfers
                    </button>
                    <button id="referrals-tab" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Referrals
                    </button>
                    <button id="payments-tab" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Payments
                    </button>
                </nav>
            </div>
            
            <!-- Withdrawals Tab Content -->
            <div id="withdrawals-content" class="pt-4">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">MPESA Code</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $stmt = $conn->prepare("SELECT * FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                        $stmt->execute([$user_id]);
                        $withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (count($withdrawals) > 0):
                            foreach ($withdrawals as $withdrawal):
                        ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M j, Y H:i', strtotime($withdrawal['created_at'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">KES <?php echo number_format($withdrawal['amount'], 2); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($withdrawal['phone']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php echo $withdrawal['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                           ($withdrawal['status'] == 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                    <?php echo ucfirst($withdrawal['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $withdrawal['mpesa_code'] ? htmlspecialchars($withdrawal['mpesa_code']) : 'N/A'; ?></td>
                        </tr>
                        <?php
                            endforeach;
                        else:
                        ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No withdrawals yet</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <?php if (count($withdrawals) > 0): ?>
                <div class="mt-4 text-right">
                    <a href="processes-withdrawals.php?user_id=<?php echo $user_id; ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-primaryDark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        View All Withdrawals
                    </a>
                </div>
                <?php endif; ?>
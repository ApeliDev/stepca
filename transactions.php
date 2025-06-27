<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'usercontrollers/WalletController.php';

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

// Initialize wallet controller
$wallet = new WalletController();
$transactions = $wallet->getTransactionHistory($user_id, 50);
?>
<!DOCTYPE html>
<html lang="en">
<!-- Similar head section as wallet.php -->
<body>
    <!-- Similar header/sidebar structure -->
    
    <main class="flex-1 p-4 lg:p-6 overflow-y-auto">
        <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 overflow-hidden animate-fadeIn">
            <div class="p-6 border-b border-primary/20">
                <h3 class="text-xl font-semibold text-white flex items-center">
                    <i class="fas fa-history mr-3 text-primary"></i>
                    Transaction History
                </h3>
            </div>
            
            <div class="p-6">
                <?php if (count($transactions) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-primary/5">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Details</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-primary/10">
                                <?php foreach ($transactions as $transaction): ?>
                                <tr class="hover:bg-primary/5 transition-colors">
                                    <td class="px-6 py-4 text-sm text-lightGray">
                                        <?php echo date('M j, Y H:i', strtotime($transaction['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <?php echo $wallet->formatTransactionType($transaction['type']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-lightGray">
                                        <?php if ($transaction['destination']): ?>
                                            To: <?php echo htmlspecialchars($transaction['destination']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-semibold <?php echo $transaction['type'] === 'withdrawal' ? 'text-red-400' : 'text-white'; ?>">
                                        <?php echo $transaction['type'] === 'withdrawal' ? '-' : ''; ?>KES <?php echo number_format($transaction['amount'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php echo $wallet->getStatusBadge($transaction['status']); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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
    </main>
    
    <!-- Footer scripts -->
     <script src="assets/js/transactions.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
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

// Check if user has paid registration fee
if ($user['is_active'] == 0 && basename($_SERVER['PHP_SELF']) != 'payment.php') {
    header('Location: payment.php');
    exit;
}

// Get all user investments
$stmt = $conn->prepare("
    SELECT i.*, p.name as product_name 
    FROM investments i
    JOIN investment_products p ON i.product_id = p.id
    WHERE i.user_id = ?
    ORDER BY i.created_at DESC
");
$stmt->execute([$user_id]);
$investments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Same head section as investments.php -->
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
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-white">Investment History</h1>
                    <div class="flex space-x-2">
                        <a href="investments.php" class="px-4 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded-lg hover:shadow-lg transition-all">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Investments
                        </a>
                    </div>
                </div>

                <!-- Investment History Table -->
                <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 overflow-hidden animate-fadeIn">
                    <div class="p-6 border-b border-primary/20">
                        <h3 class="text-xl font-semibold text-white flex items-center">
                            <i class="fas fa-history mr-3 text-primary"></i>
                            All Investments
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-primary/5">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Start Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Maturity Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Return</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-primary/10">
                                <?php if (count($investments) > 0): ?>
                                    <?php foreach ($investments as $investment): 
                                        $return = $investment['expected_return_amount'] - $investment['amount'];
                                        $status_color = [
                                            'active' => 'text-blue-400',
                                            'matured' => 'text-green-400',
                                            'cancelled' => 'text-yellow-400',
                                            'withdrawn' => 'text-primary'
                                        ][$investment['status']];
                                    ?>
                                        <tr class="hover:bg-primary/5 transition-colors">
                                            <td class="px-6 py-4 text-sm text-white font-medium"><?php echo htmlspecialchars($investment['product_name']); ?></td>
                                            <td class="px-6 py-4 text-sm text-white">KES <?php echo number_format($investment['amount'], 2); ?></td>
                                            <td class="px-6 py-4">
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-<?php echo str_replace('text-', '', $status_color); ?>/20 <?php echo $status_color; ?>">
                                                    <?php echo ucfirst($investment['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-lightGray"><?php echo date('M j, Y', strtotime($investment['start_date'])); ?></td>
                                            <td class="px-6 py-4 text-sm text-lightGray"><?php echo date('M j, Y', strtotime($investment['maturity_date'])); ?></td>
                                            <td class="px-6 py-4 text-sm text-white">KES <?php echo number_format($return, 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-lightGray">
                                            <i class="fas fa-history text-4xl mb-4 opacity-50"></i>
                                            <p>No investment history yet</p>
                                            <a href="invest-products.php" class="mt-4 inline-block px-4 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded-lg hover:shadow-lg transition-all text-sm">
                                                Start Investing
                                            </a>
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

    <script src="assets/js/main.js"></script>
</body>
</html>
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

// Get user's exchange history
$stmt = $conn->prepare("
    SELECT * FROM orders 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Exchange</title>
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
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-white">Exchange History</h1>
                    <div class="flex space-x-2">
                        <a href="exchange-buy.php" class="px-4 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded-lg hover:shadow-lg transition-all">
                            <i class="fas fa-dollar-sign mr-2"></i> Buy USD
                        </a>
                        <a href="exchange-sell.php" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:shadow-lg transition-all">
                            <i class="fas fa-money-bill-wave mr-2"></i> Sell USD
                        </a>
                    </div>
                </div>

                <!-- Exchange History Table -->
                <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 overflow-hidden animate-fadeIn">
                    <div class="p-6 border-b border-primary/20">
                        <h3 class="text-xl font-semibold text-white flex items-center">
                            <i class="fas fa-history mr-3 text-primary"></i>
                            All Exchange Transactions
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-primary/5">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Rate</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-primary/10">
                                <?php if (count($orders) > 0): ?>
                                    <?php foreach ($orders as $order): 
                                        $type_color = $order['order_type'] == 'buy' ? 'text-green-400' : 'text-red-400';
                                        $status_color = [
                                            'pending' => 'text-yellow-400',
                                            'processing' => 'text-blue-400',
                                            'completed' => 'text-green-400',
                                            'failed' => 'text-red-400',
                                            'cancelled' => 'text-gray-400'
                                        ][$order['status']];
                                    ?>
                                        <tr class="hover:bg-primary/5 transition-colors">
                                            <td class="px-6 py-4 text-sm text-lightGray"><?php echo date('M j, Y H:i', strtotime($order['created_at'])); ?></td>
                                            <td class="px-6 py-4 text-sm font-medium <?php echo $type_color; ?>">
                                                <?php echo ucfirst($order['order_type']); ?> USD
                                            </td>
                                            <td class="px-6 py-4 text-sm text-white">$<?php echo number_format($order['amount'], 2); ?></td>
                                            <td class="px-6 py-4 text-sm text-white">KES <?php echo number_format($order['rate'], 2); ?></td>
                                            <td class="px-6 py-4 text-sm text-white">KES <?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td class="px-6 py-4">
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-<?php echo str_replace('text-', '', $status_color); ?>/20 <?php echo $status_color; ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-lightGray">
                                            <i class="fas fa-exchange-alt text-4xl mb-4 opacity-50"></i>
                                            <p>No exchange transactions yet</p>
                                            <div class="mt-4 flex space-x-2 justify-center">
                                                <a href="exchange-buy.php" class="px-4 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded-lg hover:shadow-lg transition-all text-sm">
                                                    Buy USD
                                                </a>
                                                <a href="exchange-sell.php" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:shadow-lg transition-all text-sm">
                                                    Sell USD
                                                </a>
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

    <script src="assets/js/main.js"></script>
</body>
</html>
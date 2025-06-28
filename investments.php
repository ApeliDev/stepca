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

// Get user's active investments
$stmt = $conn->prepare("
    SELECT i.*, p.name as product_name, p.expected_return_rate, p.return_period_days 
    FROM investments i
    JOIN investment_products p ON i.product_id = p.id
    WHERE i.user_id = ? AND i.status = 'active'
    ORDER BY i.maturity_date ASC
");
$stmt->execute([$user_id]);
$active_investments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get matured investments
$stmt = $conn->prepare("
    SELECT i.*, p.name as product_name 
    FROM investments i
    JOIN investment_products p ON i.product_id = p.id
    WHERE i.user_id = ? AND i.status = 'matured'
    ORDER BY i.maturity_date DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$matured_investments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total invested amount
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(amount), 0) as total_invested 
    FROM investments 
    WHERE user_id = ? AND status IN ('active', 'matured')
");
$stmt->execute([$user_id]);
$total_invested = $stmt->fetch(PDO::FETCH_ASSOC)['total_invested'];

// Get total expected returns
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(expected_return_amount - amount), 0) as total_projected_earnings 
    FROM investments 
    WHERE user_id = ? AND status = 'active'
");
$stmt->execute([$user_id]);
$total_projected_earnings = $stmt->fetch(PDO::FETCH_ASSOC)['total_projected_earnings'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - My Investments</title>
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
                    <h1 class="text-2xl font-bold text-white">My Investments</h1>
                    <a href="invest-products.php" class="px-4 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded-lg hover:shadow-lg transition-all">
                        <i class="fas fa-plus mr-2"></i> New Investment
                    </a>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Total Invested -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 hover:border-primary/40 transition-all duration-300 animate-scaleIn">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-lightGray text-sm font-medium">Total Invested</p>
                                <p class="text-3xl font-bold text-white mt-2">KES <?php echo number_format($total_invested, 2); ?></p>
                                <p class="text-primary text-sm mt-1 flex items-center">
                                    <i class="fas fa-coins mr-1"></i>
                                    Across all products
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 p-4 rounded-xl">
                                <i class="fas fa-piggy-bank text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Projected Earnings -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 hover:border-primary/40 transition-all duration-300 animate-scaleIn" style="animation-delay: 0.1s;">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-lightGray text-sm font-medium">Projected Earnings</p>
                                <p class="text-3xl font-bold text-white mt-2">KES <?php echo number_format($total_projected_earnings, 2); ?></p>
                                <p class="text-primary text-sm mt-1 flex items-center">
                                    <i class="fas fa-chart-line mr-1"></i>
                                    From active investments
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-green-500 to-green-600 p-4 rounded-xl">
                                <i class="fas fa-hand-holding-usd text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Active Investments -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 hover:border-primary/40 transition-all duration-300 animate-scaleIn" style="animation-delay: 0.2s;">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-lightGray text-sm font-medium">Active Investments</p>
                                <p class="text-3xl font-bold text-white mt-2"><?php echo count($active_investments); ?></p>
                                <p class="text-blue-400 text-sm mt-1 flex items-center">
                                    <i class="fas fa-clock mr-1"></i>
                                    Currently running
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-4 rounded-xl">
                                <i class="fas fa-chart-pie text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Investments Table -->
                <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 overflow-hidden animate-fadeIn mb-8">
                    <div class="p-6 border-b border-primary/20">
                        <h3 class="text-xl font-semibold text-white flex items-center">
                            <i class="fas fa-chart-line mr-3 text-primary"></i>
                            Active Investments
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-primary/5">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Rate</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Expected Return</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Maturity Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Days Left</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-primary/10">
                                <?php if (count($active_investments) > 0): ?>
                                    <?php foreach ($active_investments as $investment): 
                                        $days_left = (strtotime($investment['maturity_date']) - time()) / (60 * 60 * 24);
                                        $days_left = $days_left > 0 ? ceil($days_left) : 0;
                                    ?>
                                        <tr class="hover:bg-primary/5 transition-colors">
                                            <td class="px-6 py-4 text-sm text-white font-medium"><?php echo htmlspecialchars($investment['product_name']); ?></td>
                                            <td class="px-6 py-4 text-sm text-white">KES <?php echo number_format($investment['amount'], 2); ?></td>
                                            <td class="px-6 py-4 text-sm text-white"><?php echo $investment['expected_return_rate']; ?>%</td>
                                            <td class="px-6 py-4 text-sm text-white">KES <?php echo number_format($investment['expected_return_amount'], 2); ?></td>
                                            <td class="px-6 py-4 text-sm text-lightGray"><?php echo date('M j, Y', strtotime($investment['maturity_date'])); ?></td>
                                            <td class="px-6 py-4">
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-500/20 text-blue-400">
                                                    <?php echo $days_left; ?> days
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <button class="px-3 py-1 text-xs bg-red-500/20 text-red-400 rounded-full hover:bg-red-500/30 transition-colors">
                                                    Cancel
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center text-lightGray">
                                            <i class="fas fa-piggy-bank text-4xl mb-4 opacity-50"></i>
                                            <p>No active investments yet</p>
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

                <!-- Matured Investments -->
                <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 overflow-hidden animate-fadeIn" style="animation-delay: 0.1s;">
                    <div class="p-6 border-b border-primary/20">
                        <h3 class="text-xl font-semibold text-white flex items-center">
                            <i class="fas fa-check-circle mr-3 text-primary"></i>
                            Recently Matured Investments
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-primary/5">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Return</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Maturity Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-primary/10">
                                <?php if (count($matured_investments) > 0): ?>
                                    <?php foreach ($matured_investments as $investment): ?>
                                        <tr class="hover:bg-primary/5 transition-colors">
                                            <td class="px-6 py-4 text-sm text-white font-medium"><?php echo htmlspecialchars($investment['product_name']); ?></td>
                                            <td class="px-6 py-4 text-sm text-white">KES <?php echo number_format($investment['amount'], 2); ?></td>
                                            <td class="px-6 py-4 text-sm text-white">KES <?php echo number_format($investment['expected_return_amount'] - $investment['amount'], 2); ?></td>
                                            <td class="px-6 py-4 text-sm text-lightGray"><?php echo date('M j, Y', strtotime($investment['maturity_date'])); ?></td>
                                            <td class="px-6 py-4">
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-500/20 text-green-400">
                                                    Matured
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-lightGray">
                                            <i class="fas fa-check-circle text-4xl mb-4 opacity-50"></i>
                                            <p>No matured investments yet</p>
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
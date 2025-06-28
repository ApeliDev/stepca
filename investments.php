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

// Get investment products
$investment_products = [];
$stmt = $conn->prepare("SELECT * FROM investment_products WHERE is_active = 1");
$stmt->execute();
$investment_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's active investments
$user_investments = [];
$stmt = $conn->prepare("SELECT i.*, p.name as product_name FROM investments i 
                       JOIN investment_products p ON i.product_id = p.id 
                       WHERE i.user_id = ? AND i.status = 'active'");
$stmt->execute([$user_id]);
$user_investments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get exchange rates
$exchange_rates = [];
$stmt = $conn->prepare("SELECT * FROM exchange_rates WHERE is_active = 1 AND valid_to IS NULL OR valid_to > NOW()");
$stmt->execute();
$exchange_rates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get platform accounts
$platform_accounts = [];
$stmt = $conn->prepare("SELECT * FROM platform_accounts WHERE user_id = ?");
$stmt->execute([$user_id]);
$platform_accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent orders
$recent_orders = [];
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_investment'])) {
        $product_id = $_POST['product_id'];
        $amount = $_POST['amount'];
        $payout_method = $_POST['payout_method'];
        
        // Validate input
        if (empty($product_id) {
            $error = "Please select an investment product";
        } elseif (empty($amount) || !is_numeric($amount) || $amount <= 0) {
            $error = "Please enter a valid investment amount";
        } else {
            // Get product details
            $stmt = $conn->prepare("SELECT * FROM investment_products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                $error = "Invalid investment product selected";
            } elseif ($amount < $product['min_investment_amount']) {
                $error = "Minimum investment amount is KES " . number_format($product['min_investment_amount'], 2);
            } elseif (!empty($product['max_investment_amount']) && $amount > $product['max_investment_amount']) {
                $error = "Maximum investment amount is KES " . number_format($product['max_investment_amount'], 2);
            } else {
                // Calculate expected return
                $daily_rate = $product['expected_return_rate'] / 365;
                $return_amount = $amount * (1 + ($daily_rate * $product['return_period_days'] / 100));
                
                // Calculate dates
                $start_date = date('Y-m-d');
                $maturity_date = date('Y-m-d', strtotime("+{$product['return_period_days']} days"));
                
                // Create investment
                $stmt = $conn->prepare("INSERT INTO investments (user_id, product_id, amount, expected_return_amount, start_date, maturity_date, payout_method) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $product_id, $amount, $return_amount, $start_date, $maturity_date, $payout_method]);
                
                $success = "Investment created successfully!";
                header("Refresh:2");
            }
        }
    }
    
    if (isset($_POST['create_order'])) {
        $order_type = $_POST['order_type'];
        $currency_pair = $_POST['currency_pair'];
        $amount = $_POST['amount'];
        $payment_method = $_POST['payment_method'];
        
        // Validate input
        if (empty($currency_pair)) {
            $error = "Please select a currency pair";
        } elseif (empty($amount) || !is_numeric($amount) || $amount <= 0) {
            $error = "Please enter a valid amount";
        } else {
            // Get exchange rate
            list($base_currency, $target_currency) = explode('/', $currency_pair);
            $stmt = $conn->prepare("SELECT * FROM exchange_rates 
                                   WHERE base_currency = ? AND target_currency = ? 
                                   AND (valid_to IS NULL OR valid_to > NOW())");
            $stmt->execute([$base_currency, $target_currency]);
            $rate = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$rate) {
                $error = "No exchange rate available for selected currency pair";
            } else {
                $rate_value = $order_type == 'buy' ? $rate['sell_rate'] : $rate['buy_rate'];
                $total_amount = $amount * $rate_value;
                
                // Create order
                $stmt = $conn->prepare("INSERT INTO orders (user_id, order_type, currency_pair, amount, rate, total_amount, payment_method) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $order_type, $currency_pair, $amount, $rate_value, $total_amount, $payment_method]);
                
                $success = "Order created successfully!";
                header("Refresh:2");
            }
        }
    }
    
    if (isset($_POST['create_transfer'])) {
        $platform_type = $_POST['platform_type'];
        $platform_name = $_POST['platform_name'];
        $account_id = $_POST['account_id'];
        $amount = $_POST['amount'];
        
        // Validate input
        if (empty($platform_type)) {
            $error = "Please select a platform type";
        } elseif (empty($platform_name)) {
            $error = "Please enter platform name";
        } elseif (empty($account_id)) {
            $error = "Please enter account ID";
        } elseif (empty($amount) || !is_numeric($amount) || $amount <= 0) {
            $error = "Please enter a valid amount";
        } elseif ($amount > $user['balance']) {
            $error = "Insufficient balance for this transfer";
        } else {
            // Create order
            $stmt = $conn->prepare("INSERT INTO orders (user_id, order_type, currency_pair, amount, total_amount, payment_method, platform, platform_reference) 
                                  VALUES (?, 'buy', 'USD/USD', ?, ?, 'wallet', ?, ?)");
            $stmt->execute([$user_id, $amount, $amount, $platform_type, $account_id]);
            
            $success = "Transfer order created successfully!";
            header("Refresh:2");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Investments & Exchanges</title>
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
                <!-- Page Header -->
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-white">
                        <i class="fas fa-exchange-alt mr-3 text-primary"></i>
                        Investments & Exchanges
                    </h1>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Balance Card -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 hover:border-primary/40 transition-all duration-300 animate-scaleIn">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-lightGray text-sm font-medium">Available Balance</p>
                                <p class="text-3xl font-bold text-white mt-2">KES <?php echo number_format($user['balance'], 2); ?></p>
                                <p class="text-primary text-sm mt-1 flex items-center">
                                    <i class="fas fa-wallet mr-1"></i>
                                    Ready to invest/transfer
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-primary to-primaryDark p-4 rounded-xl">
                                <i class="fas fa-wallet text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Active Investments Card -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 hover:border-primary/40 transition-all duration-300 animate-scaleIn" style="animation-delay: 0.1s;">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-lightGray text-sm font-medium">Active Investments</p>
                                <p class="text-3xl font-bold text-white mt-2"><?php echo count($user_investments); ?></p>
                                <p class="text-green-400 text-sm mt-1 flex items-center">
                                    <i class="fas fa-chart-line mr-1"></i>
                                    Growing your money
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-green-500 to-green-600 p-4 rounded-xl">
                                <i class="fas fa-piggy-bank text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Exchange Orders Card -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 hover:border-primary/40 transition-all duration-300 animate-scaleIn" style="animation-delay: 0.2s;">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-lightGray text-sm font-medium">Exchange Orders</p>
                                <p class="text-3xl font-bold text-white mt-2"><?php 
                                    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
                                    $stmt->execute([$user_id]);
                                    echo $stmt->fetchColumn();
                                ?></p>
                                <p class="text-blue-400 text-sm mt-1 flex items-center">
                                    <i class="fas fa-exchange-alt mr-1"></i>
                                    Currency exchanges
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-4 rounded-xl">
                                <i class="fas fa-money-bill-wave text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Platform Accounts Card -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 hover:border-primary/40 transition-all duration-300 animate-scaleIn" style="animation-delay: 0.3s;">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-lightGray text-sm font-medium">Linked Accounts</p>
                                <p class="text-3xl font-bold text-white mt-2"><?php echo count($platform_accounts); ?></p>
                                <p class="text-purple-400 text-sm mt-1 flex items-center">
                                    <i class="fas fa-random mr-1"></i>
                                    For easy transfers
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-4 rounded-xl">
                                <i class="fas fa-link text-white text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="mb-8 border-b border-primary/20">
                    <nav class="flex space-x-8">
                        <button id="investments-tab" class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-primary text-primary" onclick="switchTab('investments')">
                            <i class="fas fa-piggy-bank mr-2"></i>Investments
                        </button>
                        <button id="exchanges-tab" class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-transparent text-lightGray hover:text-primary hover:border-primary/40" onclick="switchTab('exchanges')">
                            <i class="fas fa-exchange-alt mr-2"></i>Currency Exchange
                        </button>
                        <button id="transfers-tab" class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-transparent text-lightGray hover:text-primary hover:border-primary/40" onclick="switchTab('transfers')">
                            <i class="fas fa-random mr-2"></i>Platform Transfers
                        </button>
                    </nav>
                </div>

                <!-- Tab Contents -->
                <div class="space-y-8">
                    <!-- Investments Tab -->
                    <div id="investments-content" class="tab-content">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Create Investment Form -->
                            <div class="lg:col-span-1 bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 animate-fadeIn">
                                <h3 class="text-xl font-semibold text-white mb-4 flex items-center">
                                    <i class="fas fa-plus-circle mr-3 text-primary"></i>
                                    New Investment
                                </h3>
                                <?php if (isset($error)): ?>
                                    <div class="mb-4 p-4 bg-red-500/20 text-red-300 rounded-lg">
                                        <?php echo $error; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (isset($success)): ?>
                                    <div class="mb-4 p-4 bg-green-500/20 text-green-300 rounded-lg">
                                        <?php echo $success; ?>
                                    </div>
                                <?php endif; ?>
                                <form method="POST" class="space-y-4">
                                    <div>
                                        <label for="product_id" class="block text-sm font-medium text-lightGray mb-2">Investment Product</label>
                                        <select id="product_id" name="product_id" class="w-full px-4 py-3 bg-darker border border-primary/20 rounded-lg text-white focus:outline-none focus:border-primary">
                                            <option value="">Select Product</option>
                                            <?php foreach ($investment_products as $product): ?>
                                                <option value="<?php echo $product['id']; ?>">
                                                    <?php echo htmlspecialchars($product['name']); ?> 
                                                    (<?php echo $product['expected_return_rate']; ?>% for <?php echo $product['return_period_days']; ?> days)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="amount" class="block text-sm font-medium text-lightGray mb-2">Amount (KES)</label>
                                        <input type="number" id="amount" name="amount" step="0.01" min="0" class="w-full px-4 py-3 bg-darker border border-primary/20 rounded-lg text-white focus:outline-none focus:border-primary" placeholder="Enter amount">
                                    </div>
                                    <div>
                                        <label for="payout_method" class="block text-sm font-medium text-lightGray mb-2">Payout Method</label>
                                        <select id="payout_method" name="payout_method" class="w-full px-4 py-3 bg-darker border border-primary/20 rounded-lg text-white focus:outline-none focus:border-primary">
                                            <option value="wallet">Wallet</option>
                                            <option value="bank">Bank Transfer</option>
                                            <option value="crypto">Crypto Wallet</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="create_investment" class="w-full px-6 py-3 bg-gradient-to-r from-primary to-primaryDark text-white font-medium rounded-lg hover:shadow-lg transition-all">
                                        <i class="fas fa-check-circle mr-2"></i> Create Investment
                                    </button>
                                </form>
                            </div>

                            <!-- Active Investments -->
                            <div class="lg:col-span-2 bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 overflow-hidden animate-fadeIn">
                                <div class="p-6 border-b border-primary/20">
                                    <h3 class="text-xl font-semibold text-white flex items-center">
                                        <i class="fas fa-chart-line mr-3 text-primary"></i>
                                        Your Active Investments
                                    </h3>
                                </div>
                                <div class="overflow-x-auto">
                                    <?php if (count($user_investments) > 0): ?>
                                        <table class="w-full">
                                            <thead class="bg-primary/5">
                                                <tr>
                                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Product</th>
                                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Amount</th>
                                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Expected Return</th>
                                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Maturity Date</th>
                                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-primary/10">
                                                <?php foreach ($user_investments as $investment): ?>
                                                    <tr class="hover:bg-primary/5 transition-colors">
                                                        <td class="px-6 py-4 text-sm text-white font-medium"><?php echo htmlspecialchars($investment['product_name']); ?></td>
                                                        <td class="px-6 py-4 text-sm text-white">KES <?php echo number_format($investment['amount'], 2); ?></td>
                                                        <td class="px-6 py-4 text-sm text-green-400">KES <?php echo number_format($investment['expected_return_amount'], 2); ?></td>
                                                        <td class="px-6 py-4 text-sm text-lightGray"><?php echo date('M j, Y', strtotime($investment['maturity_date'])); ?></td>
                                                        <td class="px-6 py-4">
                                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-500/20 text-green-400">
                                                                Active
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php else: ?>
                                        <div class="p-8 text-center text-lightGray">
                                            <i class="fas fa-piggy-bank text-4xl mb-4 opacity-50"></i>
                                            <p>You don't have any active investments yet</p>
                                            <p class="text-sm mt-2">Start by creating your first investment above</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Investment Products -->
                        <div class="mt-8 bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 overflow-hidden animate-fadeIn">
                            <div class="p-6 border-b border-primary/20">
                                <h3 class="text-xl font-semibold text-white flex items-center">
                                    <i class="fas fa-box-open mr-3 text-primary"></i>
                                    Available Investment Products
                                </h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                                <?php foreach ($investment_products as $product): ?>
                                    <div class="bg-darker/80 rounded-xl border border-primary/20 p-6 hover:border-primary/40 transition-all">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-primaryDark text-white flex items-center justify-center">
                                                <i class="fas fa-chart-pie text-xl"></i>
                                            </div>
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                                <?php echo $product['risk_level'] == 'low' ? 'bg-green-500/20 text-green-400' : 
                                                    ($product['risk_level'] == 'medium' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400'); ?>">
                                                <?php echo ucfirst($product['risk_level']); ?> risk
                                            </span>
                                        </div>
                                        <h4 class="text-lg font-bold text-white mb-2"><?php echo htmlspecialchars($product['name']); ?></h4>
                                        <p class="text-lightGray text-sm mb-4"><?php echo htmlspecialchars($product['description']); ?></p>
                                        <div class="space-y-3">
                                            <div class="flex justify-between items-center">
                                                <span class="text-lightGray text-sm">Return Rate</span>
                                                <span class="text-primary font-semibold"><?php echo $product['expected_return_rate']; ?>%</span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-lightGray text-sm">Duration</span>
                                                <span class="text-white font-semibold"><?php echo $product['return_period_days']; ?> days</span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-lightGray text-sm">Min Investment</span>
                                                <span class="text-white font-semibold">KES <?php echo number_format($product['min_investment_amount'], 2); ?></span>
                                            </div>
                                            <?php if ($product['max_investment_amount']): ?>
                                            <div class="flex justify-between items-center">
                                                <span class="text-lightGray text-sm">Max Investment</span>
                                                <span class="text-white font-semibold">KES <?php echo number_format($product['max_investment_amount'], 2); ?></span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Exchanges Tab -->
                    <div id="exchanges-content" class="tab-content hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Create Exchange Order Form -->
                            <div class="lg:col-span-1 bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 animate-fadeIn">
                                <h3 class="text-xl font-semibold text-white mb-4 flex items-center">
                                    <i class="fas fa-exchange-alt mr-3 text-primary"></i>
                                    New Exchange Order
                                </h3>
                                <?php if (isset($error)): ?>
                                    <div class="mb-4 p-4 bg-red-500/20 text-red-300 rounded-lg">
                                        <?php echo $error; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (isset($success)): ?>
                                    <div class="mb-4 p-4 bg-green-500/20 text-green-300 rounded-lg">
                                        <?php echo $success; ?>
                                    </div>
                                <?php endif; ?>
                                <form method="POST" class="space-y-4">
                                    <div>
                                        <label for="order_type" class="block text-sm font-medium text-lightGray mb-2">Order Type</label>
                                        <select id="order_type" name="order_type" class="w-full px-4 py-3 bg-darker border border-primary/20 rounded-lg text-white focus:outline-none focus:border-primary">
                                            <option value="buy">Buy Foreign Currency</option>
                                            <option value="sell">Sell Foreign Currency</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="currency_pair" class="block text-sm font-medium text-lightGray mb-2">Currency Pair</label>
                                        <select id="currency_pair" name="currency_pair" class="w-full px-4 py-3 bg-darker border border-primary/20 rounded-lg text-white focus:outline-none focus:border-primary">
                                            <option value="">Select Currency Pair</option>
                                            <?php foreach ($exchange_rates as $rate): ?>
                                                <option value="<?php echo $rate['base_currency']; ?>/<?php echo $rate['target_currency']; ?>">
                                                    <?php echo $rate['base_currency']; ?>/<?php echo $rate['target_currency']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="amount" class="block text-sm font-medium text-lightGray mb-2">Amount</label>
                                        <input type="number" id="amount" name="amount" step="0.01" min="0" class="w-full px-4 py-3 bg-darker border border-primary/20 rounded-lg text-white focus:outline-none focus:border-primary" placeholder="Enter amount">
                                    </div>
                                    <div>
                                        <label for="payment_method" class="block text-sm font-medium text-lightGray mb-2">Payment Method</label>
                                        <select id="payment_method" name="payment_method" class="w-full px-4 py-3 bg-darker border border-primary/20 rounded-lg text-white focus:outline-none focus:border-primary">
                                            <option value="mpesa">M-Pesa</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="crypto_wallet">Crypto Wallet</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="create_order" class="w-full px-6 py-3 bg-gradient-to-r from-primary to-primaryDark text-white font-medium rounded-lg hover:shadow-lg transition-all">
                                        <i class="fas fa-check-circle mr-2"></i> Create Order
                                    </button>
                                </form>
                            </div>

                            <!-- Exchange Rates -->
                            <div class="lg:col-span-2 bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 overflow-hidden animate-fadeIn">
                                <div class="p-6 border-b border-primary/20">
                                    <h3 class="text-xl font-semibold text-white flex items-center">
                                        <i class="fas fa-coins mr-3 text-primary"></i>
                                        Current Exchange Rates
                                    </h3>
                                </div>
                                <div class="overflow-x-auto">
                                    <?php if (count($exchange_rates) > 0): ?>
                                        <table class="w-full">
                                            <thead class="bg-primary/5">
                                                <tr>
                                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Currency Pair</th>
                                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Buy Rate</th>
                                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Sell Rate</th>
                                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Mid Rate</th>
                                                    <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Source</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-primary/10">
                                                <?php foreach ($exchange_rates as $rate): ?>
                                                    <tr class="hover:bg-primary/5 transition-colors">
                                                        <td class="px-6 py-4 text-sm text-white font-medium"><?php echo $rate['base_currency']; ?>/<?php echo $rate['target_currency']; ?></td>
                                                        <td class="px-6 py-4 text-sm text-green-400"><?php echo number_format($rate['buy_rate'], 6); ?></td>
                                                        <td class="px-6 py-4 text-sm text-red-400"><?php echo number_format($rate['sell_rate'], 6); ?></td>
                                                        <td class="px-6 py-4 text-sm text-white"><?php echo number_format($rate['mid_rate'], 6); ?></td>
                                                        <td class="px-6 py-4 text-sm text-lightGray"><?php echo ucfirst(str_replace('_', ' ', $rate['source'])); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php else: ?>
                                        <div class="p-8 text-center text-lightGray">
                                            <i class="fas fa-coins text-4xl mb-4 opacity-50"></i>
                                            <p>No exchange rates available at the moment</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Orders -->
                        <div class="mt-8 bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 overflow-hidden animate-fadeIn">
                            <div class="p-6 border-b border-primary/20">
                                <h3 class="text-xl font-semibold text-white flex items-center">
                                    <i class="fas fa-history mr-3 text-primary"></i>
                                    Your Recent Exchange Orders
                                </h3>
                            </div>
                            <div class="overflow-x-auto">
                                <?php if (count($recent_orders) > 0): ?>
                                    <table class="w-full">
                                        <thead class="bg-primary/5">
                                            <tr>
                                                <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Date</th>
                                                <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Type</th>
                                                <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Currency Pair</th>
                                                <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Amount</th>
                                                <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Rate</th>
                                                <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Total</th>
                                                <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-primary/10">
                                            <?php foreach ($recent_orders as $order): ?>
                                                <tr class="hover:bg-primary/5 transition-colors">
                                                    <td class="px-6 py-4 text-sm text-lightGray"><?php echo date('M j, Y H:i', strtotime($order['created_at'])); ?></td>
                                                    <td class="px-6 py-4 text-sm text-white font-medium">
                                                        <?php echo ucfirst($order['order_type']); ?>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-white"><?php echo $order['currency_pair']; ?></td>
                                                    <td class="px-6 py-4 text-sm text-white"><?php echo number_format($order['amount'], 2); ?> <?php echo explode('/', $order['currency_pair'])[0]; ?></td>
                                                    <td class="px-6 py-4 text-sm text-white"><?php echo number_format($order['rate'], 6); ?></td>
                                                    <td class="px-6 py-4 text-sm text-white"><?php echo number_format($order['total_amount'], 2); ?> <?php echo explode('/', $order['currency_pair'])[1]; ?></td>
                                                    <td class="px-6 py-4">
                                                        <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                                            <?php echo $order['status'] == 'completed' ? 'bg-green-500/20 text-green-400' : 
                                                                ($order['status'] == 'failed' ? 'bg-red-500/20 text-red-400' : 'bg-yellow-500/20 text-yellow-400'); ?>">
                                                            <?php echo ucfirst($order['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <div class="p-8 text-center text-lightGray">
                                        <i class="fas fa-exchange-alt text-4xl mb-4 opacity-50"></i>
                                        <p>You don't have any exchange orders yet</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Transfers Tab -->
                    <div id="transfers-content" class="tab-content hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Create Transfer Form -->
                            <div class="lg:col-span-1 bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 animate-fadeIn">
                                <h3 class="text-xl font-semibold text-white mb-4 flex items-center">
                                    <i class="fas fa-random mr-3 text-primary"></i>
                                    New Platform Transfer
                                </h3>
                                <?php if (isset($error)): ?>
                                    <div class="mb-4 p-4 bg-red-500/20 text-red-300 rounded-lg">
                                        <?php echo $error; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (isset($success)): ?>
                                    <div class="mb-4 p-4 bg-green-500/20 text-green-300 rounded-lg">
                                        <?php echo $success; ?>
                                    </div>
                                <?php endif; ?>
                                <form method="POST" class="space-y-4">
                                    <div>
                                        <label for="platform_type" class="block text-sm font-medium text-lightGray mb-2">Platform Type</label>
                                        <select id="platform_type" name="platform_type" class="w-full px-4 py-3 bg-darker border border-primary/20 rounded-lg text-white focus:outline-none focus:border-primary">
                                            <option value="">Select Platform Type</option>
                                            <option value="crypto">Crypto Exchange</option>
                                            <option value="deriv">Deriv Trading</option>
                                            <option value="forex">Forex Broker</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="platform_name" class="block text-sm font-medium text-lightGray mb-2">Platform Name</label>
                                        <input type="text" id="platform_name" name="platform_name" class="w-full px-4 py-3 bg-darker border border-primary/20 rounded-lg text-white focus:outline-none focus:border-primary" placeholder="e.g. Binance, Deriv, etc">
                                    </div>
                                    <div>
                                        <label for="account_id" class="block text-sm font-medium text-lightGray mb-2">Account ID/Wallet</label>
                                        <input type="text" id="account_id" name="account_id" class="w-full px-4 py-3 bg-darker border border-primary/20 rounded-lg text-white focus:outline-none focus:border-primary" placeholder="Your account ID or wallet address">
                                    </div>
                                    <div>
                                        <label for="amount" class="block text-sm font-medium text-lightGray mb-2">Amount (KES)</label>
                                        <input type="number" id="amount" name="amount" step="0.01" min="0" class="w-full px-4 py-3 bg-darker border border-primary/20 rounded-lg text-white focus:outline-none focus:border-primary" placeholder="Enter amount">
                                    </div>
                                    <button type="submit" name="create_transfer" class="w-full px-6 py-3 bg-gradient-to-r from-primary to-primaryDark text-white font-medium rounded-lg hover:shadow-lg transition-all">
                                        <i class="fas fa-paper-plane mr-2"></i> Initiate Transfer
                                    </button>
                                </form>
                            </div>

                            <!-- Linked Accounts -->
                            <div class="lg:col-span-2 bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 overflow-hidden animate-fadeIn">
                                <div class="p-6 border-b border-primary/20 flex justify-between items-center">
                                    <h3 class="text-xl font-semibold text-white flex items-center">
                                        <i class="fas fa-link mr-3 text-primary"></i>
                                        Your Linked Accounts
                                    </h3>
                                    <button class="px-4 py-2 bg-primary/10 border border-primary/20 text-primary rounded-lg hover:bg-primary/20 transition-all text-sm">
                                        <i class="fas fa-plus mr-2"></i> Add Account
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
                                    <?php if (count($platform_accounts) > 0): ?>
                                        <?php foreach ($platform_accounts as $account): ?>
                                            <div class="bg-darker/80 rounded-xl border border-primary/20 p-6 hover:border-primary/40 transition-all">
                                                <div class="flex items-center justify-between mb-4">
                                                    <div class="w-12 h-12 rounded-xl 
                                                        <?php echo $account['platform_type'] == 'crypto' ? 'bg-gradient-to-br from-yellow-500 to-yellow-600' : 
                                                            ($account['platform_type'] == 'deriv' ? 'bg-gradient-to-br from-blue-500 to-blue-600' : 'bg-gradient-to-br from-green-500 to-green-600'); ?> 
                                                        text-white flex items-center justify-center">
                                                        <i class="<?php echo $account['platform_type'] == 'crypto' ? 'fab fa-bitcoin' : 
                                                            ($account['platform_type'] == 'deriv' ? 'fas fa-chart-line' : 'fas fa-money-bill-wave'); ?> text-xl"></i>
                                                    </div>
                                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-primary/10 text-primary">
                                                        <?php echo ucfirst($account['platform_type']); ?>
                                                    </span>
                                                </div>
                                                <h4 class="text-lg font-bold text-white mb-1"><?php echo htmlspecialchars($account['platform_name']); ?></h4>
                                                <p class="text-lightGray text-sm mb-4">Account ID: <?php echo htmlspecialchars($account['account_id']); ?></p>
                                                <div class="flex space-x-3">
                                                    <button class="flex-1 px-4 py-2 bg-primary/10 border border-primary/20 text-primary rounded-lg hover:bg-primary/20 transition-all text-sm">
                                                        <i class="fas fa-paper-plane mr-2"></i> Transfer
                                                    </button>
                                                    <button class="px-4 py-2 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg hover:bg-red-500/20 transition-all text-sm">
                                                        <i class="fas fa-trash mr-2"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-span-2 p-8 text-center text-lightGray">
                                            <i class="fas fa-unlink text-4xl mb-4 opacity-50"></i>
                                            <p>You haven't linked any platform accounts yet</p>
                                            <p class="text-sm mt-2">Link your accounts for faster transfers</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Transfer History -->
                        <div class="mt-8 bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 overflow-hidden animate-fadeIn">
                            <div class="p-6 border-b border-primary/20">
                                <h3 class="text-xl font-semibold text-white flex items-center">
                                    <i class="fas fa-history mr-3 text-primary"></i>
                                    Transfer History
                                </h3>
                            </div>
                            <div class="overflow-x-auto">
                                <?php 
                                $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? AND platform IS NOT NULL ORDER BY created_at DESC LIMIT 10");
                                $stmt->execute([$user_id]);
                                $transfers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <?php if (count($transfers) > 0): ?>
                                    <table class="w-full">
                                        <thead class="bg-primary/5">
                                            <tr>
                                                <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Date</th>
                                                <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Platform</th>
                                                <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Account ID</th>
                                                <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Amount</th>
                                                <th class="px-6 py-4 text-left text-xs font-medium text-lightGray uppercase tracking-wider">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-primary/10">
                                            <?php foreach ($transfers as $transfer): ?>
                                                <tr class="hover:bg-primary/5 transition-colors">
                                                    <td class="px-6 py-4 text-sm text-lightGray"><?php echo date('M j, Y H:i', strtotime($transfer['created_at'])); ?></td>
                                                    <td class="px-6 py-4 text-sm text-white font-medium">
                                                        <?php echo ucfirst($transfer['platform']); ?>
                                                        <span class="block text-xs text-lightGray"><?php echo $transfer['platform_reference']; ?></span>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-white"><?php echo $transfer['platform_reference']; ?></td>
                                                    <td class="px-6 py-4 text-sm text-white">KES <?php echo number_format($transfer['amount'], 2); ?></td>
                                                    <td class="px-6 py-4">
                                                        <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                                            <?php echo $transfer['status'] == 'completed' ? 'bg-green-500/20 text-green-400' : 
                                                                ($transfer['status'] == 'failed' ? 'bg-red-500/20 text-red-400' : 'bg-yellow-500/20 text-yellow-400'); ?>">
                                                            <?php echo ucfirst($transfer['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <div class="p-8 text-center text-lightGray">
                                        <i class="fas fa-random text-4xl mb-4 opacity-50"></i>
                                        <p>You haven't made any platform transfers yet</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Tab switching functionality
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-content').classList.remove('hidden');
            
            // Update tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('border-primary', 'text-primary');
                button.classList.add('border-transparent', 'text-lightGray');
            });
            
            // Highlight selected tab button
            document.getElementById(tabName + '-tab').classList.remove('border-transparent', 'text-lightGray');
            document.getElementById(tabName + '-tab').classList.add('border-primary', 'text-primary');
        }
        
        // Mobile menu toggle
        document.getElementById('mobile-menu-toggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const icon = document.getElementById('menu-icon');
            
            sidebar.classList.toggle('translate-x-0');
            overlay.classList.toggle('hidden');
            
            if (sidebar.classList.contains('translate-x-0')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
        
        // Close sidebar when clicking overlay
        document.getElementById('sidebar-overlay').addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('translate-x-0');
            document.getElementById('sidebar-overlay').classList.add('hidden');
            document.getElementById('menu-icon').classList.remove('fa-times');
            document.getElementById('menu-icon').classList.add('fa-bars');
        });
    </script>
</body>
</html>
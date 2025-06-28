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

// Get current exchange rates
$stmt = $conn->prepare("SELECT * FROM exchange_rates WHERE base_currency = 'USD' AND target_currency = 'KES' AND is_active = 1 ORDER BY valid_from DESC LIMIT 1");
$stmt->execute();
$exchange_rate = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle buy order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_dollars'])) {
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $phone = $_POST['phone'];
    
    if (!$exchange_rate) {
        $_SESSION['error'] = "Exchange rate not available. Please try again later.";
    } elseif ($amount < 10) {
        $_SESSION['error'] = "Minimum purchase amount is $10";
    } else {
        // Calculate total KES needed
        $total_kes = $amount * $exchange_rate['buy_rate'];
        
        // Check if user has enough balance
        if ($total_kes > $user['balance']) {
            $_SESSION['error'] = "Insufficient balance. You need KES " . number_format($total_kes, 2);
        } else {
            // Create order
            $stmt = $conn->prepare("
                INSERT INTO orders 
                (user_id, order_type, currency_pair, amount, rate, total_amount, payment_method, status)
                VALUES (?, 'buy', 'USD/KES', ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$user_id, $amount, $exchange_rate['buy_rate'], $total_kes, $payment_method]);
            
            // Deduct from user balance
            $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$total_kes, $user_id]);
            
            $_SESSION['success'] = "Buy order for $".number_format($amount,2)." created successfully!";
            header("Location: exchange-history.php");
            exit;
        }
    }
}
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
                    <h1 class="text-2xl font-bold text-white">Buy US Dollars</h1>
                    <a href="exchange-history.php" class="px-4 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded-lg hover:shadow-lg transition-all">
                        <i class="fas fa-history mr-2"></i> Exchange History
                    </a>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg mb-6 animate-fadeIn">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Buy Form -->
                    <div class="lg:col-span-2 bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 animate-fadeIn">
                        <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                            <i class="fas fa-dollar-sign mr-3 text-primary"></i>
                            Buy USD
                        </h3>
                        
                        <form method="POST" action="">
                            <div class="mb-6">
                                <label class="block text-lightGray text-sm mb-2">Amount in USD</label>
                                <input 
                                    type="number" 
                                    name="amount" 
                                    min="10" 
                                    step="0.01"
                                    class="w-full px-4 py-3 bg-darker border border-primary/20 rounded-lg focus:outline-none focus:border-primary/50"
                                    placeholder="Enter amount in USD"
                                    required
                                >
                            </div>
                            
                            <div class="mb-6">
                                <label class="block text-lightGray text-sm mb-2">Payment Method</label>
                                <select 
                                    name="payment_method" 
                                    class="w-full px-4 py-3 bg-darker border border-primary/20 rounded-lg focus:outline-none focus:border-primary/50"
                                    required
                                >
                                    <option value="mpesa">M-Pesa</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="crypto_wallet">Crypto Wallet</option>
                                </select>
                            </div>
                            
                            <div class="mb-6">
                                <label class="block text-lightGray text-sm mb-2">Phone Number (for M-Pesa)</label>
                                <input 
                                    type="tel" 
                                    name="phone" 
                                    class="w-full px-4 py-3 bg-darker border border-primary/20 rounded-lg focus:outline-none focus:border-primary/50"
                                    placeholder="2547XXXXXXXX"
                                    value="<?php echo htmlspecialchars($user['phone']); ?>"
                                    required
                                >
                            </div>
                            
                            <div class="mb-6 bg-darker/50 border border-primary/20 rounded-lg p-4">
                                <div class="flex justify-between mb-2">
                                    <span class="text-lightGray">Exchange Rate:</span>
                                    <span class="font-semibold">1 USD = KES <?php echo number_format($exchange_rate ? $exchange_rate['buy_rate'] : 0, 2); ?></span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-lightGray">Amount in KES:</span>
                                    <span class="font-semibold" id="kesAmount">KES 0.00</span>
                                </div>
                                <div class="flex justify-between text-primary font-semibold">
                                    <span>Total to Pay:</span>
                                    <span id="totalPay">KES 0.00</span>
                                </div>
                            </div>
                            
                            <button 
                                type="submit" 
                                name="buy_dollars" 
                                class="w-full px-4 py-3 bg-gradient-to-r from-primary to-primaryDark text-white rounded-lg hover:shadow-lg transition-all font-semibold"
                            >
                                Buy USD
                            </button>
                        </form>
                    </div>
                    
                    <!-- Exchange Info -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 animate-fadeIn" style="animation-delay: 0.1s;">
                        <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                            <i class="fas fa-info-circle mr-3 text-primary"></i>
                            Exchange Information
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-lightGray text-sm mb-1">Current Rates</h4>
                                <div class="bg-darker/50 border border-primary/20 rounded-lg p-3">
                                    <div class="flex justify-between mb-1">
                                        <span>Buy USD:</span>
                                        <span class="font-semibold">1 USD = KES <?php echo number_format($exchange_rate ? $exchange_rate['buy_rate'] : 0, 2); ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Sell USD:</span>
                                        <span class="font-semibold">1 USD = KES <?php echo number_format($exchange_rate ? $exchange_rate['sell_rate'] : 0, 2); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="text-lightGray text-sm mb-1">How It Works</h4>
                                <div class="bg-darker/50 border border-primary/20 rounded-lg p-3 space-y-2">
                                    <div class="flex items-start">
                                        <span class="bg-primary/20 text-primary rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2 mt-0.5">1</span>
                                        <span>Enter the USD amount you want to buy</span>
                                    </div>
                                    <div class="flex items-start">
                                        <span class="bg-primary/20 text-primary rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2 mt-0.5">2</span>
                                        <span>We'll show you the equivalent in KES</span>
                                    </div>
                                    <div class="flex items-start">
                                        <span class="bg-primary/20 text-primary rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2 mt-0.5">3</span>
                                        <span>Make payment via your preferred method</span>
                                    </div>
                                    <div class="flex items-start">
                                        <span class="bg-primary/20 text-primary rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2 mt-0.5">4</span>
                                        <span>Dollars will be credited to your account</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="text-lightGray text-sm mb-1">Processing Time</h4>
                                <div class="bg-darker/50 border border-primary/20 rounded-lg p-3">
                                    <p>USD purchases are processed within <span class="font-semibold text-primary">1-2 business hours</span> after payment confirmation.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Calculate KES amount as user types USD amount
        const exchangeRate = <?php echo $exchange_rate ? $exchange_rate['buy_rate'] : 0; ?>;
        document.querySelector('input[name="amount"]').addEventListener('input', function(e) {
            const usdAmount = parseFloat(e.target.value) || 0;
            const kesAmount = usdAmount * exchangeRate;
            
            document.getElementById('kesAmount').textContent = 'KES ' + kesAmount.toFixed(2);
            document.getElementById('totalPay').textContent = 'KES ' + kesAmount.toFixed(2);
        });
    </script>
    
    <script src="assets/js/main.js"></script>
</body>
</html>
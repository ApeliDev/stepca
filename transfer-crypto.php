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

// Handle transfer submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transfer_crypto'])) {
    $amount = $_POST['amount'];
    $wallet_address = $_POST['wallet_address'];
    $network = $_POST['network'];
    
    if ($amount < 10) {
        $_SESSION['error'] = "Minimum transfer amount is $10";
    } elseif ($amount > $user['balance']) {
        $_SESSION['error'] = "Insufficient balance for this transfer";
    } else {
        // Create transfer record
        $stmt = $conn->prepare("
            INSERT INTO transfers 
            (sender_id, receiver_id, amount, status, receiver_phone, transfer_fee, remarks)
            VALUES (?, 0, ?, 'pending', ?, 0, ?)
        ");
        $stmt->execute([$user_id, $amount, $wallet_address, "Crypto transfer to $network wallet"]);
        
        // Deduct from user balance
        $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $stmt->execute([$amount, $user_id]);
        
        $_SESSION['success'] = "Crypto transfer of $".number_format($amount,2)." initiated successfully!";
        header("Location: transfer-history.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Crypto Transfer</title>
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
                    <h1 class="text-2xl font-bold text-white">Transfer to Crypto Wallet</h1>
                    <a href="transfer-history.php" class="px-4 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded-lg hover:shadow-lg transition-all">
                        <i class="fas fa-history mr-2"></i> Transfer History
                    </a>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg mb-6 animate-fadeIn">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Transfer Form -->
                    <div class="lg:col-span-2 bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 animate-fadeIn">
                        <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                            <i class="fab fa-bitcoin mr-3 text-primary"></i>
                            Crypto Transfer
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
                                <label class="block text-lightGray text-sm mb-2">Crypto Network</label>
                                <select 
                                    name="network" 
                                    class="w-full px-4 py-3 bg-darker border border-primary/20 rounded-lg focus:outline-none focus:border-primary/50"
                                    required
                                >
                                    <option value="Bitcoin">Bitcoin (BTC)</option>
                                    <option value="Ethereum">Ethereum (ETH)</option>
                                    <option value="USDT">Tether (USDT)</option>
                                    <option value="USDC">USD Coin (USDC)</option>
                                    <option value="Binance">Binance Coin (BNB)</option>
                                </select>
                            </div>
                            
                            <div class="mb-6">
                                <label class="block text-lightGray text-sm mb-2">Wallet Address</label>
                                <input 
                                    type="text" 
                                    name="wallet_address" 
                                    class="w-full px-4 py-3 bg-darker border border-primary/20 rounded-lg focus:outline-none focus:border-primary/50"
                                    placeholder="Enter recipient wallet address"
                                    required
                                >
                            </div>
                            
                            <div class="mb-6 bg-darker/50 border border-primary/20 rounded-lg p-4">
                                <div class="flex justify-between mb-2">
                                    <span class="text-lightGray">Transfer Fee:</span>
                                    <span class="font-semibold">$0.00</span>
                                </div>
                                <div class="flex justify-between text-primary font-semibold">
                                    <span>Total to Deduct:</span>
                                    <span id="totalDeduct">$0.00</span>
                                </div>
                            </div>
                            
                            <button 
                                type="submit" 
                                name="transfer_crypto" 
                                class="w-full px-4 py-3 bg-gradient-to-r from-primary to-primaryDark text-white rounded-lg hover:shadow-lg transition-all font-semibold"
                            >
                                Transfer Crypto
                            </button>
                        </form>
                    </div>
                    
                    <!-- Transfer Info -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 animate-fadeIn" style="animation-delay: 0.1s;">
                        <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                            <i class="fas fa-info-circle mr-3 text-primary"></i>
                            Transfer Information
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-lightGray text-sm mb-1">Supported Networks</h4>
                                <div class="bg-darker/50 border border-primary/20 rounded-lg p-3">
                                    <div class="flex items-center mb-2">
                                        <i class="fab fa-bitcoin text-orange-500 mr-2"></i>
                                        <span>Bitcoin (BTC)</span>
                                    </div>
                                    <div class="flex items-center mb-2">
                                        <i class="fab fa-ethereum text-purple-500 mr-2"></i>
                                        <span>Ethereum (ETH)</span>
                                    </div>
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-coins text-blue-500 mr-2"></i>
                                        <span>Tether (USDT)</span>
                                    </div>
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-coins text-blue-400 mr-2"></i>
                                        <span>USD Coin (USDC)</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fab fa-btc text-yellow-500 mr-2"></i>
                                        <span>Binance Coin (BNB)</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="text-lightGray text-sm mb-1">How It Works</h4>
                                <div class="bg-darker/50 border border-primary/20 rounded-lg p-3 space-y-2">
                                    <div class="flex items-start">
                                        <span class="bg-primary/20 text-primary rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2 mt-0.5">1</span>
                                        <span>Enter the USD amount you want to transfer</span>
                                    </div>
                                    <div class="flex items-start">
                                        <span class="bg-primary/20 text-primary rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2 mt-0.5">2</span>
                                        <span>Select the crypto network</span>
                                    </div>
                                    <div class="flex items-start">
                                        <span class="bg-primary/20 text-primary rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2 mt-0.5">3</span>
                                        <span>Enter the recipient wallet address</span>
                                    </div>
                                    <div class="flex items-start">
                                        <span class="bg-primary/20 text-primary rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2 mt-0.5">4</span>
                                        <span>Equivalent crypto will be sent to the wallet</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="text-lightGray text-sm mb-1">Processing Time</h4>
                                <div class="bg-darker/50 border border-primary/20 rounded-lg p-3">
                                    <p>Crypto transfers are processed within <span class="font-semibold text-primary">1-3 business hours</span> after confirmation.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Calculate total amount as user types
        document.querySelector('input[name="amount"]').addEventListener('input', function(e) {
            const amount = parseFloat(e.target.value) || 0;
            document.getElementById('totalDeduct').textContent = '$' + amount.toFixed(2);
        });
    </script>
    
    <script src="assets/js/main.js"></script>
</body>
</html>
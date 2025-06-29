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

// Get available investment products
$stmt = $conn->prepare("SELECT * FROM investment_products WHERE is_active = 1 ORDER BY risk_level, return_period_days");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle investment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invest'])) {
    $product_id = $_POST['product_id'];
    $amount = $_POST['amount'];
    
    // Validate amount
    $stmt = $conn->prepare("SELECT * FROM investment_products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        $_SESSION['error'] = "Invalid investment product selected";
    } elseif ($amount < $product['min_investment_amount']) {
        $_SESSION['error'] = "Minimum investment amount is KES " . number_format($product['min_investment_amount'], 2);
    } elseif ($product['max_investment_amount'] && $amount > $product['max_investment_amount']) {
        $_SESSION['error'] = "Maximum investment amount is KES " . number_format($product['max_investment_amount'], 2);
    } elseif ($amount > $user['balance']) {
        $_SESSION['error'] = "Insufficient balance for this investment";
    } else {
        // Calculate expected return with compound interest
        $return_rate = $product['expected_return_rate'] / 100;
        $days = $product['return_period_days'];
        
        // Compound interest calculation based on compounding frequency
        switch ($product['compounding_frequency']) {
            case 'daily':
                $n = 365;
                $periods = $days;
                break;
            case 'weekly':
                $n = 52;
                $periods = $days / 7;
                break;
            case 'monthly':
                $n = 12;
                $periods = $days / 30;
                break;
            case 'quarterly':
                $n = 4;
                $periods = $days / 90;
                break;
            default: // monthly by default
                $n = 12;
                $periods = $days / 30;
        }
        
        $expected_return = $amount * pow((1 + ($return_rate/$n)), $periods);
        
        // Calculate loyalty bonus
        $stmt = $conn->prepare("SELECT COUNT(*) FROM investments WHERE user_id = ? AND status = 'completed'");
        $stmt->execute([$user_id]);
        $previous_investments = $stmt->fetchColumn();
        
        $loyalty_bonus = 0;
        if ($previous_investments > 0) {
            $loyalty_bonus = min($product['loyalty_bonus_rate']/100 * $previous_investments, 0.05);
            $expected_return *= (1 + $loyalty_bonus);
        }
        
        // Calculate large investment bonus
        $large_bonus = 0;
        if ($amount >= $product['large_investment_bonus_threshold']) {
            $large_bonus = $product['large_investment_bonus_rate']/100;
            $expected_return *= (1 + $large_bonus);
        }
        
        // Calculate dates
        $start_date = date('Y-m-d');
        $maturity_date = date('Y-m-d', strtotime("+{$days} days"));
        
        // Create investment
        $stmt = $conn->prepare("
            INSERT INTO investments 
            (user_id, product_id, amount, currency, expected_return_amount, 
             start_date, maturity_date, status, loyalty_bonus_rate, large_investment_bonus_rate)
            VALUES (?, ?, ?, 'KES', ?, ?, ?, 'active', ?, ?)
        ");
        $stmt->execute([
            $user_id, 
            $product_id, 
            $amount, 
            $expected_return, 
            $start_date, 
            $maturity_date,
            $loyalty_bonus * 100,
            $large_bonus * 100
        ]);
        
        // Deduct from user balance
        $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $stmt->execute([$amount, $user_id]);
        
        $_SESSION['success'] = "Investment of KES " . number_format($amount, 2) . " created successfully!";
        header("Location: investments.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Investment Products</title>
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
                    <h1 class="text-2xl font-bold text-white">Investment Products</h1>
                    <a href="investments.php" class="px-4 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded-lg hover:shadow-lg transition-all">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Investments
                    </a>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg mb-6 animate-fadeIn">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <!-- Investment Products Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <?php foreach ($products as $product): 
                        $risk_color = [
                            'low' => 'text-green-400',
                            'medium' => 'text-yellow-400',
                            'high' => 'text-red-400'
                        ][$product['risk_level']];
                    ?>
                        <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 hover:border-primary/40 transition-all duration-300 animate-scaleIn">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xl font-bold text-white"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <span class="px-3 py-1 text-xs rounded-full bg-primary/10 text-primary">
                                    <?php echo ucfirst($product['risk_level']); ?> Risk
                                </span>
                            </div>
                            <p class="text-lightGray text-sm mb-6"><?php echo htmlspecialchars($product['description']); ?></p>
                            
                            <div class="space-y-3 mb-6">
                                <div class="flex justify-between">
                                    <span class="text-lightGray text-sm">Annual Return</span>
                                    <span class="text-white font-semibold"><?php echo $product['expected_return_rate']; ?>%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-lightGray text-sm">Compounding</span>
                                    <span class="text-white font-semibold"><?php echo ucfirst($product['compounding_frequency']); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-lightGray text-sm">Duration</span>
                                    <span class="text-white font-semibold"><?php echo $product['return_period_days']; ?> days</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-lightGray text-sm">Min Investment</span>
                                    <span class="text-white font-semibold">KES <?php echo number_format($product['min_investment_amount'], 2); ?></span>
                                </div>
                                <?php if ($product['max_investment_amount']): ?>
                                <div class="flex justify-between">
                                    <span class="text-lightGray text-sm">Max Investment</span>
                                    <span class="text-white font-semibold">KES <?php echo number_format($product['max_investment_amount'], 2); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if ($product['large_investment_bonus_threshold'] > 0): ?>
                                <div class="flex justify-between">
                                    <span class="text-lightGray text-sm">Large Investment Bonus</span>
                                    <span class="text-white font-semibold">KES <?php echo number_format($product['large_investment_bonus_threshold'], 2); ?>+ gets <?php echo $product['large_investment_bonus_rate']; ?>%</span>
                                </div>
                                <?php endif; ?>
                                <?php if ($product['loyalty_bonus_rate'] > 0): ?>
                                <div class="flex justify-between">
                                    <span class="text-lightGray text-sm">Loyalty Bonus</span>
                                    <span class="text-white font-semibold"><?php echo $product['loyalty_bonus_rate']; ?>% per previous investment</span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <button 
                                onclick="document.getElementById('investModal<?php echo $product['id']; ?>').showModal()"
                                class="w-full px-4 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded-lg hover:shadow-lg transition-all"
                            >
                                Invest Now
                            </button>
                            
                            <!-- Investment Modal -->
                            <dialog id="investModal<?php echo $product['id']; ?>" class="bg-darker/90 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 w-full max-w-md text-white animate-scaleIn">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-xl font-bold">Invest in <?php echo htmlspecialchars($product['name']); ?></h3>
                                    <button onclick="document.getElementById('investModal<?php echo $product['id']; ?>').close()" class="text-lightGray hover:text-white">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                
                                <form method="POST" action="">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    
                                    <div class="mb-4">
                                        <label class="block text-lightGray text-sm mb-2">Investment Amount (KES)</label>
                                        <input 
                                            type="number" 
                                            name="amount" 
                                            min="<?php echo $product['min_investment_amount']; ?>" 
                                            <?php if ($product['max_investment_amount']): ?>
                                            max="<?php echo $product['max_investment_amount']; ?>"
                                            <?php endif; ?>
                                            step="0.01"
                                            class="w-full px-4 py-2 bg-darker border border-primary/20 rounded-lg focus:outline-none focus:border-primary/50"
                                            required
                                            oninput="calculateReturn(<?php echo $product['id']; ?>, <?php echo $product['expected_return_rate']; ?>, <?php echo $product['return_period_days']; ?>, '<?php echo $product['compounding_frequency']; ?>', <?php echo $product['loyalty_bonus_rate']; ?>, <?php echo $product['large_investment_bonus_threshold']; ?>, <?php echo $product['large_investment_bonus_rate']; ?>)"
                                        >
                                        <p class="text-xs text-lightGray mt-1">
                                            Min: KES <?php echo number_format($product['min_investment_amount'], 2); ?>
                                            <?php if ($product['max_investment_amount']): ?>
                                            | Max: KES <?php echo number_format($product['max_investment_amount'], 2); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    
                                    <div class="mb-6">
                                        <label class="block text-lightGray text-sm mb-2">Projected Returns</label>
                                        <div class="px-4 py-3 bg-darker/50 border border-primary/20 rounded-lg">
                                            <div class="flex justify-between mb-2">
                                                <span>Principal:</span>
                                                <span id="principalAmount<?php echo $product['id']; ?>">KES 0.00</span>
                                            </div>
                                            <div class="flex justify-between mb-2">
                                                <span>Base Interest:</span>
                                                <span id="baseInterest<?php echo $product['id']; ?>">KES 0.00</span>
                                            </div>
                                            <div class="flex justify-between mb-2 text-yellow-400" id="loyaltyBonusRow<?php echo $product['id']; ?>" style="display: none;">
                                                <span>Loyalty Bonus:</span>
                                                <span id="loyaltyBonus<?php echo $product['id']; ?>">KES 0.00</span>
                                            </div>
                                            <div class="flex justify-between mb-2 text-green-400" id="largeBonusRow<?php echo $product['id']; ?>" style="display: none;">
                                                <span>Large Investment Bonus:</span>
                                                <span id="largeBonus<?php echo $product['id']; ?>">KES 0.00</span>
                                            </div>
                                            <div class="flex justify-between font-semibold text-primary border-t border-primary/20 pt-2 mt-2">
                                                <span>Total Return:</span>
                                                <span id="totalReturn<?php echo $product['id']; ?>">KES 0.00</span>
                                            </div>
                                        </div>
                                        <p class="text-xs text-lightGray mt-1">
                                            Matures in <?php echo $product['return_period_days']; ?> days
                                        </p>
                                    </div>
                                    
                                    <button 
                                        type="submit" 
                                        name="invest" 
                                        class="w-full px-4 py-2 bg-gradient-to-r from-primary to-primaryDark text-white rounded-lg hover:shadow-lg transition-all"
                                    >
                                        Confirm Investment
                                    </button>
                                </form>
                            </dialog>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($products)): ?>
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-8 text-center animate-fadeIn">
                        <i class="fas fa-box-open text-4xl mb-4 text-primary opacity-50"></i>
                        <h3 class="text-xl font-semibold text-white mb-2">No Investment Products Available</h3>
                        <p class="text-lightGray mb-4">Check back later for new investment opportunities</p>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script>
        // Calculate projected returns
        function calculateReturn(productId, annualRate, days, compounding, loyaltyRate, largeThreshold, largeRate) {
            const amount = parseFloat(document.querySelector(`#investModal${productId} input[name="amount"]`).value) || 0;
            
            // Determine compounding parameters
            let n, periods;
            switch (compounding) {
                case 'daily':
                    n = 365;
                    periods = days;
                    break;
                case 'weekly':
                    n = 52;
                    periods = days / 7;
                    break;
                case 'monthly':
                    n = 12;
                    periods = days / 30;
                    break;
                case 'quarterly':
                    n = 4;
                    periods = days / 90;
                    break;
                default: // monthly by default
                    n = 12;
                    periods = days / 30;
            }
            
            // Base compound interest calculation
            const rate = annualRate / 100;
            const baseReturn = amount * Math.pow(1 + (rate/n), periods);
            const baseInterest = baseReturn - amount;
            
            // Calculate loyalty bonus (simulate 2 previous investments for demo)
            const previousInvestments = 2; // In real app, get this from server
            let loyaltyBonus = 0;
            if (previousInvestments > 0 && loyaltyRate > 0) {
                loyaltyBonus = baseReturn * (Math.min(loyaltyRate/100 * previousInvestments, 0.05));
                document.getElementById(`loyaltyBonusRow${productId}`).style.display = 'flex';
                document.getElementById(`loyaltyBonus${productId}`).textContent = 'KES ' + loyaltyBonus.toFixed(2);
            } else {
                document.getElementById(`loyaltyBonusRow${productId}`).style.display = 'none';
            }
            
            // Calculate large investment bonus
            let largeBonus = 0;
            if (amount >= largeThreshold && largeRate > 0) {
                largeBonus = baseReturn * (largeRate/100);
                document.getElementById(`largeBonusRow${productId}`).style.display = 'flex';
                document.getElementById(`largeBonus${productId}`).textContent = 'KES ' + largeBonus.toFixed(2);
            } else {
                document.getElementById(`largeBonusRow${productId}`).style.display = 'none';
            }
            
            // Calculate totals
            const totalReturn = baseReturn + loyaltyBonus + largeBonus;
            const totalInterest = baseInterest + loyaltyBonus + largeBonus;
            
            // Update display
            document.getElementById(`principalAmount${productId}`).textContent = 'KES ' + amount.toFixed(2);
            document.getElementById(`baseInterest${productId}`).textContent = 'KES ' + baseInterest.toFixed(2);
            document.getElementById(`totalReturn${productId}`).textContent = 'KES ' + totalReturn.toFixed(2);
        }
    </script>
</body>
</html>
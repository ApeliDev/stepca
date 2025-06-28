<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/wallet_functions.php'; // New wallet functions

// Start secure session
secure_session_start();
$db = new Database();
$conn = $db->connect();

// Authentication check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit;
}

// CSRF token generation for forms
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user_id = $_SESSION['user_id'];
$current_page = basename($_SERVER['PHP_SELF']);

// Get user data with enhanced security
try {
    $user = get_user_with_wallet($conn, $user_id);
    
    // Check if user exists and is active
    if (!$user) {
        header('Location: logout.php?reason=account_not_found');
        exit;
    }

    // Check if user has paid registration fee (except on payment page)
    if ($user['is_active'] == 0 && $current_page != 'payment.php') {
        header('Location: payment.php');
        exit;
    }

    // Calculate balances with proper validation
    $total_balance = calculate_total_balance($user);
    $max_withdrawal = calculate_max_withdrawal($user);
    
    // Get transactions with pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    $transactions = get_wallet_transactions($conn, $user_id, $limit, $offset);
    $totalTransactions = count_wallet_transactions($conn, $user_id);
    
    // Get referral statistics
    $referral_stats = get_referral_stats($conn, $user_id);
    
    // Get wallet summary statistics
    $wallet_stats = get_wallet_stats($conn, $user_id);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error'] = "A system error occurred. Please try again later.";
    header('Location: dashboard.php');
    exit;
}

// Include the HTML head with CSP and security headers
include 'includes/secure_header.php';
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(SITE_NAME); ?> - Wallet</title>
    
    <!-- Preload critical resources -->
    <link rel="preload" href="assets/css/main.css" as="style">
    <link rel="preload" href="assets/js/wallet.js" as="script">
    
    <!-- CSS -->
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/wallet.css?v=<?= filemtime('assets/css/wallet.css') ?>">
    
    <!-- JavaScript with defer -->
    <script src="assets/js/wallet.js?v=<?= filemtime('assets/js/wallet.js') ?>" defer></script>
    
    <style>
        [data-theme="dark"] {
            --color-primary: #4CAF50;
            --color-primary-dark: #45a049;
            --color-dark: #0f0f23;
            --color-darker: #1a1a2e;
            --color-darkest: #16213e;
            --color-lightGray: #9CA3AF;
            --color-lighterGray: #D1D5DB;
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(5deg); }
            66% { transform: translateY(10px) rotate(-3deg); }
        }
    </style>
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
                <!-- Wallet Overview -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <!-- Main Balance Card -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 transition-all hover:shadow-lg hover:border-primary/30">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-lightGray mb-1">Total Balance</p>
                                <h3 class="text-2xl font-bold" id="total-balance">KES <?= htmlspecialchars(number_format($total_balance, 2)) ?></h3>
                            </div>
                            <div class="bg-primary/10 p-3 rounded-xl">
                                <i class="fas fa-wallet text-primary text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-lightGray flex justify-between items-center">
                            <span>Includes all funds</span>
                            <button onclick="refreshBalances()" class="text-primary hover:text-primaryDark">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Available Balance Card -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 transition-all hover:shadow-lg hover:border-primary/30">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-lightGray mb-1">Available Balance</p>
                                <h3 class="text-2xl font-bold" id="available-balance">KES <?= htmlspecialchars(number_format($user['available_balance'] ?? 0, 2)) ?></h3>
                            </div>
                            <div class="bg-green-500/10 p-3 rounded-xl">
                                <i class="fas fa-coins text-green-500 text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-lightGray">
                            <span>Ready to use</span>
                        </div>
                    </div>
                    
                    <!-- Locked Balance Card -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 transition-all hover:shadow-lg hover:border-primary/30">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-lightGray mb-1">Locked Balance</p>
                                <h3 class="text-2xl font-bold" id="locked-balance">KES <?= htmlspecialchars(number_format($user['locked_balance'] ?? 0, 2)) ?></h3>
                            </div>
                            <div class="bg-yellow-500/10 p-3 rounded-xl">
                                <i class="fas fa-lock text-yellow-500 text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-lightGray">
                            <span>Pending transactions</span>
                        </div>
                    </div>
                    
                    <!-- Referral Balance Card -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 transition-all hover:shadow-lg hover:border-primary/30">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-lightGray mb-1">Referral Bonus</p>
                                <h3 class="text-2xl font-bold" id="referral-balance">KES <?= htmlspecialchars(number_format($user['referral_bonus_balance'] ?? 0, 2)) ?></h3>
                            </div>
                            <div class="bg-blue-500/10 p-3 rounded-xl">
                                <i class="fas fa-users text-blue-500 text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-lightGray flex justify-between items-center">
                            <span>From referrals</span>
                            <a href="referrals.php" class="text-blue-400 hover:text-blue-300">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- Withdraw Card -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-lg flex items-center">
                                <i class="fas fa-money-bill-wave mr-2 text-primary"></i>
                                Withdraw Funds
                            </h4>
                            <span class="text-xs bg-primary/10 text-primary px-2 py-1 rounded">MPesa</span>
                        </div>
                        <p class="text-sm text-lightGray mb-4">Withdraw to your M-Pesa account instantly</p>
                        <div class="flex justify-between items-center">
                            <span class="text-sm">Max: KES <?= htmlspecialchars(number_format($max_withdrawal, 2)) ?></span>
                            <a href="withdraw.php" class="px-4 py-2 bg-primary/10 text-primary rounded-lg hover:bg-primary/20 transition-colors text-sm font-medium">
                                Withdraw Now
                            </a>
                        </div>
                    </div>
                    
                    <!-- Transfer Card -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-lg flex items-center">
                                <i class="fas fa-exchange-alt mr-2 text-primary"></i>
                                Transfer Funds
                            </h4>
                            <span class="text-xs bg-blue-500/10 text-blue-400 px-2 py-1 rounded">Users</span>
                        </div>
                        <p class="text-sm text-lightGray mb-4">Send money to other <?= htmlspecialchars(SITE_NAME) ?> users</p>
                        <div class="flex justify-between items-center">
                            <span class="text-sm">Max: KES <?= htmlspecialchars(number_format($user['available_balance'] ?? 0, 2)) ?></span>
                            <a href="transfer.php" class="px-4 py-2 bg-blue-500/10 text-blue-400 rounded-lg hover:bg-blue-500/20 transition-colors text-sm font-medium">
                                Transfer Now
                            </a>
                        </div>
                    </div>
                    
                    <!-- Deposit Card -->
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-lg flex items-center">
                                <i class="fas fa-plus-circle mr-2 text-primary"></i>
                                Add Funds
                            </h4>
                            <span class="text-xs bg-green-500/10 text-green-400 px-2 py-1 rounded">MPesa</span>
                        </div>
                        <p class="text-sm text-lightGray mb-4">Deposit money to your wallet via M-Pesa</p>
                        <div class="flex justify-between items-center">
                            <span class="text-sm">Min: KES 100</span>
                            <a href="deposit.php" class="px-4 py-2 bg-green-500/10 text-green-400 rounded-lg hover:bg-green-500/20 transition-colors text-sm font-medium">
                                Deposit Now
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 overflow-hidden">
                    <div class="p-6 border-b border-primary/20">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-semibold text-white flex items-center">
                                <i class="fas fa-history mr-3 text-primary"></i>
                                Recent Transactions
                            </h3>
                            <div class="flex space-x-2">
                                <button onclick="exportTransactions()" class="text-xs bg-primary/10 text-primary px-3 py-1 rounded hover:bg-primary/20">
                                    <i class="fas fa-download mr-1"></i> Export
                                </button>
                                <button onclick="refreshTransactions()" class="text-xs bg-blue-500/10 text-blue-400 px-3 py-1 rounded hover:bg-blue-500/20">
                                    <i class="fas fa-sync-alt mr-1"></i> Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <?php if (count($transactions) > 0): ?>
                            <div class="space-y-4" id="transactions-list">
                                <?php foreach ($transactions as $txn): ?>
                                <div class="flex items-center justify-between p-4 bg-darker/40 rounded-xl border border-primary/10 hover:border-primary/20 transition-colors">
                                    <div class="flex items-center">
                                        <div class="p-3 rounded-lg mr-4 <?= get_txn_icon_class($txn['transaction_type']) ?>">
                                            <i class="<?= get_txn_icon($txn['transaction_type']) ?>"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-medium"><?= htmlspecialchars(ucfirst($txn['display_type'] ?? $txn['transaction_type'])) ?></h4>
                                            <p class="text-xs text-lightGray">
                                                <?= htmlspecialchars(date('M j, Y \a\t g:i a', strtotime($txn['created_at']))) ?>
                                                <?php if (!empty($txn['description'])): ?>
                                                    • <?= htmlspecialchars($txn['description']) ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold <?= $txn['amount'] < 0 ? 'text-red-400' : 'text-green-400' ?>">
                                            <?= $txn['amount'] < 0 ? '-' : '+' ?>KES <?= htmlspecialchars(number_format(abs($txn['amount']), 2) ?>
                                        </p>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?= get_status_class($txn['status']) ?>">
                                            <?= htmlspecialchars(ucfirst($txn['status'])) ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($totalTransactions > $limit): ?>
                            <div class="mt-6 flex justify-between items-center">
                                <span class="text-sm text-lightGray">
                                    Showing <?= $offset + 1 ?>-<?= min($offset + $limit, $totalTransactions) ?> of <?= $totalTransactions ?> transactions
                                </span>
                                <div class="flex space-x-2">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?= $page - 1 ?>" class="px-3 py-1 bg-darker/60 border border-primary/20 rounded hover:bg-primary/10">
                                            Previous
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($offset + $limit < $totalTransactions): ?>
                                        <a href="?page=<?= $page + 1 ?>" class="px-3 py-1 bg-darker/60 border border-primary/20 rounded hover:bg-primary/10">
                                            Next
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mt-6 text-center">
                                <a href="transactions.php" class="inline-flex items-center text-primary hover:text-primaryDark text-sm font-medium">
                                    View Full Transaction History
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="py-8 text-center">
                                <div class="bg-primary/10 p-4 rounded-full inline-block mb-4">
                                    <i class="fas fa-exchange-alt text-primary text-2xl"></i>
                                </div>
                                <p class="text-lightGray">No transactions yet</p>
                                <p class="text-lightGray/60 text-sm mt-1">Your transaction history will appear here</p>
                                <a href="deposit.php" class="mt-4 inline-block px-4 py-2 bg-primary/10 text-primary rounded-lg hover:bg-primary/20 transition-colors text-sm font-medium">
                                    Make Your First Deposit
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Stats Section -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 text-center">
                        <div class="bg-gradient-to-br from-blue-500 to-blue-600 w-16 h-16 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-users text-white text-2xl"></i>
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2"><?= htmlspecialchars($referral_stats['total_referrals']) ?></h4>
                        <p class="text-lightGray text-sm">Total Referrals</p>
                        <div class="mt-2">
                            <span class="text-xs bg-blue-500/10 text-blue-400 px-2 py-1 rounded">
                                <?= htmlspecialchars($referral_stats['active_referrals']) ?> active
                            </span>
                        </div>
                    </div>

                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 text-center">
                        <div class="bg-gradient-to-br from-green-500 to-green-600 w-16 h-16 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-hand-holding-usd text-white text-2xl"></i>
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2">KES <?= htmlspecialchars(number_format($wallet_stats['total_deposits'], 2)) ?></h4>
                        <p class="text-lightGray text-sm">Total Deposits</p>
                        <div class="mt-2">
                            <span class="text-xs bg-green-500/10 text-green-400 px-2 py-1 rounded">
                                <?= htmlspecialchars($wallet_stats['deposit_count'] ?? 0) ?> transactions
                            </span>
                        </div>
                    </div>

                    <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 p-6 text-center">
                        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 w-16 h-16 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-coins text-white text-2xl"></i>
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2">KES <?= htmlspecialchars(number_format($referral_stats['total_earned'], 2)) ?></h4>
                        <p class="text-lightGray text-sm">Referral Earnings</p>
                        <div class="mt-2">
                            <a href="referrals.php" class="text-xs bg-yellow-500/10 text-yellow-400 px-2 py-1 rounded hover:bg-yellow-500/20">
                                View Referrals
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Transaction Details Modal -->
    <div id="transaction-modal" class="fixed inset-0 bg-black/70 z-50 hidden items-center justify-center p-4">
        <div class="bg-darker rounded-xl border border-primary/20 w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-primary/20 flex justify-between items-center">
                <h3 class="text-xl font-semibold">Transaction Details</h3>
                <button onclick="closeModal()" class="text-lightGray hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6" id="transaction-details">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div id="loading-spinner" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
        <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-primary"></div>
    </div>

    <script>
        // CSRF token for AJAX requests
        const csrfToken = "<?= $_SESSION['csrf_token'] ?>";
        
        // Refresh wallet balances
        async function refreshBalances() {
            try {
                showLoading();
                const response = await fetch('api/wallet/balances', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('total-balance').textContent = `KES ${data.total_balance}`;
                    document.getElementById('available-balance').textContent = `KES ${data.available_balance}`;
                    document.getElementById('locked-balance').textContent = `KES ${data.locked_balance}`;
                    document.getElementById('referral-balance').textContent = `KES ${data.referral_balance}`;
                    
                    // Show success notification
                    showNotification('Balances updated successfully', 'success');
                } else {
                    showNotification(data.message || 'Failed to refresh balances', 'error');
                }
            } catch (error) {
                showNotification('Network error. Please try again.', 'error');
                console.error('Error:', error);
            } finally {
                hideLoading();
            }
        }
        
        // Refresh transactions list
        async function refreshTransactions() {
            try {
                showLoading();
                const response = await fetch('api/wallet/transactions?limit=10', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const container = document.getElementById('transactions-list');
                    container.innerHTML = '';
                    
                    if (data.transactions.length > 0) {
                        data.transactions.forEach(txn => {
                            container.innerHTML += `
                                <div class="flex items-center justify-between p-4 bg-darker/40 rounded-xl border border-primary/10 hover:border-primary/20 transition-colors">
                                    <div class="flex items-center">
                                        <div class="p-3 rounded-lg mr-4 ${getTxnIconClass(txn.transaction_type)}">
                                            <i class="${getTxnIcon(txn.transaction_type)}"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-medium">${txn.display_type || txn.transaction_type}</h4>
                                            <p class="text-xs text-lightGray">
                                                ${new Date(txn.created_at).toLocaleString()}
                                                ${txn.description ? `• ${txn.description}` : ''}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold ${txn.amount < 0 ? 'text-red-400' : 'text-green-400'}">
                                            ${txn.amount < 0 ? '-' : '+'}KES ${Math.abs(txn.amount).toFixed(2)}
                                        </p>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${getStatusClass(txn.status)}">
                                            ${txn.status.charAt(0).toUpperCase() + txn.status.slice(1)}
                                        </span>
                                    </div>
                                </div>
                            `;
                        });
                        
                        showNotification('Transactions refreshed', 'success');
                    }
                } else {
                    showNotification(data.message || 'Failed to refresh transactions', 'error');
                }
            } catch (error) {
                showNotification('Network error. Please try again.', 'error');
                console.error('Error:', error);
            } finally {
                hideLoading();
            }
        }
        
        // Export transactions to CSV
        function exportTransactions() {
            window.location.href = `api/wallet/export?csrf_token=${csrfToken}`;
        }
        
        // View transaction details
        async function viewTransaction(id) {
            try {
                showLoading();
                const response = await fetch(`api/wallet/transaction/${id}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('transaction-details').innerHTML = `
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-lightGray">Transaction ID:</span>
                                <span>${data.transaction.id}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-lightGray">Type:</span>
                                <span class="capitalize">${data.transaction.transaction_type}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-lightGray">Amount:</span>
                                <span class="${data.transaction.amount < 0 ? 'text-red-400' : 'text-green-400'}">
                                    ${data.transaction.amount < 0 ? '-' : '+'}KES ${Math.abs(data.transaction.amount).toFixed(2)}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-lightGray">Status:</span>
                                <span class="${getStatusClass(data.transaction.status)} px-2 py-1 rounded-full text-xs">
                                    ${data.transaction.status.charAt(0).toUpperCase() + data.transaction.status.slice(1)}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-lightGray">Date:</span>
                                <span>${new Date(data.transaction.created_at).toLocaleString()}</span>
                            </div>
                            ${data.transaction.description ? `
                            <div class="flex justify-between">
                                <span class="text-lightGray">Description:</span>
                                <span class="text-right">${data.transaction.description}</span>
                            </div>` : ''}
                            ${data.transaction.metadata ? `
                            <div class="mt-4 p-3 bg-darker/40 rounded-lg">
                                <h5 class="text-sm font-medium mb-2">Additional Details</h5>
                                <pre class="text-xs overflow-auto">${JSON.stringify(JSON.parse(data.transaction.metadata), null, 2)}</pre>
                            </div>` : ''}
                        </div>
                    `;
                    
                    document.getElementById('transaction-modal').classList.remove('hidden');
                    document.getElementById('transaction-modal').classList.add('flex');
                } else {
                    showNotification(data.message || 'Failed to load transaction details', 'error');
                }
            } catch (error) {
                showNotification('Network error. Please try again.', 'error');
                console.error('Error:', error);
            } finally {
                hideLoading();
            }
        }
        
        // Helper functions
        function getTxnIcon(type) {
            const icons = {
                'deposit': 'fa-arrow-down',
                'withdrawal': 'fa-arrow-up',
                'transfer': 'fa-exchange-alt',
                'referral': 'fa-users',
                'investment': 'fa-chart-line',
                'fee': 'fa-file-invoice-dollar',
                'default': 'fa-wallet'
            };
            return `fas ${icons[type] || icons.default}`;
        }
        
        function getTxnIconClass(type) {
            const classes = {
                'deposit': 'bg-green-500/10 text-green-400',
                'withdrawal': 'bg-red-500/10 text-red-400',
                'transfer': 'bg-blue-500/10 text-blue-400',
                'referral': 'bg-purple-500/10 text-purple-400',
                'investment': 'bg-yellow-500/10 text-yellow-400',
                'fee': 'bg-gray-500/10 text-gray-400',
                'default': 'bg-primary/10 text-primary'
            };
            return classes[type] || classes.default;
        }
        
        function getStatusClass(status) {
            const classes = {
                'completed': 'bg-green-500/20 text-green-400',
                'failed': 'bg-red-500/20 text-red-400',
                'pending': 'bg-yellow-500/20 text-yellow-400',
                'reversed': 'bg-gray-500/20 text-gray-400'
            };
            return classes[status] || 'bg-gray-500/20 text-gray-400';
        }
        
        function showLoading() {
            document.getElementById('loading-spinner').classList.remove('hidden');
            document.getElementById('loading-spinner').classList.add('flex');
        }
        
        function hideLoading() {
            document.getElementById('loading-spinner').classList.remove('flex');
            document.getElementById('loading-spinner').classList.add('hidden');
        }
        
        function closeModal() {
            document.getElementById('transaction-modal').classList.remove('flex');
            document.getElementById('transaction-modal').classList.add('hidden');
        }
        
        function showNotification(message, type = 'info') {
            // Implement your notification system here
            console.log(`[${type}] ${message}`);
            alert(message); // Replace with a proper notification system
        }
    </script>
</body>
</html>
<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/mpesa.php';

// Session validation
if (!isset($_SESSION['registering_user'])) {
    header('Location: register.php');
    exit;
}

$userId = $_SESSION['registering_user'];
$user = getUserById($userId);

// User validation
if (!$user) {
    unset($_SESSION['registering_user']);
    header('Location: register.php');
    exit;
}

// Redirect if already active
if ($user['is_active']) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// Process payment initiation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        try {
            $db = (new Database())->connect();
            
            // Check for existing pending payments
            $stmt = $db->prepare("SELECT id, created_at FROM payments 
                                WHERE user_id = ? AND status = 'pending'
                                ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$userId]);
            $pendingPayment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($pendingPayment) {
                $createdTime = new DateTime($pendingPayment['created_at']);
                $currentTime = new DateTime();
                $interval = $currentTime->diff($createdTime);
                
                if ($interval->i < 15) { // Within 15-minute window
                    $error = 'You already have a pending payment request. Please check your M-PESA menu.';
                } else {
                    // Expire old pending payment
                    $stmt = $db->prepare("UPDATE payments SET status = 'expired' WHERE id = ?");
                    $stmt->execute([$pendingPayment['id']]);
                }
            }
            
            if (empty($error)) {
                $mpesa = new MpesaPayment();
                $response = $mpesa->initiateSTKPush(
                    $user['phone'],
                    REGISTRATION_FEE,
                    ($_ENV['ACCOUNT_REFERENCE']) . '-' . $user['id'],
                    'Stepcashier Registration'
                );
                
                if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
                    // Record the payment request
                    $stmt = $db->prepare("INSERT INTO payments 
                                        (user_id, amount, merchant_request_id, checkout_request_id, status, created_at) 
                                        VALUES (?, ?, ?, ?, 'pending', NOW())");
                    $stmt->execute([
                        $userId,
                        REGISTRATION_FEE,
                        $response['MerchantRequestID'] ?? '',
                        $response['CheckoutRequestID'] ?? ''
                    ]);
                    
                    $success = 'Payment request sent to your phone. Please complete the payment on your M-PESA menu.';
                } else {
                    $error = $mpesa->getResponseDescription($response);
                }
            }
        } catch (Exception $e) {
            error_log("Payment initiation error: " . $e->getMessage());
            $error = 'System error. Please try again later.';
        }
    }
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Primary SEO Meta Tags -->
    <title>Stepcashier - Earn Money Through Affiliate Referrals | Direct M-Pesa Withdrawals</title>
    <meta name="title" content="Stepcashier - Earn Money Through Affiliate Referrals | Direct M-Pesa Withdrawals">
    <meta name="description" content="Join Stepcashier and start earning money through affiliate referrals today. Enjoy direct M-Pesa withdrawals with a minimum of just KES 100. Simple, fast, and reliable earning platform in Kenya.">
    <meta name="keywords" content="affiliate marketing Kenya, M-Pesa withdrawals, earn money online Kenya, referral program, affiliate commissions, online earning platform, make money Kenya, direct withdrawals">
    <meta name="author" content="Stepcashier">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://stepcashier.com">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://stepcashier.com">
    <meta property="og:title" content="Stepcashier - Earn Money Through Affiliate Referrals | Direct M-Pesa Withdrawals">
    <meta property="og:description" content="Join Stepcashier and start earning money through affiliate referrals today. Enjoy direct M-Pesa withdrawals with a minimum of just KES 100.">
    <meta property="og:image" content="https://stepcashier.com/assets/images/stepcashier-social-preview.jpg">
    <meta property="og:site_name" content="Stepcashier">
    <meta property="og:locale" content="en_KE">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://stepcashier.com">
    <meta property="twitter:title" content="Stepcashier - Earn Money Through Affiliate Referrals | Direct M-Pesa Withdrawals">
    <meta property="twitter:description" content="Join Stepcashier and start earning money through affiliate referrals today. Enjoy direct M-Pesa withdrawals with a minimum of just KES 100.">
    <meta property="twitter:image" content="https://stepcashier.com/assets/images/stepcashier-social-preview.jpg">

    <!-- Additional SEO Meta Tags -->
    <meta name="google-site-verification" content="your-google-site-verification-code">
    <meta name="theme-color" content="#4CAF50">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Stepcashier">

    <!-- Geo Tags for Kenya -->
    <meta name="geo.region" content="KE">
    <meta name="geo.country" content="Kenya">
    <meta name="geo.placename" content="Nairobi">

    <!-- Language and Location -->
    <meta http-equiv="content-language" content="en-KE">
    <link rel="alternate" hreflang="en-ke" href="https://stepcashier.com">
    <link rel="alternate" hreflang="sw-ke" href="https://stepcashier.com/sw">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">

    <!-- Preconnect for Performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">

    <!-- External Stylesheets -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Stepcashier",
        "description": "Affiliate marketing platform with direct M-Pesa withdrawals in Kenya",
        "url": "https://stepcashier.com",
        "logo": "https://stepcashier.com/assets/images/logo.png",
        "sameAs": [
            "https://twitter.com/stepcashier",
            "https://facebook.com/stepcashier"
        ],
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "Kenya",
            "addressRegion": "Nairobi"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "customer service",
            "availableLanguage": ["English", "Swahili"]
        }
    }
    </script>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "Stepcashier",
        "description": "Earn money through affiliate referrals with direct M-Pesa withdrawals",
        "url": "https://stepcashier.com",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "https://stepcashier.com/search?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Stepcashier Affiliate Program",
        "description": "Earn money through affiliate referrals with minimum withdrawal of KES 100 via M-Pesa",
        "provider": {
            "@type": "Organization",
            "name": "Stepcashier"
        },
        "areaServed": {
            "@type": "Country",
            "name": "Kenya"
        },
        "serviceType": "Affiliate Marketing Platform",
        "offers": {
            "@type": "Offer",
            "description": "Direct M-Pesa withdrawals starting from KES 100",
            "priceCurrency": "KES",
            "price": "0",
            "priceSpecification": {
                "@type": "PriceSpecification",
                "minPrice": "100",
                "priceCurrency": "KES",
                "description": "Minimum withdrawal amount"
            }
        }
    }
    </script>
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
                        'spin-slow': 'spin 3s linear infinite',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'bounce-gentle': 'bounceGentle 2s infinite',
                        'fade-in-out': 'fadeInOut 2s ease-in-out infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'progress': 'progress 8s ease-in-out infinite',
                        'glow': 'glow 2s ease-in-out infinite alternate',
                        'wave': 'wave 1.5s ease-in-out infinite',
                        'slide-in': 'slideIn 0.5s ease-out forwards',
                        'tilt': 'tilt 10s ease-in-out infinite',
                    },
                    keyframes: {
                        bounceGentle: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        fadeInOut: {
                            '0%, 100%': { opacity: '0.5' },
                            '50%': { opacity: '1' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0px) rotate(0deg)' },
                            '33%': { transform: 'translateY(-20px) rotate(5deg)' },
                            '66%': { transform: 'translateY(10px) rotate(-3deg)' },
                        },
                        progress: {
                            '0%': { width: '0%' },
                            '50%': { width: '70%' },
                            '100%': { width: '95%' },
                        },
                        glow: {
                            'from': { 
                                boxShadow: '0 0 20px rgba(76, 175, 80, 0.3), 0 0 40px rgba(76, 175, 80, 0.1)',
                                textShadow: '0 0 10px rgba(76, 175, 80, 0.5)'
                            },
                            'to': { 
                                boxShadow: '0 0 30px rgba(76, 175, 80, 0.6), 0 0 60px rgba(76, 175, 80, 0.2)',
                                textShadow: '0 0 20px rgba(76, 175, 80, 0.8)'
                            },
                        },
                        wave: {
                            '0%, 100%': { transform: 'rotate(0deg)' },
                            '25%': { transform: 'rotate(5deg)' },
                            '75%': { transform: 'rotate(-5deg)' },
                        },
                        slideIn: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        tilt: {
                            '0%, 100%': { transform: 'rotate(0deg)' },
                            '25%': { transform: 'rotate(1deg)' },
                            '75%': { transform: 'rotate(-1deg)' },
                        }
                    }
                }
            }
        }
    </script>
    <!-- Performance and Analytics -->
    <link rel="dns-prefetch" href="//www.google-analytics.com">
    
    <!-- Google Analytics (replace with your GA4 measurement ID) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'GA_MEASUREMENT_ID');
    </script>
</head>
<body class="font-sans bg-gradient-to-br from-dark via-darker to-darkest min-h-screen flex items-center justify-center p-4">
    <!-- Main Container -->
    <div class="w-full max-w-lg mx-auto relative z-20">
        <!-- Payment Card -->
        <div class="bg-darker/95 backdrop-blur-sm rounded-2xl shadow-2xl border border-primary/20 overflow-hidden">
            <!-- Header Section -->
            <div class="px-8 pt-8 pb-6 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-xl bg-gradient-to-br from-primary to-primaryDark text-white text-2xl shadow-lg shadow-primary/30 mb-4">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h1 class="text-2xl font-bold text-primary mb-2">Complete Your Registration</h1>
                <p class="text-lightGray text-sm">To activate your account, please pay the one-time registration fee</p>
                <div class="mt-4 inline-flex items-center px-4 py-2 bg-primary/10 border border-primary/30 rounded-full text-primary text-lg font-bold">
                    KES <?php echo number_format(REGISTRATION_FEE); ?>
                </div>
            </div>

            <!-- Alerts Section -->
            <div class="px-8">
                <?php if ($error): ?>
                    <div class="p-3 rounded-lg mb-4 bg-red-500/10 border border-red-500/30 text-red-300 animate-slideIn text-sm">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="p-3 rounded-lg mb-4 bg-green-500/10 border border-green-500/30 text-green-300 animate-slideIn text-sm">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span><?php echo htmlspecialchars($success); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- User Info Section -->
            <div class="px-8 pb-6">
                <div class="bg-gray-800/50 rounded-xl p-4 border border-gray-700/50 mb-6">
                    <h3 class="text-white font-semibold mb-3 flex items-center">
                        <i class="fas fa-user mr-2 text-primary"></i>
                        Payment Details
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-lightGray">Name:</span>
                            <span class="text-white font-medium"><?php echo htmlspecialchars($user['name']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-lightGray">Phone:</span>
                            <span class="text-white font-medium"><?php echo htmlspecialchars($user['phone']); ?></span>
                        </div>
                        <div class="flex justify-between border-t border-gray-700 pt-2 mt-3">
                            <span class="text-lightGray">Amount:</span>
                            <span class="text-primary font-bold text-lg">KES <?php echo number_format(REGISTRATION_FEE); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="mb-6">
                    <h3 class="text-white font-semibold mb-3 flex items-center">
                        <i class="fas fa-mobile-alt mr-2 text-primary"></i>
                        Payment Method
                    </h3>
                    <div class="bg-mpesa/10 border-2 border-mpesa/30 rounded-xl p-4 relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-mpesa rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas fa-mobile-alt text-white text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-white font-semibold">M-PESA</p>
                                    <p class="text-lightGray text-sm">Secure mobile payment</p>
                                </div>
                            </div>
                            <div class="text-mpesa">
                                <i class="fas fa-check-circle text-2xl"></i>
                            </div>
                        </div>
                        <div class="absolute top-0 right-0 w-20 h-20 bg-mpesa/20 rounded-full -translate-y-10 translate-x-10"></div>
                    </div>
                </div>

                <!-- Payment Button -->
                <form method="POST" action="" id="paymentForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <button type="submit" id="payBtn" class="w-full py-4 bg-gradient-to-r from-mpesa to-mpesaDark text-white font-semibold text-lg rounded-xl transition-all hover:shadow-lg hover:shadow-mpesa/30 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-mpesa/50 mb-6">
                        <i class="fas fa-mobile-alt mr-2"></i>
                        Pay Now via M-PESA
                    </button>
                    
                    <!-- Payment status container -->
                    <div id="paymentStatus" class="hidden p-3 rounded-lg mb-4 bg-blue-500/10 border border-blue-500/30 text-blue-300 text-sm">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        <span id="statusMessage">Checking payment status...</span>
                    </div>
                </form>

                <!-- Payment Instructions -->
                <div class="bg-gray-800/50 rounded-xl p-4 border border-gray-700/50">
                    <h3 class="text-white font-semibold mb-3 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-primary"></i>
                        Payment Instructions
                    </h3>
                    <ol class="text-lightGray text-sm space-y-2 list-decimal list-inside">
                        <li>Click "Pay Now via M-PESA" button</li>
                        <li>Check your phone for M-PESA push notification</li>
                        <li>Enter your M-PESA PIN to complete payment</li>
                        <li>Your account will be activated automatically</li>
                    </ol>
                    
                    <div class="mt-4 pt-4 border-t border-gray-700 text-center">
                        <p class="text-lightGray text-sm mb-2">Having trouble?</p>
                        <a href="tel:<?php echo SUPPORT_PHONE; ?>" class="inline-flex items-center text-primary hover:text-primaryDark transition-colors text-sm font-medium">
                            <i class="fas fa-phone mr-2"></i>
                            Call <?php echo SUPPORT_PHONE; ?> for assistance
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Features -->
        <div class="mt-6 grid grid-cols-3 gap-3 text-center">
            <div class="bg-gray-800/30 rounded-lg p-3 border border-gray-700/50">
                <i class="fas fa-shield-alt text-primary text-lg mb-2 block"></i>
                <p class="text-lightGray text-xs">Secure Payment</p>
            </div>
            <div class="bg-gray-800/30 rounded-lg p-3 border border-gray-700/50">
                <i class="fas fa-lock text-primary text-lg mb-2 block"></i>
                <p class="text-lightGray text-xs">Encrypted</p>
            </div>
            <div class="bg-gray-800/30 rounded-lg p-3 border border-gray-700/50">
                <i class="fas fa-bolt text-primary text-lg mb-2 block"></i>
                <p class="text-lightGray text-xs">Instant Activation</p>
            </div>
        </div>
    </div>

    <!-- Payment Timeout Modal -->
    <div id="paymentTimeoutModal" class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center hidden">
        <div class="bg-darker rounded-xl p-6 max-w-sm w-full mx-4">
            <div class="text-center mb-4">
                <div class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-exclamation-triangle text-red-400 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Payment Request Expired</h3>
                <p class="text-lightGray text-sm">Your M-PESA payment request has expired. Please try again.</p>
            </div>
            <button onclick="hideModalAndReset()" 
                    class="w-full py-3 bg-red-500/20 hover:bg-red-500/30 border border-red-500/30 text-red-300 rounded-lg transition-colors">
                Try Again
            </button>
        </div>
    </div>

    <!-- Payment Success Modal -->
    <div id="paymentSuccessModal" class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center hidden">
        <div class="bg-darker rounded-xl p-6 max-w-sm w-full mx-4">
            <div class="text-center mb-4">
                <div class="w-16 h-16 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-check-circle text-green-400 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Payment Successful!</h3>
                <p class="text-lightGray text-sm">Your account has been activated successfully.</p>
            </div>
            <button onclick="redirectToDashboard()" 
                    class="w-full py-3 bg-green-500/20 hover:bg-green-500/30 border border-green-500/30 text-green-300 rounded-lg transition-colors">
                Go to Dashboard
            </button>
        </div>
    </div>

    <script>
        // Payment processing variables
        let paymentCheckInterval;
        let paymentTimeout;
        let isProcessing = false;

        // Enhanced form submission with better status checking
        document.getElementById('paymentForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (isProcessing) return;
            isProcessing = true;
            
            const btn = document.getElementById('payBtn');
            const statusDiv = document.getElementById('paymentStatus');
            const statusMessage = document.getElementById('statusMessage');
            const originalText = btn.innerHTML;
            
            // Show loading state
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing Payment...';
            btn.disabled = true;
            statusDiv.classList.remove('hidden');
            statusMessage.textContent = "Sending payment request to your phone...";
            
            try {
                // Submit form via AJAX
                const formData = new FormData(this);
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const text = await response.text();
                
                // Create a DOM parser to extract the success/error message
                const parser = new DOMParser();
                const doc = parser.parseFromString(text, 'text/html');
                const successDiv = doc.querySelector('.bg-green-500\\/10');
                const errorDiv = doc.querySelector('.bg-red-500\\/10');
                
                if (successDiv) {
                    // Payment request sent successfully
                    statusMessage.innerHTML = '<i class="fas fa-check-circle mr-2"></i> ' + 
                        successDiv.textContent.trim();
                    statusDiv.className = 'p-3 rounded-lg mb-4 bg-green-500/10 border border-green-500/30 text-green-300 text-sm';
                    
                    // Start checking payment status
                    startPaymentStatusCheck(<?php echo $userId; ?>);
                    
                    // Set timeout for payment request (2 minutes)
                    paymentTimeout = setTimeout(() => {
                        if (isProcessing) {
                            handlePaymentTimeout(btn, originalText);
                        }
                    }, 120000);
                    
                } else if (errorDiv) {
                    // Show error message
                    showPaymentError(errorDiv.textContent.trim(), btn, originalText, statusDiv, statusMessage);
                }
            } catch (error) {
                showPaymentError('Network error. Please try again.', btn, originalText, statusDiv, statusMessage);
                console.error('Payment error:', error);
            } finally {
                isProcessing = false;
            }
        });

        function startPaymentStatusCheck(userId) {
            // Clear any existing interval
            if (paymentCheckInterval) {
                clearInterval(paymentCheckInterval);
            }
            
            // Start checking every 5 seconds
            paymentCheckInterval = setInterval(() => {
                checkPaymentStatus(userId);
            }, 5000);
        }

        async function checkPaymentStatus(userId) {
            const statusDiv = document.getElementById('paymentStatus');
            const statusMessage = document.getElementById('statusMessage');
            
            try {
                const response = await fetch(`check_payment.php?user_id=${userId}&_=${new Date().getTime()}`);
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                const data = await response.json();
                
                if (data.status === 'completed') {
                    // Payment completed
                    clearPaymentIntervals();
                    showPaymentSuccess();
                } else if (data.status === 'failed') {
                    // Payment failed
                    clearPaymentIntervals();
                    showPaymentError(data.message || 'Payment failed. Please try again.');
                }
                // If pending, do nothing - will check again on next interval
            } catch (error) {
                console.error('Status check error:', error);
                // Don't show error to user - will retry on next interval
            }
        }

        function clearPaymentIntervals() {
            if (paymentCheckInterval) {
                clearInterval(paymentCheckInterval);
                paymentCheckInterval = null;
            }
            
            if (paymentTimeout) {
                clearTimeout(paymentTimeout);
                paymentTimeout = null;
            }
            
            isProcessing = false;
        }

        function handlePaymentTimeout(btn, originalText) {
            clearPaymentIntervals();
            
            const statusDiv = document.getElementById('paymentStatus');
            const statusMessage = document.getElementById('statusMessage');
            
            statusMessage.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i> Payment request expired';
            statusDiv.className = 'p-3 rounded-lg mb-4 bg-red-500/10 border border-red-500/30 text-red-300 text-sm';
            
            btn.innerHTML = originalText;
            btn.disabled = false;
            
            document.getElementById('paymentTimeoutModal').classList.remove('hidden');
        }

        function showPaymentError(message, btn = null, originalText = null, statusDiv = null, statusMessage = null) {
            if (!statusDiv) statusDiv = document.getElementById('paymentStatus');
            if (!statusMessage) statusMessage = document.getElementById('statusMessage');
            
            statusMessage.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i> ' + message;
            statusDiv.className = 'p-3 rounded-lg mb-4 bg-red-500/10 border border-red-500/30 text-red-300 text-sm';
            statusDiv.classList.remove('hidden');
            
            if (btn && originalText) {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        function showPaymentSuccess() {
            const statusDiv = document.getElementById('paymentStatus');
            const statusMessage = document.getElementById('statusMessage');
            
            statusMessage.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Payment received!';
            statusDiv.className = 'p-3 rounded-lg mb-4 bg-green-500/10 border border-green-500/30 text-green-300 text-sm';
            
            document.getElementById('paymentSuccessModal').classList.remove('hidden');
        }

        function hideModalAndReset() {
            document.getElementById('paymentTimeoutModal').classList.add('hidden');
            resetPaymentForm();
        }

        function resetPaymentForm() {
            const btn = document.getElementById('payBtn');
            const originalText = '<i class="fas fa-mobile-alt mr-2"></i> Pay Now via M-PESA';
            
            btn.innerHTML = originalText;
            btn.disabled = false;
            
            const statusDiv = document.getElementById('paymentStatus');
            statusDiv.classList.add('hidden');
            
            clearPaymentIntervals();
        }

        function redirectToDashboard() {
            window.location.href = 'dashboard.php';
        }

        // Clean up intervals when leaving the page
        window.addEventListener('beforeunload', function() {
            clearPaymentIntervals();
        });
    </script>
</body>
</html>
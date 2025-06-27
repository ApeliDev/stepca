<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/notifications.php';

$error = '';
$success = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Verify token
    $userId = validatePasswordResetToken($token);
    if (!$userId) {
        $error = 'Invalid or expired reset token.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid form submission. Please try again.';
    } elseif (!rateLimit('password_reset')) {
        $error = 'Too many reset attempts. Please try again later.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        // Verify token again
        $userId = validatePasswordResetToken($token);
        
        if ($userId) {
            // Update password
            if (resetUserPassword($userId, $password)) {
                // Send notification
                Notification::sendPasswordChangedNotification($userId);
                
                $success = 'Your password has been updated successfully. You can now <a href="login.php" class="text-primary font-semibold hover:underline">login</a> with your new password.';
            } else {
                $error = 'Failed to update password. Please try again.';
            }
        } else {
            $error = 'Invalid or expired reset token.';
        }
    }
} else {
    header('Location: forgotpassword.php');
    exit;
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
                        float: 'float 6s ease-in-out infinite',
                        slideIn: 'slideIn 0.3s ease-out',
                        pulse: 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
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
    <!-- Animated Background -->
    <div class="fixed top-0 left-0 w-full h-full pointer-events-none z-10">
        <div class="absolute top-[10%] left-[10%] text-primary opacity-10 text-2xl animate-float"><i class="fas fa-key"></i></div>
        <div class="absolute top-[20%] right-[10%] text-primary opacity-10 text-2xl animate-float" style="animation-delay: 1s;"><i class="fas fa-shield-alt"></i></div>
        <div class="absolute bottom-[30%] left-[15%] text-primary opacity-10 text-2xl animate-float" style="animation-delay: 2s;"><i class="fas fa-lock"></i></div>
        <div class="absolute bottom-[10%] right-[20%] text-primary opacity-10 text-2xl animate-float" style="animation-delay: 3s;"><i class="fas fa-user-lock"></i></div>
        <div class="absolute top-[50%] left-[5%] text-primary opacity-10 text-2xl animate-float" style="animation-delay: 4s;"><i class="fas fa-check-circle"></i></div>
        <div class="absolute top-[60%] right-[5%] text-primary opacity-10 text-2xl animate-float" style="animation-delay: 5s;"><i class="fas fa-eye-slash"></i></div>
    </div>

    <!-- Main Container -->
    <div class="w-full max-w-md mx-auto relative z-20">
        <!-- Set New Password Card -->
        <div class="bg-darker/95 backdrop-blur-sm rounded-2xl shadow-2xl border border-primary/20 overflow-hidden">
            <!-- Header Section -->
            <div class="px-8 pt-10 pb-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-xl bg-gradient-to-br from-primary to-primaryDark text-white text-2xl shadow-lg shadow-primary/30 mb-6">
                    <i class="fas fa-key"></i>
                </div>
                <h1 class="text-2xl font-bold text-primary mb-2">StepCashier</h1>
                <p class="text-lightGray text-sm mb-6">Smart Investment Platform</p>
                <h2 class="text-xl font-semibold text-white mb-1">Set New Password</h2>
                <p class="text-lightGray text-sm">Create a strong, secure password for your account</p>
            </div>

            <!-- Form Section -->
            <div class="px-8 pb-8">
                <?php if (!empty($error)): ?>
                    <div class="p-3 rounded-lg mb-6 bg-red-500/10 border border-red-500/30 text-red-300 animate-slideIn text-sm">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="p-3 rounded-lg mb-6 bg-green-500/10 border border-green-500/30 text-green-300 animate-slideIn text-sm">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span><?php echo $success; ?></span>
                    </div>
                    <div class="text-center mt-6">
                        <a href="login.php" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary to-primaryDark text-white font-semibold rounded-lg transition-all hover:shadow-lg hover:shadow-primary/30 hover:-translate-y-0.5">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Continue to Login
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (isset($userId) && !$success): ?>
                <form method="POST" action="" id="passwordResetForm" class="space-y-6">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    
                    <!-- New Password Field -->
                    <div>
                        <label for="password" class="block text-lighterGray text-sm font-medium mb-2">New Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-lightGray">
                                <i class="fas fa-lock text-sm"></i>
                            </div>
                            <input type="password" id="password" name="password" placeholder="Enter new password" required minlength="8"
                                class="w-full pl-10 pr-12 py-3 bg-gray-800/80 border border-gray-600/50 rounded-lg text-white placeholder-gray-400 transition-all focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
                            <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-lightGray hover:text-primary transition-colors">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                        <!-- Password Strength Indicator -->
                        <div class="mt-2">
                            <div class="flex items-center space-x-1 mb-1">
                                <div class="h-1 flex-1 bg-gray-700 rounded-full overflow-hidden">
                                    <div id="passwordStrength" class="h-full transition-all duration-300 rounded-full"></div>
                                </div>
                                <span id="strengthText" class="text-xs text-lightGray min-w-16"></span>
                            </div>
                            <div id="passwordRequirements" class="text-xs text-lightGray space-y-1">
                                <div id="req-length" class="flex items-center"><i class="fas fa-circle text-gray-500 mr-2 text-xs"></i> At least 8 characters</div>
                                <div id="req-uppercase" class="flex items-center"><i class="fas fa-circle text-gray-500 mr-2 text-xs"></i> One uppercase letter</div>
                                <div id="req-lowercase" class="flex items-center"><i class="fas fa-circle text-gray-500 mr-2 text-xs"></i> One lowercase letter</div>
                                <div id="req-number" class="flex items-center"><i class="fas fa-circle text-gray-500 mr-2 text-xs"></i> One number</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Confirm Password Field -->
                    <div>
                        <label for="confirm_password" class="block text-lighterGray text-sm font-medium mb-2">Confirm Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-lightGray">
                                <i class="fas fa-lock text-sm"></i>
                            </div>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required minlength="8"
                                class="w-full pl-10 pr-12 py-3 bg-gray-800/80 border border-gray-600/50 rounded-lg text-white placeholder-gray-400 transition-all focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
                            <button type="button" id="toggleConfirmPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-lightGray hover:text-primary transition-colors">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                        <div id="passwordMatch" class="mt-2 text-xs hidden">
                            <div class="flex items-center">
                                <i id="matchIcon" class="mr-2 text-xs"></i>
                                <span id="matchText"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" id="submitBtn" disabled class="w-full py-3 bg-gradient-to-r from-gray-600 to-gray-700 text-gray-300 font-semibold rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-primary/50 cursor-not-allowed">
                        <i class="fas fa-save mr-2"></i>
                        Update Password
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Security Badge -->
        <div class="mt-6 text-center">
            <div class="inline-flex items-center px-4 py-2 bg-gray-800/50 rounded-full text-lightGray text-xs">
                <i class="fas fa-shield-alt text-primary mr-2"></i>
                Your password is encrypted and secure
            </div>
        </div>
    </div>

    <script src="assets/js/reset.js"></script>
    <script>
        
    // Password visibility toggles
    document.getElementById('togglePassword').addEventListener('click', function() {
        const password = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            password.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
        const confirmPassword = document.getElementById('confirm_password');
        const icon = this.querySelector('i');
        
        if (confirmPassword.type === 'password') {
            confirmPassword.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            confirmPassword.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    // Password strength checker
    function checkPasswordStrength(password) {
        let score = 0;
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /\d/.test(password)
        };

        // Update requirement indicators
        Object.keys(requirements).forEach(req => {
            const element = document.getElementById(`req-${req}`);
            const icon = element.querySelector('i');
            if (requirements[req]) {
                icon.classList.remove('fa-circle', 'text-gray-500');
                icon.classList.add('fa-check-circle', 'text-green-400');
                element.classList.add('text-green-400');
                score++;
            } else {
                icon.classList.remove('fa-check-circle', 'text-green-400');
                icon.classList.add('fa-circle', 'text-gray-500');
                element.classList.remove('text-green-400');
            }
        });

        // Update strength bar
        const strengthBar = document.getElementById('passwordStrength');
        const strengthText = document.getElementById('strengthText');
        
        if (score === 0) {
            strengthBar.style.width = '0%';
            strengthText.textContent = '';
        } else if (score <= 2) {
            strengthBar.style.width = '33%';
            strengthBar.className = 'h-full transition-all duration-300 rounded-full bg-red-500';
            strengthText.textContent = 'Weak';
            strengthText.className = 'text-xs text-red-400 min-w-16';
        } else if (score === 3) {
            strengthBar.style.width = '66%';
            strengthBar.className = 'h-full transition-all duration-300 rounded-full bg-yellow-500';
            strengthText.textContent = 'Good';
            strengthText.className = 'text-xs text-yellow-400 min-w-16';
        } else {
            strengthBar.style.width = '100%';
            strengthBar.className = 'h-full transition-all duration-300 rounded-full bg-green-500';
            strengthText.textContent = 'Strong';
            strengthText.className = 'text-xs text-green-400 min-w-16';
        }

        return score;
    }

    // Password matching checker
    function checkPasswordMatch() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const matchDiv = document.getElementById('passwordMatch');
        const matchIcon = document.getElementById('matchIcon');
        const matchText = document.getElementById('matchText');

        if (confirmPassword.length > 0) {
            matchDiv.classList.remove('hidden');
            if (password === confirmPassword) {
                matchIcon.className = 'fas fa-check-circle text-green-400 mr-2 text-xs';
                matchText.textContent = 'Passwords match';
                matchText.className = 'text-green-400';
                return true;
            } else {
                matchIcon.className = 'fas fa-times-circle text-red-400 mr-2 text-xs';
                matchText.textContent = 'Passwords do not match';
                matchText.className = 'text-red-400';
                return false;
            }
        } else {
            matchDiv.classList.add('hidden');
            return false;
        }
    }

    // Update submit button state
    function updateSubmitButton() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const submitBtn = document.getElementById('submitBtn');
        
        const strengthScore = checkPasswordStrength(password);
        const passwordsMatch = checkPasswordMatch();
        
        if (strengthScore >= 3 && passwordsMatch && password.length >= 8) {
            submitBtn.disabled = false;
            submitBtn.className = 'w-full py-3 bg-gradient-to-r from-primary to-primaryDark text-white font-semibold rounded-lg transition-all hover:shadow-lg hover:shadow-primary/30 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-primary/50';
        } else {
            submitBtn.disabled = true;
            submitBtn.className = 'w-full py-3 bg-gradient-to-r from-gray-600 to-gray-700 text-gray-300 font-semibold rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-primary/50 cursor-not-allowed';
        }
    }

    // Event listeners
    document.getElementById('password').addEventListener('input', updateSubmitButton);
    document.getElementById('confirm_password').addEventListener('input', updateSubmitButton);

    // Form submission
    document.getElementById('passwordResetForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        
        if (!submitBtn.disabled) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating Password...';
            
            // Re-enable after 5 seconds in case of error
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Update Password';
            }, 5000);
        }
    });

    </script>
</body>
</html>
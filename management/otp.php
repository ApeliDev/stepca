<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Security headers
//header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
//header("X-Frame-Options: DENY");
//header("X-Content-Type-Options: nosniff");
//header("Referrer-Policy: strict-origin-when-cross-origin");

// Check if user should be on this page
if (!isset($_SESSION['admin_temp_id'])) {
    header('Location: login.php');
    exit;
}

// If already fully logged in, redirect to dashboard
if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';
$otpMethod = isset($_SESSION['admin_otp_method']) ? $_SESSION['admin_otp_method'] : 'email';

// Process OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify_otp'])) {
        if (!validateCSRFToken($_POST['csrf_token'])) {
            $error = 'Invalid form submission. Please try again.';
        } else {
            $otp = trim($_POST['otp']);
            $adminId = $_SESSION['admin_temp_id'];
            
            $result = verifyAdminOTP($adminId, $otp);
            if ($result['success']) {
                // OTP verified successfully, redirect to dashboard
                header('Location: dashboard.php');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    } elseif (isset($_POST['resend_otp'])) {
        $result = resendAdminOTP();
        if ($result['success']) {
            $success = 'New OTP has been sent.';
        } else {
            $error = $result['message'];
        }
    }
}

$admin = getCurrentAdmin();
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - StepCashier Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4CAF50',
                        adminPrimary: '#6366F1',
                        adminPrimaryDark: '#4F46E5',
                        dark: '#0f0f23',
                        darker: '#1a1a2e',
                        lightGray: '#9CA3AF',
                        lighterGray: '#D1D5DB',
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans bg-gradient-to-br from-dark via-darker to-dark min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md mx-auto">
        <div class="bg-darker/95 backdrop-blur-sm rounded-2xl shadow-2xl border border-adminPrimary/20 overflow-hidden">
            <!-- Header -->
            <div class="px-8 pt-8 pb-6 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-xl bg-gradient-to-br from-adminPrimary to-adminPrimaryDark text-white text-2xl shadow-lg mb-6">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">Two-Factor Authentication</h1>
                <p class="text-lightGray text-sm">
                    We've sent a verification code to your 
                    <?php echo $otpMethod === 'email' ? 'email' : 'phone'; ?>
                </p>
                <?php if ($admin): ?>
                    <p class="text-adminPrimary text-xs mt-2">
                        <?php echo $otpMethod === 'email' ? 'Email: ' . substr($admin['email'], 0, 3) . '***@***' . substr(strrchr($admin['email'], '@'), 3) : 'SMS sent'; ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Form Section -->
            <div class="px-8 pb-8">
                <?php if (!empty($error)): ?>
                    <div class="p-3 rounded-lg mb-6 bg-red-500/10 border border-red-500/30 text-red-300 text-sm">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="p-3 rounded-lg mb-6 bg-green-500/10 border border-green-500/30 text-green-300 text-sm">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="otpForm" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    
                    <!-- OTP Input -->
                    <div>
                        <label for="otp" class="block text-lighterGray text-sm font-medium mb-2">
                            Enter 6-digit verification code
                        </label>
                        <input type="text" id="otp" name="otp" maxlength="6" pattern="[0-9]{6}" required
                            class="w-full px-4 py-3 text-center text-2xl tracking-widest bg-gray-800/80 border border-gray-600/50 rounded-lg text-white placeholder-gray-400 transition-all focus:outline-none focus:border-adminPrimary focus:ring-2 focus:ring-adminPrimary/20"
                            placeholder="000000" autocomplete="one-time-code">
                    </div>
                    
                    <!-- Verify Button -->
                    <button type="submit" name="verify_otp" class="w-full py-3 bg-gradient-to-r from-adminPrimary to-adminPrimaryDark text-white font-semibold rounded-lg transition-all hover:shadow-lg hover:shadow-adminPrimary/30 hover:-translate-y-0.5">
                        <i class="fas fa-check mr-2"></i>
                        Verify Code
                    </button>
                    
                    <!-- Resend OTP -->
                    <div class="text-center">
                        <p class="text-lightGray text-sm mb-3">Didn't receive the code?</p>
                        <button type="submit" name="resend_otp" class="text-adminPrimary hover:text-adminPrimaryDark transition-colors text-sm font-medium">
                            <i class="fas fa-redo mr-1"></i>
                            Resend Code
                        </button>
                    </div>
                    
                    <!-- Logout Option -->
                    <div class="pt-4 border-t border-gray-600/30">
                        <a href="<?php echo BASE_URL; ?>/management/logout" class="block w-full text-center py-2 text-lightGray hover:text-white transition-colors text-sm">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Cancel & Logout
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Timer -->
        <div class="mt-6 text-center">
            <div class="inline-flex items-center px-4 py-2 bg-gray-800/50 rounded-full text-lightGray text-xs">
                <i class="fas fa-clock text-adminPrimary mr-2"></i>
                Code expires in <span id="timer" class="ml-1 font-mono">10:00</span>
            </div>
        </div>
    </div>

    <script>
        // Auto-focus OTP input
        document.getElementById('otp').focus();
        
        // Auto-submit when 6 digits entered
        document.getElementById('otp').addEventListener('input', function(e) {
            if (e.target.value.length === 6) {
                // Small delay to show the complete code
                setTimeout(() => {
                    document.getElementById('otpForm').querySelector('button[name="verify_otp"]').click();
                }, 500);
            }
        });
        
        // Only allow numbers
        document.getElementById('otp').addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'Tab') {
                e.preventDefault();
            }
        });
        
        // Countdown timer (10 minutes)
        let timeLeft = 600; // 10 minutes in seconds
        const timerElement = document.getElementById('timer');
        
        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                timerElement.textContent = 'Expired';
                timerElement.classList.add('text-red-400');
                return;
            }
            
            timeLeft--;
            setTimeout(updateTimer, 1000);
        }
        
        updateTimer();
    </script>
</body>
</html>
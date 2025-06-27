<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/notifications.php';

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid form submission. Please try again.';
    } elseif (!rateLimit('admin_password_reset', 5, 1800)) { // Stricter: 5 attempts per 30 minutes
        $error = 'Too many reset attempts. Please try again later.';
    } else {
        $email = trim($_POST['email']);
        
        // Basic validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Generate reset token
            $token = generateAdminPasswordResetToken($email);
            
            if ($token) {
                // Get admin ID for notification
                $db = (new Database())->connect();
                $stmt = $db->prepare("SELECT id, name FROM admins WHERE email = ?");
                $stmt->execute([$email]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($admin) {
                    // Log the password reset request
                    logAdminActivity($admin['id'], 'password_reset_requested', null, null, null, null, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
                    
                    // Send reset email 
                    Notification::sendAdminPasswordResetEmail($admin['id'], $token);
                    
                    $success = 'If this email exists in our admin system, you will receive a reset link shortly.';
                }
            } else {
                $success = 'If this email exists in our admin system, you will receive a reset link shortly.';
            }
        }
    }
}
// Generate CSRF token
$csrfToken = generateCSRFToken();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StepCashier - Admin Password Reset</title>
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
                        adminPrimary: '#6366F1',
                        adminPrimaryDark: '#4F46E5',
                    },
                    animation: {
                        float: 'float 6s ease-in-out infinite',
                        slideIn: 'slideIn 0.3s ease-out',
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
</head>
<body class="font-sans bg-gradient-to-br from-dark via-darker to-darkest min-h-screen flex items-center justify-center p-4">
    <!-- Animated Background -->
    <div class="fixed top-0 left-0 w-full h-full pointer-events-none z-10">
        <div class="absolute top-[10%] left-[10%] text-adminPrimary opacity-10 text-2xl animate-float"><i class="fas fa-key"></i></div>
        <div class="absolute top-[20%] right-[10%] text-adminPrimary opacity-10 text-2xl animate-float" style="animation-delay: 1s;"><i class="fas fa-shield-alt"></i></div>
        <div class="absolute bottom-[30%] left-[15%] text-adminPrimary opacity-10 text-2xl animate-float" style="animation-delay: 2s;"><i class="fas fa-user-shield"></i></div>
        <div class="absolute bottom-[10%] right-[20%] text-adminPrimary opacity-10 text-2xl animate-float" style="animation-delay: 3s;"><i class="fas fa-envelope"></i></div>
        <div class="absolute top-[50%] left-[5%] text-adminPrimary opacity-10 text-2xl animate-float" style="animation-delay: 4s;"><i class="fas fa-cog"></i></div>
        <div class="absolute top-[60%] right-[5%] text-adminPrimary opacity-10 text-2xl animate-float" style="animation-delay: 5s;"><i class="fas fa-check-circle"></i></div>
    </div>

    <!-- Main Container -->
    <div class="w-full max-w-md mx-auto relative z-20">
        <!-- Password Reset Card -->
        <div class="bg-darker/95 backdrop-blur-sm rounded-2xl shadow-2xl border border-adminPrimary/20 overflow-hidden">
            <!-- Header Section -->
            <div class="px-8 pt-10 pb-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-xl bg-gradient-to-br from-adminPrimary to-adminPrimaryDark text-white text-2xl shadow-lg shadow-adminPrimary/30 mb-6">
                    <i class="fas fa-key"></i>
                </div>
                <h1 class="text-2xl font-bold text-adminPrimary mb-2">StepCashier Admin</h1>
                <p class="text-lightGray text-sm mb-6">Administrative Portal</p>
                
                <!-- Admin Badge -->
                <div class="inline-flex items-center px-3 py-1 bg-adminPrimary/10 border border-adminPrimary/30 rounded-full mb-4">
                    <i class="fas fa-shield-alt text-adminPrimary text-xs mr-2"></i>
                    <span class="text-adminPrimary text-xs font-medium">Admin Reset</span>
                </div>
                
                <h2 class="text-xl font-semibold text-white mb-1">Reset Admin Password</h2>
                <p class="text-lightGray text-sm">Enter your admin email to receive a secure reset link</p>
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
                        <span><?php echo htmlspecialchars($success); ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo BASE_URL; ?>/management/forgotpassword" id="adminResetForm" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    
                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-lighterGray text-sm font-medium mb-2">Admin Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-lightGray">
                                <i class="fas fa-envelope text-sm"></i>
                            </div>
                            <input type="email" id="email" name="email" placeholder="Enter your admin email address" required
                                class="w-full pl-10 pr-4 py-3 bg-gray-800/80 border border-gray-600/50 rounded-lg text-white placeholder-gray-400 transition-all focus:outline-none focus:border-adminPrimary focus:ring-2 focus:ring-adminPrimary/20">
                        </div>
                        <p class="text-lightGray text-xs mt-2">
                            <i class="fas fa-info-circle text-adminPrimary mr-1"></i>
                            We'll send you a secure link to reset your admin password
                        </p>
                    </div>
                    
                    <!-- Security Notice -->
                    <div class="p-3 rounded-lg bg-yellow-500/10 border border-yellow-500/30 text-yellow-300 text-sm">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <strong>Security Notice:</strong> This action will be logged for security purposes.
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-adminPrimary to-adminPrimaryDark text-white font-semibold rounded-lg transition-all hover:shadow-lg hover:shadow-adminPrimary/30 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-adminPrimary/50">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Send Admin Reset Link
                    </button>
                    
                    <!-- Back Links -->
                    <div class="text-center pt-4 space-y-2">
                        <p class="text-lightGray text-sm">
                            Remember your password? 
                            <a href="<?php echo BASE_URL; ?>/management/login" class="text-adminPrimary font-medium hover:text-adminPrimaryDark hover:underline transition-colors">Admin Login</a>
                        </p>
                        <p class="text-lightGray text-sm">
                            Need user access? 
                            <a href="<?php echo BASE_URL; ?>/login" class="text-primary font-medium hover:text-primaryDark hover:underline transition-colors">User Login</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <!-- Enhanced Security Badge -->
        <div class="mt-6 text-center">
            <div class="inline-flex items-center px-4 py-2 bg-gray-800/50 rounded-full text-lightGray text-xs">
                <i class="fas fa-shield-alt text-adminPrimary mr-2"></i>
                Enhanced security • All actions logged • Email verification required
            </div>
        </div>
    </div>

    <script>
        // Form validation
        document.getElementById('adminResetForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            
            if (!email) {
                e.preventDefault();
                alert('Please enter your admin email address');
                return false;
            }
            
            // Admin email validation
            if (!email.includes('@') || email.length < 5) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return false;
            }
            
            
        });
    </script>
</body>
</html>
<?php
require_once '../includes/config.php';
require_once '../includes/admin_auth.php';
require_once '../includes/functions.php';


header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");


if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid form submission. Please try again.';
    } elseif (!rateLimit('admin_login', 3, 600)) { // Stricter rate limiting for admin
        $error = 'Too many login attempts. Please try again later.';
    } else {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        $loginResult = loginAdmin($email, $password);
        if ($loginResult['success']) {
            // Update last login and reset login attempts
            $db = (new Database())->connect();
            $stmt = $db->prepare("UPDATE admins SET last_login = NOW(), login_attempts = 0, locked_until = NULL WHERE email = ?");
            $stmt->execute([$email]);
            
            // Log admin login
            logAdminActivity($_SESSION['admin_id'], 'login', null, null, null, null, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
            
            // Redirect to admin dashboard
            header('Location: dashboard.php');
            exit;
        } else {
            $error = $loginResult['message'];
            
            // Increment login attempts for this email
            $db = (new Database())->connect();
            $stmt = $db->prepare("UPDATE admins SET login_attempts = login_attempts + 1 WHERE email = ?");
            $stmt->execute([$email]);
            
            // Lock account after 5 failed attempts
            $stmt = $db->prepare("SELECT login_attempts FROM admins WHERE email = ?");
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin && $admin['login_attempts'] >= 5) {
                $lockUntil = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                $stmt = $db->prepare("UPDATE admins SET locked_until = ? WHERE email = ?");
                $stmt->execute([$lockUntil, $email]);
                $error = 'Account locked due to too many failed attempts. Try again in 30 minutes.';
            }
        }
    }
}

$csrfToken = generateCSRFToken();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StepCashier - Admin Portal Login</title>
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
</head>
<body class="font-sans bg-gradient-to-br from-dark via-darker to-darkest min-h-screen flex items-center justify-center p-4">
    <!-- Animated Background -->
    <div class="fixed top-0 left-0 w-full h-full pointer-events-none z-10">
        <div class="absolute top-[10%] left-[10%] text-adminPrimary opacity-10 text-2xl animate-float"><i class="fas fa-user-shield"></i></div>
        <div class="absolute top-[20%] right-[10%] text-adminPrimary opacity-10 text-2xl animate-float" style="animation-delay: 1s;"><i class="fas fa-cog"></i></div>
        <div class="absolute bottom-[30%] left-[15%] text-adminPrimary opacity-10 text-2xl animate-float" style="animation-delay: 2s;"><i class="fas fa-database"></i></div>
        <div class="absolute bottom-[10%] right-[20%] text-adminPrimary opacity-10 text-2xl animate-float" style="animation-delay: 3s;"><i class="fas fa-chart-bar"></i></div>
        <div class="absolute top-[50%] left-[5%] text-adminPrimary opacity-10 text-2xl animate-float" style="animation-delay: 4s;"><i class="fas fa-users"></i></div>
        <div class="absolute top-[60%] right-[5%] text-adminPrimary opacity-10 text-2xl animate-float" style="animation-delay: 5s;"><i class="fas fa-key"></i></div>
    </div>

    <!-- Main Container -->
    <div class="w-full max-w-md mx-auto relative z-20">
        <!-- Login Card -->
        <div class="bg-darker/95 backdrop-blur-sm rounded-2xl shadow-2xl border border-adminPrimary/20 overflow-hidden">
            <!-- Header Section -->
            <div class="px-8 pt-10 pb-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-xl bg-gradient-to-br from-adminPrimary to-adminPrimaryDark text-white text-2xl shadow-lg shadow-adminPrimary/30 mb-6">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h1 class="text-2xl font-bold text-adminPrimary mb-2">StepCashier Admin</h1>
                <h2 class="text-xl font-semibold text-white mb-1">Admin Portal</h2>
                <p class="text-lightGray text-sm">Secure administrative access</p>
                
                <!-- Admin Badge -->
                <div class="inline-flex items-center px-3 py-1 bg-adminPrimary/10 border border-adminPrimary/30 rounded-full mt-4">
                    <i class="fas fa-shield-alt text-adminPrimary text-xs mr-2"></i>
                    <span class="text-adminPrimary text-xs font-medium">Administrative Access</span>
                </div>
            </div>

            <!-- Form Section -->
            <div class="px-8 pb-8">
                <?php if (!empty($error)): ?>
                    <div class="p-3 rounded-lg mb-6 bg-red-500/10 border border-red-500/30 text-red-300 animate-slideIn text-sm">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo BASE_URL; ?>/management/login" id="adminLoginForm" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    
                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-lighterGray text-sm font-medium mb-2">Admin Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-lightGray">
                                <i class="fas fa-envelope text-sm"></i>
                            </div>
                            <input type="email" id="email" name="email" placeholder="Enter admin email" required
                                class="w-full pl-10 pr-4 py-3 bg-gray-800/80 border border-gray-600/50 rounded-lg text-white placeholder-gray-400 transition-all focus:outline-none focus:border-adminPrimary focus:ring-2 focus:ring-adminPrimary/20">
                        </div>
                    </div>
                    
                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-lighterGray text-sm font-medium mb-2">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-lightGray">
                                <i class="fas fa-lock text-sm"></i>
                            </div>
                            <input type="password" id="password" name="password" placeholder="Enter admin password" required
                                class="w-full pl-10 pr-12 py-3 bg-gray-800/80 border border-gray-600/50 rounded-lg text-white placeholder-gray-400 transition-all focus:outline-none focus:border-adminPrimary focus:ring-2 focus:ring-adminPrimary/20">
                            <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-lightGray hover:text-adminPrimary transition-colors">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                        </div>

                        <div class="text-right mt-2">
                            <a href="<?php echo BASE_URL; ?>/management/forgotpassword" class="text-primary text-sm hover:text-primaryDark hover:underline transition-colors">Forgot password?</a>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-adminPrimary to-adminPrimaryDark text-white font-semibold rounded-lg transition-all hover:shadow-lg hover:shadow-adminPrimary/30 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-adminPrimary/50">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Access Admin Panel
                    </button>
                    
                    <!-- Back to User Login -->
                    <p class="text-lightGray text-sm text-center pt-4">
                        Not an admin? 
                        <a href="<?php echo BASE_URL; ?>/login" class="text-primary font-medium hover:text-primaryDark hover:underline transition-colors">User Login</a>
                    </p>
                </form>
            </div>
        </div>

        <!-- Security Badge -->
        <div class="mt-6 text-center">
            <div class="inline-flex items-center px-4 py-2 bg-gray-800/50 rounded-full text-lightGray text-xs">
                <i class="fas fa-shield-alt text-adminPrimary mr-2"></i>
                Enhanced security • Activity logged
            </div>
        </div>
    </div>
    <?php include 'js/login.php'; ?>
</body>
</html>
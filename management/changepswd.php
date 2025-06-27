<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Security headers
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

// Check if user should be on this page
if (!isset($_SESSION['admin_password_change_required']) || !isset($_SESSION['admin_temp_id'])) {
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
$errors = [];

// Process password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Validate passwords match
        if ($newPassword !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            // Validate password strength
            $strengthCheck = validateAdminPasswordStrength($newPassword);
            if (!$strengthCheck['valid']) {
                $errors = $strengthCheck['errors'];
            } else {
                // Change password
                $adminId = $_SESSION['admin_temp_id'];
                $result = forceAdminPasswordChange($adminId, $newPassword);
                
                if ($result['success']) {
                    // Password changed successfully
                    unset($_SESSION['admin_password_change_required']);
                    unset($_SESSION['admin_temp_id']);
                    
                    // Set full session
                    $db = (new Database())->connect();
                    $stmt = $db->prepare("SELECT * FROM admins WHERE id = ?");
                    $stmt->execute([$adminId]);
                    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($admin) {
                        setAdminSession($admin);
                        header('Location: dashboard.php');
                        exit;
                    }
                } else {
                    $error = $result['message'] ?? 'Failed to change password. Please try again.';
                    if (isset($result['errors'])) {
                        $errors = $result['errors'];
                    }
                }
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
    <title>Password Change Required - StepCashier Admin</title>
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
        <div class="bg-darker/95 backdrop-blur-sm rounded-2xl shadow-2xl border border-orange-400/20 overflow-hidden">
            <!-- Header -->
            <div class="px-8 pt-8 pb-6 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-xl bg-gradient-to-br from-orange-500 to-red-500 text-white text-2xl shadow-lg mb-6">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">Password Change Required</h1>
                <p class="text-lightGray text-sm">
                    Your password has expired and must be changed before you can continue.
                </p>
                <div class="mt-4 p-3 bg-orange-500/10 border border-orange-500/30 rounded-lg">
                    <p class="text-orange-300 text-xs">
                        <i class="fas fa-info-circle mr-1"></i>
                        For security, admin passwords expire every 90 days.
                    </p>
                </div>
            </div>

            <!-- Form Section -->
            <div class="px-8 pb-8">
                <?php if (!empty($error)): ?>
                    <div class="p-3 rounded-lg mb-6 bg-red-500/10 border border-red-500/30 text-red-300 text-sm">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="p-3 rounded-lg mb-6 bg-red-500/10 border border-red-500/30 text-red-300 text-sm">
                        <div class="font-medium mb-2">Password requirements not met:</div>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="passwordChangeForm" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    
                    <!-- New Password -->
                    <div>
                        <label for="new_password" class="block text-lighterGray text-sm font-medium mb-2">
                            New Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-lightGray">
                                <i class="fas fa-lock text-sm"></i>
                            </div>
                            <input type="password" id="new_password" name="new_password" required
                                class="w-full pl-10 pr-12 py-3 bg-gray-800/80 border border-gray-600/50 rounded-lg text-white placeholder-gray-400 transition-all focus:outline-none focus:border-adminPrimary focus:ring-2 focus:ring-adminPrimary/20"
                                placeholder="Enter new password">
                            <button type="button" id="toggleNewPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-lightGray hover:text-adminPrimary transition-colors">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div>
                        <label for="confirm_password" class="block text-lighterGray text-sm font-medium mb-2">
                            Confirm New Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-lightGray">
                                <i class="fas fa-lock text-sm"></i>
                            </div>
                            <input type="password" id="confirm_password" name="confirm_password" required
                                class="w-full pl-10 pr-12 py-3 bg-gray-800/80 border border-gray-600/50 rounded-lg text-white placeholder-gray-400 transition-all focus:outline-none focus:border-adminPrimary focus:ring-2 focus:ring-adminPrimary/20"
                                placeholder="Confirm new password">
                            <button type="button" id="toggleConfirmPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-lightGray hover:text-adminPrimary transition-colors">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Password Requirements -->
                    <div class="bg-gray-800/30 p-4 rounded-lg">
                        <h4 class="text-white text-sm font-medium mb-3">Password Requirements:</h4>
                        <ul class="space-y-1 text-xs text-lightGray">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-400 w-4 mr-2" id="req-length"></i>
                                At least 10 characters long
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-400 w-4 mr-2" id="req-upper"></i>
                                At least one uppercase letter
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-400 w-4 mr-2" id="req-lower"></i>
                                At least one lowercase letter
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-400 w-4 mr-2" id="req-number"></i>
                                At least one number
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-400 w-4 mr-2" id="req-special"></i>
                                At least one special character
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" id="submitBtn" class="w-full py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white font-semibold rounded-lg transition-all hover:shadow-lg hover:shadow-orange-500/30 hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-key mr-2"></i>
                        Change Password
                    </button>
                    
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
    </div>

    <script>
        // Password visibility toggles
        document.getElementById('toggleNewPassword').addEventListener('click', function() {
            const input = document.getElementById('new_password');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
        
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const input = document.getElementById('confirm_password');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
        
        // Real-time password validation
        const passwordInput = document.getElementById('new_password');
        const confirmInput = document.getElementById('confirm_password');
        const submitBtn = document.getElementById('submitBtn');
        
        function validatePassword() {
            const password = passwordInput.value;
            const requirements = {
                length: password.length >= 10,
                upper: /[A-Z]/.test(password),
                lower: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[^A-Za-z0-9]/.test(password)
            };
            
            // Update requirement indicators
            Object.keys(requirements).forEach(req => {
                const icon = document.getElementById(`req-${req}`);
                if (requirements[req]) {
                    icon.classList.remove('fa-times', 'text-red-400');
                    icon.classList.add('fa-check', 'text-green-400');
                } else {
                    icon.classList.remove('fa-check', 'text-green-400');
                    icon.classList.add('fa-times', 'text-red-400');
                }
            });
            
            // Check if passwords match
            const passwordsMatch = password === confirmInput.value && password.length > 0;
            const allRequirementsMet = Object.values(requirements).every(r => r);
            
            // Enable/disable submit button
            submitBtn.disabled = !(allRequirementsMet && passwordsMatch);
        }
        
        passwordInput.addEventListener('input', validatePassword);
        confirmInput.addEventListener('input', validatePassword);
        
        // Initial validation
        validatePassword();
    </script>
</body>
</html>
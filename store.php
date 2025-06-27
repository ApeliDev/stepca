<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/notifications.php';
require_once 'components/auth_header.php';
require_once 'components/auth_background.php';
require_once 'components/auth_layout.php';
require_once 'components/auth_form_elements.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

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
    } elseif (!rateLimit('password_reset', 5, 300)) {
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

// Generate CSRF token
$csrfToken = generateCSRFToken();

// Render page
renderAuthHeader('Set New Password');
startAuthLayout();
?>

<?php 
renderAuthHeader_Section([
    'icon' => 'fas fa-key',
    'title' => 'StepCashier',
    'subtitle' => 'Set New Password',
    'description' => 'Create a strong, secure password for your account',
    'padding_top' => '10',
    'padding_bottom' => '8'
]);
?>

<!-- Form Section -->
<div class="px-8 pb-8">
    <?php renderError($error); ?>
    
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
        
        <?php 
        renderInputField([
            'name' => 'password',
            'label' => 'New Password',
            'type' => 'password',
            'icon' => 'fas fa-lock',
            'placeholder' => 'Enter new password',
            'required' => true,
            'minlength' => 8,
            'show_toggle' => true,
            'additional_html' => '
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
            '
        ]);
        ?>
        
        <?php 
        renderInputField([
            'name' => 'confirm_password',
            'label' => 'Confirm Password',
            'type' => 'password',
            'icon' => 'fas fa-lock',
            'placeholder' => 'Confirm new password',
            'required' => true,
            'minlength' => 8,
            'show_toggle' => true,
            'additional_html' => '
                <div id="passwordMatch" class="mt-2 text-xs hidden">
                    <div class="flex items-center">
                        <i id="matchIcon" class="mr-2 text-xs"></i>
                        <span id="matchText"></span>
                    </div>
                </div>
            '
        ]);
        ?>
        
        <?php 
        renderSubmitButton([
            'text' => 'Update Password',
            'icon' => 'fas fa-save',
            'id' => 'submitBtn',
            'disabled' => true,
            'class' => 'bg-gradient-to-r from-gray-600 to-gray-700 text-gray-300 cursor-not-allowed'
        ]);
        ?>
    </form>
    <?php endif; ?>
</div>

<?php 
$additionalFooter = <<<HTML
<div class="mt-6 text-center">
    <div class="inline-flex items-center px-4 py-2 bg-gray-800/50 rounded-full text-lightGray text-xs">
        <i class="fas fa-shield-alt text-primary mr-2"></i>
        Your password is encrypted and secure
    </div>
</div>
HTML;

endAuthLayout('assets/js/reset.js', $additionalFooter);
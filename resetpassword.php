<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/notifications.php';
require_once 'components/auth_header.php';
require_once 'components/auth_background.php';
require_once 'components/auth_layout.php';
require_once 'components/auth_form_elements.php';

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

// Render page
renderAuthHeader('Reset Password');
startAuthLayout();
?>

<?php 
renderAuthHeader_Section([
    'icon' => 'fas fa-key',
    'title' => 'StepCashier',
    'subtitle' => 'Reset Your Password',
    'description' => 'Create a new secure password for your account'
]);
?>

<!-- Form Section -->
<div class="px-8 pb-8">
    <?php renderError($error); ?>
    <?php renderSuccess($success); ?>
    
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
            'placeholder' => 'Enter your new password',
            'required' => true,
            'minlength' => 8,
            'show_toggle' => true,
            'help_text' => 'Password must be at least 8 characters with uppercase, lowercase, and numbers',
            'strength_meter' => true
        ]);
        ?>
        
        <?php 
        renderInputField([
            'name' => 'confirm_password',
            'label' => 'Confirm Password',
            'type' => 'password',
            'icon' => 'fas fa-lock',
            'placeholder' => 'Confirm your new password',
            'required' => true,
            'minlength' => 8,
            'show_toggle' => true,
            'match_field' => 'password'
        ]);
        ?>
        
        <?php 
        renderSubmitButton([
            'text' => 'Update Password',
            'icon' => 'fas fa-save',
            'disabled' => true,
            'id' => 'submitBtn'
        ]);
        ?>
        
        <p class="text-lightGray text-sm text-center pt-4">
            Remember your password? 
            <?php renderAuthLink([
                'url' => BASE_URL . '/login',
                'text' => 'Sign In'
            ]); ?>
        </p>
    </form>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="text-center mt-6">
        <?php 
        renderAuthLink([
            'url' => BASE_URL . '/login',
            'text' => 'Continue to Login',
            'icon' => 'fas fa-sign-in-alt',
            'class' => 'inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary to-primaryDark text-white font-semibold rounded-lg transition-all hover:shadow-lg hover:shadow-primary/30 hover:-translate-y-0.5'
        ]);
        ?>
    </div>
    <?php endif; ?>
</div>

<?php 
endAuthLayout('assets/js/reset.js');
?>
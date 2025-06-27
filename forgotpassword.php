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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid form submission. Please try again.';
    } elseif (!rateLimit('password_reset', 5, 300)) {
        $error = 'Too many reset attempts. Please try again later.';
    } else {
        $email = trim($_POST['email']);
        
        // Basic validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Generate reset token
            $token = generatePasswordResetToken($email);
            
            if ($token) {
                // Get user ID for notification
                $db = (new Database())->connect();
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    Notification::sendPasswordResetEmail($user['id'], $token);
                    
                    $success = 'If this email exists in our system, you will receive a reset link shortly.';
                }
            } else {
                $success = 'If this email exists in our system, you will receive a reset link shortly.';
            }
        }
    }
}

// Generate CSRF token
$csrfToken = generateCSRFToken();

// Render page
renderAuthHeader('Reset Password');
startAuthLayout();

?>

<?php 
renderAuthHeader_Section([
    'icon' => 'fas fa-key',
    'title' => 'StepCashier',
    'subtitle' => 'Reset Password',
    'description' => 'Enter your email to receive a reset link',
    'padding_top' => '10',
    'padding_bottom' => '8'
]);
?>

<!-- Form Section -->
<div class="px-8 pb-8">
    <?php renderError($error); ?>
    <?php renderSuccess($success); ?>
    
    <form method="POST" action="" id="resetForm" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        
        <?php 
        renderInputField([
            'name' => 'email',
            'label' => 'Email Address',
            'type' => 'email',
            'icon' => 'fas fa-envelope',
            'placeholder' => 'Enter your email address',
            'required' => true,
            'help_text' => 'We\'ll send you a secure link to reset your password'
        ]);
        ?>
        
        <?php 
        renderSubmitButton([
            'text' => 'Send Reset Link',
            'icon' => 'fas fa-paper-plane'
        ]);
        ?>
        
        <!-- Back to Login Link -->
        <p class="text-lightGray text-sm text-center pt-4">
            Remember your password? 
            <?php renderAuthLink([
                'url' => BASE_URL . '/login',
                'text' => 'Sign In'
            ]); ?>
        </p>
    </form>
</div>

<?php 
$additionalFooter = <<<HTML
<div class="mt-6 text-center">
    <div class="inline-flex items-center px-4 py-2 bg-gray-800/50 rounded-full text-lightGray text-xs">
        <i class="fas fa-shield-alt text-primary mr-2"></i>
        Secure password reset with email verification
    </div>
</div>
HTML;

endAuthLayout('assets/js/forgotpassword.js', $additionalFooter);
?>
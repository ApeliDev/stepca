<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
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
    } elseif (!rateLimit('login', 5, 300)) {
        $error = 'Too many login attempts. Please try again later.';
    } else {
        // Validate and sanitize input
        $emailOrPhone = trim($_POST['email_or_phone'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($emailOrPhone)) {
            $error = 'Please enter your email address or phone number.';
        } elseif (empty($password)) {
            $error = 'Please enter your password.';
        } else {
            $loginResult = loginUser($emailOrPhone, $password);
            
            if ($loginResult['success']) {
                // Update last login - check if input is email or phone
                $db = (new Database())->connect();
                $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE email = ? OR phone = ?");
                $stmt->execute([$emailOrPhone, $emailOrPhone]);
                
                // Redirect to dashboard
                header('Location: dashboard.php');
                exit;
            } elseif (isset($loginResult['needs_payment']) && $loginResult['needs_payment']) {
                // Direct redirect to payment page - no JSON response
                header('Location: ' . $loginResult['payment_url']);
                exit;
            } else {
                $error = $loginResult['message'];
            }
        }
    }
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// Render page
renderAuthHeader('Login');
startAuthLayout();
?>

<?php 
renderAuthHeader_Section([
    'icon' => 'fas fa-chart-line',
    'title' => 'StepCashier',
    'subtitle' => 'Welcome Back',
    'description' => 'Sign in to access your dashboard'
]);
?>

<!-- Form Section -->
<div class="px-8 pb-8">
    <?php renderError($error); ?>
    <?php renderSuccess($success); ?>
    
    <form method="POST" action="" id="loginForm" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        
        <?php 
        renderInputField([
            'name' => 'email_or_phone',
            'label' => 'Email or Phone Number',
            'type' => 'text',
            'icon' => 'fas fa-user',
            'placeholder' => 'Enter your email or phone number',
            'required' => true,
            'value' => $_POST['email_or_phone'] ?? '',
            'help_text' => 'You can use either your email address or phone number'
        ]);
        ?>
        
        <?php 
        renderInputField([
            'name' => 'password',
            'label' => 'Password',
            'type' => 'password',
            'icon' => 'fas fa-lock',
            'placeholder' => 'Enter your password',
            'required' => true,
            'show_toggle' => true
        ]);
        ?>
        
        <div class="text-right">
            <?php renderAuthLink([
                'url' => BASE_URL . '/forgotpassword',
                'text' => 'Forgot password?',
                'class' => 'text-primary text-sm hover:text-primaryDark hover:underline transition-colors'
            ]); ?>
        </div>
        
        <?php 
        renderSubmitButton([
            'text' => 'Sign In Securely',
            'icon' => 'fas fa-sign-in-alt'
        ]);
        ?>
        
        <!-- Register Link -->
        <p class="text-lightGray text-sm text-center pt-4">
            New to StepCashier? 
            <?php renderAuthLink([
                'url' => BASE_URL . '/register',
                'text' => 'Create Account'
            ]); ?>
        </p>
    </form>
</div>

<?php 
endAuthLayout('assets/js/login.js');
?>
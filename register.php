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
    header('Location: home.php');
    exit;
}

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid form submission. Please try again.';
    } elseif (!rateLimit('registration', 5, 300)) {
        $error = 'Too many registration attempts. Please try again later.';
    } else {
        // Validate inputs
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        $referralCode = isset($_POST['referral_code']) ? trim($_POST['referral_code']) : null;
        
        // Basic validation
        if (empty($name) || empty($email) || empty($phone) || empty($password)) {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            // Register user
            $result = registerUser($name, $email, $phone, $password, $referralCode);
            
            if ($result['success']) {
                // Send notifications
                Notification::sendRegistrationNotification($result['user_id']);
                
                // Store user ID in session for payment page
                $_SESSION['registering_user'] = $result['user_id'];
                
                // Redirect to payment
                header('Location: payment.php');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// Render page
renderAuthHeader('Register');
startAuthLayout('max-w-lg');
//renderAuthBackground('register');
?>

<?php 
renderAuthHeader_Section([
    'icon' => 'fas fa-user-plus',
    'title' => 'Create Your Account',
    'subtitle' => 'Join StepCashier',
    'description' => 'Register now to start earning from referrals',
    'badge' => [
        'icon' => 'tag',
        'text' => 'Only KES 500 one-time payment required'
    ]
]);
?>

<!-- Form Section -->
<div class="px-8 pb-8">
    <?php renderError($error); ?>
    <?php renderSuccess($success); ?>
    
    <form method="POST" action="" id="registerForm" class="space-y-5">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        
        <?php 
        renderInputField([
            'name' => 'name',
            'label' => 'Full Name',
            'type' => 'text',
            'icon' => 'fas fa-user',
            'placeholder' => 'Enter your full name',
            'required' => true,
            'value' => $_POST['name'] ?? ''
        ]);
        ?>
        
        <?php 
        renderInputField([
            'name' => 'email',
            'label' => 'Email Address',
            'type' => 'email',
            'icon' => 'fas fa-envelope',
            'placeholder' => 'Enter your email',
            'required' => true,
            'value' => $_POST['email'] ?? ''
        ]);
        ?>
        
        <?php 
        renderInputField([
            'name' => 'phone',
            'label' => 'Phone Number',
            'type' => 'tel',
            'icon' => 'fas fa-phone',
            'placeholder' => 'e.g. 0712345678',
            'required' => true,
            'value' => $_POST['phone'] ?? ''
        ]);
        ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php 
            renderInputField([
                'name' => 'password',
                'label' => 'Password',
                'type' => 'password',
                'icon' => 'fas fa-lock',
                'placeholder' => 'Min. 8 characters',
                'required' => true,
                'minlength' => 8,
                'show_toggle' => true,
                'class' => 'md:col-span-1'
            ]);
            ?>
            
            <?php 
            renderInputField([
                'name' => 'confirm_password',
                'label' => 'Confirm Password',
                'type' => 'password',
                'icon' => 'fas fa-lock',
                'placeholder' => 'Confirm password',
                'required' => true,
                'show_toggle' => true,
                'class' => 'md:col-span-1'
            ]);
            ?>
        </div>
        
        <!-- Password Match Indicator -->
        <div id="passwordMatch" class="hidden text-sm">
            <div class="flex items-center text-red-400" id="passwordMismatch">
                <i class="fas fa-times-circle mr-2"></i>
                Passwords do not match
            </div>
            <div class="flex items-center text-primary" id="passwordMatches">
                <i class="fas fa-check-circle mr-2"></i>
                Passwords match
            </div>
        </div>
        
        <?php 
        renderInputField([
            'name' => 'referral_code',
            'label' => 'Referral Code',
            'type' => 'text',
            'icon' => 'fas fa-gift',
            'placeholder' => 'Enter referral code',
            'optional' => true,
            'value' => isset($_GET['ref']) ? htmlspecialchars($_GET['ref']) : ($_POST['referral_code'] ?? '')
        ]);
        ?>
        
        <?php 
        renderSubmitButton([
            'text' => 'Register Now',
            'icon' => 'fas fa-user-plus'
        ]);
        ?>
        
        <!-- Login Link -->
        <p class="text-lightGray text-sm text-center pt-4">
            Already have an account? 
            <?php renderAuthLink([
                'url' => BASE_URL . '/login',
                'text' => 'Login here'
            ]); ?>
        </p>
    </form>
</div>

<?php 
$additionalFooter = <<<HTML
<div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-3 text-center">
    <div class="bg-gray-800/30 rounded-lg p-3 border border-gray-700/50">
        <i class="fas fa-shield-alt text-primary text-lg mb-2 block"></i>
        <p class="text-lightGray text-xs">Secure Registration</p>
    </div>
    <div class="bg-gray-800/30 rounded-lg p-3 border border-gray-700/50">
        <i class="fas fa-money-bill-wave text-primary text-lg mb-2 block"></i>
        <p class="text-lightGray text-xs">Start Earning Today</p>
    </div>
</div>
HTML;

endAuthLayout('assets/js/reg.js', $additionalFooter);
?>
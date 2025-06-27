<?php 
require_once '../includes/auth.php';
require_once '../includes/db.php';
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\QRServerProvider;

$admin = getCurrentAdmin();
if (!$admin) {
    header('Location: login.php');
    exit;
}

// Check if 2FA is enabled
$db = (new Database())->connect();
$stmt = $db->prepare("SELECT two_factor_enabled FROM admins WHERE id = ?");
$stmt->execute([$admin['id']]);
$adminDetails = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$adminDetails['two_factor_enabled']) {
    header('Location: profile.php');
    exit;
}

// Create QR provider instance
$qrProvider = new QRServerProvider();

// Handle form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['disable_2fa'])) {
        // Disable 2FA
        $stmt = $db->prepare("UPDATE admins SET two_factor_enabled = 0, two_factor_secret = NULL WHERE id = ?");
        $stmt->execute([$admin['id']]);
        
        // Clear 2FA session
        unset($_SESSION['admin_2fa_verified']);
        
        // Redirect to profile
        header('Location: profile.php');
        exit;
    } elseif (isset($_POST['verify_code'])) {
        $code = $_POST['code'] ?? '';
        
        // Get admin's secret
        $stmt = $db->prepare("SELECT two_factor_secret FROM admins WHERE id = ?");
        $stmt->execute([$admin['id']]);
        $adminDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($adminDetails && $adminDetails['two_factor_secret']) {
            $tfa = new TwoFactorAuth($qrProvider, 'StepCashier'); 
            if ($tfa->verifyCode($adminDetails['two_factor_secret'], $code)) {
                $_SESSION['admin_2fa_verified'] = true;
            } else {
                $error = 'Invalid verification code. Please try again.';
            }
        } else {
            $error = 'Two-factor authentication is not properly configured.';
        }
    }
}

include '../includes/admin_header.php'; 
?>

<div class="bg-white shadow rounded-lg overflow-hidden max-w-2xl mx-auto">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Disable Two-Factor Authentication</h3>
        <p class="mt-1 text-sm text-gray-500">Remove two-factor authentication from your account</p>
    </div>
    
    <div class="px-4 py-5 sm:p-6">
        <?php if (!isset($_SESSION['admin_2fa_verified'])): ?>
            <div class="space-y-6">
                <div>
                    <p class="text-sm text-gray-600 mb-4">To disable two-factor authentication, please verify your identity by entering a code from your authenticator app.</p>
                </div>
                
                <!-- Verification Form -->
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <?php if ($error): ?>
                        <div class="rounded-md bg-red-50 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800"><?php echo $error; ?></h3>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700">Verification Code</label>
                        <div class="mt-1">
                            <input type="text" name="code" id="code" class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Enter 6-digit code" maxlength="6" pattern="\d{6}" required>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Enter the 6-digit code from your authenticator app</p>
                    </div>
                    
                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="profile.php" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Cancel
                        </a>
                        <button type="submit" name="verify_code" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primaryDark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Verify
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <div class="rounded-md bg-yellow-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Warning</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>Disabling two-factor authentication reduces your account security. Are you sure you want to proceed?</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="profile.php" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Cancel
                        </a>
                        <button type="submit" name="disable_2fa" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Disable Two-Factor Authentication
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>
<?php
// Load environment variables
require_once __DIR__ . '/../vendor/autoload.php'; 
use Dotenv\Dotenv as DotenvClass;
use RobThree\Auth\TwoFactorAuth;

$dotenv = DotenvClass::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Site settings
define('SITE_NAME', $_ENV['SITE_NAME']);
define('BASE_URL', $_ENV['BASE_URL']);

// Payment settings
define('REGISTRATION_FEE', $_ENV['REGISTRATION_FEE']);
define('REFERRAL_BONUS', $_ENV['REFERRAL_BONUS']);
define('SUPPORT_PHONE', $_ENV['SUPPORT_PHONE']);
define('ADMIN_EMAIL', $_ENV['ADMIN_EMAIL']);
define('SUPPORT_EMAIL', $_ENV['SUPPORT_EMAIL']);
define('PRIVACY_POLICY_URL', $_ENV['PRIVACY_POLICY_URL']);
define('TERMS_OF_SERVICE_URL', $_ENV['TERMS_OF_SERVICE_URL']);
define('COMPANY_ADDRESS', $_ENV['COMPANY_ADDRESS']);
define('SECURITY_TIPS_URL', $_ENV['SECURITY_TIPS_URL']);
define('TWITTER_URL', $_ENV['TWITTER_URL']);
define('FACEBOOK_URL', $_ENV['FACEBOOK_URL']);
define('INSTAGRAM_URL', $_ENV['INSTAGRAM_URL']);
define('LINKEDIN_URL', $_ENV['LINKEDIN_URL']);
define('GROUP_SHARE_AMOUNT', $_ENV['GROUP_SHARE_AMOUNT']);


// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}




// Error reporting
//error_reporting(E_ALL);
//ini_set('display_errors', 1);


// Enable error logging
//ini_set('log_errors', 1);

// Set custom error log file in your project directory
//ini_set('error_log', __DIR__ . '/debug.log');

// Optional: Also display errors during development (remove in production)
//ini_set('display_errors', 1);
//error_reporting(E_ALL);

// Test the logging
//error_log("Error logging initialized - " . date('Y-m-d H:i:s'));

// Function to log with more detail
//function debug_log($message, $data = null) {
 //   $log_message = "[" . date('Y-m-d H:i:s') . "] " . $message;
 //   if ($data !== null) {
////        $log_message .= " | Data: " . print_r($data, true);
 //   }
 //   error_log($log_message);
//}
?>
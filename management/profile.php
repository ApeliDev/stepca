<?php 
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$admin = getCurrentAdmin();
if (!$admin) {
    header('Location: login.php');
    exit;
}

// Get full admin details from database
$db = (new Database())->connect();
$stmt = $db->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$admin['id']]);
$adminDetails = $stmt->fetch(PDO::FETCH_ASSOC);

// Format dates for display
$lastLogin = $adminDetails['last_login'] ? date('M j, Y g:i a', strtotime($adminDetails['last_login'])) : 'Never';
$createdAt = date('M j, Y', strtotime($adminDetails['created_at']));
$passwordChangedAt = $adminDetails['password_changed_at'] ? date('M j, Y', strtotime($adminDetails['password_changed_at'])) : 'Never';

include '../includes/admin_header.php'; 
?>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Admin Profile</h3>
        <p class="mt-1 text-sm text-gray-500">Manage your account settings and security</p>
    </div>
    
    <div class="px-4 py-5 sm:p-6">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <!-- Profile Information -->
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Profile Information</h3>
                    <p class="mt-1 text-sm text-gray-600">Update your basic profile details.</p>
                </div>
            </div>
            
            <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="shadow overflow-hidden sm:rounded-md">
                    <div class="px-4 py-5 bg-white sm:p-6">
                        <div class="grid grid-cols-6 gap-6">
                            <div class="col-span-6 sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Full Name</label>
                                <div class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($adminDetails['name']); ?></div>
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Email Address</label>
                                <div class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($adminDetails['email']); ?></div>
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Role</label>
                                <div class="mt-1 text-sm text-gray-900 capitalize"><?php echo htmlspecialchars($adminDetails['role']); ?></div>
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Account Status</label>
                                <div class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $adminDetails['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo $adminDetails['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Last Login</label>
                                <div class="mt-1 text-sm text-gray-900"><?php echo $lastLogin; ?></div>
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Account Created</label>
                                <div class="mt-1 text-sm text-gray-900"><?php echo $createdAt; ?></div>
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Password Last Changed</label>
                                <div class="mt-1 text-sm text-gray-900"><?php echo $passwordChangedAt; ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                        <a href="edit-profile.php" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primaryDark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Security Section -->
    <div class="px-4 py-5 sm:p-6 border-t border-gray-200">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Security</h3>
                    <p class="mt-1 text-sm text-gray-600">Manage your account security settings.</p>
                </div>
            </div>
            
            <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="shadow overflow-hidden sm:rounded-md">
                    <div class="px-4 py-5 bg-white sm:p-6">
                        <div class="space-y-6">
                            <!-- Password Change -->
                            <div class="border-b border-gray-200 pb-6">
                                <h4 class="text-md font-medium text-gray-900">Password</h4>
                                <p class="mt-1 text-sm text-gray-500">Change your account password</p>
                                <div class="mt-4">
                                    <a href="change-password.php" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primaryDark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                        Change Password
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Two-Factor Authentication -->
                            <div>
                                <h4 class="text-md font-medium text-gray-900">Two-Factor Authentication</h4>
                                <p class="mt-1 text-sm text-gray-500">Add an extra layer of security to your account</p>
                                
                                <div class="mt-4 flex items-center">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $adminDetails['two_factor_enabled'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo $adminDetails['two_factor_enabled'] ? 'Enabled' : 'Disabled'; ?>
                                    </span>
                                    
                                    <div class="ml-4">
                                        <a href="<?php echo $adminDetails['two_factor_enabled'] ? 'disable-2fa.php' : 'enable-2fa.php'; ?>" class="text-sm font-medium <?php echo $adminDetails['two_factor_enabled'] ? 'text-red-600 hover:text-red-500' : 'text-primary hover:text-primaryDark'; ?>">
                                            <?php echo $adminDetails['two_factor_enabled'] ? 'Disable' : 'Enable'; ?> Two-Factor Authentication
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>
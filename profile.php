<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
$db = new Database();
$conn = $db->connect();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user has paid registration fee if not redirect to payment page
if ($user['is_active'] == 0 && basename($_SERVER['PHP_SELF']) != 'payment.php') {
    header('Location: payment.php');
    exit;
}

// Handle profile update
$success_message = '';
$error_message = '';

// Check if POST request and action is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] == 'update_profile') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $bio = trim($_POST['bio']);
        
        // Validate inputs
        if (empty($name) || empty($email) || empty($phone)) {
            $error_message = 'Name, email, and phone are required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Please enter a valid email address.';
        } else {
            // Check if email is already taken by another user
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetchColumn()) {
                $error_message = 'This email is already registered by another user.';
            } else {
                // Update profile
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, bio = ? WHERE id = ?");
                if ($stmt->execute([$name, $email, $phone, $bio, $user_id])) {
                    $success_message = 'Profile updated successfully!';
                    $user['name'] = $name;
                    $user['email'] = $email;
                    $user['phone'] = $phone;
                    $user['bio'] = $bio;
                } else {
                    $error_message = 'Error updating profile. Please try again.';
                }
            }
        }
    }

    // Handle password change
    if ($_POST['action'] == 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = 'All password fields are required.';
        } elseif ($new_password !== $confirm_password) {
            $error_message = 'New passwords do not match.';
        } elseif (strlen($new_password) < 6) {
            $error_message = 'New password must be at least 6 characters long.';
        } elseif (!password_verify($current_password, $user['password'])) {
            $error_message = 'Current password is incorrect.';
        } else {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashed_password, $user_id])) {
                $success_message = 'Password changed successfully!';
            } else {
                $error_message = 'Error changing password. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Profile</title>
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
                    },
                    animation: {
                        float: 'float 6s ease-in-out infinite',
                        slideIn: 'slideIn 0.3s ease-out',
                        pulse: 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        fadeIn: 'fadeIn 0.5s ease-out',
                        scaleIn: 'scaleIn 0.3s ease-out',
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
                        },
                        fadeIn: {
                            'from': { opacity: '0' },
                            'to': { opacity: '1' },
                        },
                        scaleIn: {
                            'from': { opacity: '0', transform: 'scale(0.9)' },
                            'to': { opacity: '1', transform: 'scale(1)' },
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-dark via-darker to-darkest min-h-screen text-white">
    <!-- Animated Background -->
    <div class="fixed top-0 left-0 w-full h-full pointer-events-none z-0 overflow-hidden">
        <div class="absolute top-[10%] left-[10%] text-primary opacity-5 text-2xl animate-float"><i class="fas fa-user-circle"></i></div>
        <div class="absolute top-[20%] right-[10%] text-primary opacity-5 text-2xl animate-float" style="animation-delay: 1s;"><i class="fas fa-cog"></i></div>
        <div class="absolute bottom-[30%] left-[15%] text-primary opacity-5 text-2xl animate-float" style="animation-delay: 2s;"><i class="fas fa-shield-alt"></i></div>
        <div class="absolute bottom-[10%] right-[20%] text-primary opacity-5 text-2xl animate-float" style="animation-delay: 3s;"><i class="fas fa-edit"></i></div>
    </div>

    <div class="flex h-screen relative z-10">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-screen">
            <!-- Top Navigation -->
            <?php include 'includes/header.php'; ?>

            <!-- Main Content Area -->
            <main class="flex-1 p-4 lg:p-6 overflow-y-auto">
                <!-- Success/Error Messages -->
                <?php if (!empty($success_message)): ?>
                <div class="mb-6 bg-green-500/20 border border-green-500/30 text-green-400 px-4 py-3 rounded-xl animate-slideIn">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo $success_message; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                <div class="mb-6 bg-red-500/20 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl animate-slideIn">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo $error_message; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Profile Header -->
                <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 overflow-hidden animate-fadeIn mb-6">
                    <div class="p-6">
                        <div class="flex items-center space-x-6">
                            <div class="relative">
                                <img class="h-24 w-24 rounded-full border-4 border-primary/30 shadow-lg" 
                                     src="<?php echo $user['profile_pic'] ? 'assets/images/profile/'.$user['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($user['name']).'&background=4CAF50&color=fff&size=96'; ?>" 
                                     alt="<?php echo htmlspecialchars($user['name']); ?>">
                                <div class="absolute bottom-0 right-0 bg-primary rounded-full p-2 cursor-pointer hover:bg-primaryDark transition-colors">
                                    <i class="fas fa-camera text-white text-sm"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h1 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($user['name']); ?></h1>
                                <p class="text-lightGray"><?php echo htmlspecialchars($user['email']); ?></p>
                                <div class="flex items-center mt-2 space-x-4">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $user['is_active'] ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400'; ?>">
                                        <?php echo $user['is_active'] ? 'Active Member' : 'Pending Payment'; ?>
                                    </span>
                                    <span class="text-xs text-lightGray">
                                        <i class="fas fa-calendar-alt mr-1"></i>
                                        Joined <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Tabs -->
                <div class="bg-darker/60 backdrop-blur-sm rounded-2xl border border-primary/20 overflow-hidden animate-fadeIn" style="animation-delay: 0.1s;">
                    <!-- Tab Navigation -->
                    <div class="border-b border-primary/20">
                        <nav class="flex space-x-8 px-6" aria-label="Tabs">
                            <button onclick="showTab('profile')" id="profile-tab" class="tab-button py-4 px-1 border-b-2 border-transparent text-sm font-medium text-lightGray hover:text-white transition-colors">
                                <i class="fas fa-user mr-2"></i>
                                Profile Information
                            </button>
                            <button onclick="showTab('security')" id="security-tab" class="tab-button py-4 px-1 border-b-2 border-transparent text-sm font-medium text-lightGray hover:text-white transition-colors">
                                <i class="fas fa-shield-alt mr-2"></i>
                                Security Settings
                            </button>
                            <button onclick="showTab('activity')" id="activity-tab" class="tab-button py-4 px-1 border-b-2 border-transparent text-sm font-medium text-lightGray hover:text-white transition-colors">
                                <i class="fas fa-chart-line mr-2"></i>
                                Account Activity
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6">
                        <!-- Profile Information Tab -->
                        <div id="profile-content" class="tab-content">
                            <form method="POST" class="space-y-6">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="animate-slideIn">
                                        <label for="name" class="block text-sm font-medium text-lightGray mb-2">Full Name</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-user text-primary"></i>
                                            </div>
                                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" 
                                                   class="block w-full pl-10 pr-3 py-3 bg-gray-800/80 border border-gray-600/50 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all" 
                                                   placeholder="Enter your full name" required>
                                        </div>
                                    </div>

                                    <div class="animate-slideIn" style="animation-delay: 0.1s;">
                                        <label for="email" class="block text-sm font-medium text-lightGray mb-2">Email Address</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-envelope text-primary"></i>
                                            </div>
                                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                                                   class="block w-full pl-10 pr-3 py-3 bg-gray-800/80 border border-gray-600/50 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all" 
                                                   placeholder="Enter your email" required>
                                        </div>
                                    </div>

                                    <div class="animate-slideIn" style="animation-delay: 0.2s;">
                                        <label for="phone" class="block text-sm font-medium text-lightGray mb-2">Phone Number</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-phone text-primary"></i>
                                            </div>
                                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" 
                                                   class="block w-full pl-10 pr-3 py-3 bg-gray-800/80 border border-gray-600/50 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all" 
                                                   placeholder="Enter your phone number" required>
                                        </div>
                                    </div>

                                    <div class="animate-slideIn" style="animation-delay: 0.3s;">
                                        <label for="referral_code" class="block text-sm font-medium text-lightGray mb-2">Referral Code</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-link text-primary"></i>
                                            </div>
                                            <input type="text" id="referral_code" value="<?php echo htmlspecialchars($user['referral_code']); ?>" 
                                                   class="block w-full pl-10 pr-3 py-3 bg-gray-800/50 border border-gray-600/30 rounded-xl text-gray-400 cursor-not-allowed" 
                                                   readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="animate-slideIn" style="animation-delay: 0.4s;">
                                    <label for="bio" class="block text-sm font-medium text-lightGray mb-2">Bio (Optional)</label>
                                    <div class="relative">
                                        <textarea id="bio" name="bio" rows="4" 
                                                  class="block w-full px-3 py-3 bg-gray-800/80 border border-gray-600/50 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all resize-none" 
                                                  placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                    </div>
                                </div>

                                <div class="flex justify-end animate-slideIn" style="animation-delay: 0.5s;">
                                    <button type="submit" 
                                            class="px-6 py-3 bg-gradient-to-r from-primary to-primaryDark text-white font-medium rounded-xl hover:shadow-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary/50">
                                        <i class="fas fa-save mr-2"></i>
                                        Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Security Settings Tab -->
                        <div id="security-content" class="tab-content hidden">
                            <form method="POST" class="space-y-6">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-4 animate-slideIn">
                                    <div class="flex items-center">
                                        <i class="fas fa-exclamation-triangle text-yellow-400 mr-3"></i>
                                        <div>
                                            <h4 class="text-yellow-400 font-medium">Password Security</h4>
                                            <p class="text-yellow-400/80 text-sm mt-1">Choose a strong password with at least 6 characters.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <div class="animate-slideIn" style="animation-delay: 0.1s;">
                                        <label for="current_password" class="block text-sm font-medium text-lightGray mb-2">Current Password</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-lock text-primary"></i>
                                            </div>
                                            <input type="password" id="current_password" name="current_password" 
                                                   class="block w-full pl-10 pr-3 py-3 bg-gray-800/80 border border-gray-600/50 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all" 
                                                   placeholder="Enter your current password" required>
                                        </div>
                                    </div>

                                    <div class="animate-slideIn" style="animation-delay: 0.2s;">
                                        <label for="new_password" class="block text-sm font-medium text-lightGray mb-2">New Password</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-key text-primary"></i>
                                            </div>
                                            <input type="password" id="new_password" name="new_password" 
                                                   class="block w-full pl-10 pr-3 py-3 bg-gray-800/80 border border-gray-600/50 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all" 
                                                   placeholder="Enter your new password" required>
                                        </div>
                                    </div>

                                    <div class="animate-slideIn" style="animation-delay: 0.3s;">
                                        <label for="confirm_password" class="block text-sm font-medium text-lightGray mb-2">Confirm New Password</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-check-circle text-primary"></i>
                                            </div>
                                            <input type="password" id="confirm_password" name="confirm_password" 
                                                   class="block w-full pl-10 pr-3 py-3 bg-gray-800/80 border border-gray-600/50 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all" 
                                                   placeholder="Confirm your new password" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end animate-slideIn" style="animation-delay: 0.4s;">
                                    <button type="submit" 
                                            class="px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white font-medium rounded-xl hover:shadow-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-red-500/50">
                                        <i class="fas fa-shield-alt mr-2"></i>
                                        Change Password
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Account Activity Tab -->
                        <div id="activity-content" class="tab-content hidden">
                            <div class="space-y-6">
                                <!-- Quick Stats -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-slideIn">
                                    <div class="bg-darker/40 backdrop-blur-sm rounded-xl border border-primary/20 p-6">
                                        <div class="flex items-center">
                                            <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-3 rounded-xl">
                                                <i class="fas fa-calendar-check text-white"></i>
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm text-lightGray">Member Since</p>
                                                <p class="text-lg font-semibold text-white"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-darker/40 backdrop-blur-sm rounded-xl border border-primary/20 p-6">
                                        <div class="flex items-center">
                                            <div class="bg-gradient-to-br from-green-500 to-green-600 p-3 rounded-xl">
                                                <i class="fas fa-users text-white"></i>
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm text-lightGray">Total Referrals</p>
                                                <?php
                                                $stmt = $conn->prepare("SELECT COUNT(*) FROM referrals WHERE referrer_id = ?");
                                                $stmt->execute([$user_id]);
                                                $user_referrals = $stmt->fetchColumn();
                                                ?>
                                                <p class="text-lg font-semibold text-white"><?php echo $user_referrals; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-darker/40 backdrop-blur-sm rounded-xl border border-primary/20 p-6">
                                        <div class="flex items-center">
                                            <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 p-3 rounded-xl">
                                                <i class="fas fa-coins text-white"></i>
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm text-lightGray">Total Earned</p>
                                                <?php
                                                $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) FROM referral_earnings WHERE user_id = ?");
                                                $stmt->execute([$user_id]);
                                                $user_earnings = $stmt->fetchColumn();
                                                ?>
                                                <p class="text-lg font-semibold text-white">KES <?php echo number_format($user_earnings, 2); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Recent Activity -->
                                <div class="bg-darker/40 backdrop-blur-sm rounded-xl border border-primary/20 p-6 animate-slideIn" style="animation-delay: 0.1s;">
                                    <h4 class="text-lg font-semibold text-white mb-4 flex items-center">
                                        <i class="fas fa-history mr-2 text-primary"></i>
                                        Recent Activity
                                    </h4>
                                    
                                    <div class="space-y-4">
                                        <div class="flex items-center p-4 bg-primary/5 rounded-xl">
                                            <div class="bg-green-500/20 p-2 rounded-lg">
                                                <i class="fas fa-user-plus text-green-400"></i>
                                            </div>
                                            <div class="ml-4 flex-1">
                                                <p class="text-white text-sm">Account created</p>
                                                <p class="text-lightGray text-xs"><?php echo date('M j, Y \a\t H:i', strtotime($user['created_at'])); ?></p>
                                            </div>
                                        </div>
                                        
                                        <?php if ($user['is_active']): ?>
                                        <div class="flex items-center p-4 bg-primary/5 rounded-xl">
                                            <div class="bg-primary/20 p-2 rounded-lg">
                                                <i class="fas fa-check-circle text-primary"></i>
                                            </div>
                                            <div class="ml-4 flex-1">
                                                <p class="text-white text-sm">Account activated</p>
                                                <p class="text-lightGray text-xs">Registration fee paid</p>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($user_referrals > 0): ?>
                                        <div class="flex items-center p-4 bg-primary/5 rounded-xl">
                                            <div class="bg-blue-500/20 p-2 rounded-lg">
                                                <i class="fas fa-users text-blue-400"></i>
                                            </div>
                                            <div class="ml-4 flex-1">
                                                <p class="text-white text-sm">Started referring members</p>
                                                <p class="text-lightGray text-xs"><?php echo $user_referrals; ?> referrals made</p>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
   <script src="assets/js/profile.js"></script>
   <script src="assets/js/main.js"></script>
</body>
</html>
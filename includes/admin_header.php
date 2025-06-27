<?php
require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

$db = new Database();
$conn = $db->connect();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get admin data
$admin_id = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Check permissions
$permissions = json_decode($admin['permissions'], true) ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> Admin - <?php echo ucfirst(basename($_SERVER['PHP_SELF'], '.php')); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
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
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <?php include 'admin_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Top Navigation -->
            <nav class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center">
                            <h1 class="text-xl font-bold text-gray-900"><?php echo ucfirst(basename($_SERVER['PHP_SELF'], '.php')); ?></h1>
                        </div>
                        <div class="flex items-center">
                            <div class="ml-4 flex items-center md:ml-6">
                                <!-- Admin dropdown -->
                                <div class="ml-3 relative">
                                    <div>
                                        <button type="button" class="max-w-xs bg-white flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary" id="admin-menu-button" aria-expanded="false" aria-haspopup="true">
                                            <span class="sr-only">Open admin menu</span>
                                            <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin['name']); ?>&background=4CAF50&color=fff" alt="<?php echo htmlspecialchars($admin['name']); ?>">
                                        </button>
                                    </div>
                                    <div class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none hidden" role="menu" aria-orientation="vertical" aria-labelledby="admin-menu-button" tabindex="-1">
                                        <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1" id="admin-menu-item-0">Settings</a>
                                        <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1" id="admin-menu-item-2">Sign out</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
            
            <!-- Main content area -->
            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
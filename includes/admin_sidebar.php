<div class="hidden md:flex md:flex-shrink-0">
    <div class="flex flex-col w-64">
        <div class="flex flex-col h-0 flex-1 border-r border-gray-200 bg-white">
            <div class="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
                <div class="flex items-center flex-shrink-0 px-4">
                    <img class="h-8 w-auto" src="../assets/images/logo.png" alt="<?php echo SITE_NAME; ?>">
                    <span class="ml-2 text-xl font-bold text-gray-900">Admin</span>
                </div>
                <nav class="mt-5 flex-1 px-2 space-y-1">
                    <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-gray-100 text-primary' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                        <i class="fas fa-tachometer-alt mr-3 flex-shrink-0 text-gray-400 group-hover:text-gray-500"></i>
                        Dashboard
                    </a>
                    
                    <a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'bg-gray-100 text-primary' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                        <i class="fas fa-users mr-3 flex-shrink-0 text-gray-400 group-hover:text-gray-500"></i>
                        Users
                    </a>
                    
                    <a href="payments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'bg-gray-100 text-primary' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                        <i class="fas fa-money-bill-wave mr-3 flex-shrink-0 text-gray-400 group-hover:text-gray-500"></i>
                        Payments
                    </a>
                    
                    <a href="processes-withdrawals.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'processes-withdrawals.php' ? 'bg-gray-100 text-primary' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                        <i class="fas fa-exchange-alt mr-3 flex-shrink-0 text-gray-400 group-hover:text-gray-500"></i>
                        Process Withdrawals
                        <span class="ml-auto inline-block py-0.5 px-2 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                            <?php
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM withdrawals WHERE status = 'failed'");
                            $stmt->execute();
                            $failed_withdrawals = $stmt->fetchColumn();
                            echo $failed_withdrawals;
                            ?>
                        </span>
                    </a>
                    
                    <a href="credit-account.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'credit-account.php' ? 'bg-gray-100 text-primary' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                        <i class="fas fa-plus-circle mr-3 flex-shrink-0 text-gray-400 group-hover:text-gray-500"></i>
                        Credit Account
                    </a>
                    
                    <a href="suspend-user.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'suspend-user.php' ? 'bg-gray-100 text-primary' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                        <i class="fas fa-user-slash mr-3 flex-shrink-0 text-gray-400 group-hover:text-gray-500"></i>
                        Suspend User
                    </a>
                    
                    <a href="settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'bg-gray-100 text-primary' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                        <i class="fas fa-cog mr-3 flex-shrink-0 text-gray-400 group-hover:text-gray-500"></i>
                        Settings
                    </a>
                    <a href="profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'bg-gray-100 text-primary' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                        <i class="fas fa-cog mr-3 flex-shrink-0 text-gray-400 group-hover:text-gray-500"></i>
                        Profile
                    </a>

                    <a href="../logout.php" class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                        <i class="fas fa-sign-out-alt mr-3 flex-shrink-0 text-gray-400 group-hover:text-gray-500"></i>
                        Logout
                    </a>
                </nav>
            </div>
            <div class="flex-shrink-0 flex border-t border-gray-200 p-4">
                <div class="flex items-center">
                    <div>
                        <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin['name']); ?>&background=4CAF50&color=fff" alt="<?php echo htmlspecialchars($admin['name']); ?>">
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($admin['name']); ?></p>
                        <p class="text-xs font-medium text-gray-500"><?php echo ucfirst(str_replace('_', ' ', $admin['role'])); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Mobile Menu Toggle -->
<button id="mobile-menu-toggle" class="md:hidden fixed top-4 left-4 z-50 p-2 rounded-lg bg-darker/90 backdrop-blur-sm border border-primary/20 text-primary shadow-lg transition-all duration-200 focus:outline-none flex items-center justify-center group">
    <span class="sr-only">Open sidebar</span>
    <i id="menu-icon" class="fas fa-bars text-lg transition-transform duration-200 group-aria-expanded:rotate-90"></i>
</button>

<!-- Sidebar -->
<div id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-darker/95 backdrop-blur-sm border-r border-primary/20 transform -translate-x-full transition-transform duration-300 ease-in-out md:translate-x-0 md:static md:inset-0">
    <div class="flex flex-col h-full">
        <!-- Logo Section -->
        <div class="flex items-center justify-center px-6 py-8 border-b border-primary/20">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-primaryDark text-white flex items-center justify-center mr-3">
                    <i class="fas fa-chart-line text-lg"></i>
                </div>
                <span class="text-xl font-bold text-primary"><?php echo SITE_NAME; ?></span>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <!-- Dashboard -->
            <a href="account.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'account.php' ? 'flex items-center px-4 py-3 text-primary bg-primary/10 rounded-xl border border-primary/20' : 'flex items-center px-4 py-3 text-lightGray rounded-xl hover:bg-primary/10 hover:text-primary'; ?> transition-all">
                <i class="fas fa-tachometer-alt mr-3 text-lg"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            
            <!-- Currency Exchange -->
            <div class="group">
                <button class="w-full flex items-center justify-between px-4 py-3 text-lightGray rounded-xl hover:bg-primary/10 hover:text-primary transition-all">
                    <div class="flex items-center">
                        <i class="fas fa-exchange-alt mr-3 text-lg"></i>
                        <span class="font-medium">Currency Exchange</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200 group-[.active]:rotate-180"></i>
                </button>
                <div class="hidden group-[.active]:block pl-4 mt-1 space-y-1">
                    <a href="exchange-buy.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['exchange-buy.php']) ? 'flex items-center px-4 py-2 text-primary bg-primary/10 rounded-lg border border-primary/20' : 'flex items-center px-4 py-2 text-lightGray rounded-lg hover:bg-primary/10 hover:text-primary'; ?> transition-all">
                        <i class="fas fa-dollar-sign mr-3 text-sm"></i>
                        <span>Buy Dollars</span>
                    </a>
                    <a href="exchange-sell.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['exchange-sell.php']) ? 'flex items-center px-4 py-2 text-primary bg-primary/10 rounded-lg border border-primary/20' : 'flex items-center px-4 py-2 text-lightGray rounded-lg hover:bg-primary/10 hover:text-primary'; ?> transition-all">
                        <i class="fas fa-money-bill-wave mr-3 text-sm"></i>
                        <span>Sell Dollars</span>
                    </a>
                    <a href="exchange-history.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['exchange-history.php']) ? 'flex items-center px-4 py-2 text-primary bg-primary/10 rounded-lg border border-primary/20' : 'flex items-center px-4 py-2 text-lightGray rounded-lg hover:bg-primary/10 hover:text-primary'; ?> transition-all">
                        <i class="fas fa-history mr-3 text-sm"></i>
                        <span>Exchange History</span>
                    </a>
                </div>
            </div>
            
            <!-- Investments -->
            <div class="group">
                <button class="w-full flex items-center justify-between px-4 py-3 text-lightGray rounded-xl hover:bg-primary/10 hover:text-primary transition-all">
                    <div class="flex items-center">
                        <i class="fas fa-piggy-bank mr-3 text-lg"></i>
                        <span class="font-medium">Investments</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200 group-[.active]:rotate-180"></i>
                </button>
                <div class="hidden group-[.active]:block pl-4 mt-1 space-y-1">
                    <a href="investments.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['investments.php']) ? 'flex items-center px-4 py-2 text-primary bg-primary/10 rounded-lg border border-primary/20' : 'flex items-center px-4 py-2 text-lightGray rounded-lg hover:bg-primary/10 hover:text-primary'; ?> transition-all">
                        <i class="fas fa-chart-pie mr-3 text-sm"></i>
                        <span>My Investments</span>
                    </a>
                    <a href="invest-products.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['invest-products.php']) ? 'flex items-center px-4 py-2 text-primary bg-primary/10 rounded-lg border border-primary/20' : 'flex items-center px-4 py-2 text-lightGray rounded-lg hover:bg-primary/10 hover:text-primary'; ?> transition-all">
                        <i class="fas fa-box-open mr-3 text-sm"></i>
                        <span>Investment Products</span>
                    </a>
                    <a href="invest-history.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['invest-history.php']) ? 'flex items-center px-4 py-2 text-primary bg-primary/10 rounded-lg border border-primary/20' : 'flex items-center px-4 py-2 text-lightGray rounded-lg hover:bg-primary/10 hover:text-primary'; ?> transition-all">
                        <i class="fas fa-receipt mr-3 text-sm"></i>
                        <span>Investment History</span>
                    </a>
                </div>
            </div>
            
            <!-- Platform Transfers -->
            <div class="group">
                <button class="w-full flex items-center justify-between px-4 py-3 text-lightGray rounded-xl hover:bg-primary/10 hover:text-primary transition-all">
                    <div class="flex items-center">
                        <i class="fas fa-random mr-3 text-lg"></i>
                        <span class="font-medium">Platform Transfers</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200 group-[.active]:rotate-180"></i>
                </button>
                <div class="hidden group-[.active]:block pl-4 mt-1 space-y-1">
                    <a href="transfer-crypto.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['transfer-crypto.php']) ? 'flex items-center px-4 py-2 text-primary bg-primary/10 rounded-lg border border-primary/20' : 'flex items-center px-4 py-2 text-lightGray rounded-lg hover:bg-primary/10 hover:text-primary'; ?> transition-all">
                        <i class="fab fa-bitcoin mr-3 text-sm"></i>
                        <span>To Crypto Wallet</span>
                    </a>
                    <a href="transfer-deriv.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['transfer-deriv.php']) ? 'flex items-center px-4 py-2 text-primary bg-primary/10 rounded-lg border border-primary/20' : 'flex items-center px-4 py-2 text-lightGray rounded-lg hover:bg-primary/10 hover:text-primary'; ?> transition-all">
                        <i class="fas fa-exchange-alt mr-3 text-sm"></i>
                        <span>To Deriv Account</span>
                    </a>
                    <a href="transfer-history.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['transfer-history.php']) ? 'flex items-center px-4 py-2 text-primary bg-primary/10 rounded-lg border border-primary/20' : 'flex items-center px-4 py-2 text-lightGray rounded-lg hover:bg-primary/10 hover:text-primary'; ?> transition-all">
                        <i class="fas fa-history mr-3 text-sm"></i>
                        <span>Transfer History</span>
                    </a>
                </div>
            </div>
            
            <!-- Wallet -->
            <a href="wallet.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'wallet.php' ? 'flex items-center px-4 py-3 text-primary bg-primary/10 rounded-xl border border-primary/20' : 'flex items-center px-4 py-3 text-lightGray rounded-xl hover:bg-primary/10 hover:text-primary'; ?> transition-all">
                <i class="fas fa-wallet mr-3 text-lg"></i>
                <span class="font-medium">My Wallet</span>
            </a>
            
            <!-- Referrals -->
            <a href="referrals.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'referrals.php' ? 'flex items-center px-4 py-3 text-primary bg-primary/10 rounded-xl border border-primary/20' : 'flex items-center px-4 py-3 text-lightGray rounded-xl hover:bg-primary/10 hover:text-primary'; ?> transition-all">
                <i class="fas fa-users mr-3 text-lg"></i>
                <span class="font-medium">Referrals</span>
            </a>
            
            <!-- Notifications -->
            <a href="notifications.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'flex items-center px-4 py-3 text-primary bg-primary/10 rounded-xl border border-primary/20' : 'flex items-center px-4 py-3 text-lightGray rounded-xl hover:bg-primary/10 hover:text-primary'; ?> transition-all">
                <i class="fas fa-bell mr-3 text-lg"></i>
                <span class="font-medium">Notifications</span>
                <?php 
                $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
                $stmt->execute([$user_id]);
                $unread = $stmt->fetchColumn();
                if ($unread > 0): ?>
                <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full"><?php echo $unread; ?></span>
                <?php endif; ?>
            </a>
        </nav>

        <!-- User Profile -->
        <div class="p-4 border-t border-primary/20">
            <div class="flex items-center">
                <img class="h-10 w-10 rounded-full border-2 border-primary/30" src="<?php echo $user['profile_pic'] ? '../assets/images/profile/'.$user['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($user['name']).'&background=4CAF50&color=fff'; ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-white"><?php echo htmlspecialchars($user['name']); ?></p>
                    <p class="text-xs text-lightGray"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-30 md:hidden hidden"></div>

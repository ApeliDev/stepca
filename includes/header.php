

<header class="bg-darker/95 backdrop-blur-sm border-b border-primary/20 px-4 lg:px-6 py-4">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <h1 class="text-2xl font-bold text-white ml-12 md:ml-0">Dashboard</h1>
        </div>
        <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <a href="notifications.php" class="relative p-2 text-lightGray hover:text-primary transition-colors">
                <i class="fas fa-bell text-lg"></i>
                <?php if ($unread > 0): ?>
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo $unread; ?></span>
                <?php endif; ?>
            </a>
            
            <!-- Profile Dropdown -->
            <div class="relative">
                <button id="profile-dropdown" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-primary/10 transition-colors">
                    <img class="h-8 w-8 rounded-full border-2 border-primary/30" src="<?php echo $user['profile_pic'] ? '../assets/images/profile/'.$user['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($user['name']).'&background=4CAF50&color=fff'; ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
                    <i class="fas fa-chevron-down text-sm text-lightGray"></i>
                </button>
                
                <div id="profile-menu" class="absolute right-0 top-full mt-2 w-48 bg-darker/95 backdrop-blur-sm rounded-xl border border-primary/20 shadow-xl hidden z-50">
                    <a href="profile.php" class="block px-4 py-3 text-sm text-lightGray hover:text-primary hover:bg-primary/10 transition-colors">
                        <i class="fas fa-user mr-2"></i>Your Profile
                    </a>
                    <a href="logout.php" class="block px-4 py-3 text-sm text-lightGray hover:text-primary hover:bg-primary/10 transition-colors">
                        <i class="fas fa-sign-out-alt mr-2"></i>Sign out
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
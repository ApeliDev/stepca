
    document.addEventListener('DOMContentLoaded', function() {
    // Sidebar/mobile menu logic
    const toggleBtn = document.getElementById('mobile-menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const menuIcon = document.getElementById('menu-icon');
    let open = false;

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        if (menuIcon) {
            menuIcon.classList.remove('fa-bars');
            menuIcon.classList.add('fa-arrow-left');
        }
        open = true;
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        if (menuIcon) {
            menuIcon.classList.remove('fa-arrow-left');
            menuIcon.classList.add('fa-bars');
        }
        open = false;
    }

    if (toggleBtn && sidebar && overlay) {
        toggleBtn.addEventListener('click', function() {
            open ? closeSidebar() : openSidebar();
        });
        overlay.addEventListener('click', closeSidebar);
    }

    // Sidebar dropdown toggles
    const dropdownGroups = document.querySelectorAll('.group');
    
    dropdownGroups.forEach(group => {
        const button = group.querySelector('button');
        const dropdown = group.querySelector('div.hidden');
        
        if (button && dropdown) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Close other dropdowns first
                dropdownGroups.forEach(otherGroup => {
                    if (otherGroup !== group && otherGroup.classList.contains('active')) {
                        otherGroup.classList.remove('active');
                    }
                });
                
                // Toggle current dropdown
                if (group.classList.contains('active')) {
                    group.classList.remove('active');
                } else {
                    group.classList.add('active');
                }
            });
        }
    });

    // Auto-expand active dropdown based on current page
    const currentPage = window.location.pathname.split('/').pop();
    const activeLinks = [
        // Currency Exchange pages
        'exchange-buy.php', 'exchange-sell.php', 'exchange-history.php',
        // Investment pages
        'investments.php', 'invest-products.php', 'invest-history.php',
        // Transfer pages
        'transfer-crypto.php', 'transfer-deriv.php', 'transfer-history.php'
    ];
    
    if (activeLinks.includes(currentPage)) {
        dropdownGroups.forEach(group => {
            const links = group.querySelectorAll('a');
            links.forEach(link => {
                if (link.href.includes(currentPage)) {
                    group.classList.add('active');
                }
            });
        });
    }

    // Profile dropdown logic
    const profileButton = document.getElementById('profile-dropdown');
    const profileMenu = document.getElementById('profile-menu');
    if (profileButton && profileMenu) {
        profileButton.addEventListener('click', function(e) {
            e.preventDefault();
            if (profileMenu.classList.contains('hidden')) {
                const rect = profileButton.getBoundingClientRect();
                profileMenu.style.position = 'fixed';
                profileMenu.style.top = (rect.bottom + 8) + 'px';
                profileMenu.style.right = (window.innerWidth - rect.right) + 'px';
                profileMenu.style.zIndex = '99999';
                document.body.appendChild(profileMenu);
                profileMenu.classList.remove('hidden');
            } else {
                profileMenu.classList.add('hidden');
            }
        });

        document.addEventListener('click', function(e) {
            if (!profileButton.contains(e.target) && !profileMenu.contains(e.target)) {
                profileMenu.classList.add('hidden');
            }
        });
    }

    // Close sidebar dropdowns when clicking outside (mobile)
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 768) { // Only on mobile
            const clickedInsideSidebar = sidebar && sidebar.contains(e.target);
            const clickedToggleBtn = toggleBtn && toggleBtn.contains(e.target);
            
            if (!clickedInsideSidebar && !clickedToggleBtn && open) {
                // Close any open dropdowns when sidebar is closed
                dropdownGroups.forEach(group => {
                    if (group.classList.contains('active')) {
                        group.classList.remove('active');
                    }
                });
            }
        }
    });

    // Add animation classes to elements
    ['fadeIn', 'scaleIn', 'float', 'slideIn', 'pulse'].forEach(anim => {
        document.querySelectorAll(`.animate-${anim}`).forEach(el => {
            el.classList.add(`animate-${anim}`);
        });
    });

    // Add smooth transitions for dropdowns
    dropdownGroups.forEach(group => {
        const dropdown = group.querySelector('div.hidden');
        if (dropdown) {
            dropdown.style.transition = 'all 0.3s ease-in-out';
        }
    });
});
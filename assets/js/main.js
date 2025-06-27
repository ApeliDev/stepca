document.addEventListener('DOMContentLoaded', function() {
    // Sidebar/mobile menu logic
    const toggleBtn = document.getElementById('mobile-menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const menuIcon = document.getElementById('menu-icon');
    let open = false;

    // Debug: Check if elements exist
    console.log('Toggle button:', toggleBtn);
    console.log('Sidebar:', sidebar);
    console.log('Overlay:', overlay);
    console.log('Menu icon:', menuIcon);

    function openSidebar() {
        if (sidebar) {
            sidebar.classList.remove('-translate-x-full');
        }
        if (overlay) {
            overlay.classList.remove('hidden');
        }
        if (menuIcon) {
            menuIcon.classList.remove('fa-bars');
            menuIcon.classList.add('fa-arrow-left');
        }
        open = true;
        console.log('Sidebar opened');
    }

    function closeSidebar() {
        if (sidebar) {
            sidebar.classList.add('-translate-x-full');
        }
        if (overlay) {
            overlay.classList.add('hidden');
        }
        if (menuIcon) {
            menuIcon.classList.remove('fa-arrow-left');
            menuIcon.classList.add('fa-bars');
        }
        open = false;
        console.log('Sidebar closed');
    }

    // Add event listeners for sidebar toggle
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Toggle button clicked, current state:', open);
            open ? closeSidebar() : openSidebar();
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function(e) {
            e.preventDefault();
            closeSidebar();
        });
    }

    // Enhanced Sidebar dropdown toggles
    const dropdownGroups = document.querySelectorAll('.group');
    console.log('Found dropdown groups:', dropdownGroups.length);
    
    dropdownGroups.forEach((group, index) => {
        const button = group.querySelector('button');
        const dropdown = group.querySelector('div.hidden, div:not(.hidden)'); // Look for both hidden and visible divs
        
        console.log(`Group ${index}:`, { button: !!button, dropdown: !!dropdown });
        
        if (button && dropdown) {
            // Add smooth transition to dropdown
            dropdown.style.transition = 'all 0.3s ease-in-out';
            dropdown.style.overflow = 'hidden';

            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log(`Dropdown ${index} clicked`);
                
                // Close other dropdowns first
                dropdownGroups.forEach(otherGroup => {
                    if (otherGroup !== group && otherGroup.classList.contains('active')) {
                        otherGroup.classList.remove('active');
                        console.log('Closed other dropdown');
                    }
                });
                
                // Toggle current dropdown
                if (group.classList.contains('active')) {
                    group.classList.remove('active');
                    console.log('Closed current dropdown');
                } else {
                    group.classList.add('active');
                    console.log('Opened current dropdown');
                }
            });
        }
    });

    // Auto-expand active dropdown based on current page
    const currentPage = window.location.pathname.split('/').pop();
    console.log('Current page:', currentPage);
    
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
                    console.log('Auto-expanded dropdown for current page');
                }
            });
        });
    }

    // Profile dropdown logic (if exists)
    const profileButton = document.getElementById('profile-dropdown');
    const profileMenu = document.getElementById('profile-menu');
    if (profileButton && profileMenu) {
        profileButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (profileMenu.classList.contains('hidden')) {
                const rect = profileButton.getBoundingClientRect();
                profileMenu.style.position = 'fixed';
                profileMenu.style.top = (rect.bottom + 8) + 'px';
                profileMenu.style.right = (window.innerWidth - rect.right) + 'px';
                profileMenu.style.zIndex = '99999';
                if (document.body !== profileMenu.parentNode) {
                    document.body.appendChild(profileMenu);
                }
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

    // Close sidebar when clicking outside (mobile)
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 768) { // Only on mobile
            const clickedInsideSidebar = sidebar && sidebar.contains(e.target);
            const clickedToggleBtn = toggleBtn && toggleBtn.contains(e.target);
            
            if (!clickedInsideSidebar && !clickedToggleBtn && open) {
                closeSidebar();
                
                // Close any open dropdowns when sidebar is closed
                dropdownGroups.forEach(group => {
                    if (group.classList.contains('active')) {
                        group.classList.remove('active');
                    }
                });
            }
        }
    });

    // Fallback: Add click handlers directly to dropdown buttons by class if groups don't work
    setTimeout(() => {
        const directButtons = document.querySelectorAll('.group button');
        console.log('Direct buttons found:', directButtons.length);
        
        directButtons.forEach((btn, index) => {
            // Remove any existing listeners and add new ones
            btn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const parentGroup = btn.closest('.group');
                if (parentGroup) {
                    const isActive = parentGroup.classList.contains('active');
                    
                    // Close all other dropdowns
                    document.querySelectorAll('.group.active').forEach(activeGroup => {
                        if (activeGroup !== parentGroup) {
                            activeGroup.classList.remove('active');
                        }
                    });
                    
                    // Toggle current
                    if (isActive) {
                        parentGroup.classList.remove('active');
                    } else {
                        parentGroup.classList.add('active');
                    }
                    
                    console.log(`Direct button ${index} toggled`);
                }
            };
        });
    }, 100);

    // Add debugging for mobile detection
    console.log('Window width:', window.innerWidth);
    console.log('Is mobile:', window.innerWidth < 768);
    
    // Force show sidebar on desktop
    if (window.innerWidth >= 768) {
        if (sidebar) {
            sidebar.classList.remove('-translate-x-full');
        }
    }
});
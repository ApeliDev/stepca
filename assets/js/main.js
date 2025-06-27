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

    // Add animation classes to elements
    ['fadeIn', 'scaleIn', 'float', 'slideIn', 'pulse'].forEach(anim => {
        document.querySelectorAll(`.animate-${anim}`).forEach(el => {
            el.classList.add(`animate-${anim}`);
        });
    });
});

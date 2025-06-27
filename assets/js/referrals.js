
// Custom toast notification for referral actions
function showReferralToast(message, type = 'info') {
    const toastColors = {
        success: 'from-green-500 to-green-600',
        error: 'from-red-500 to-red-600',
        info: 'from-primary to-primaryDark'
    };
    
    const toastIcons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        info: 'fas fa-info-circle'
    };
    
    const toast = $(`
        <div class="fixed top-4 right-4 z-50 bg-gradient-to-r ${toastColors[type]} text-white px-6 py-3 rounded-xl shadow-lg transform translate-x-full transition-transform duration-300 backdrop-blur-sm">
            <div class="flex items-center">
                <i class="${toastIcons[type]} mr-2"></i>
                <span>${message}</span>
            </div>
        </div>
    `);
    
    $('body').append(toast);
    
    // Slide in
    setTimeout(() => {
        toast.removeClass('translate-x-full');
    }, 100);
    
    // Slide out and remove
    setTimeout(() => {
        toast.addClass('translate-x-full');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// Add staggered animations on page load
document.addEventListener('DOMContentLoaded', function() {
    // Animate referral cards
    document.querySelectorAll('.animate-scaleIn').forEach((el, index) => {
        el.style.animationDelay = (index * 0.1) + 's';
    });
    
    // Animate referral list items
    document.querySelectorAll('[data-referral-item]').forEach((el, index) => {
        el.style.animationDelay = (0.5 + index * 0.1) + 's';
        el.classList.add('animate-slideIn');
    });
    
    // Add hover effects to referral cards
    document.querySelectorAll('[data-referral-card]').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 10px 25px rgba(76, 175, 80, 0.1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
});

// Add click animation to copy button
document.getElementById('referral-link').nextElementSibling.addEventListener('click', function() {
    this.style.transform = 'scale(0.95)';
    setTimeout(() => {
        this.style.transform = 'scale(1)';
    }, 150);
});
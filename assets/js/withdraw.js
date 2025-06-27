$(document).ready(function() {
    $('#withdraw-form').submit(function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Processing...');
        
        $.ajax({
            url: form.attr('#action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showWithdrawToast('Withdrawal request submitted successfully!', 'success');
                    // Reload page to show updated balance and new withdrawal
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showWithdrawToast('Error: ' + response.message, 'error');
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function() {
                showWithdrawToast('An error occurred. Please try again.', 'error');
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Add input validation and formatting
    $('#amount').on('input', function() {
        var value = $(this).val();
        var max = parseFloat($(this).attr('max'));
        var min = parseFloat($(this).attr('min'));
        
        if (value > max) {
            $(this).val(max);
        } else if (value < 0) {
            $(this).val('');
        }
    });
    
    // Add phone number formatting
    $('#phone').on('input', function() {
        var value = $(this).val().replace(/\D/g, '');
        if (value.length > 10) {
            value = value.substring(0, 10);
        }
        $(this).val(value);
    });
});

// Custom toast notification for withdrawal actions
function showWithdrawToast(message, type = 'info') {
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
    // Animate balance cards
    document.querySelectorAll('.animate-scaleIn').forEach((el, index) => {
        el.style.animationDelay = (index * 0.1) + 's';
    });
    
    // Add hover effects to balance cards
    document.querySelectorAll('[data-balance-card]').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 10px 25px rgba(76, 175, 80, 0.1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
    
    // Add click animation to submit button
    document.querySelector('#withdraw-form button[type="submit"]').addEventListener('click', function() {
        if (!this.disabled) {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        }
    });
});
$(document).ready(function() {
    $('#transfer-form').submit(function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.html();
        
        // Validate form
        var amount = parseFloat($('#amount').val());
        var phone = $('#phone').val().trim();
        
        if (amount < 10) {
            showTransferToast('Minimum transfer amount is KES 10', 'error');
            return;
        }
        
        if (phone.length < 10) {
            showTransferToast('Please enter a valid phone number', 'error');
            return;
        }
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Processing...');
        
        $.ajax({
            url: form.attr('#action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showTransferToast('Transfer request submitted successfully!', 'success');
                    // Reset form
                    form[0].reset();
                    // Reload page to show updated balance and new transfer
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showTransferToast('Error: ' + response.message, 'error');
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function() {
                showTransferToast('An error occurred. Please try again.', 'error');
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
            showTransferToast('Amount cannot exceed your available balance', 'error');
        } else if (value < 0) {
            $(this).val('');
        }
        
        // Update button state
        updateTransferButton();
    });
    
    // Add phone number formatting
    $('#phone').on('input', function() {
        var value = $(this).val().replace(/\D/g, '');
        if (value.length > 10) {
            value = value.substring(0, 10);
        }
        $(this).val(value);
        updateTransferButton();
    });
    
    // Update transfer button state
    function updateTransferButton() {
        var amount = parseFloat($('#amount').val()) || 0;
        var phone = $('#phone').val().trim();
        var submitBtn = $('#transfer-form button[type="submit"]');
        
        if (amount >= 10 && phone.length >= 10) {
            submitBtn.removeClass('opacity-50 cursor-not-allowed');
        } else {
            submitBtn.addClass('opacity-50 cursor-not-allowed');
        }
    }
    
    // Add recipient lookup (optional feature)
    $('#phone').on('blur', function() {
        var phone = $(this).val().trim();
        if (phone.length === 10) {
            // You can add AJAX call here to check if recipient exists
            // and show their name for confirmation
        }
    });
});

// Custom toast notification for transfer actions
function showTransferToast(message, type = 'info') {
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
    document.querySelector('#transfer-form button[type="submit"]').addEventListener('click', function() {
        if (!this.disabled) {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        }
    });
    
    // Add loading animation to transfer history rows
    document.querySelectorAll('tbody tr').forEach((row, index) => {
        if (!row.querySelector('[colspan]')) {
            row.style.opacity = '0';
            row.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                row.style.transition = 'all 0.3s ease-out';
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            }, 100 + (index * 50));
        }
    });
});
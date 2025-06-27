// Document ready function
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize popovers
    $('[data-toggle="popover"]').popover();
    
    // Format phone numbers as they're typed
    $('input[type="tel"]').on('input', function() {
        var value = $(this).val().replace(/\D/g, '');
        if (value.length > 0) {
            value = '0' + value.substring(1);
        }
        $(this).val(value);
    });
    
    // Format currency inputs
    $('input[type="number"][step="1"]').on('input', function() {
        var value = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(value);
    });
    
    // Prevent form submission on enter key
    $('form').on('keydown', 'input', function(e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });
    
    // Initialize sidebar toggle for mobile
    $('#sidebar-toggle').click(function() {
        $('body').toggleClass('sidebar-collapsed');
    });
    
    // Initialize dropdown menus
    $('.dropdown-toggle').click(function(e) {
        e.preventDefault();
        $(this).next('.dropdown-menu').toggleClass('hidden');
    });
    
    // Close dropdowns when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('.dropdown').length) {
            $('.dropdown-menu').addClass('hidden');
        }
    });
});

// Format numbers with commas
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Copy text to clipboard
function copyToClipboard(text) {
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val(text).select();
    document.execCommand("copy");
    $temp.remove();
}

// Show alert message
function showAlert(message, type = 'success') {
    var alertClass = type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
    var alertHtml = `
        <div class="fixed top-4 right-4 z-50 rounded-md p-4 ${alertClass} shadow-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    ${type === 'success' ? 
                        '<i class="fas fa-check-circle h-5 w-5 text-green-500"></i>' : 
                        '<i class="fas fa-exclamation-circle h-5 w-5 text-red-500"></i>'}
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">${message}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button type="button" class="inline-flex rounded-md focus:outline-none">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    var $alert = $(alertHtml);
    $('body').append($alert);
    
    // Auto remove after 5 seconds
    setTimeout(function() {
        $alert.fadeOut(300, function() {
            $(this).remove();
        });
    }, 5000);
    
    // Manual remove on click
    $alert.find('button').click(function() {
        $alert.fadeOut(300, function() {
            $(this).remove();
        });
    });
}
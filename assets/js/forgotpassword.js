 
    // Form validation and enhancement
    document.getElementById('resetForm').addEventListener('submit', function(e) {
        const email = document.getElementById('email').value;
        const submitBtn = this.querySelector('button[type="submit"]');
        
        if (!email) {
            e.preventDefault();
            return;
        }
        
        // Disable button to prevent double submission
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
        
        // Re-enable after 3 seconds in case of error
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Send Reset Link';
        }, 5000);
    });

    // Enhanced form validation
    document.getElementById('email').addEventListener('input', function() {
        const email = this.value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            this.classList.add('border-red-500');
            this.classList.remove('border-gray-600/50');
        } else {
            this.classList.remove('border-red-500');
            this.classList.add('border-gray-600/50');
        }
    });
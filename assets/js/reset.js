
    // Password visibility toggles
    document.getElementById('togglePassword').addEventListener('click', function() {
        const password = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            password.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
        const confirmPassword = document.getElementById('confirm_password');
        const icon = this.querySelector('i');
        
        if (confirmPassword.type === 'password') {
            confirmPassword.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            confirmPassword.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    // Password strength checker
    function checkPasswordStrength(password) {
        let score = 0;
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /\d/.test(password)
        };

        // Update requirement indicators
        Object.keys(requirements).forEach(req => {
            const element = document.getElementById(`req-${req}`);
            const icon = element.querySelector('i');
            if (requirements[req]) {
                icon.classList.remove('fa-circle', 'text-gray-500');
                icon.classList.add('fa-check-circle', 'text-green-400');
                element.classList.add('text-green-400');
                score++;
            } else {
                icon.classList.remove('fa-check-circle', 'text-green-400');
                icon.classList.add('fa-circle', 'text-gray-500');
                element.classList.remove('text-green-400');
            }
        });

        // Update strength bar
        const strengthBar = document.getElementById('passwordStrength');
        const strengthText = document.getElementById('strengthText');
        
        if (score === 0) {
            strengthBar.style.width = '0%';
            strengthText.textContent = '';
        } else if (score <= 2) {
            strengthBar.style.width = '33%';
            strengthBar.className = 'h-full transition-all duration-300 rounded-full bg-red-500';
            strengthText.textContent = 'Weak';
            strengthText.className = 'text-xs text-red-400 min-w-16';
        } else if (score === 3) {
            strengthBar.style.width = '66%';
            strengthBar.className = 'h-full transition-all duration-300 rounded-full bg-yellow-500';
            strengthText.textContent = 'Good';
            strengthText.className = 'text-xs text-yellow-400 min-w-16';
        } else {
            strengthBar.style.width = '100%';
            strengthBar.className = 'h-full transition-all duration-300 rounded-full bg-green-500';
            strengthText.textContent = 'Strong';
            strengthText.className = 'text-xs text-green-400 min-w-16';
        }

        return score;
    }

    // Password matching checker
    function checkPasswordMatch() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const matchDiv = document.getElementById('passwordMatch');
        const matchIcon = document.getElementById('matchIcon');
        const matchText = document.getElementById('matchText');

        if (confirmPassword.length > 0) {
            matchDiv.classList.remove('hidden');
            if (password === confirmPassword) {
                matchIcon.className = 'fas fa-check-circle text-green-400 mr-2 text-xs';
                matchText.textContent = 'Passwords match';
                matchText.className = 'text-green-400';
                return true;
            } else {
                matchIcon.className = 'fas fa-times-circle text-red-400 mr-2 text-xs';
                matchText.textContent = 'Passwords do not match';
                matchText.className = 'text-red-400';
                return false;
            }
        } else {
            matchDiv.classList.add('hidden');
            return false;
        }
    }

    // Update submit button state
    function updateSubmitButton() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const submitBtn = document.getElementById('submitBtn');
        
        const strengthScore = checkPasswordStrength(password);
        const passwordsMatch = checkPasswordMatch();
        
        if (strengthScore >= 3 && passwordsMatch && password.length >= 8) {
            submitBtn.disabled = false;
            submitBtn.className = 'w-full py-3 bg-gradient-to-r from-primary to-primaryDark text-white font-semibold rounded-lg transition-all hover:shadow-lg hover:shadow-primary/30 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-primary/50';
        } else {
            submitBtn.disabled = true;
            submitBtn.className = 'w-full py-3 bg-gradient-to-r from-gray-600 to-gray-700 text-gray-300 font-semibold rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-primary/50 cursor-not-allowed';
        }
    }

    // Event listeners
    document.getElementById('password').addEventListener('input', updateSubmitButton);
    document.getElementById('confirm_password').addEventListener('input', updateSubmitButton);

    // Form submission
    document.getElementById('passwordResetForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        
        if (!submitBtn.disabled) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating Password...';
            
            // Re-enable after 5 seconds in case of error
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Update Password';
            }, 5000);
        }
    });


// Password toggle functionality
function setupPasswordToggle(inputId, toggleId) {
    document.getElementById(toggleId).addEventListener('click', function() {
        const password = document.getElementById(inputId);
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });
}

setupPasswordToggle('password', 'togglePassword');
setupPasswordToggle('confirm_password', 'toggleConfirmPassword');

// Password matching validation
function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const matchIndicator = document.getElementById('passwordMatch');
    const mismatch = document.getElementById('passwordMismatch');
    const matches = document.getElementById('passwordMatches');
    const submitBtn = document.getElementById('submitBtn');

    if (confirmPassword.length > 0) {
        matchIndicator.classList.remove('hidden');
        
        if (password === confirmPassword) {
            mismatch.classList.add('hidden');
            matches.classList.remove('hidden');
            submitBtn.disabled = false;
        } else {
            mismatch.classList.remove('hidden');
            matches.classList.add('hidden');
            submitBtn.disabled = true;
        }
    } else {
        matchIndicator.classList.add('hidden');
        submitBtn.disabled = false;
    }
}

document.getElementById('password').addEventListener('input', checkPasswordMatch);
document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);

// Form submission with loading state
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('submitBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creating Account...';
    btn.disabled = true;
    
    // Re-enable button after 5 seconds (in case of error)
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 5000);
});

// Input focus animations
document.querySelectorAll('input').forEach(input => {
    input.addEventListener('focus', function() {
        this.style.transform = 'scale(1.01)';
    });
    
    input.addEventListener('blur', function() {
        this.style.transform = 'scale(1)';
    });
});

// Phone number formatting (Kenya format)
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 10) {
        value = value.slice(0, 10);
    }
    e.target.value = value;
});

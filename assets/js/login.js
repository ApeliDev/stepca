
// Password toggle functionality
document.getElementById('togglePassword').addEventListener('click', function() {
    const password = document.getElementById('password');
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    
    const icon = this.querySelector('i');
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
});

// Form submission with loading state
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Signing In...';
    btn.disabled = true;
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 3000);
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

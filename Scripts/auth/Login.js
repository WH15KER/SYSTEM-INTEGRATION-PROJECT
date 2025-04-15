document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('form');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            // Basic client-side validation
            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in both email and password fields');
                return;
            }
            
            // Simple email format check
            if (!email.includes('@') || !email.includes('.')) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return;
            }
            
            // Password length check
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long');
                return;
            }
        });
        
        // Toggle password visibility
        const passwordInput = document.getElementById('password');
        const togglePassword = document.createElement('i');
        togglePassword.className = 'fas fa-eye toggle-password';
        togglePassword.style.cursor = 'pointer';
        togglePassword.style.position = 'absolute';
        togglePassword.style.right = '15px';
        togglePassword.style.top = '50%';
        togglePassword.style.transform = 'translateY(-50%)';
        
        passwordInput.parentNode.appendChild(togglePassword);
        
        togglePassword.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            }
        });
    }
});
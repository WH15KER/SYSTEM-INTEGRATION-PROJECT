document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('adminLoginForm');
    const loginButton = document.getElementById('loginButton');
    const btnText = document.querySelector('.btn-text');
    const btnLoader = document.querySelector('.btn-loader');
    
    if (loginForm) {
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.querySelector('.toggle-password i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
        
        // Attach toggle function to the eye icon
        const togglePasswordBtn = document.querySelector('.toggle-password');
        if (togglePasswordBtn) {
            togglePasswordBtn.addEventListener('click', togglePassword);
        }
        
        // Form submission handling
        loginForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            // Basic client-side validation
            if (!username || !password) {
                e.preventDefault();
                alert('Please fill in both email and password fields');
                return;
            }
            
            // Simple email format check
            if (!username.includes('@') || !username.includes('.')) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return;
            }
            
            // Show loading state
            btnText.style.display = 'none';
            btnLoader.style.display = 'block';
            loginButton.disabled = true;
        });
    }
    
    // Check for remember token on page load
    checkRememberToken();
    
    function checkRememberToken() {
        const rememberToken = getCookie('remember_token');
        if (rememberToken) {
            // Auto-fill the remember me checkbox
            const rememberCheckbox = document.querySelector('input[name="remember"]');
            if (rememberCheckbox) {
                rememberCheckbox.checked = true;
            }
        }
    }
    
    // Helper function to get cookie value
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }
});
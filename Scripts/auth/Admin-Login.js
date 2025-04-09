function togglePassword() {
    const passwordField = document.getElementById('password');
    const eyeIcon = document.querySelector('.toggle-password i');
    
    if (passwordField.type === "password") {
        passwordField.type = "text";
        eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        passwordField.type = "password";
        eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const adminLoginForm = document.getElementById('adminLoginForm');
    if (adminLoginForm) {
        adminLoginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const loginBtn = document.getElementById('loginButton');
            const btnText = loginBtn.querySelector('.btn-text');
            const btnLoader = loginBtn.querySelector('.btn-loader');
            
            // Show loading state
            btnText.style.display = 'none';
            btnLoader.style.display = 'inline-block';
            
            // Simulate authentication (replace with actual auth)
            setTimeout(function() {
                // Here you would typically validate credentials
                console.log('Authentication attempt');
                
                // Reset button state (in a real app, this would be after response)
                btnText.style.display = 'inline-block';
                btnLoader.style.display = 'none';
                
                // Redirect if successful
                // window.location.href = 'admin-dashboard.html';
            }, 1500);
        });
    }
});
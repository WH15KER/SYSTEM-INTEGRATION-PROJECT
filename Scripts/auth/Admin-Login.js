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

        ;
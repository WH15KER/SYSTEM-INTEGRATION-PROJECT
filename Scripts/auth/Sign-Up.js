document.addEventListener('DOMContentLoaded', function() {
    // Terms checkbox functionality
    const termsCheckbox = document.getElementById('terms');
    const signupButton = document.querySelector('.btn-signup');
    
    if (termsCheckbox && signupButton) {
        termsCheckbox.addEventListener('change', function() {
            signupButton.disabled = !this.checked;
        });
    }

    // Password strength indicator
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const strengthBars = document.querySelectorAll('.strength-bar');
            const password = this.value;
            let strength = 0;
            
            // Reset bars
            strengthBars.forEach(bar => {
                bar.style.backgroundColor = '#eee';
            });
            
            // Check password strength
            if (password.length >= 8) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^A-Za-z0-9]/)) strength++;
            
            // Update bars
            for (let i = 0; i < strength && i < strengthBars.length; i++) {
                let color;
                if (strength <= 1) color = '#ff5252'; // Weak (red)
                else if (strength === 2) color = '#ffab40'; // Medium (orange)
                else color = '#4caf50'; // Strong (green)
                
                strengthBars[i].style.backgroundColor = color;
            }
        });
    }

    // Account type toggle effect
    const toggleOptions = document.querySelectorAll('.toggle-option');
    toggleOptions.forEach(option => {
        option.addEventListener('click', function() {
            toggleOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
        });
    });
});
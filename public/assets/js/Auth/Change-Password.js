// Password visibility toggle
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.querySelector(`#${fieldId} + .toggle-password i`);
    if (field.type === "password") {
        field.type = "text";
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        field.type = "password";
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function checkPasswordStrength(password) {
    const strengthBar = document.querySelector('.strength-bar');
    const strengthValue = document.getElementById('strengthValue');
    const segments = document.querySelectorAll('.strength-segment');
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
    };

    // Update requirement indicators
    document.getElementById('req-length').className = requirements.length ? 'valid' : '';
    document.getElementById('req-uppercase').className = requirements.uppercase ? 'valid' : '';
    document.getElementById('req-number').className = requirements.number ? 'valid' : '';
    document.getElementById('req-special').className = requirements.special ? 'valid' : '';

    // Calculate strength
    let strength = 0;
    if (requirements.length) strength++;
    if (requirements.uppercase) strength++;
    if (requirements.number) strength++;
    if (requirements.special) strength++;

    // Update UI
    segments.forEach((seg, index) => {
        seg.className = 'strength-segment';
        if (index < strength) {
            seg.classList.add(`strength-${strength}`);
        }
    });

    const strengthText = ['Weak', 'Fair', 'Good', 'Strong'][strength - 1] || 'Weak';
    strengthValue.textContent = strengthText;
    strengthValue.className = `strength-${strength}`;
}

function checkPasswordMatch() {
    const matchIndicator = document.getElementById('passwordMatch');
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;

    if (!confirmPassword) {
        matchIndicator.textContent = '';
        return;
    }

    if (password === confirmPassword) {
        matchIndicator.textContent = 'Passwords match!';
        matchIndicator.className = 'match';
    } else {
        matchIndicator.textContent = 'Passwords do not match';
        matchIndicator.className = 'no-match';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm-password');
    const passwordForm = document.getElementById('passwordForm');

    if (passwordField) {
        passwordField.addEventListener('input', function() {
            checkPasswordStrength(this.value);
            checkPasswordMatch();
        });
    }

    if (confirmPasswordField) {
        confirmPasswordField.addEventListener('input', checkPasswordMatch);
    }

    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return;
            }
            
            // Show loading state
            document.querySelector('.btn-text').style.display = 'none';
            document.querySelector('.btn-loader').style.display = 'flex';
            
            // Simulate API call
            setTimeout(function() {
                // Hide form elements
                document.querySelector('h1').style.display = 'none';
                document.querySelector('.instructions').style.display = 'none';
                document.getElementById('passwordForm').style.display = 'none';
                document.querySelector('.login-link').style.display = 'none';
                
                // Show success message
                document.querySelector('.success-message').style.display = 'block';
            }, 1500);
        });
    }
});
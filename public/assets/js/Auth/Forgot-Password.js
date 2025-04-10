document.addEventListener('DOMContentLoaded', function() {
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            const email = document.getElementById('email').value;
            const feedback = document.querySelector('.input-feedback');
            const btnText = document.querySelector('.btn-text');
            const btnLoader = document.querySelector('.btn-loader');
            
            // Simple validation
            if (!email || !email.includes('@')) {
                feedback.textContent = 'Please enter a valid email address';
                feedback.style.color = '#e74c3c';
                return;
            }
            
            // Show loading state
            btnText.style.display = 'none';
            btnLoader.style.display = 'flex';
            feedback.textContent = '';
            
            // Simulate API call
            setTimeout(function() {
                // Hide form elements
                document.querySelector('h1').style.display = 'none';
                document.querySelector('.instructions').style.display = 'none';
                document.querySelector('.form-group').style.display = 'none';
                document.getElementById('submitBtn').style.display = 'none';
                document.querySelector('.login-link').style.display = 'none';
                
                // Show success message
                document.querySelector('.success-message').style.display = 'block';
            }, 1500);
        });
    }
});
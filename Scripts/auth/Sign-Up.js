document.addEventListener('DOMContentLoaded', function() {
    // Get references to the checkbox and button
    const termsCheckbox = document.getElementById('terms');
    const signupButton = document.getElementById('signupButton');

    // Add event listener to the checkbox if they exist
    if (termsCheckbox && signupButton) {
        termsCheckbox.addEventListener('change', function() {
            // Enable/disable button based on checkbox state
            signupButton.disabled = !this.checked;
        });
    }
});
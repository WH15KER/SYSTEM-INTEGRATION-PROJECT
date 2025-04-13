document.addEventListener('DOMContentLoaded', function() {
    // Contact form validation
    const contactForm = document.querySelector('.form-container form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            // Client-side validation
            const name = this.querySelector('#name').value.trim();
            const surname = this.querySelector('#surname').value.trim();
            const email = this.querySelector('#email').value.trim();
            const message = this.querySelector('#message').value.trim();
            
            if (!name || !surname || !email || !message) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return;
            }
            
            // Basic email validation
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return;
            }
            
            // If all validations pass, allow form submission
            // Server-side validation will handle the rest
        });
    }
});
document.addEventListener('DOMContentLoaded', function() {
    // Contact form validation can be added here
    const contactForm = document.querySelector('.form-container form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Form validation and submission logic
            alert('Thank you for your message! We will contact you soon.');
            this.reset();
        });
    }
});
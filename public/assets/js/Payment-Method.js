document.addEventListener('DOMContentLoaded', function() {
    // Payment method selection
    const paymentMethods = document.querySelectorAll('.payment-method');
    paymentMethods.forEach(method => {
        method.addEventListener('click', () => {
            paymentMethods.forEach(m => {
                m.classList.remove('active');
                m.querySelector('.fa-check-circle').classList.add('hidden');
            });
            method.classList.add('active');
            method.querySelector('.fa-check-circle').classList.remove('hidden');
        });
    });

    // Payment processing
    const payButton = document.getElementById('payButton');
    if (payButton) {
        payButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            const button = this;
            const spinner = button.querySelector('.spinner');
            const text = button.querySelector('.button-text');
            
            if (!document.getElementById('paymentForm').checkValidity()) {
                alert('Please fill in all required fields correctly.');
                return;
            }
            
            if (!document.getElementById('terms').checked) {
                alert('Please agree to the Terms of Service and Privacy Policy.');
                return;
            }
            
            // Simulate payment processing
            button.disabled = true;
            text.textContent = "Processing Payment...";
            spinner.classList.remove('hidden');
            
            setTimeout(() => {
                alert("Payment successful! Your lab results are now available.");
                button.disabled = false;
                text.textContent = "Pay Php 50.00";
                spinner.classList.add('hidden');
            }, 2000);
        });
    }

    // Format card number input
    const cardNumberInput = document.getElementById('card-number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '');
            if (value.length > 0) {
                value = value.match(new RegExp('.{1,4}', 'g')).join(' ');
            }
            e.target.value = value;
        });
    }
});
document.addEventListener('DOMContentLoaded', function() {
    // Payment method selection
    const paymentMethods = document.querySelectorAll('.payment-method');
    const paymentMethodInput = document.getElementById('paymentMethod');
    
    paymentMethods.forEach(method => {
        method.addEventListener('click', () => {
            const methodValue = method.getAttribute('data-method');
            paymentMethodInput.value = methodValue;
            
            paymentMethods.forEach(m => {
                m.classList.remove('active');
                m.querySelector('.fa-check-circle').classList.add('hidden');
            });
            method.classList.add('active');
            method.querySelector('.fa-check-circle').classList.remove('hidden');
        });
    });

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

    // Form submission handling
    const paymentForm = document.getElementById('paymentForm');
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            const payButton = document.getElementById('payButton');
            if (payButton) {
                const spinner = payButton.querySelector('.spinner');
                const text = payButton.querySelector('.button-text');
                
                payButton.disabled = true;
                text.textContent = "Processing Payment...";
                spinner.classList.remove('hidden');
            }
        });
    }
});
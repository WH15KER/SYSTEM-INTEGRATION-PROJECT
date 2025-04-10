document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            this.classList.add('active');
            document.getElementById(`${tabId}-tab`).classList.add('active');
        });
    });
    
    // Download all records functionality
    const downloadAllBtn = document.getElementById('downloadAllBtn');
    if (downloadAllBtn) {
        downloadAllBtn.addEventListener('click', function() {
            const spinner = document.createElement('i');
            spinner.className = 'fas fa-spinner fa-spin';
            this.innerHTML = '';
            this.appendChild(spinner);
            this.appendChild(document.createTextNode(' Preparing Download...'));
            this.disabled = true;
            
            setTimeout(() => {
                alert('All your medical records have been compiled and will download shortly.');
                this.innerHTML = '';
                this.appendChild(document.createElement('i'));
                this.querySelector('i').className = 'fas fa-download';
                this.appendChild(document.createTextNode(' Download All Records'));
                this.disabled = false;
            }, 2000);
        });
    }
    
    // Share records functionality
    const shareRecordsBtn = document.getElementById('shareRecordsBtn');
    if (shareRecordsBtn) {
        shareRecordsBtn.addEventListener('click', function() {
            alert('Share functionality would open a dialog to share your records with selected providers.');
        });
    }
    
    // Add medication functionality
    const addMedicationBtn = document.getElementById('addMedicationBtn');
    if (addMedicationBtn) {
        addMedicationBtn.addEventListener('click', function() {
            alert('A form would appear to add new medication to your record.');
        });
    }
    
    // Add allergy functionality
    const addAllergyBtn = document.getElementById('addAllergyBtn');
    if (addAllergyBtn) {
        addAllergyBtn.addEventListener('click', function() {
            alert('A form would appear to add new allergy to your record.');
        });
    }
    
    // Add immunization functionality
    const addImmunizationBtn = document.getElementById('addImmunizationBtn');
    if (addImmunizationBtn) {
        addImmunizationBtn.addEventListener('click', function() {
            alert('A form would appear to add new immunization to your record.');
        });
    }
    
    // Animation for cards on scroll
    const observerOptions = {
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.summary-card, .visit-card, .medication-card, .allergy-card, .immunization-card').forEach(card => {
        observer.observe(card);
    });
});
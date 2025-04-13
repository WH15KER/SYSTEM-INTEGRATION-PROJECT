document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            tab.classList.add('active');
            const tabId = tab.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
            
            // Store the active tab in sessionStorage
            sessionStorage.setItem('activeBillingTab', tabId);
        });
    });
    
    // Restore active tab if exists
    const activeTab = sessionStorage.getItem('activeBillingTab');
    if (activeTab) {
        document.querySelector(`.tab[data-tab="${activeTab}"]`).click();
    }
});
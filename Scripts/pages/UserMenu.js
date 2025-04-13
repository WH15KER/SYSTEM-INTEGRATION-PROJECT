// Scripts/UserMenu.js
document.addEventListener('DOMContentLoaded', function() {
    // Handle logout functionality
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // You can add any pre-logout actions here if needed
            window.location.href = 'logout.php';
        });
    }

    // Enhance dropdown accessibility for user menu
    const userMenuDropdown = document.querySelector('.user-menu .dropdown');
    if (userMenuDropdown) {
        userMenuDropdown.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                const btn = this.querySelector('.dropbtn');
                if (btn) btn.click();
                e.preventDefault();
            }
        });
    }
});
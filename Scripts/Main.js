document.addEventListener('DOMContentLoaded', function() {
    // Check login status
    const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
    
    // Get elements
    const mainNavLinks = document.getElementById('mainNavLinks');
    const authButtons = document.getElementById('authButtons');
    const userMenu = document.getElementById('userMenu');
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileMenuContent = document.querySelector('.mobile-menu-content');
    
    // Toggle login state
    function toggleLoginState() {
        if (isLoggedIn) {
            mainNavLinks.style.display = "flex";
            userMenu.style.display = "block";
            authButtons.style.display = "none";
        } else {
            mainNavLinks.style.display = "none";
            userMenu.style.display = "none";
            authButtons.style.display = "flex";
        }
    }
    
    // Initialize login state
    toggleLoginState();
    
    // Mobile menu functionality
    if (hamburgerBtn) {
        hamburgerBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
            
            // Clone appropriate content to mobile menu
            mobileMenuContent.innerHTML = '';
            if (isLoggedIn) {
                const navClone = mainNavLinks.cloneNode(true);
                const userClone = userMenu.cloneNode(true);
                mobileMenuContent.appendChild(navClone);
                mobileMenuContent.appendChild(userClone);
                
                // Make dropdowns work in mobile
                const mobileDropdowns = mobileMenuContent.querySelectorAll('.dropdown');
                mobileDropdowns.forEach(dropdown => {
                    const btn = dropdown.querySelector('.dropbtn');
                    const content = dropdown.querySelector('.dropdown-content');
                    
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        content.style.display = content.style.display === 'block' ? 'none' : 'block';
                    });
                });
            } else {
                const authClone = authButtons.cloneNode(true);
                mobileMenuContent.appendChild(authClone);
            }
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.navbar') && !e.target.closest('.mobile-menu')) {
            mobileMenu.classList.remove('active');
        }
    });
    
    // Logout functionality
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if(confirm('Are you sure you want to logout?')) {
                localStorage.removeItem('isLoggedIn');
                window.location.href = 'Login-Page.html';
            }
        });
    }
    
    // Better dropdown accessibility
    document.querySelectorAll('.dropdown').forEach(dropdown => {
        dropdown.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                this.querySelector('.dropbtn').click();
                e.preventDefault();
            }
        });
    });
});
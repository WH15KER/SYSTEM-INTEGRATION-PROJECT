document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const mainNavLinks = document.getElementById('mainNavLinks');
    const userMenu = document.getElementById('userMenu');
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileMenuContent = document.querySelector('.mobile-menu-content');
    
    // Mobile menu functionality
    if (hamburgerBtn) {
        hamburgerBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
            
            // Clone appropriate content to mobile menu
            if (mobileMenuContent) {
                mobileMenuContent.innerHTML = '';
                const navClone = mainNavLinks.cloneNode(true);
                mobileMenuContent.appendChild(navClone);
                
                if (userMenu) {
                    const userClone = userMenu.cloneNode(true);
                    mobileMenuContent.appendChild(userClone);
                }
                
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
            }
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.navbar') && !e.target.closest('.mobile-menu') && mobileMenu) {
            mobileMenu.classList.remove('active');
        }
    });
    
    // Better dropdown accessibility
    document.querySelectorAll('.dropdown').forEach(dropdown => {
        dropdown.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                const btn = this.querySelector('.dropbtn');
                if (btn) btn.click();
                e.preventDefault();
            }
        });
    });
});
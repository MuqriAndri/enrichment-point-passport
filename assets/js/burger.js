// filepath: assets/js/mobile-menu.js
document.addEventListener('DOMContentLoaded', function() {
    const burgerMenu = document.querySelector('.burger-menu');
    const mobileMenuOverlay = document.querySelector('.mobile-menu-overlay');
    const body = document.body;

    if (burgerMenu && mobileMenuOverlay) {
        burgerMenu.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            
            if (!isExpanded) {
                mobileMenuOverlay.classList.add('active');
                body.style.overflow = 'hidden'; // Prevent scrolling when menu is open
            } else {
                mobileMenuOverlay.classList.remove('active');
                body.style.overflow = ''; // Re-enable scrolling
            }
        });

        // Close menu when clicking outside
        mobileMenuOverlay.addEventListener('click', function(e) {
            if (e.target === this) {
                burgerMenu.setAttribute('aria-expanded', 'false');
                mobileMenuOverlay.classList.remove('active');
                body.style.overflow = '';
            }
        });

        // Add styles for the active mobile menu
        const style = document.createElement('style');
        style.textContent = `
            .mobile-menu-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1001;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            
            .mobile-menu-overlay.active {
                display: block;
                opacity: 1;
            }
            
            .mobile-menu-content {
                position: fixed;
                top: 0;
                left: 0;
                width: 85%;
                max-width: 300px;
                height: 100%;
                background: white;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
                overflow-y: auto;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .mobile-menu-overlay.active .mobile-menu-content {
                transform: translateX(0);
            }
            
            body.dark .mobile-menu-content {
                background: #161b22;
                color: #e6edf3;
            }
        `;
        document.head.appendChild(style);
    }
});
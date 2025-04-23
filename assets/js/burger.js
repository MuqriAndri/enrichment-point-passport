// filepath: assets/js/mobile-menu.js
document.addEventListener('DOMContentLoaded', function() {
    initializeMobileMenu();
});

function initializeMobileMenu() {
    const burgerMenu = document.querySelector('.burger-menu');
    const mobileOverlay = document.querySelector('.mobile-menu-overlay');
    
    if (!burgerMenu || !mobileOverlay) {
        return;
    }
    
    // Toggle menu when burger is clicked
    burgerMenu.addEventListener('click', function() {
        burgerMenu.classList.toggle('active');
        mobileOverlay.classList.toggle('active');
        
        const isExpanded = burgerMenu.classList.contains('active');
        burgerMenu.setAttribute('aria-expanded', isExpanded);
        
        document.body.style.overflow = isExpanded ? 'hidden' : '';
    });
    
    // Close menu when clicking outside
    mobileOverlay.addEventListener('click', function(e) {
        if (e.target === mobileOverlay) {
            burgerMenu.classList.remove('active');
            mobileOverlay.classList.remove('active');
            burgerMenu.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }
    });
    
    // Handle Escape key to close the menu
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileOverlay.classList.contains('active')) {
            burgerMenu.classList.remove('active');
            mobileOverlay.classList.remove('active');
            burgerMenu.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }
    });
}
// profile-dropdown.js - Self-initializing dropdown functionality

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Profile dropdown: DOM loaded, initializing');
    initializeDropdowns();
});

function initializeDropdowns() {
    console.log('Profile dropdown: Starting initialization');
    const profileTrigger = document.querySelector('.profile-trigger');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    if (!profileTrigger || !dropdownMenu) {
        console.log('Profile dropdown: Required elements not found in the DOM');
        return;
    }

    console.log('Profile dropdown: Elements found, setting up events');
    setupDropdownEvents(profileTrigger, dropdownMenu);
}

function setupDropdownEvents(trigger, menu) {
    // Click event for opening/closing dropdown
    trigger.addEventListener('click', (e) => {
        console.log('Profile dropdown: Trigger clicked');
        e.stopPropagation();
        toggleDropdown(menu);
    });

    // Keyboard accessibility
    trigger.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            console.log('Profile dropdown: Trigger keyboard activated');
            e.preventDefault();
            toggleDropdown(menu);
        }
    });

    // Close when clicking outside
    document.addEventListener('click', (e) => {
        if (!menu.contains(e.target) && !trigger.contains(e.target)) {
            closeDropdown(menu);
        }
    });

    // Close dropdown when escape key is pressed
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && menu.classList.contains('show')) {
            closeDropdown(menu);
        }
    });
}

function toggleDropdown(menu) {
    const isExpanded = menu.classList.contains('show');
    console.log(isExpanded ? 'Profile dropdown: Closing' : 'Profile dropdown: Opening');
    
    menu.classList.toggle('show');
    menu.setAttribute('aria-hidden', isExpanded);
    menu.previousElementSibling.setAttribute('aria-expanded', !isExpanded);
}

function closeDropdown(menu) {
    console.log('Profile dropdown: Closing dropdown');
    menu.classList.remove('show');
    menu.setAttribute('aria-hidden', 'true');
    menu.previousElementSibling.setAttribute('aria-expanded', 'false');
}
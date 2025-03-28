// settings.js

// Function to toggle dark mode
function toggleDarkMode() {
    const body = document.body;
    const isDarkMode = body.classList.contains('dark-mode');
    
    // Toggle dark mode class on the body
    body.classList.toggle('dark-mode', !isDarkMode);
    
    // Save the user's preference in localStorage
    localStorage.setItem('darkMode', !isDarkMode);
}

// Load the dark mode setting from localStorage on page load
window.onload = function() {
    const darkModeSetting = localStorage.getItem('darkMode');
    
    // Apply dark mode if the setting is true
    if (darkModeSetting === 'true') {
        document.body.classList.add('dark-mode');
        document.getElementById('darkModeToggle').checked = true; // Ensure the toggle is in the right state
    }
};

// Add event listener to the dark mode toggle checkbox
document.getElementById('darkModeToggle').addEventListener('change', toggleDarkMode);

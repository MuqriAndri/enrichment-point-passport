/**
 * Custom script to enforce calendar icon styling for datetime-local inputs
 */
document.addEventListener('DOMContentLoaded', function() {
    // Apply styling directly after DOM is loaded
    const styleFixForDatetime = document.createElement('style');
    styleFixForDatetime.textContent = `
        /* Strong override for calendar icon styling */
        input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            background-color: transparent !important;
            cursor: pointer !important;
            color: var(--primary-color) !important;
            opacity: 1 !important;
            filter: invert(30%) sepia(90%) saturate(1000%) hue-rotate(175deg) !important;
        }
        
        /* Target Webkit/Blink browsers specifically */
        @media screen and (-webkit-min-device-pixel-ratio:0) {
            input[type="datetime-local"] {
                color-scheme: light !important;
                background-color: white !important;
            }
            
            input[type="datetime-local"]::-webkit-calendar-picker-indicator {
                filter: invert(30%) sepia(90%) saturate(1000%) hue-rotate(175deg) !important;
            }
        }
        
        /* Firefox specific styling */
        @-moz-document url-prefix() {
            input[type="datetime-local"] {
                background-color: white !important;
                color: var(--text-dark) !important;
            }
        }
    `;
    document.head.appendChild(styleFixForDatetime);
    
    // Direct manipulation of inputs for stronger enforcement
    const datetimeInputs = document.querySelectorAll('input[type="datetime-local"]');
    datetimeInputs.forEach(input => {
        // Create an observer to watch for any browser resets of the style
        const observer = new MutationObserver(function(mutations) {
            input.style.colorScheme = 'light';
            input.style.backgroundColor = 'white';
        });
        
        observer.observe(input, { attributes: true, attributeFilter: ['style'] });
    });
}); 
// Add this to your events.js file or appropriate script file
document.addEventListener('DOMContentLoaded', function() {
    // Get all prev and next slides
    const touchSlides = document.querySelectorAll('.event-slide.prev, .event-slide.next');
    
    // Add touch event handlers for mobile
    touchSlides.forEach(slide => {
        slide.addEventListener('touchstart', function() {
            this.classList.add('touch-active');
        });
        
        slide.addEventListener('touchend', function() {
            // Keep the active state for a short time before removing
            setTimeout(() => {
                this.classList.remove('touch-active');
            }, 1000); // Keep visible for 1 second after touch
        });
        
        // Handle touch move/cancel events
        ['touchmove', 'touchcancel'].forEach(eventType => {
            slide.addEventListener(eventType, function() {
                setTimeout(() => {
                    this.classList.remove('touch-active');
                }, 500);
            });
        });
    });
});

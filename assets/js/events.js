// Image slider functionality
document.querySelectorAll('.image-slider').forEach(slider => {
    const images = slider.querySelectorAll('.event-image');
    const indicators = slider.querySelectorAll('.indicator');
    let currentIndex = 0;
    let startX = 0;
    let isDragging = false;

    const showImage = (index) => {
        images.forEach((img, i) => {
            img.style.display = i === index ? 'block' : 'none';
        });
        indicators.forEach((indicator, i) => {
            indicator.classList.toggle('active', i === index);
        });
        currentIndex = index;
    };

    // Add swipe functionality for mobile and laptop views
    const startSwipe = (x) => {
        startX = x;
        isDragging = true;
    };

    const endSwipe = (x) => {
        if (!isDragging) return;
        isDragging = false;
        if (startX > x + 50) {
            // Swipe left - next image
            const nextIndex = (currentIndex + 1) % images.length;
            showImage(nextIndex);
        } else if (startX < x - 50) {
            // Swipe right - previous image
            const prevIndex = (currentIndex - 1 + images.length) % images.length;
            showImage(prevIndex);
        }
    };

    // Touch events for mobile
    slider.addEventListener('touchstart', (e) => startSwipe(e.touches[0].clientX));
    slider.addEventListener('touchend', (e) => endSwipe(e.changedTouches[0].clientX));

    // Mouse events for laptop
    slider.addEventListener('mousedown', (e) => {
        startSwipe(e.clientX);
        isDragging = true;
    });

    slider.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        const currentX = e.clientX;
        if (startX > currentX + 50) {
            const nextIndex = (currentIndex + 1) % images.length;
            showImage(nextIndex);
            isDragging = false; // Prevent multiple triggers
        } else if (startX < currentX - 50) {
            const prevIndex = (currentIndex - 1 + images.length) % images.length;
            showImage(prevIndex);
            isDragging = false; // Prevent multiple triggers
        }
    });

    slider.addEventListener('mouseup', (e) => {
        endSwipe(e.clientX);
    });

    slider.addEventListener('mouseleave', () => {
        isDragging = false; // Cancel drag if mouse leaves slider
    });

    // Add click event listeners to indicators for manual sliding
    indicators.forEach(indicator => {
        indicator.addEventListener('click', () => {
            const index = parseInt(indicator.getAttribute('data-index'));
            showImage(index);
        });
    });

    // Initialize the first image
    showImage(currentIndex);
});

// Event carousel
document.addEventListener('DOMContentLoaded', function() {
    initGlideStyleCarousel();
    initTiltEffect();
});

function initGlideStyleCarousel() {
    const container = document.querySelector('.container');
    const slider = document.querySelector('.event-slider');
    if (!slider) return;
    
    // Add necessary wrapper
    slider.classList.add('glide-carousel');
    
    const slides = slider.querySelectorAll('.event-slide');
    if (slides.length < 2) return; // Allow for 2 or more slides
    
    // Remove any existing carousel navigation
    document.querySelectorAll('.carousel-nav, #carousel-nav-container').forEach(el => {
        el.remove();
    });
    
    // Configure carousel controls
    const carouselNav = document.createElement('div');
    carouselNav.className = 'carousel-nav';
    carouselNav.style.cssText = 'display: flex; justify-content: center; gap: 20px; margin: 0 auto; position: relative; z-index: 20;';
    
    const prevBtn = document.createElement('button');
    prevBtn.className = 'carousel-btn prev-btn';
    prevBtn.innerHTML = '&larr;';
    prevBtn.setAttribute('aria-label', 'Previous event');
    prevBtn.style.cssText = 'width: 50px; height: 50px; border-radius: 50%; background-color: #1a365d; color: white; border: none; cursor: pointer; font-size: 1.5rem; display: flex; align-items: center; justify-content: center;';
    
    const nextBtn = document.createElement('button');
    nextBtn.className = 'carousel-btn next-btn';
    nextBtn.innerHTML = '&rarr;';
    nextBtn.setAttribute('aria-label', 'Next event');
    nextBtn.style.cssText = 'width: 50px; height: 50px; border-radius: 50%; background-color: #1a365d; color: white; border: none; cursor: pointer; font-size: 1.5rem; display: flex; align-items: center; justify-content: center;';
    
    carouselNav.appendChild(prevBtn);
    carouselNav.appendChild(nextBtn);
    
    // Create a dedicated container for the navigation
    const navContainer = document.createElement('div');
    navContainer.id = 'carousel-nav-container';
    navContainer.style.cssText = 'width: 100%; text-align: center; margin: 20px auto 40px; clear: both; position: relative;';
    navContainer.appendChild(carouselNav);
    
    // Find the calendar container
    const calendarContainer = document.querySelector('.calendar-container');
    
    // Always insert after the slider directly
    slider.insertAdjacentElement('afterend', navContainer);
    
    // Setup variables
    let currentIndex = Math.min(1, slides.length - 1); // Start with the first or second slide
    
    // Position all slides initially
    updateCarousel();
    
    // Add navigation events - modified for looping
    prevBtn.addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + slides.length) % slides.length;
        updateCarousel();
    });
    
    nextBtn.addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % slides.length;
        updateCarousel();
    });
    
    // Make slides clickable
    slides.forEach((slide, index) => {
        slide.addEventListener('click', () => {
            if (index !== currentIndex) {
                currentIndex = index;
                updateCarousel();
            }
        });
    });
    
    // Update carousel positions and states
    function updateCarousel() {
        slides.forEach((slide, index) => {
            // Reset previous transforms and states
            slide.classList.remove('active', 'prev', 'next', 'far-prev', 'far-next', 'very-far-prev', 'very-far-next');
            slide.style.transform = '';
            slide.style.zIndex = '';
            slide.style.opacity = '';
            
            // Calculate relative position with wrapping for cycle effect
            let position = index - currentIndex;
            
            // Handle wrapping for continuous loop effect
            if (position < -Math.floor(slides.length / 2)) {
                position += slides.length;
            } else if (position > Math.floor(slides.length / 2)) {
                position -= slides.length;
            }
            
            // Assign position classes
            if (position === 0) {
                slide.classList.add('active');
            } else if (position === -1 || (position === slides.length - 1 && currentIndex === 0)) {
                slide.classList.add('prev');
            } else if (position === 1 || (position === -(slides.length - 1) && currentIndex === slides.length - 1)) {
                slide.classList.add('next'); 
            } else if (position === -2 || (position === slides.length - 2 && currentIndex <= 1)) {
                slide.classList.add('far-prev');
            } else if (position === 2 || (position === -(slides.length - 2) && currentIndex >= slides.length - 2)) {
                slide.classList.add('far-next');
            } else if (position === -3 || (position === slides.length - 3 && currentIndex <= 2)) {
                slide.classList.add('very-far-prev');
            } else if (position === 3 || (position === -(slides.length - 3) && currentIndex >= slides.length - 3)) {
                slide.classList.add('very-far-next');
            } else if (position < -3) {
                slide.classList.add('very-far-prev');
            } else if (position > 3) {
                slide.classList.add('very-far-next');
            }
        });
        
        // Remove button disabled states since we are now looping
        prevBtn.disabled = false;
        nextBtn.disabled = false;
    }
    
    // Add keyboard navigation with looping
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            currentIndex = (currentIndex - 1 + slides.length) % slides.length;
            updateCarousel();
        } else if (e.key === 'ArrowRight') {
            currentIndex = (currentIndex + 1) % slides.length;
            updateCarousel();
        }
    });
    
    // Auto-rotate the carousel
    let autoRotateInterval;
    
    function startAutoRotate() {
        if (autoRotateInterval) clearInterval(autoRotateInterval);
        autoRotateInterval = setInterval(() => {
            currentIndex = (currentIndex + 1) % slides.length;
            updateCarousel();
        }, 5000); // Rotate every 5 seconds
    }
    
    // Start auto-rotation
    startAutoRotate();
    
    // Pause rotation on hover/focus
    slider.addEventListener('mouseenter', () => {
        clearInterval(autoRotateInterval);
    });
    
    slider.addEventListener('mouseleave', () => {
        startAutoRotate();
    });
    
    // Stop auto-rotation when user interacts with controls
    carouselNav.addEventListener('mouseenter', () => {
        clearInterval(autoRotateInterval);
    });
    
    // Responsive adjustment
    window.addEventListener('resize', updateCarousel);
}

// Simpler tilt effect for cards
function initTiltEffect() {
    const eventSlides = document.querySelectorAll('.event-slide');
    
    eventSlides.forEach(slide => {
        // Add tilt effect on mouse move
        slide.addEventListener('mousemove', function(e) {
            // Only apply to active slide or next/prev slides
            if (!slide.classList.contains('active') && 
                !slide.classList.contains('next') && 
                !slide.classList.contains('prev')) {
                return;
            }
            
            const rect = slide.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            // Calculate tilt values (maximum 15 degrees)
            const tiltX = ((y / rect.height) - 0.5) * 10;
            const tiltY = ((x / rect.width) - 0.5) * -10;
            
            // Apply the tilt effect
            slide.style.transform = `perspective(1000px) rotateX(${tiltX}deg) rotateY(${tiltY}deg)`;
            
            // Add shine effect
            updateShineEffect(slide, x, y);
        });
        
        // Reset tilt on mouse leave
        slide.addEventListener('mouseleave', function() {
            slide.style.transform = '';
            
            // Reset shine effect
            const shine = slide.querySelector('.shine-effect');
            if (shine) {
                shine.style.opacity = '0';
            }
        });
        
        // Create shine effect element
        createShineEffect(slide);
    });
    
    function createShineEffect(element) {
        // Check if shine effect already exists
        if (element.querySelector('.shine-effect')) return;
        
        const shine = document.createElement('div');
        shine.className = 'shine-effect';
        element.appendChild(shine);
    }
    
    function updateShineEffect(element, x, y) {
        const shine = element.querySelector('.shine-effect');
        if (!shine) return;
        
        const rect = element.getBoundingClientRect();
        const percentX = Math.round((x / rect.width) * 100);
        const percentY = Math.round((y / rect.height) * 100);
        
        shine.style.background = `radial-gradient(circle at ${percentX}% ${percentY}%, rgba(255,255,255,0.4) 0%, rgba(255,255,255,0) 80%)`;
        shine.style.opacity = '1';
    }
}

// Calendar functionality  
function generateCalendar(events) {
    const calendar = document.querySelector('.calendar-grid');
    if (!calendar) {
        console.error('Calendar grid element not found');
        return;
    }
    
    const today = new Date();
    const currentMonth = today.getMonth();
    const currentYear = today.getFullYear();

    // Get the first and last day of the month
    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const lastDate = new Date(currentYear, currentMonth + 1, 0).getDate();

    // Clear the calendar
    calendar.innerHTML = '';

    // Add empty days for the first week
    for (let i = 0; i < firstDay; i++) {
        const emptyDay = document.createElement('div');
        emptyDay.classList.add('calendar-day', 'inactive');
        emptyDay.setAttribute('aria-hidden', 'true');
        calendar.appendChild(emptyDay);
    }

    // Add days of the month
    for (let date = 1; date <= lastDate; date++) {
        const day = document.createElement('div');
        day.classList.add('calendar-day');
        day.textContent = date;
        day.setAttribute('role', 'gridcell');
        day.setAttribute('tabindex', '0');

        // Highlight today's date
        if (date === today.getDate() && currentMonth === today.getMonth() && currentYear === today.getFullYear()) {
            day.classList.add('today');
            day.setAttribute('aria-label', `Today, ${date}`);
        }

        // Highlight event dates
        if (events && Array.isArray(events)) {
            const event = events.find(event => {
                if (!event || !event.date) return false;
                const eventDate = new Date(event.date);
                return (
                    eventDate.getDate() === date &&
                    eventDate.getMonth() === currentMonth &&
                    eventDate.getFullYear() === currentYear
                );
            });

            if (event) {
                day.classList.add('has-event');
                day.setAttribute('data-event', event.name); // Add event name as a data attribute
            }
        }

        calendar.appendChild(day);
    }
}

function fixNavigationPlacement() {
    const slider = document.querySelector('.event-slider');
    const divider = document.querySelector('.section-divider');
    const calendarContainer = document.querySelector('.calendar-container');
    const navContainer = document.getElementById('carousel-nav-container');
    
    if (!slider || !navContainer) return;
    
    // Remove the navigation container from its current position
    navContainer.remove();
    
    // Insert it between the slider and divider
    if (divider) {
        // Insert before the divider
        divider.parentNode.insertBefore(navContainer, divider);
    } else if (calendarContainer) {
        // If no divider, insert before calendar
        calendarContainer.parentNode.insertBefore(navContainer, calendarContainer);
    } else {
        // Fallback - insert after slider
        slider.parentNode.insertBefore(navContainer, slider.nextSibling);
    }
}

// Call this after the carousel is initialized
document.addEventListener('DOMContentLoaded', function() {
    // Wait a short moment for other scripts to complete
    setTimeout(fixNavigationPlacement, 100);
});

// Initialize calendar when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
});

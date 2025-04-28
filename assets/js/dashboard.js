// Constants
const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
const DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Initialize all components when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard: DOM loaded, initializing');
    initializeCalendar();
    initializeProgressCircle();
    handleEventRegistration();
    initializeMobileMenu();
    initializeNewsSlider();
    initializeEnrichmentPointProgress();
});

function initializeCalendar() {
    console.log('Dashboard: Initializing calendar');
    const calendarGrid = document.querySelector('.calendar-grid');
    if (!calendarGrid) {
        console.log('Dashboard: Calendar grid not found');
        return;
    }

    const currentDate = new Date();
    const currentMonth = currentDate.getMonth();
    const currentYear = currentDate.getFullYear();
    
    calendarGrid.innerHTML = '';

    // Calendar configuration
    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const lastDate = new Date(currentYear, currentMonth + 1, 0).getDate();
    const events = getCalendarEvents();

    // Add empty days for the start of the month
    createEmptyDays(calendarGrid, firstDay);

    // Create calendar days
    createCalendarDays(calendarGrid, lastDate, currentDate, events);

    // Add keyboard navigation
    addCalendarKeyboardNavigation(calendarGrid);
    console.log('Dashboard: Calendar initialized');
}

function getCalendarEvents() {
    return {
        '2025-02-15': {
            title: 'Cultural Exchange Program',
            time: '9:00 AM',
            location: 'Main Hall'
        },
        '2025-02-22': {
            title: 'Innovation Challenge',
            time: '2:00 PM',
            location: 'Innovation Lab'
        }
    };
}

function createEmptyDays(grid, count) {
    for (let i = 0; i < count; i++) {
        const emptyDay = document.createElement('div');
        emptyDay.className = 'calendar-day inactive';
        emptyDay.setAttribute('aria-hidden', 'true');
        grid.appendChild(emptyDay);
    }
}

function createCalendarDays(grid, lastDate, currentDate, events) {
    const currentYear = currentDate.getFullYear();
    const currentMonth = currentDate.getMonth();

    for (let date = 1; date <= lastDate; date++) {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';
        dayElement.textContent = date;
        dayElement.setAttribute('role', 'gridcell');
        dayElement.setAttribute('tabindex', '0');

        const dateString = formatDateString(currentYear, currentMonth + 1, date);
        
        if (date === currentDate.getDate()) {
            dayElement.classList.add('today');
            dayElement.setAttribute('aria-label', `Today, ${date} ${MONTHS[currentMonth]}`);
        }

        if (events[dateString]) {
            addEventToDay(dayElement, events[dateString], dateString);
        }

        grid.appendChild(dayElement);
    }
}

function formatDateString(year, month, date) {
    return `${year}-${String(month).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
}

function addEventToDay(dayElement, event, dateString) {
    dayElement.classList.add('has-event');
    dayElement.setAttribute('aria-label', `${event.title} on ${dateString}`);
    
    const tooltip = document.createElement('div');
    tooltip.className = 'event-tooltip';
    tooltip.innerHTML = `
        <strong>${event.title}</strong><br>
        ${event.time} - ${event.location}
    `;
    dayElement.appendChild(tooltip);

    dayElement.addEventListener('click', () => selectDay(dayElement));
    dayElement.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            selectDay(dayElement);
        }
    });
}

function selectDay(dayElement) {
    document.querySelectorAll('.calendar-day').forEach(day => {
        day.classList.remove('selected');
        day.setAttribute('aria-selected', 'false');
    });
    dayElement.classList.add('selected');
    dayElement.setAttribute('aria-selected', 'true');
}

function addCalendarKeyboardNavigation(grid) {
    grid.addEventListener('keydown', (e) => {
        const current = document.activeElement;
        if (!current.classList.contains('calendar-day')) return;

        const days = [...grid.querySelectorAll('.calendar-day:not(.inactive)')];
        const currentIndex = days.indexOf(current);

        switch(e.key) {
            case 'ArrowRight':
                if (currentIndex < days.length - 1) days[currentIndex + 1].focus();
                break;
            case 'ArrowLeft':
                if (currentIndex > 0) days[currentIndex - 1].focus();
                break;
            case 'ArrowUp':
                if (currentIndex >= 7) days[currentIndex - 7].focus();
                break;
            case 'ArrowDown':
                if (currentIndex + 7 < days.length) days[currentIndex + 7].focus();
                break;
        }
    });
}

function initializeProgressCircle() {
    console.log('Dashboard: Initializing progress circle');
    const progressCircle = document.querySelector('.progress-circle');
    const pointsDisplay = document.querySelector('.points');
    const percentageText = document.querySelector('.percentage-text');
    const progressbar = document.querySelector('.circular-progress');
    
    if (!progressCircle || !pointsDisplay) {
        console.log('Dashboard: Progress circle elements not found');
        return;
    }

    // Get the current points from the points span
    const currentPoints = parseInt(pointsDisplay.textContent);
    const totalPoints = 64;

    // Calculate percentage and cap it at 100%
    const percentage = Math.min((currentPoints / totalPoints) * 100, 100);
    const roundedPercentage = percentage.toFixed(0);

    // Update progress circle
    progressCircle.style.strokeDasharray = `${roundedPercentage}, 100`;

    // Update percentage text
    if (percentageText) {
        percentageText.textContent = roundedPercentage;
    }

    // Update aria value
    if (progressbar) {
        progressbar.setAttribute('aria-valuenow', roundedPercentage);
    }
    console.log(`Dashboard: Progress circle set to ${roundedPercentage}%`);
}

function handleEventRegistration() {
    console.log('Dashboard: Setting up event registration');
    const eventList = document.querySelector('.event-list');
    if (!eventList) {
        console.log('Dashboard: Event list not found');
        return;
    }

    eventList.addEventListener('click', async (e) => {
        const button = e.target.closest('.register-btn');
        if (!button || button.disabled) return;

        console.log('Dashboard: Register button clicked', button.dataset.eventId);
        try {
            await registerForEvent(button);
        } catch (error) {
            console.error('Dashboard: Error registering for event:', error);
            // Show error message to user if showNotification is available
            if (typeof showNotification === 'function') {
                showNotification('Error registering for event. Please try again.', 'error');
            }
        }
    });
}

async function registerForEvent(button) {
    const eventId = button.dataset.eventId;
    console.log(`Dashboard: Registering for event ${eventId}`);
    const response = await fetch('/api/events/register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ eventId })
    });

    if (response.ok) {
        updateRegistrationButton(button);
        // Show success message if showNotification is available
        if (typeof showNotification === 'function') {
            showNotification('Successfully registered for event!', 'success');
        }
    } else {
        throw new Error('Registration failed');
    }
}

function updateRegistrationButton(button) {
    button.textContent = 'Registered';
    button.disabled = true;
    button.classList.add('registered');
    button.setAttribute('aria-label', 'Already registered for this event');
    console.log('Dashboard: Updated registration button');
}

// Mobile Burger Menu Functionality
function initializeMobileMenu() {
    console.log('Dashboard: Initializing mobile menu');
    const burgerMenu = document.querySelector('.burger-menu');
    const mobileOverlay = document.querySelector('.mobile-menu-overlay');
    
    if (!burgerMenu || !mobileOverlay) {
        console.log('Dashboard: Mobile menu elements not found');
        return;
    }
    
    // Toggle menu when burger is clicked
    burgerMenu.addEventListener('click', function() {
        burgerMenu.classList.toggle('active');
        mobileOverlay.classList.toggle('active');
        
        // Update aria-expanded attribute
        const isExpanded = burgerMenu.classList.contains('active');
        burgerMenu.setAttribute('aria-expanded', isExpanded);
        
        // Prevent body scrolling when menu is open
        document.body.style.overflow = isExpanded ? 'hidden' : '';
        
        console.log(`Dashboard: Mobile menu ${isExpanded ? 'opened' : 'closed'}`);
    });
    
    // Close menu when clicking outside
    mobileOverlay.addEventListener('click', function(e) {
        if (e.target === mobileOverlay) {
            burgerMenu.classList.remove('active');
            mobileOverlay.classList.remove('active');
            burgerMenu.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
            console.log('Dashboard: Mobile menu closed by overlay click');
        }
    });
    
    // Initialize the mobile search functionality
    const mobileSearch = document.querySelector('.mobile-search');
    if (mobileSearch) {
        mobileSearch.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchValue = mobileSearch.querySelector('input').value;
            console.log(`Dashboard: Mobile search for: ${searchValue}`);
            // Implement actual search functionality here
        });
    }
    
    // Handle Escape key to close the menu
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileOverlay.classList.contains('active')) {
            burgerMenu.classList.remove('active');
            mobileOverlay.classList.remove('active');
            burgerMenu.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
            console.log('Dashboard: Mobile menu closed by Escape key');
        }
    });
}

// News Slider functionality
function initializeNewsSlider() {
    const sliderContainer = document.querySelector('.news-slider-container');
    const prevButton = document.querySelector('.slider-arrow.prev');
    const nextButton = document.querySelector('.slider-arrow.next');
    
    if (!sliderContainer || !prevButton || !nextButton) return;
    
    const itemWidth = sliderContainer.querySelector('.news-item').offsetWidth + 16; // Include gap
    const visibleItems = Math.floor(sliderContainer.offsetWidth / itemWidth);
    const totalItems = sliderContainer.querySelectorAll('.news-item').length;
    let currentIndex = 0;
    
    // Check if we should show arrows
    if (totalItems <= visibleItems) {
        prevButton.style.display = 'none';
        nextButton.style.display = 'none';
    } else {
        // Hide prev button initially
        prevButton.style.display = 'none';
    }
    
    // Function to update slider position
    function updateSliderPosition() {
        sliderContainer.scrollTo({
            left: currentIndex * itemWidth,
            behavior: 'smooth'
        });
        
        // Update arrow visibility
        prevButton.style.display = currentIndex > 0 ? 'flex' : 'none';
        nextButton.style.display = currentIndex < totalItems - visibleItems ? 'flex' : 'none';
    }
    
    // Event listeners for arrow buttons
    prevButton.addEventListener('click', () => {
        if (currentIndex > 0) {
            currentIndex--;
            updateSliderPosition();
        }
    });
    
    nextButton.addEventListener('click', () => {
        if (currentIndex < totalItems - visibleItems) {
            currentIndex++;
            updateSliderPosition();
        }
    });
    
    // Resize handling
    window.addEventListener('resize', () => {
        const newVisibleItems = Math.floor(sliderContainer.offsetWidth / itemWidth);
        if (newVisibleItems !== visibleItems) {
            visibleItems = newVisibleItems;
            updateSliderPosition();
        }
    });
}

function initializeEnrichmentPointProgress() {
    // Get the enrichment point value and update circular progress
    const pointsElement = document.querySelector('.points');
    if (!pointsElement) return;
    
    const points = parseInt(pointsElement.textContent);
    const totalPoints = 64; // Maximum points possible
    const percentage = Math.round((points / totalPoints) * 100);
    
    // Update circular progress bar
    const progressCircle = document.querySelector('.progress-circle');
    const percentageText = document.querySelector('.percentage-text');
    
    if (progressCircle) {
        progressCircle.setAttribute('stroke-dasharray', `${percentage}, 100`);
    }
    
    if (percentageText) {
        percentageText.textContent = percentage;
    }
}
// Constants
const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
const DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Initialize all components when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeCalendar();
    initializeDropdowns();
    initializeNotifications();
    initializeProgressCircle();
    handleEventRegistration();
});

function initializeCalendar() {
    const calendarGrid = document.querySelector('.calendar-grid');
    if (!calendarGrid) return;

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

function initializeDropdowns() {
    const profileTrigger = document.querySelector('.profile-trigger');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    if (profileTrigger && dropdownMenu) {
        setupDropdownEvents(profileTrigger, dropdownMenu);
    }
}

function setupDropdownEvents(trigger, menu) {
    trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleDropdown(menu);
    });

    trigger.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            toggleDropdown(menu);
        }
    });

    document.addEventListener('click', (e) => {
        if (!menu.contains(e.target)) {
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
    menu.classList.toggle('show');
    menu.setAttribute('aria-hidden', isExpanded);
    menu.previousElementSibling.setAttribute('aria-expanded', !isExpanded);
}

function closeDropdown(menu) {
    menu.classList.remove('show');
    menu.setAttribute('aria-hidden', 'true');
    menu.previousElementSibling.setAttribute('aria-expanded', 'false');
}

async function initializeNotifications() {
    const notificationBtn = document.querySelector('.notification-btn');
    if (!notificationBtn) return;

    try {
        const response = await fetchNotifications();
        if (response.ok) {
            const notifications = await response.json();
            updateNotificationBadge(notifications.length);
        }
    } catch (error) {
        console.error('Error initializing notifications:', error);
    }

    notificationBtn.addEventListener('click', handleNotificationClick);
}

async function fetchNotifications() {
    return await fetch('/api/notifications', {
        headers: {
            'Accept': 'application/json'
        }
    });
}

function updateNotificationBadge(count) {
    const badge = document.querySelector('.notification-badge');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'block' : 'none';
        badge.setAttribute('aria-label', `${count} unread notifications`);
    }
}

async function handleNotificationClick() {
    try {
        const response = await fetchNotifications();
        if (response.ok) {
            const notifications = await response.json();
            updateNotificationBadge(notifications.length);
            // Additional notification handling logic here
        }
    } catch (error) {
        console.error('Error handling notifications:', error);
    }
}

function initializeProgressCircle() {
    const progressCircle = document.querySelector('.progress-circle');
    const pointsDisplay = document.querySelector('.points');
    const percentageText = document.querySelector('.percentage-text');
    const progressbar = document.querySelector('.circular-progress');
    
    if (!progressCircle || !pointsDisplay) return; // Guard clause if elements don't exist

    // Get the current points from the points span
    const currentPoints = parseInt(pointsDisplay.textContent);
    const totalPoints = 64;

    // Calculate percentage
    const percentage = (currentPoints / totalPoints) * 100;
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
}

function handleEventRegistration() {
    const eventList = document.querySelector('.event-list');
    if (!eventList) return;

    eventList.addEventListener('click', async (e) => {
        const button = e.target.closest('.register-btn');
        if (!button || button.disabled) return;

        try {
            await registerForEvent(button);
        } catch (error) {
            console.error('Error registering for event:', error);
            // Show error message to user
        }
    });
}

async function registerForEvent(button) {
    const eventId = button.dataset.eventId;
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
    } else {
        throw new Error('Registration failed');
    }
}

function updateRegistrationButton(button) {
    button.textContent = 'Registered';
    button.disabled = true;
    button.classList.add('registered');
    button.setAttribute('aria-label', 'Already registered for this event');
}
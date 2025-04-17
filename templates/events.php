<!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Events</title>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/events.css">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
        <script src="<?php echo BASE_URL; ?>/assets/js/events.js" defer></script> <!-- Added events.js -->
    </head>

    <body>
        <div class="dashboard-container">
            <nav class="top-nav">
                <div class="nav-left">
                    <img src="<?php echo BASE_URL; ?>/assets/images/logo/politeknik-brunei-logo.png" alt="PB Logo" class="nav-logo">
                    <h2>Enrichment Point Passport</h2>
                </div>
                <div class="nav-right">
                    <div class="search-bar">
                        <input type="text" placeholder="Search events..." aria-label="Search activities">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                    <div class="nav-actions">
                        <button class="notification-btn" aria-label="Notifications">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span class="notification-badge" aria-label="3 notifications">3</span>
                        </button>
                        <div class="profile-dropdown">
                            <div class="profile-trigger" tabindex="0" role="button" aria-haspopup="true" aria-expanded="false">
                                <div class="user-avatar">
                                    <?php if (isset($_SESSION['profile_picture'])): ?>
                                        <img src="<?php echo $_SESSION['profile_picture']; ?>" alt="Profile Picture">
                                    <?php else: ?>
                                        <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                                    <?php endif; ?>
                                </div>
                                <span class="user-name"><?php echo explode(' ', $_SESSION['full_name'])[0]; ?></span>
                            </div>
                            <div class="dropdown-menu" role="menu">
                                <a href="<?php echo BASE_URL; ?>/profile" class="dropdown-item" role="menuitem">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    My Profile
                                </a>
                                <a href="<?php echo BASE_URL; ?>/settings" class="dropdown-item" role="menuitem">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="3"></circle>
                                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06-.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                    </svg>
                                    Settings
                                </a>
                                <div class="dropdown-divider" role="separator"></div>
                                <a href="<?php echo BASE_URL; ?>/logout" class="dropdown-item" role="menuitem">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" y1="12" x2="9" y2="12"></line>
                                    </svg>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="main-content">
                <div class="main-wrapper">
                    <div class="tab-navigation">
                        <a href="<?php echo BASE_URL; ?>/dashboard" class="tab-item">Dashboard</a>
                        <a href="<?php echo BASE_URL; ?>/ep" class="tab-item">Enrichment Point</a>
                        <a href="<?php echo BASE_URL; ?>/events" class="tab-item active">Events</a>
                        <a href="<?php echo BASE_URL; ?>/cca" class="tab-item">CCAs</a>
                        <a href="<?php echo BASE_URL; ?>/history" class="tab-item">History</a>
                    </div>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-error">
                            <?php
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>
                    <div class="container">
                        <h1>Upcoming and Available Events</h1>
                        <div class="event-slider">
                            <?php
                            $events = [
                                [
                                    "name" => "SICT & SBS RAYA 2025",
                                    "description" => "Raya event for 2025 by Politeknik Brunei for SICT and SBS students.",
                                    "date" => "2025-04-17",
                                    "time" => "10:00 AM",
                                    "location" => "Ong Sum Ping",
                                    "enrichment_points" => 20,
                                    "images" => [
                                        "event-a-1.jpg",
                                        "event-a-2.jpg",
                                        "event-a-3.jpg"
                                    ]
                                ],
                                [
                                    "name" => "Event B",
                                    "description" => "New description for event B.",
                                    "date" => "2025-04-29",
                                    "time" => "10:30 AM",
                                    "location" => "New Location B",
                                    "enrichment_points" => 25,
                                    "images" => [
                                        "event-b-1.jpg",
                                        "event-b-2.jpg",
                                        "event-b-3.jpg"
                                    ]
                                ],
                                [
                                    "name" => "Event C",
                                    "description" => "New description for event C.",
                                    "date" => "2025-09-20",
                                    "time" => "02:00 PM",
                                    "location" => "New Location C",
                                    "enrichment_points" => 35,
                                    "images" => [
                                        "event-c-1.jpg",
                                        "event-c-2.jpg",
                                        "event-c-3.jpg"
                                    ]
                                ]
                            ];

                            foreach ($events as $event) {
                                // Determine the status of the event
                                $eventDate = strtotime($event['date']);
                                $currentDate = strtotime(date('Y-m-d'));
                                $status = $eventDate > $currentDate ? "Upcoming" : "Available";

                                echo "<div class='event-slide'>";
                                echo "<h2>" . $event['name'] . "</h2>";
                                echo "<span class='event-status " . strtolower($status) . "'>$status</span>"; // Add status indicator
                                echo "<div class='image-slider'>";
                                echo "<div class='image-box'>";
                                echo "<div class='image-container'>";
                                foreach ($event['images'] as $index => $image) {
                                    $display = $index === 0 ? "block" : "none";
                                    echo "<img src='" . BASE_URL . "/assets/images/events/$image' alt='" . $event['name'] . " Image' class='event-image' style='display: $display;'>";
                                }
                                echo "</div>";
                                echo "<button class='nav-button left' aria-label='Previous Image'>&lt;</button>";
                                echo "<button class='nav-button right' aria-label='Next Image'>&gt;</button>";
                                echo "</div>";
                                echo "<div class='image-indicators'>";
                                foreach ($event['images'] as $index => $image) {
                                    $activeClass = $index === 0 ? "active" : "";
                                    echo "<span class='indicator $activeClass' data-index='$index'></span>";
                                }
                                echo "</div>";
                                echo "</div>";
                                echo "<p class='short-description'>" . substr($event['description'], 0, 50) . "...</p>";
                                echo "<button class='btn btn-secondary learn-more-btn small-btn' 
                                    data-name='" . $event['name'] . "' 
                                    data-description='" . $event['description'] . "' 
                                    data-date='" . $event['date'] . "' 
                                    data-time='" . $event['time'] . "' 
                                    data-location='" . $event['location'] . "' 
                                    data-points='" . $event['enrichment_points'] . "' 
                                    data-status='" . $status . "'>Learn More</button>";
                                echo "</div>";
                            }
                            ?>
                        </div>
                        <div id="event-modal" class="modal" style="display: none;">
                            <div class="modal-content">
                                <span class="close-btn">&times;</span>
                                <h2 id="modal-event-name"></h2>
                                <p id="modal-event-description"></p>
                                <p id="modal-event-date"></p>
                                <p id="modal-event-time"></p>
                                <p id="modal-event-location"></p>
                                <p id="modal-event-points"></p>
                                <button id="modal-register-btn" class="btn btn-primary">Register</button>
                            </div>
                        </div>
                        <div class="calendar-container">
                            <h2>Event Calendar</h2>
<<<<<<< HEAD
                            <div id="calendar"></div>
                        </div>
                        <script>
                            function generateCalendar(events) {
                                const calendar = document.getElementById('calendar');
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
                                    emptyDay.classList.add('calendar-day');
                                    calendar.appendChild(emptyDay);
                                }

                                // Add days of the month
                                for (let date = 1; date <= lastDate; date++) {
                                    const day = document.createElement('div');
                                    day.classList.add('calendar-day');
                                    day.textContent = date;

                                    // Highlight today's date
                                    if (date === today.getDate() && currentMonth === today.getMonth() && currentYear === today.getFullYear()) {
                                        day.classList.add('today');
                                    }

                                    // Highlight event dates
                                    const event = events.find(event => {
                                        const eventDate = new Date(event.date);
                                        return (
                                            eventDate.getDate() === date &&
                                            eventDate.getMonth() === currentMonth &&
                                            eventDate.getFullYear() === currentYear
                                        );
                                    });

                                    if (event) {
                                        day.classList.add('event-day');
                                        day.setAttribute('data-event', event.name); // Add event name as a data attribute
                                    } else {
                                        day.setAttribute('data-event', 'No events'); // Default message for no events
                                    }

                                    // Add tooltip on hover
                                    const tooltip = document.createElement('div');
                                    tooltip.classList.add('tooltip');
                                    tooltip.textContent = day.getAttribute('data-event');
                                    day.appendChild(tooltip);

                                    // Show tooltip on hover
                                    day.addEventListener('mouseenter', () => {
                                        tooltip.style.display = 'block';
                                    });

                                    day.addEventListener('mouseleave', () => {
                                        tooltip.style.display = 'none';
                                    });

                                    calendar.appendChild(day);
                                }
                            }
                        </script>
=======
                            <section class="calendar-section" aria-labelledby="calendar-heading">
                                <h3 id="calendar-heading">Calendar</h3>
                                <div class="calendar-header" role="row">
                                    <div role="columnheader">Sun</div>
                                    <div role="columnheader">Mon</div>
                                    <div role="columnheader">Tue</div>
                                    <div role="columnheader">Wed</div>
                                    <div role="columnheader">Thu</div>
                                    <div role="columnheader">Fri</div>
                                    <div role="columnheader">Sat</div>
                                </div>
                                <div class="calendar-grid" role="grid">
                                </div>
                            </section>
                        </div>
>>>>>>> 3b738d1 (Update)
                        <script>
                            // Constants
                            const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                            const DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

                            // Initialize calendar when DOM is loaded
                            document.addEventListener('DOMContentLoaded', function() {
                                console.log('Events: DOM loaded, initializing calendar');
                                initializeCalendar();
                            });

                            function initializeCalendar() {
                                console.log('Events: Initializing calendar');
                                const calendarGrid = document.querySelector('.calendar-grid');
                                if (!calendarGrid) {
                                    console.log('Events: Calendar grid not found');
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
                                console.log('Events: Calendar initialized');
                            }

                            function getCalendarEvents() {
                                // Convert PHP events to a usable JS object format
                                const phpEvents = <?php echo json_encode($events); ?>;
                                const formattedEvents = {};
                                
                                phpEvents.forEach(event => {
                                    // Determine if event is upcoming or available
                                    const eventDate = new Date(event.date);
                                    const today = new Date();
                                    today.setHours(0, 0, 0, 0);
                                    
                                    const status = eventDate > today ? "Upcoming" : "Available";
                                    
                                    formattedEvents[event.date] = {
                                        title: event.name,
                                        time: event.time,
                                        location: event.location,
                                        status: status,
                                        description: event.description,
                                        enrichment_points: event.enrichment_points
                                    };
                                });
                                
                                return formattedEvents;
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
                                    
                                    if (date === currentDate.getDate() && currentMonth === currentDate.getMonth()) {
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
                                
                                // Add status class for styling
                                if (event.status === "Upcoming") {
                                    dayElement.classList.add('upcoming-event');
                                } else {
                                    dayElement.classList.add('available-event');
                                }
                                
                                dayElement.setAttribute('aria-label', `${event.title} on ${dateString}: ${event.status}`);
                                
                                const tooltip = document.createElement('div');
                                tooltip.className = 'event-tooltip';
                                tooltip.innerHTML = `
                                    <strong>${event.title}</strong><br>
                                    <span class="event-status ${event.status.toLowerCase()}">${event.status}</span><br>
                                    ${event.time} - ${event.location}
                                `;
                                dayElement.appendChild(tooltip);

                                dayElement.addEventListener('click', () => selectDay(dayElement, event));
                                dayElement.addEventListener('keypress', (e) => {
                                    if (e.key === 'Enter' || e.key === ' ') {
                                        e.preventDefault();
                                        selectDay(dayElement, event);
                                    }
                                });
                            }

                            function selectDay(dayElement, event) {
                                document.querySelectorAll('.calendar-day').forEach(day => {
                                    day.classList.remove('selected');
                                    day.setAttribute('aria-selected', 'false');
                                });
                                dayElement.classList.add('selected');
                                dayElement.setAttribute('aria-selected', 'true');
                                
                                // Show event details in the modal
                                if (event) {
                                    showEventDetails(event);
                                }
                            }
                            
                            function showEventDetails(event) {
                                // Open the event modal with detailed information
                                const modal = document.getElementById('event-modal');
                                const modalName = document.getElementById('modal-event-name');
                                const modalDescription = document.getElementById('modal-event-description');
                                const modalDate = document.getElementById('modal-event-date');
                                const modalTime = document.getElementById('modal-event-time');
                                const modalLocation = document.getElementById('modal-event-location');
                                const modalPoints = document.getElementById('modal-event-points');
                                const modalRegisterBtn = document.getElementById('modal-register-btn');
                                
                                if (modal && modalName) {
                                    modalName.textContent = event.title;
                                    modalDescription.textContent = "Description: " + event.description;
                                    modalDate.textContent = "Date: " + dateString;
                                    modalTime.textContent = "Time: " + event.time;
                                    modalLocation.textContent = "Location: " + event.location;
                                    modalPoints.textContent = "Enrichment Points: " + event.enrichment_points;
                                    
                                    // Update register button based on event status
                                    if (event.status === "Upcoming") {
                                        modalRegisterBtn.textContent = "Not Available Yet";
                                        modalRegisterBtn.disabled = true;
                                        modalRegisterBtn.classList.add('disabled');
                                    } else {
                                        modalRegisterBtn.textContent = "Register";
                                        modalRegisterBtn.disabled = false;
                                        modalRegisterBtn.classList.remove('disabled');
                                    }
                                    
                                    // Show the modal
                                    modal.style.display = 'flex';
                                }
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
                        </script>
                        <style>
                            /* Calendar styles from dashboard.css */
                            .calendar-section {
                                background-color: white;
                                border-radius: 8px;
                                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                                padding: 20px;
                                height: 100%;
                                display: flex;
                                flex-direction: column;
                            }

                            .calendar-section h3 {
                                font-size: 1.2rem;
                                margin-bottom: 15px;
                                color: #2c3e50;
                            }

                            .calendar-header {
                                display: grid;
                                grid-template-columns: repeat(7, 1fr);
                                gap: 4px;
                                text-align: center;
                                font-weight: 500;
                                margin-bottom: 10px;
                                color: #2c3e50;
                            }

                            .calendar-grid {
                                display: grid;
                                grid-template-columns: repeat(7, 1fr);
                                gap: 4px;
                                flex-grow: 1;
                            }

                            .calendar-day {
                                height: 40px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                position: relative;
                                border-radius: 4px;
                                cursor: pointer;
                                transition: all 0.2s;
                                font-size: 0.9rem;
                                border: 1px solid transparent;
                            }

                            .calendar-day:hover:not(.inactive) {
                                background-color: #f5f5f5;
                            }

                            .calendar-day.today {
                                background-color: #e6f7ff;
                                font-weight: bold;
                                border: 1px solid #1890ff;
                            }

                            .calendar-day.has-event {
                                font-weight: bold;
                            }

                            .calendar-day.has-event::after {
                                content: '';
                                position: absolute;
                                bottom: 5px;
                                left: 50%;
                                transform: translateX(-50%);
                                width: 6px;
                                height: 6px;
                                border-radius: 50%;
                                background-color: #1890ff;
                            }

                            .calendar-day.inactive {
                                color: #ccc;
                                cursor: default;
                            }

                            .calendar-day.selected {
                                background-color: #1890ff;
                                color: white;
                            }
                            
                            /* Additional styles for event status */
                            .calendar-day.upcoming-event::after {
                                background-color: #4b8bf4; /* Blue for upcoming */
                            }
                            
                            .calendar-day.available-event::after {
                                background-color: #52c41a; /* Green for available */
                            }

                            /* Event Tooltip */
                            .event-tooltip {
                                position: absolute;
                                bottom: 100%;
                                left: 50%;
                                transform: translateX(-50%);
                                background: white;
                                padding: 8px 12px;
                                border-radius: 4px;
                                box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
                                font-size: 0.8rem;
                                white-space: nowrap;
                                pointer-events: none;
                                opacity: 0;
                                visibility: hidden;
                                transition: all 0.2s;
                                border: 1px solid #e0e0e0;
                                z-index: 1000;
                                text-align: left;
                            }

                            .calendar-day:hover .event-tooltip {
                                opacity: 1;
                                visibility: visible;
                                transform: translateX(-50%) translateY(-5px);
                            }
                            
                            .event-status {
                                display: inline-block;
                                padding: 2px 6px;
                                border-radius: 4px;
                                font-size: 0.8em;
                                margin: 2px 0;
                            }
                            
                            .event-status.upcoming {
                                background-color: #e6f7ff;
                                color: #1890ff;
                            }
                            
                            .event-status.available {
                                background-color: #f6ffed;
                                color: #52c41a;
                            }
                            
                            /* Modal improvements */
                            .modal {
                                background-color: rgba(0, 0, 0, 0.5);
                                backdrop-filter: blur(2px);
                            }
                            
                            .modal-content {
                                max-width: 500px;
                                width: 90%;
                                border-radius: 8px;
                                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
                            }
                            
                            .close-btn {
                                font-size: 1.5rem;
                                color: #555;
                                transition: color 0.2s;
                            }
                            
                            .close-btn:hover {
                                color: #000;
                            }
                            
                            .btn.disabled {
                                opacity: 0.6;
                                cursor: not-allowed;
                            }
                            
                            /* Responsive styles */
                            @media screen and (max-width: 768px) {
                                .calendar-day {
                                    height: 35px;
                                    font-size: 0.8rem;
                                }
                                
                                .event-tooltip {
                                    width: 160px;
                                    font-size: 0.7rem;
                                }
                            }
                        </style>
                        <script src="<?php echo BASE_URL; ?>/assets/js/profile-dropdown.js"></script>
                        <script>
                            const events = <?php echo json_encode($events); ?>;
                            document.addEventListener('DOMContentLoaded', () => {
                                generateCalendar(events);
                            });
                        </script>
                    </div>
    </body>

    </html>

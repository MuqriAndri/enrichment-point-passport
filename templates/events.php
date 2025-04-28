<?php
session_start();
$isDark = false;
if (isset($_SESSION['user_id']) && isset($profilesDB)) {
    $stmt = $profilesDB->prepare("SELECT dark_mode FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $isDark = $user && $user['dark_mode'];
}
?>

<!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Events - Politeknik Brunei</title>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/events.css">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/burger.css">
        <link rel="icon" href="https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com/logo/politeknik-brunei-logo-2.png" type="image/png">

        <script src="<?php echo BASE_URL; ?>/assets/js/events.js" defer></script>
        
        <!-- Add S3 connection optimizations -->
        <link rel="preconnect" href="https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com">
        <link rel="dns-prefetch" href="https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com">
        
        <!-- Load critical images with higher priority -->
        <?php 
        // Preload only the most critical images (limit to 2)
        $preloadCount = 0;
        if (isset($events) && is_array($events)) {
            foreach ($events as $event) {
                if (!empty($event['events_images']) && $preloadCount < 2) {
                    echo '<link rel="preload" href="' . htmlspecialchars(trim($event['events_images'])) . '" as="image" fetchpriority="high">';
                    $preloadCount++;
                }
            }
        }
        ?>
    </head>

    <body class="<?php echo $isDark ? 'dark' : ''; ?>">
        <div class="dashboard-container">
            <nav class="top-nav">
                <div class="nav-left">  
                <button class="burger-menu" aria-label="Toggle menu" aria-expanded="false">
                    <span class="burger-line"></span>
                    <span class="burger-line"></span>
                    <span class="burger-line"></span>
                 </button>
                    <img src="https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com/logo/politeknik-brunei-logo.png" alt="PB Logo" class="nav-logo">
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
                                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
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

            <div class="mobile-menu-overlay">
            <div class="mobile-menu-content">
                <div class="mobile-menu-header">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php if (isset($_SESSION['profile_picture'])): ?>
                                <img src="<?php echo $_SESSION['profile_picture']; ?>" alt="Profile Picture">
                            <?php else: ?>
                                <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <div class="user-details">
                            <h3><?php echo $_SESSION['full_name']; ?></h3>
                            <p><?php echo $_SESSION['role']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="mobile-search">
                    <input type="text" placeholder="Search activities..." aria-label="Search activities">
                    <button type="button" aria-label="Search">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </div>
                <nav class="mobile-nav">
                    <a href="<?php echo BASE_URL; ?>/dashboard" class="mobile-nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false) ? 'active' : ''; ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        Dashboard
                    </a>
                    <a href="<?php echo BASE_URL; ?>/ep" class="mobile-nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/ep') !== false) ? 'active' : ''; ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M16 12l-4 4-4-4M12 8v8"></path>
                        </svg>
                        Enrichment Point
                    </a>
                    <a href="<?php echo BASE_URL; ?>/events" class="mobile-nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/events') !== false) ? 'active' : ''; ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        Events
                    </a>
                    <a href="<?php echo BASE_URL; ?>/cca" class="mobile-nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/cca') !== false) ? 'active' : ''; ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        CCAs
                    </a>
                    <a href="<?php echo BASE_URL; ?>/history" class="mobile-nav-item <?php echo (strpos($_SERVER['REQUEST_URI'], '/history') !== false) ? 'active' : ''; ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        History
                    </a>
                </nav>
                <div class="mobile-menu-footer">
                    <a href="<?php echo BASE_URL; ?>/profile" class="mobile-nav-item">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        My Profile
                    </a>
                    <a href="<?php echo BASE_URL; ?>/settings" class="mobile-nav-item">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                        Settings
                    </a>
                    <a href="<?php echo BASE_URL; ?>/logout" class="mobile-nav-item">
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
                        <section class="events-header">
                            <h1>Upcoming and Available Events</h1>
                            <p>Attend events and accumulate enrichment points through active engagement</p>
                            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'committee')): ?>
                            <div class="events-actions" style="margin-top: 1rem;">
                                <a href="<?php echo BASE_URL; ?>/events-management" class="btn btn-secondary">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 20h9"></path>
                                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                    </svg>
                                    Manage Events
                                </a>
                            </div>
                            <?php endif; ?>
                        </section>
                        
                        <div class="event-slider">
                            <?php
                            // Fetch events from database
                            $events = [];
                            try {
                                // Modified query to get only Scheduled events for carousel
                                $stmt = $eventsDB->prepare("SELECT * FROM events WHERE status = 'Scheduled' ORDER BY created_at DESC");
                                $stmt->execute();
                                $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (count($events) === 0) {
                                    throw new Exception("No scheduled events found");
                                }
                            } catch (Exception $e) {
                                // Log the error but don't show to user
                                error_log("Error fetching events: " . $e->getMessage());
                            }
                            
                            // Debug: Display raw event data
                            echo "<div style='display: none;'>";
                            echo "<h3>Debug: Raw Events Data</h3>";
                            echo "<pre>";
                            var_dump($events);
                            echo "</pre>";
                            echo "</div>";
                            
                            if (count($events) > 0) {
                                foreach ($events as $event) {
                                    // Parse date and time from created_at timestamp
                                    $date = new DateTime($event['created_at']);
                                    
                                    // Determine the status of the event based on date
                                    $eventDate = $date->format('Y-m-d');
                                    $currentDate = date('Y-m-d');
                                    $status = strtotime($eventDate) > strtotime($currentDate) ? "Upcoming" : "Available";
                                    
                                    // Create event card
                                    echo "<div class='event-slide'>";
                                    echo "<h2>" . htmlspecialchars($event['event_name']) . "</h2>";
                                    echo "<span class='event-status " . strtolower($status) . "'>$status</span>";
                                    echo "<div class='image-slider'>";
                                    echo "<div class='image-box'>";
                                    echo "<div class='image-container'>";
                                    
                                    // Display the image from events_images field (VARCHAR)
                                    if (!empty($event['events_images'])) {
                                        // Get original image URL
                                        $imageUrl = trim($event['events_images']);
                                        
                                        // Add loading container with spinner
                                        echo "<div class='image-loading-container'>";
                                        echo "<div class='image-loading-indicator'></div>";
                                        
                                        // Add efficient S3 image loading with native lazy loading and size attributes
                                        echo "<img 
                                            data-src='" . htmlspecialchars($imageUrl) . "' 
                                            src='data:image/svg+xml,%3Csvg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 800 250\"%3E%3C/svg%3E'
                                            alt='" . htmlspecialchars($event['event_name']) . "' 
                                            class='event-image'
                                            loading='lazy'
                                            width='800'
                                            height='250'
                                            onload='this.classList.add(\"loaded\"); this.parentNode.classList.add(\"loaded\");'
                                            style='display: block; max-width: 100%; height: 250px; object-fit: cover;'>";
                                        echo "</div>"; // Close image-loading-container
                                    } else {
                                        echo "<!-- No image URL found for this event -->";
                                    }
                                    
                                    echo "</div>";
                                    
                                    // Only show navigation buttons if needed in the future
                                    echo "<button class='nav-button left' aria-label='Previous Image'>&lt;</button>";
                                    echo "<button class='nav-button right' aria-label='Next Image'>&gt;</button>";
                                    
                                    echo "</div>";
                                    echo "<div class='image-indicators'>";
                                    
                                    // Since there's just one image, we only need one indicator
                                    echo "<span class='indicator active' data-index='0'></span>";
                                    
                                    echo "</div>";
                                    echo "</div>";
                                    
                                    // Display description and add Learn More button
                                    $description = $event['event_description'] ?? "No description available";
                                    echo "<p class='short-description'>" . htmlspecialchars(substr($description, 0, 100)) . (strlen($description) > 100 ? "..." : "") . "</p>";
                                    
                                    // Format date and time for data attributes
                                    $eventDate = new DateTime($event['event_date'] ?? $event['created_at']);
                                    $formattedDate = $eventDate->format('F j, Y');
                                    
                                    $startTime = new DateTime($event['start_time'] ?? '09:00:00');
                                    $endTime = new DateTime($event['end_time'] ?? '17:00:00');
                                    $formattedTime = $startTime->format('h:i A') . ' - ' . $endTime->format('h:i A');
                                    
                                    // Get enrichment points from the event data
                                    $enrichmentPoints = $event['enrichment_points_awarded'] ?? 20;
                                    
                                    echo "<a href='" . BASE_URL . "/events/details/" . $event['event_id'] . "' 
                                        class='btn btn-secondary learn-more-btn small-btn'
                                        data-id='" . $event['event_id'] . "'
                                        data-name='" . htmlspecialchars($event['event_name']) . "'
                                        data-description='" . htmlspecialchars($description) . "'
                                        data-date='" . $formattedDate . "'
                                        data-time='" . $formattedTime . "'
                                        data-location='" . htmlspecialchars($event['event_location'] ?? 'TBD') . "'
                                        data-points='" . $enrichmentPoints . "'
                                        data-status='" . $status . "'
                                    >
                                        Learn More
                                    </a>";
                                    echo "</div>";
                                }
                            } else {
                                // No events found, display a message
                                echo "<div class='no-events-message'>";
                                echo "<svg width='64' height='64' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='1.5'>
                                        <rect x='3' y='4' width='18' height='18' rx='2' ry='2'></rect>
                                        <line x1='16' y1='2' x2='16' y2='6'></line>
                                        <line x1='8' y1='2' x2='8' y2='6'></line>
                                        <line x1='3' y1='10' x2='21' y2='10'></line>
                                      </svg>";
                                echo "<h3>No scheduled events found</h3>";
                                echo "<p>Check back later for upcoming scheduled events</p>";
                                echo "</div>";
                            }
                            ?>
                        </div>
                        
                        <!-- Section divider between events and calendar -->
                        <div class="section-divider"></div>
                        
                        <div class="calendar-container">
                            <h2>Event Calendar</h2>
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
                                    const eventDate = new Date(event.created_at);
                                    const today = new Date();
                                    today.setHours(0, 0, 0, 0);
                                    
                                    const status = eventDate > today ? "Upcoming" : "Available";
                                    
                                    formattedEvents[event.created_at.split(' ')[0]] = {
                                        title: event.event_name,
                                        time: new Date(event.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}),
                                        location: event.event_location,
                                        status: status,
                                        description: event.event_description,
                                        enrichment_points: 20,
                                        id: event.event_id
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
                                // Redirect to event details page
                                if (event && event.id) {
                                    window.location.href = "<?php echo BASE_URL; ?>/events/details/" + event.id;
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
                        <script>
                            // Optimized S3 image loading
                            document.addEventListener('DOMContentLoaded', function() {
                                const s3Images = document.querySelectorAll('img.event-image[data-src]');
                                if (!s3Images.length) return;
                                
                                // Configure IntersectionObserver
                                const observerOptions = {
                                    rootMargin: '200px 0px', // Load images before they come into view
                                    threshold: 0.01 // Start loading when 1% of the image is visible
                                };
                                
                                // Function to handle S3 image loading
                                function loadS3Image(img) {
                                    const src = img.getAttribute('data-src');
                                    if (!src) return;
                                    
                                    // Set actual image src to start loading
                                    img.setAttribute('src', src);
                                    
                                    // Handle load events
                                    img.addEventListener('load', function() {
                                        img.classList.add('loaded');
                                        if (img.parentNode.classList.contains('image-loading-container')) {
                                            img.parentNode.classList.add('loaded');
                                        }
                                    });
                                    
                                    // Handle errors gracefully
                                    img.addEventListener('error', function() {
                                        console.error('Failed to load S3 image:', src);
                                        
                                        // Try to load a smaller version if available
                                        if (src.includes('amazonaws.com') && !src.includes('-small')) {
                                            const smallerSrc = src.replace(/(\.[^.]+)$/, '-small$1');
                                            console.log('Attempting to load smaller version:', smallerSrc);
                                            img.setAttribute('src', smallerSrc);
                                        } else {
                                            // Show error state in the UI
                                            img.parentNode.classList.add('error');
                                            img.parentNode.classList.add('loaded'); // Hide spinner
                                        }
                                    });
                                }
                                
                                // Create and use IntersectionObserver if supported
                                if ('IntersectionObserver' in window) {
                                    const observer = new IntersectionObserver(function(entries) {
                                        entries.forEach(entry => {
                                            if (entry.isIntersecting) {
                                                loadS3Image(entry.target);
                                                observer.unobserve(entry.target);
                                            }
                                        });
                                    }, observerOptions);
                                    
                                    // Observe all images
                                    s3Images.forEach(img => observer.observe(img));
                                } else {
                                    // Fallback for browsers without IntersectionObserver
                                    s3Images.forEach(loadS3Image);
                                }
                                
                                // Immediately load visible images
                                setTimeout(function() {
                                    const visibleImages = Array.from(s3Images).filter(img => {
                                        const rect = img.getBoundingClientRect();
                                        return (
                                            rect.top >= 0 &&
                                            rect.left >= 0 &&
                                            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                                            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
                                        );
                                    });
                                    
                                    visibleImages.forEach(loadS3Image);
                                }, 100);
                            });
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

                            /* Additional Event Slider Styles */
                            .event-slider {
                                position: relative;
                                margin-bottom: 10px;
                            }
                            
                            /* Carousel Navigation Positioning */
                            .carousel-nav {
                                position: relative;
                                display: flex;
                                justify-content: center;
                                gap: 20px;
                                margin: 0 auto 40px;
                                padding-top: 10px;
                                z-index: 20;
                            }
                            
                            .carousel-btn {
                                width: 50px;
                                height: 50px;
                                border-radius: 50%;
                                background-color: #1a365d;
                                color: white;
                                border: none;
                                cursor: pointer;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 1.5rem;
                                transition: all 0.3s ease;
                                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                            }
                            
                            .carousel-btn:hover {
                                background-color: #efbf04;
                                transform: scale(1.1);
                            }
                            
                            /* Calendar Container Positioning */
                            .calendar-container {
                                margin-top: 20px;
                                clear: both;
                            }
                            
                            .event-slide {
                                border-radius: 8px;
                                overflow: hidden;
                                background-color: white;
                                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                                transition: transform 0.3s ease, box-shadow 0.3s ease;
                                margin-bottom: 20px;
                            }
                            
                            .event-slide:hover {
                                transform: translateY(-5px);
                                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
                            }
                            
                            .image-container {
                                height: 250px;
                                overflow: hidden;
                                border-radius: 8px 8px 0 0;
                                position: relative;
                                background-color: #f5f5f5;
                            }
                            
                            .event-image {
                                width: 100%;
                                height: 100%;
                                object-fit: cover;
                                object-position: center;
                                transition: transform 0.3s ease;
                            }
                            
                            .image-container:hover .event-image {
                                transform: scale(1.05);
                            }
                            
                            .short-description {
                                padding: 0 15px;
                                margin: 15px 0;
                                color: #555;
                                line-height: 1.5;
                            }
                            
                            .learn-more-btn {
                                margin: 10px 15px 15px;
                                display: inline-block;
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
                            
                            /* Other events section */
                            .other-events-section {
                                margin-top: 3rem;
                            }
                            
                            .other-events-section h2 {
                                margin-bottom: 1.5rem;
                                color: var(--text-dark);
                            }
                            
                            .events-grid {
                                display: grid;
                                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                                gap: 1.5rem;
                            }
                            
                            .event-card {
                                background-color: white;
                                border-radius: 8px;
                                padding: 1.25rem;
                                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                                transition: transform 0.2s, box-shadow 0.2s;
                            }
                            
                            .event-card:hover {
                                transform: translateY(-5px);
                                box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
                            }
                            
                            .event-card-header {
                                display: flex;
                                justify-content: space-between;
                                align-items: flex-start;
                                margin-bottom: 0.75rem;
                            }
                            
                            .event-card h3 {
                                font-size: 1.1rem;
                                margin: 0;
                                color: var(--text-dark);
                            }
                            
                            .event-date, .event-location {
                                font-size: 0.9rem;
                                color: var(--text-light);
                                margin: 0.5rem 0;
                            }
                            
                            .event-description {
                                margin: 1rem 0;
                                color: var(--text-dark);
                                font-size: 0.95rem;
                                line-height: 1.4;
                            }
                            
                            .event-card .learn-more-btn {
                                width: 100%;
                                margin-top: 1rem;
                            }
                            
                            .event-status.ongoing {
                                background-color: var(--success-color);
                            }
                            
                            .event-status.completed {
                                background-color: var(--text-light);
                            }
                            
                            .event-status.cancelled {
                                background-color: var(--danger-color);
                            }
                            
                            body.dark .event-card {
                                background-color: #1e1e1e;
                            }
                            
                            body.dark .event-card h3 {
                                color: #e0e0e0;
                            }
                            
                            body.dark .event-description {
                                color: #d0d0d0;
                            }
                            
                            /* Image loading styles */
                            .image-loading-container {
                                position: relative;
                                width: 100%;
                                height: 100%;
                                background-color: #f0f0f0;
                                overflow: hidden;
                            }
                            
                            body.dark .image-loading-container {
                                background-color: #2a2a2a;
                            }
                            
                            .image-loading-indicator {
                                position: absolute;
                                top: 50%;
                                left: 50%;
                                transform: translate(-50%, -50%);
                                width: 40px;
                                height: 40px;
                                border-radius: 50%;
                                border: 3px solid rgba(0, 0, 0, 0.1);
                                border-top-color: #efbf04;
                                animation: spin 1s infinite linear;
                                z-index: 1;
                            }
                            
                            /* Error state styling */
                            .image-loading-container.error::before {
                                content: "⚠️";
                                position: absolute;
                                top: 50%;
                                left: 50%;
                                transform: translate(-50%, -50%);
                                font-size: 24px;
                                z-index: 3;
                            }
                            
                            .image-loading-container.error::after {
                                content: "Image failed to load";
                                position: absolute;
                                top: calc(50% + 30px);
                                left: 50%;
                                transform: translateX(-50%);
                                font-size: 14px;
                                color: #666;
                                z-index: 3;
                                text-align: center;
                                width: 80%;
                            }
                            
                            body.dark .image-loading-indicator {
                                border: 3px solid rgba(255, 255, 255, 0.1);
                                border-top-color: #efbf04;
                            }
                            
                            @keyframes spin {
                                0% { transform: translate(-50%, -50%) rotate(0deg); }
                                100% { transform: translate(-50%, -50%) rotate(360deg); }
                            }
                            
                            .event-image {
                                opacity: 0;
                                transition: opacity 0.3s ease;
                                min-height: 50px;
                            }
                            
                            .event-image.loaded {
                                opacity: 1;
                            }
                            
                            .image-loading-container.loaded .image-loading-indicator {
                                display: none;
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
                        <script src="<?php echo BASE_URL; ?>/assets/js/search.js"></script>
                        <script src="<?php echo BASE_URL; ?>/assets/js/burger.js"></script>
                        <script src="<?php echo BASE_URL; ?>/assets/js/mobile-events.js"></script>


                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-content">
                <div class="footer-icons">
                    <div class="icon-container">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                        </svg>
                        <span>Track Progress</span>
                    </div>
                    <div class="icon-container">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                        </svg>
                        <span>Learn & Grow</span>
                    </div>
                    <div class="icon-container">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 15C8.7 15 6 12.3 6 9V4.5C6 3.1 7.1 2 8.5 2H15.5C16.9 2 18 3.1 18 4.5V9C18 12.3 15.3 15 12 15Z" />
                            <path d="M20 20H4C3.4 20 3 19.6 3 19V18C3 14.7 5.7 12 9 12H15C18.3 12 21 14.7 21 18V19C21 19.6 20.6 20 20 20Z" />
                        </svg>
                        <span>Achieve Excellence</span>
                    </div>
                </div>
                <p>Empowering students through enrichment and achievement tracking</p>
            </div>
        </footer>
    </body>

    </html>

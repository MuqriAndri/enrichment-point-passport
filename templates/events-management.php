<?php
session_start();

// Access control check - only admin or committee members can access this page
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'committee')) {
    header("Location: " . BASE_URL);
    exit();
}

// Dark mode check
$isDark = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $profilesDB->prepare("SELECT dark_mode FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $isDark = $user && $user['dark_mode'];
}

// Initialize event repository
require_once 'repositories/event-repository.php';
$eventRepo = new EventRepository($eventsDB, $profilesDB);

// Get all events
$events = $eventRepo->getAllEvents();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrichment Point Passport - Events Management</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/events.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/events-management.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/burger.css">
    <!-- Include Google Maps for location functionality -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBXXh-Lwbrw-UKAC9YsrBq09vyKNmG0Lzo&libraries=places" async defer></script>
</head>
<body class="<?php echo $isDark ? 'dark' : ''; ?>">
    <div class="dashboard-container">
        <!-- Top Navigation Bar -->
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
                    <input type="text" placeholder="Search events..." aria-label="Search events">
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

        <!-- Mobile Navigation Overlay -->
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
                    <input type="text" placeholder="Search events..." aria-label="Search events">
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

                <!-- Page Header with Actions -->
                <div class="events-management-header">
                    <h1>Events Management</h1>
                    <div class="event-actions">
                        <button id="add-event-btn" class="btn btn-primary">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Add New Event
                        </button>
                        <a href="<?php echo BASE_URL; ?>/events" class="btn btn-secondary">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 17l-5-5 5-5M18 17l-5-5 5-5"></path>
                            </svg>
                            Back to Events
                        </a>
                    </div>
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

                <!-- Events List -->
                <div class="events-list-container">
                    <div class="events-filter">
                        <input type="text" id="event-search" placeholder="Search events..." class="search-input">
                        <select id="status-filter" class="filter-select">
                            <option value="all">All Statuses</option>
                            <option value="Scheduled">Scheduled</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <table class="events-table">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Date & Time</th>
                                <th>Location</th>
                                <th>Participants</th>
                                <th>Status</th>
                                <th>EP Points</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($events)): ?>
                                <tr>
                                    <td colspan="7" class="no-data">No events found. Create your first event!</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($events as $event): ?>
                                    <tr data-status="<?php echo htmlspecialchars($event['status']); ?>">
                                        <td><?php echo htmlspecialchars($event['event_name']); ?></td>
                                        <td>
                                            <?php
                                                $date = new DateTime($event['event_date']);
                                                echo $date->format('d M Y');
                                                
                                                if ($event['start_time'] && $event['end_time']) {
                                                    $startTime = date('g:i A', strtotime($event['start_time']));
                                                    $endTime = date('g:i A', strtotime($event['end_time']));
                                                    echo '<br>' . $startTime . ' - ' . $endTime;
                                                }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($event['event_location']); ?></td>
                                        <td><?php echo htmlspecialchars($event['event_participants']); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo strtolower($event['status']); ?>">
                                                <?php echo htmlspecialchars($event['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($event['enrichment_points_awarded']); ?></td>
                                        <td class="action-buttons">
                                            <button class="edit-event-btn" data-id="<?php echo $event['event_id']; ?>">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                                Edit
                                            </button>
                                            <button class="delete-event-btn" data-id="<?php echo $event['event_id']; ?>">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                                </svg>
                                                Delete
                                            </button>
                                            <button class="view-participants-btn" data-id="<?php echo $event['event_id']; ?>">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="9" cy="7" r="4"></circle>
                                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                                </svg>
                                                Participants
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Add/Edit Event Modal -->
                <div id="event-modal" class="modal">
                    <div class="modal-content">
                        <span class="close-btn">&times;</span>
                        <h2 id="modal-title">Add New Event</h2>
                        
                        <form id="event-form" action="<?php echo BASE_URL; ?>/controllers/events.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="operation" id="operation" value="add_event">
                            <input type="hidden" name="event_id" id="event_id" value="">
                            
                            <div class="form-group">
                                <label for="event_name">Event Name <span class="required">*</span></label>
                                <input type="text" id="event_name" name="event_name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="event_description">Description</label>
                                <textarea id="event_description" name="event_description" rows="4"></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="event_date">Date <span class="required">*</span></label>
                                    <input type="date" id="event_date" name="event_date" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="start_time">Start Time <span class="required">*</span></label>
                                    <input type="time" id="start_time" name="start_time" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="end_time">End Time <span class="required">*</span></label>
                                    <input type="time" id="end_time" name="end_time" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="event_location">Location <span class="required">*</span></label>
                                <input type="text" id="event_location" name="event_location" required>
                            </div>
                            
                            <div class="form-group location-search-box">
                                <label for="location-search">Search Location</label>
                                <input type="text" id="location-search" placeholder="Search for a location...">
                                <div id="location-map"></div>
                                <p class="form-hint">Click on the map to select a location</p>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="event_participants">Max Participants <span class="required">*</span></label>
                                    <input type="number" id="event_participants" name="event_participants" min="1" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="enrichment_points_awarded">EP Points <span class="required">*</span></label>
                                    <input type="number" id="enrichment_points_awarded" name="enrichment_points_awarded" min="0" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="status">Status <span class="required">*</span></label>
                                    <select id="status" name="status" required>
                                        <option value="Scheduled">Scheduled</option>
                                        <option value="Ongoing">Ongoing</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="organizer">Organizer</label>
                                <input type="text" id="organizer" name="organizer" value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="event_image">Event Image</label>
                                <input type="file" id="event_image" name="event_image" accept="image/*">
                                <div id="image-preview" class="image-preview"></div>
                            </div>
                            
                            <div class="form-buttons">
                                <button type="submit" class="btn btn-primary">Save Event</button>
                                <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Participants Modal -->
                <div id="participants-modal" class="modal">
                    <div class="modal-content">
                        <span class="close-btn">&times;</span>
                        <h2>Event Participants</h2>
                        
                        <div id="participants-list">
                            <table class="participants-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Registration Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="participants-data">
                                    <!-- Will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Delete Confirmation Modal -->
                <div id="confirm-modal" class="modal">
                    <div class="modal-content confirm-content">
                        <h2>Confirm Deletion</h2>
                        <p>Are you sure you want to delete this event? This action cannot be undone.</p>
                        
                        <form id="delete-form" action="<?php echo BASE_URL; ?>/controllers/events.php" method="POST">
                            <input type="hidden" name="operation" value="delete_event">
                            <input type="hidden" name="event_id" id="delete-event-id" value="">
                            
                            <div class="form-buttons">
                                <button type="submit" class="btn btn-danger">Delete</button>
                                <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                            </div>
                        </form>
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
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/js/profile-dropdown.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/search.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/burger.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/events-management.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Variables
            const addEventBtn = document.getElementById('add-event-btn');
            const eventModal = document.getElementById('event-modal');
            const participantsModal = document.getElementById('participants-modal');
            const confirmModal = document.getElementById('confirm-modal');
            const closeButtons = document.querySelectorAll('.close-btn, .close-modal');
            const eventForm = document.getElementById('event-form');
            const deleteForm = document.getElementById('delete-form');
            const eventSearch = document.getElementById('event-search');
            const statusFilter = document.getElementById('status-filter');
            
            // Event listeners for modals
            addEventBtn.addEventListener('click', function() {
                // Reset form for a new event
                eventForm.reset();
                document.getElementById('operation').value = 'add_event';
                document.getElementById('event_id').value = '';
                document.getElementById('modal-title').textContent = 'Add New Event';
                document.getElementById('image-preview').innerHTML = '';
                showModal(eventModal);
                
                // Initialize Google Maps if available
                if (typeof google !== 'undefined') {
                    initializeMap();
                }
            });
            
            // Close modals when clicking close button or cancel
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    eventModal.style.display = 'none';
                    participantsModal.style.display = 'none';
                    confirmModal.style.display = 'none';
                });
            });
            
            // Close modals when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === eventModal) {
                    eventModal.style.display = 'none';
                } else if (event.target === participantsModal) {
                    participantsModal.style.display = 'none';
                } else if (event.target === confirmModal) {
                    confirmModal.style.display = 'none';
                }
            });
            
            // Event listeners for edit buttons
            document.querySelectorAll('.edit-event-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = this.dataset.id;
                    editEvent(eventId);
                });
            });
            
            // Event listeners for delete buttons
            document.querySelectorAll('.delete-event-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = this.dataset.id;
                    document.getElementById('delete-event-id').value = eventId;
                    showModal(confirmModal);
                });
            });
            
            // Event listeners for view participants buttons
            document.querySelectorAll('.view-participants-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = this.dataset.id;
                    viewParticipants(eventId);
                });
            });
            
            // Image preview
            const imageInput = document.getElementById('event_image');
            const imagePreview = document.getElementById('image-preview');
            
            imageInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        imagePreview.innerHTML = `<img src="${e.target.result}" alt="Event Image Preview">`;
                    };
                    
                    reader.readAsDataURL(this.files[0]);
                }
            });
            
            // Search functionality
            eventSearch.addEventListener('input', filterEvents);
            statusFilter.addEventListener('change', filterEvents);
            
            // Filter events based on search input and status filter
            function filterEvents() {
                const searchTerm = eventSearch.value.toLowerCase();
                const statusValue = statusFilter.value;
                const rows = document.querySelectorAll('.events-table tbody tr');
                
                rows.forEach(row => {
                    const eventName = row.cells[0].textContent.toLowerCase();
                    const eventLocation = row.cells[2].textContent.toLowerCase();
                    const eventStatus = row.dataset.status;
                    
                    const matchesSearch = eventName.includes(searchTerm) || eventLocation.includes(searchTerm);
                    const matchesStatus = statusValue === 'all' || eventStatus === statusValue;
                    
                    row.style.display = matchesSearch && matchesStatus ? '' : 'none';
                });
            }
            
            // Show modal helper
            function showModal(modal) {
                modal.style.display = 'flex';
            }
            
            // Edit event function
            function editEvent(eventId) {
                // Fetch event details via AJAX
                fetch(`${BASE_URL}/controllers/events.php?operation=get_event&event_id=${eventId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const event = data.event;
                            
                            // Populate form fields
                            document.getElementById('operation').value = 'update_event';
                            document.getElementById('event_id').value = event.event_id;
                            document.getElementById('event_name').value = event.event_name;
                            document.getElementById('event_description').value = event.event_description;
                            document.getElementById('event_date').value = formatDateForInput(event.event_date);
                            document.getElementById('start_time').value = event.start_time;
                            document.getElementById('end_time').value = event.end_time;
                            document.getElementById('event_location').value = event.event_location;
                            document.getElementById('event_participants').value = event.event_participants;
                            document.getElementById('enrichment_points_awarded').value = event.enrichment_points_awarded;
                            document.getElementById('status').value = event.status;
                            document.getElementById('organizer').value = event.organizer || '';
                            
                            // Show image preview if available
                            if (event.events_images) {
                                document.getElementById('image-preview').innerHTML = `<img src="${event.events_images}" alt="Event Image">`;
                            } else {
                                document.getElementById('image-preview').innerHTML = '';
                            }
                            
                            // Update modal title
                            document.getElementById('modal-title').textContent = 'Edit Event';
                            
                            // Show modal
                            showModal(eventModal);
                            
                            // Initialize Google Maps if available
                            if (typeof google !== 'undefined') {
                                initializeMap(event.event_location);
                            }
                        } else {
                            alert('Failed to load event details: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching event details:', error);
                        alert('An error occurred while fetching event details.');
                    });
            }
            
            // View participants function
            function viewParticipants(eventId) {
                // Fetch participants via AJAX
                fetch(`${BASE_URL}/controllers/events.php?operation=get_participants&event_id=${eventId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const participantsTable = document.getElementById('participants-data');
                            participantsTable.innerHTML = '';
                            
                            if (data.participants.length === 0) {
                                participantsTable.innerHTML = '<tr><td colspan="6">No participants have registered for this event yet.</td></tr>';
                            } else {
                                data.participants.forEach(participant => {
                                    const row = document.createElement('tr');
                                    
                                    // Format the registration date
                                    const regDate = new Date(participant.registration_date);
                                    const formattedDate = regDate.toLocaleDateString() + ' ' + regDate.toLocaleTimeString();
                                    
                                    row.innerHTML = `
                                        <td>${participant.full_name}</td>
                                        <td>${participant.email}</td>
                                        <td>${participant.participant_phone || 'N/A'}</td>
                                        <td>${formattedDate}</td>
                                        <td>
                                            <span class="status-badge ${participant.attendance_status.toLowerCase()}">
                                                ${participant.attendance_status}
                                            </span>
                                        </td>
                                        <td>
                                            <select class="status-select" data-id="${participant.participant_id}">
                                                <option value="Registered" ${participant.attendance_status === 'Registered' ? 'selected' : ''}>Registered</option>
                                                <option value="Confirmed" ${participant.attendance_status === 'Confirmed' ? 'selected' : ''}>Confirmed</option>
                                                <option value="Attended" ${participant.attendance_status === 'Attended' ? 'selected' : ''}>Attended</option>
                                                <option value="Cancelled" ${participant.attendance_status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                                            </select>
                                        </td>
                                    `;
                                    
                                    participantsTable.appendChild(row);
                                });
                                
                                // Add event listeners for status changes
                                document.querySelectorAll('.status-select').forEach(select => {
                                    select.addEventListener('change', function() {
                                        updateParticipantStatus(this.dataset.id, this.value);
                                    });
                                });
                            }
                            
                            // Show modal
                            showModal(participantsModal);
                        } else {
                            alert('Failed to load participants: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching participants:', error);
                        alert('An error occurred while fetching participants.');
                    });
            }
            
            // Update participant status function
            function updateParticipantStatus(participantId, status) {
                const formData = new FormData();
                formData.append('operation', 'update_participant_status');
                formData.append('participant_id', participantId);
                formData.append('status', status);
                
                fetch(`${BASE_URL}/controllers/events.php`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the UI to reflect the change
                        const row = document.querySelector(`.status-select[data-id="${participantId}"]`).closest('tr');
                        const statusBadge = row.querySelector('.status-badge');
                        
                        // Update badge class and text
                        statusBadge.className = `status-badge ${status.toLowerCase()}`;
                        statusBadge.textContent = status;
                    } else {
                        alert('Failed to update participant status: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error updating participant status:', error);
                    alert('An error occurred while updating participant status.');
                });
            }
            
            // Format date for date input (YYYY-MM-DD)
            function formatDateForInput(dateString) {
                const date = new Date(dateString);
                return date.toISOString().split('T')[0];
            }
            
            // Google Maps integration
            let map, marker;
            
            function initializeMap(address) {
                // Default to a central location (e.g., Politeknik Brunei)
                const defaultLocation = { lat: 4.9431, lng: 114.9425 };
                
                const mapOptions = {
                    center: defaultLocation,
                    zoom: 15,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };
                
                map = new google.maps.Map(document.getElementById('location-map'), mapOptions);
                
                marker = new google.maps.Marker({
                    position: defaultLocation,
                    map: map,
                    draggable: true,
                    title: 'Event Location'
                });
                
                // If an address is provided, try to geocode it
                if (address) {
                    const geocoder = new google.maps.Geocoder();
                    geocoder.geocode({ address: address }, function(results, status) {
                        if (status === 'OK' && results[0]) {
                            map.setCenter(results[0].geometry.location);
                            marker.setPosition(results[0].geometry.location);
                        }
                    });
                }
                
                // Add click listener to map
                google.maps.event.addListener(map, 'click', function(event) {
                    marker.setPosition(event.latLng);
                    
                    // Reverse geocode to get address
                    const geocoder = new google.maps.Geocoder();
                    geocoder.geocode({ 'location': event.latLng }, function(results, status) {
                        if (status === 'OK' && results[0]) {
                            document.getElementById('event_location').value = results[0].formatted_address;
                        }
                    });
                });
                
                // Add listener for marker drag
                google.maps.event.addListener(marker, 'dragend', function() {
                    const geocoder = new google.maps.Geocoder();
                    geocoder.geocode({ 'location': marker.getPosition() }, function(results, status) {
                        if (status === 'OK' && results[0]) {
                            document.getElementById('event_location').value = results[0].formatted_address;
                        }
                    });
                });
                
                // Setup location search autocomplete
                const locationSearch = document.getElementById('location-search');
                if (locationSearch) {
                    const autocomplete = new google.maps.places.Autocomplete(locationSearch);
                    autocomplete.addListener('place_changed', function() {
                        const place = autocomplete.getPlace();
                        
                        if (!place.geometry || !place.geometry.location) {
                            alert('No location details available for this search');
                            return;
                        }
                        
                        // Update map and marker
                        map.setCenter(place.geometry.location);
                        map.setZoom(15);
                        marker.setPosition(place.geometry.location);
                        
                        // Update location input field
                        document.getElementById('event_location').value = place.formatted_address || place.name;
                    });
                }
            }
        });
    </script>
</body>
</html> 
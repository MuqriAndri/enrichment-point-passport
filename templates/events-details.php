<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL);
    exit();
}

$isDark = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $profilesDB->prepare("SELECT dark_mode FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $isDark = $user && $user['dark_mode'];
    $_SESSION['dark_mode'] = $isDark;
}

// Get event ID from URL
$eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($eventId <= 0) {
    $_SESSION['error'] = "Invalid event ID";
    header("Location: " . BASE_URL . "/events");
    exit();
}

// Fetch event details
$event = null;
try {
    $stmt = $eventsDB->prepare("SELECT * FROM events WHERE event_id = ?");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        $_SESSION['error'] = "Event not found";
        header("Location: " . BASE_URL . "/events");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Failed to load event: " . $e->getMessage();
    header("Location: " . BASE_URL . "/events");
    exit();
}

// Process event image
$eventImage = '';
if (!empty($event['events_images'])) {
    $eventImage = $event['events_images'];
} else {
    // Use placeholder if no image
    $eventImage = BASE_URL . "/assets/images/events/event-placeholder-1.jpg";
}

// Check if user is already registered
$isRegistered = false;
$participantStatus = '';
try {
    $stmt = $eventsDB->prepare("SELECT * FROM events_participants WHERE event_id = ? AND user_id = ?");
    $stmt->execute([$eventId, $_SESSION['user_id']]);
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($registration) {
        $isRegistered = true;
        $participantStatus = $registration['status'];
    }
} catch (PDOException $e) {
    error_log("Failed to check registration: " . $e->getMessage());
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    if ($isRegistered) {
        $_SESSION['error'] = "You are already registered for this event";
    } else {
        try {
            // Get user details
            $stmt = $profilesDB->prepare("SELECT full_name, user_email FROM users WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Insert into events_participants table
            $stmt = $eventsDB->prepare("INSERT INTO events_participants (event_id, user_id, participant_name, participant_email, participant_phone, registration_date, status) VALUES (?, ?, ?, ?, ?, NOW(), 'Pending')");
            $stmt->execute([
                $eventId,
                $_SESSION['user_id'],
                $user['full_name'],
                $user['user_email'],
                $user['participant_phone'] ?? ''
            ]);
            
            // Update participant count
            $stmt = $eventsDB->prepare("UPDATE events SET event_participants = event_participants + 1 WHERE event_id = ?");
            $stmt->execute([$eventId]);
            
            $isRegistered = true;
            $participantStatus = 'Pending';
            $_SESSION['success'] = "You have successfully registered for this event";
            
            // Reload event data to get updated participant count
            $stmt = $eventsDB->prepare("SELECT * FROM events WHERE event_id = ?");
            $stmt->execute([$eventId]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $_SESSION['error'] = "Registration failed: " . $e->getMessage();
        }
    }
    
    // Redirect to avoid form resubmission
    header("Location: " . BASE_URL . "/events/details/" . $eventId);
    exit();
}

// Format date and time for display
$eventDate = new DateTime($event['event_date']);
$formattedDate = $eventDate->format('F j, Y');

$startTime = new DateTime($event['start_time']);
$endTime = new DateTime($event['end_time']);
$formattedStartTime = $startTime->format('h:i A');
$formattedEndTime = $endTime->format('h:i A');

// Calculate remaining spots
$maxParticipants = (int)$event['event_participants'];
$registeredCount = 0;
try {
    $stmt = $eventsDB->prepare("SELECT COUNT(*) FROM events_participants WHERE event_id = ?");
    $stmt->execute([$eventId]);
    $registeredCount = (int)$stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Failed to count participants: " . $e->getMessage());
}

$remainingSpots = max(0, $maxParticipants - $registeredCount);
$isFull = ($remainingSpots <= 0);
$registrationClosed = ($event['status'] !== 'Scheduled' || $isFull);

// Get participants list (for admin/committee members)
$participants = [];
if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'committee')) {
    try {
        $stmt = $eventsDB->prepare("
            SELECT p.*, u.student_id 
            FROM events_participants p
            JOIN profiles.users u ON p.user_id = u.user_id
            WHERE p.event_id = ?
            ORDER BY p.registration_date DESC
        ");
        $stmt->execute([$eventId]);
        $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Failed to load participants: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['event_name']); ?> - Politeknik Brunei</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/events.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/burger.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/events-details.css">
    <link rel="icon" href="https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com/logo/politeknik-brunei-logo-2.png" type="image/png">
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
                    <input type="text" placeholder="Search activities..." aria-label="Search activities">
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

                <div class="back-link">
                    <a href="<?php echo BASE_URL; ?>/events" class="back-link-anchor">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 12H5"></path>
                            <path d="M12 19l-7-7 7-7"></path>
                        </svg>
                        Back to Events
                    </a>
                </div>

                <div class="event-details-container">
                    <div class="event-details-header">
                        <h1><?php echo htmlspecialchars($event['event_name']); ?></h1>
                        <span class="event-status <?php echo strtolower($event['status']); ?>">
                            <?php echo htmlspecialchars($event['status']); ?>
                        </span>
                    </div>

                    <div class="event-details-content">
                        <div class="event-images-column">
                            <div class="event-main-image">
                                <img src="<?php echo htmlspecialchars($eventImage); ?>" alt="<?php echo htmlspecialchars($event['event_name']); ?>">
                            </div>
                            
                            <div class="event-ep-badge">
                                <div class="ep-icon">EP</div>
                                <div class="ep-details">
                                    <span class="ep-value"><?php echo htmlspecialchars($event['enrichment_points_awarded']); ?></span>
                                    <span class="ep-label">Points</span>
                                </div>
                            </div>
                        </div>

                        <div class="event-info-column">
                            <div class="event-meta-info">
                                <div class="meta-item">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <span><?php echo $formattedDate; ?></span>
                                </div>
                                
                                <div class="meta-item">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    <span><?php echo $formattedStartTime; ?> - <?php echo $formattedEndTime; ?></span>
                                </div>
                                
                                <div class="meta-item">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    <span><?php echo htmlspecialchars($event['event_location']); ?></span>
                                </div>
                                
                                <div class="meta-item">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                    <span>
                                        <strong>Organizer:</strong> <?php echo htmlspecialchars($event['organizer']); ?>
                                    </span>
                                </div>
                                
                                <div class="meta-item">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    <span>
                                        <strong>Participants:</strong> <?php echo $registeredCount; ?> / <?php echo $maxParticipants; ?>
                                        (<?php echo $remainingSpots; ?> spots left)
                                    </span>
                                </div>
                            </div>

                            <div class="event-description">
                                <h2>About This Event</h2>
                                <p><?php echo nl2br(htmlspecialchars($event['event_description'])); ?></p>
                            </div>

                            <div class="event-actions">
                                <?php if ($isRegistered): ?>
                                    <div class="registration-status">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                        <div>
                                            <strong>You are registered for this event</strong>
                                            <p>Your registration status: <span class="status-badge <?php echo strtolower($participantStatus); ?>"><?php echo $participantStatus; ?></span></p>
                                        </div>
                                    </div>
                                <?php elseif ($registrationClosed): ?>
                                    <div class="registration-status closed">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="15" y1="9" x2="9" y2="15"></line>
                                            <line x1="9" y1="9" x2="15" y2="15"></line>
                                        </svg>
                                        <div>
                                            <strong>Registration Closed</strong>
                                            <p><?php echo $isFull ? 'This event is full' : 'This event is no longer accepting registrations'; ?></p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <form class="registration-form" method="POST">
                                        <input type="hidden" name="action" value="register">
                                        <button type="submit" class="btn btn-primary">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="8.5" cy="7" r="4"></circle>
                                                <line x1="20" y1="8" x2="20" y2="14"></line>
                                                <line x1="23" y1="11" x2="17" y2="11"></line>
                                            </svg>
                                            Register for this Event
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'committee')): ?>
                        <div class="participants-section">
                            <h2>Registered Participants</h2>
                            
                            <?php if (!empty($participants)): ?>
                                <div class="participants-table-container">
                                    <table class="participants-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Student ID</th>
                                                <th>Email</th>
                                                <th>Registration Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($participants as $participant): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($participant['participant_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($participant['student_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($participant['participant_email']); ?></td>
                                                    <td><?php echo (new DateTime($participant['registration_date']))->format('M j, Y g:i A'); ?></td>
                                                    <td>
                                                        <span class="status-badge <?php echo strtolower($participant['status']); ?>">
                                                            <?php echo htmlspecialchars($participant['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="participant-actions">
                                                            <select class="status-select" data-participant-id="<?php echo $participant['participant_id']; ?>">
                                                                <option value="">Change Status</option>
                                                                <option value="Pending" <?php echo $participant['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                                <option value="Approved" <?php echo $participant['status'] === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                                                <option value="Attended" <?php echo $participant['status'] === 'Attended' ? 'selected' : ''; ?>>Attended</option>
                                                                <option value="No-Show" <?php echo $participant['status'] === 'No-Show' ? 'selected' : ''; ?>>No-Show</option>
                                                                <option value="Cancelled" <?php echo $participant['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                            </select>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="no-participants">
                                    <p>No participants have registered for this event yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize components as in dashboard.js
            console.log('Events Details: DOM loaded, initializing components');
            
            // Initialize mobile menu toggle
            const burgerMenu = document.querySelector('.burger-menu');
            const mobileOverlay = document.querySelector('.mobile-menu-overlay');
            
            if (burgerMenu && mobileOverlay) {
                console.log('Events Details: Initializing mobile menu');
                
                // Toggle menu when burger is clicked
                burgerMenu.addEventListener('click', function() {
                    burgerMenu.classList.toggle('active');
                    mobileOverlay.classList.toggle('active');
                    
                    // Update aria-expanded attribute
                    const isExpanded = burgerMenu.classList.contains('active');
                    burgerMenu.setAttribute('aria-expanded', isExpanded);
                    
                    // Prevent body scrolling when menu is open
                    document.body.style.overflow = isExpanded ? 'hidden' : '';
                    
                    console.log(`Events Details: Mobile menu ${isExpanded ? 'opened' : 'closed'}`);
                });
                
                // Close menu when clicking outside
                mobileOverlay.addEventListener('click', function(e) {
                    if (e.target === mobileOverlay) {
                        burgerMenu.classList.remove('active');
                        mobileOverlay.classList.remove('active');
                        burgerMenu.setAttribute('aria-expanded', 'false');
                        document.body.style.overflow = '';
                        console.log('Events Details: Mobile menu closed by overlay click');
                    }
                });
                
                // Handle Escape key to close the menu
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && mobileOverlay.classList.contains('active')) {
                        burgerMenu.classList.remove('active');
                        mobileOverlay.classList.remove('active');
                        burgerMenu.setAttribute('aria-expanded', 'false');
                        document.body.style.overflow = '';
                        console.log('Events Details: Mobile menu closed by Escape key');
                    }
                });
            }
            
            // Initialize profile dropdown
            const profileTrigger = document.querySelector('.profile-trigger');
            const dropdownMenu = document.querySelector('.dropdown-menu');
            
            if (profileTrigger && dropdownMenu) {
                console.log('Events Details: Initializing profile dropdown');
                
                // Click event for opening/closing dropdown
                profileTrigger.addEventListener('click', (e) => {
                    console.log('Events Details: Profile trigger clicked');
                    e.stopPropagation();
                    
                    const isExpanded = dropdownMenu.classList.contains('active');
                    dropdownMenu.classList.toggle('active');
                    profileTrigger.setAttribute('aria-expanded', !isExpanded);
                });
                
                // Close when clicking outside
                document.addEventListener('click', (e) => {
                    if (!dropdownMenu.contains(e.target) && !profileTrigger.contains(e.target)) {
                        dropdownMenu.classList.remove('active');
                        profileTrigger.setAttribute('aria-expanded', 'false');
                    }
                });
                
                // Close dropdown when escape key is pressed
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && dropdownMenu.classList.contains('active')) {
                        dropdownMenu.classList.remove('active');
                        profileTrigger.setAttribute('aria-expanded', 'false');
                    }
                });
            }
            
            // Handle participant status change
            const statusSelects = document.querySelectorAll('.status-select');
            statusSelects.forEach(select => {
                select.addEventListener('change', function() {
                    if (!this.value) return;
                    
                    const participantId = this.dataset.participantId;
                    const newStatus = this.value;
                    
                    // Update status via AJAX
                    fetch('<?php echo BASE_URL; ?>/api/events', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'operation=update_participant_status&participant_id=' + participantId + '&status=' + newStatus
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update UI
                            const row = this.closest('tr');
                            const statusCell = row.querySelector('td:nth-child(5) .status-badge');
                            
                            statusCell.className = 'status-badge ' + newStatus.toLowerCase();
                            statusCell.textContent = newStatus;
                            
                            // Show success message
                            alert('Participant status updated successfully');
                        } else {
                            alert('Failed to update status: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    });
                });
            });
        });
    </script>

    <!-- Include external scripts for additional functionality -->
    <script src="<?php echo BASE_URL; ?>/assets/js/dashboard.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/profile-dropdown.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/search.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/burger.js"></script>
</body>
</html> 
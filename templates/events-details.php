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

// Process event images
$eventImages = [];
if (!empty($event['events_images'])) {
    if (is_string($event['events_images'])) {
        $eventImages = json_decode($event['events_images'], true);
    } else {
        $eventImages = $event['events_images'];
    }
}

// Use placeholder if no images
if (empty($eventImages) || !is_array($eventImages)) {
    $eventImages = [
        "event-placeholder-1.jpg",
        "event-placeholder-2.jpg",
        "event-placeholder-3.jpg"
    ];
}

// Check if user is already registered
$isRegistered = false;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $eventsDB->prepare("SELECT * FROM events_participants WHERE event_id = ? AND user_id = ?");
        $stmt->execute([$eventId, $_SESSION['user_id']]);
        $registration = $stmt->fetch();
        $isRegistered = (bool)$registration;
    } catch (PDOException $e) {
        error_log("Failed to check registration: " . $e->getMessage());
    }
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    if ($isRegistered) {
        $_SESSION['error'] = "You are already registered for this event";
    } else {
        try {
            // Get user details
            $stmt = $profilesDB->prepare("SELECT full_name, email, phone FROM users WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Insert into events_participants table
            $stmt = $eventsDB->prepare("INSERT INTO events_participants (event_id, user_id, participant_name, participant_email, participant_phone, registration_date, status) VALUES (?, ?, ?, ?, ?, NOW(), 'Pending')");
            $stmt->execute([
                $eventId,
                $_SESSION['user_id'],
                $user['full_name'],
                $user['email'],
                $user['phone']
            ]);
            
            // Update participant count
            $stmt = $eventsDB->prepare("UPDATE events SET event_participants = event_participants + 1 WHERE event_id = ?");
            $stmt->execute([$eventId]);
            
            $isRegistered = true;
            $_SESSION['success'] = "You have successfully registered for this event";
            
            // Reload event data to get updated participant count
            $stmt = $eventsDB->prepare("SELECT * FROM events WHERE event_id = ?");
            $stmt->execute([$eventId]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $_SESSION['error'] = "Registration failed: " . $e->getMessage();
        }
    }
}

// Format date for display
$eventDate = new DateTime($event['created_at']);
$formattedDate = $eventDate->format('F j, Y');
$formattedTime = $eventDate->format('h:i A');

// Default enrichment points (can be replaced with actual logic)
$enrichmentPoints = 20;

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
    <title>Event Details - <?php echo htmlspecialchars($event['event_name']); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/events.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/burger.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/events-details.css">
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
            <!-- Mobile menu content from your template -->
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
                    <!-- Back button -->
                    <div class="back-link">
                        <a href="<?php echo BASE_URL; ?>/events" class="back-link-anchor">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 12H5M12 19l-7-7 7-7" />
                            </svg>
                            <span>Back to Events</span>
                        </a>
                    </div>

                    <div class="event-details-container">
                        <!-- Event header with title and status -->
                        <div class="event-details-header">
                            <h1><?php echo htmlspecialchars($event['event_name']); ?></h1>
                            <span class="event-status <?php echo strtolower($event['status']); ?>">
                                <?php echo htmlspecialchars($event['status']); ?>
                            </span>
                        </div>

                        <div class="event-details-content">
                            <!-- Left column with images -->
                            <div class="event-images-column">
                                <div class="event-main-image">
                                    <?php if (!empty($eventImages)): ?>
                                        <img id="main-event-image" src="<?php 
                                            // Check if this is a placeholder
                                            $image = $eventImages[0];
                                            $isPlaceholder = in_array($image, ["event-placeholder-1.jpg", "event-placeholder-2.jpg", "event-placeholder-3.jpg"]);
                                            echo BASE_URL . "/assets/images/" . ($isPlaceholder ? "placeholders/" : "events/") . $image; 
                                        ?>" alt="<?php echo htmlspecialchars($event['event_name']); ?>">
                                    <?php endif; ?>
                                </div>

                                <?php if (count($eventImages) > 1): ?>
                                <div class="event-thumbnails">
                                    <?php foreach ($eventImages as $index => $image): ?>
                                        <?php 
                                            $isPlaceholder = in_array($image, ["event-placeholder-1.jpg", "event-placeholder-2.jpg", "event-placeholder-3.jpg"]);
                                            $imgSrc = BASE_URL . "/assets/images/" . ($isPlaceholder ? "placeholders/" : "events/") . $image;
                                        ?>
                                        <div class="event-thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                                             data-src="<?php echo $imgSrc; ?>">
                                            <img src="<?php echo $imgSrc; ?>" alt="Thumbnail">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Right column with details and registration -->
                            <div class="event-info-column">
                                <div class="event-meta-info">
                                    <div class="meta-item">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                        <span>Date: <?php echo $formattedDate; ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                        <span>Time: <?php echo $formattedTime; ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                        <span>Location: <?php echo htmlspecialchars($event['event_location']); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="8" r="7"></circle>
                                            <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
                                        </svg>
                                        <span>Enrichment Points: <?php echo $enrichmentPoints; ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                        </svg>
                                        <span>Participants: <?php echo $event['event_participants']; ?></span>
                                    </div>
                                </div>

                                <div class="event-description">
                                    <h2>Description</h2>
                                    <p><?php echo nl2br(htmlspecialchars($event['event_description'] ?? 'No description available.')); ?></p>
                                </div>

                                <div class="event-actions">
                                    <?php if ($isRegistered): ?>
                                        <div class="registration-status">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#4CAF50" stroke-width="2">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                            </svg>
                                            <span>You are registered for this event</span>
                                        </div>
                                    <?php else: ?>
                                        <?php if ($event['status'] === 'Scheduled' || $event['status'] === 'Ongoing'): ?>
                                            <form method="POST" class="registration-form">
                                                <input type="hidden" name="action" value="register">
                                                <button type="submit" class="btn btn-primary">Register for Event</button>
                                            </form>
                                        <?php else: ?>
                                            <div class="registration-status closed">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F44336" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                                </svg>
                                                <span>Registration is closed for this event</span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'committee')): ?>
                                        <div class="admin-actions">
                                            <a href="<?php echo BASE_URL; ?>/events/edit?event_id=<?php echo $eventId; ?>" class="btn btn-secondary">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M12 20h9"></path>
                                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                                </svg>
                                                Edit Event
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Participants section (only visible to admins/committee) -->
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
                                                    <th>Registration Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($participants as $participant): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($participant['participant_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($participant['student_id']); ?></td>
                                                        <td><?php echo (new DateTime($participant['registration_date']))->format('M j, Y g:i A'); ?></td>
                                                        <td><span class="status-badge"><?php echo htmlspecialchars($participant['status']); ?></span></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="no-participants">No participants have registered for this event yet.</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Thumbnail gallery functionality
        document.addEventListener('DOMContentLoaded', function() {
            const thumbnails = document.querySelectorAll('.event-thumbnail');
            const mainImage = document.getElementById('main-event-image');
            
            thumbnails.forEach(thumbnail => {
                thumbnail.addEventListener('click', function() {
                    // Update main image
                    mainImage.src = this.getAttribute('data-src');
                    
                    // Update active class
                    thumbnails.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });
    </script>

    <script src="<?php echo BASE_URL; ?>/assets/js/profile-dropdown.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/burger.js"></script>
</body>
</html> 
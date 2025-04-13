<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL);
    exit();
}

// Extract club information from the $pageData array
$clubDetails = $pageData['details'] ?? [];
$isMember = $pageData['isMember'] ?? false;
$upcomingEvents = $pageData['upcoming_events'] ?? [];
$activities = $pageData['activities'] ?? [];
$gallery = $pageData['gallery'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrichment Point Passport - <?php echo htmlspecialchars($clubDetails['club_name'] ?? 'Club Details'); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/cca-details.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/cca-application-form.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Top Navigation Bar -->
        <nav class="top-nav">
            <div class="nav-left">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo/politeknik-brunei-logo.png" alt="PB Logo" class="nav-logo">
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

        <div class="main-content">
            <div class="main-wrapper">
                <div class="tab-navigation">
                    <a href="<?php echo BASE_URL; ?>/dashboard" class="tab-item">Dashboard</a>
                    <a href="<?php echo BASE_URL; ?>/ep" class="tab-item">Enrichment Point</a>
                    <a href="<?php echo BASE_URL; ?>/events" class="tab-item">Events</a>
                    <a href="<?php echo BASE_URL; ?>/cca" class="tab-item active">CCAs</a>
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

                <!-- Club Detail Header Section -->
                <section class="club-detail-header <?php echo strtolower(str_replace(' ', '-', $clubDetails['category'] ?? '')); ?>">
                    <div class="back-link">
                        <a href="<?php echo BASE_URL; ?>/cca">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 12H5M12 19l-7-7 7-7" />
                            </svg>
                            Back to All Clubs
                        </a>
                    </div>
                    <div class="club-header-content">
                        <div class="club-logo-large">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <?php
                                // Display different icon based on category
                                $category = strtolower($clubDetails['category'] ?? '');
                                switch ($category) {
                                    case 'sports':
                                        echo '<circle cx="12" cy="12" r="10"/>';
                                        break;
                                    case 'arts':
                                        echo '<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>';
                                        break;
                                    case 'culture':
                                        echo '<path d="M3 21h18M3 10h18M3 18h18M3 7h18"/>';
                                        break;
                                    case 'academic':
                                        echo '<path d="M12 2l10 6.5v7L12 22 2 15.5v-7L12 2z"/>';
                                        break;
                                    case 'martial arts':
                                    case 'martial-arts':
                                        echo '<path d="M14 14l-4 4M18 10l-4 4M10 18l-4 4"/>';
                                        break;
                                    default:
                                        echo '<circle cx="12" cy="12" r="10"/>';
                                }
                                ?>
                            </svg>
                        </div>
                        <div class="club-info-main">
                            <h1><?php echo htmlspecialchars($clubDetails['club_name'] ?? 'Club Details'); ?></h1>
                            <div class="club-meta">
                                <span class="category-badge"><?php echo htmlspecialchars($clubDetails['category'] ?? ''); ?></span>
                                <span class="member-count">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                    </svg>
                                    <?php echo $clubDetails['member_count'] ?? 0; ?> members
                                </span>
                                <span class="points-info">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                    </svg>
                                    2 points/session
                                </span>
                            </div>
                        </div>
                        <div class="club-actions">
                            <form method="POST" action="<?php echo BASE_URL; ?>/cca">
                                <input type="hidden" name="action" value="cca">
                                <input type="hidden" name="club_id" value="<?php echo $clubDetails['club_id'] ?? ''; ?>">
                                <?php if ($isMember): ?>
                                    <input type="hidden" name="operation" value="leave">
                                    <button type="submit" class="leave-btn">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" />
                                        </svg>
                                        Leave Club
                                    </button>
                                <?php else: ?>
                                    <input type="hidden" name="operation" value="join">
                                    <button type="submit" class="join-btn">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 5v14M5 12h14"></path>
                                        </svg>
                                        Join Club
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </section>

                <!-- Club Detail Content Section -->
                <div class="club-detail-grid">
                    <!-- About Section -->
                    <section class="club-about">
                        <div class="section-header">
                            <h2>About</h2>
                        </div>
                        <div class="about-content">
                            <p><?php echo htmlspecialchars($clubDetails['description'] ?? 'No description available.'); ?></p>

                            <div class="club-details">
                                <div class="detail-item">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <div>
                                        <strong>Meeting Schedule</strong>
                                        <p><?php echo htmlspecialchars($clubDetails['meeting_schedule'] ?? 'Not specified'); ?></p>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    <div>
                                        <strong>Location</strong>
                                        <p>
                                            <?php 
                                            $location = htmlspecialchars($clubDetails['location'] ?? 'Not specified');
                                            $lat = $clubDetails['latitude'] ?? 4.8856000;
                                            $lng = $clubDetails['longitude'] ?? 114.9370000;
                                            
                                            if ($location != 'Not specified') {
                                                echo '<a href="' . BASE_URL . '/cca-location?club_id=' . $clubDetails['club_id'] . '&location=' . urlencode($location) . '&lat=' . $lat . '&lng=' . $lng . '" class="location-link">' . $location . '</a>';
                                            } else {
                                                echo $location;
                                            }
                                            ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    <div>
                                        <strong>Advisor</strong>
                                        <p><?php echo htmlspecialchars($clubDetails['advisor'] ?? 'Not specified'); ?></p>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    <div>
                                        <strong>President</strong>
                                        <p><?php echo htmlspecialchars($clubDetails['president'] ?? 'Not specified'); ?></p>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                    <div>
                                        <strong>Contact</strong>
                                        <p><?php echo htmlspecialchars($clubDetails['contact_email'] ?? 'Not specified'); ?></p>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                    </svg>
                                    <div>
                                        <strong>Membership Fee</strong>
                                        <p><?php echo htmlspecialchars($clubDetails['membership_fee'] ?? 'Not specified'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Upcoming Events Section -->
                    <section class="club-events">
                        <div class="section-header">
                            <h2>Upcoming Events</h2>
                        </div>
                        <div class="club-event-list">
                            <?php if (!empty($upcomingEvents)): ?>
                                <?php foreach ($upcomingEvents as $event): ?>
                                    <div class="club-event-item">
                                        <div class="event-date">
                                            <span class="day"><?php echo date('d', strtotime($event['date'])); ?></span>
                                            <span class="month"><?php echo date('M', strtotime($event['date'])); ?></span>
                                        </div>
                                        <div class="event-details">
                                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                            <div class="event-meta">
                                                <span>
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="12" r="10"></circle>
                                                        <polyline points="12 6 12 12 16 14"></polyline>
                                                    </svg>
                                                    <?php echo htmlspecialchars($event['time']); ?>
                                                </span>
                                                <span>
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                                        <circle cx="12" cy="10" r="3"></circle>
                                                    </svg>
                                                    <?php echo htmlspecialchars($event['location']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-events">No upcoming events at the moment.</p>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- Activities Section -->
                    <section class="club-activities">
                        <div class="section-header">
                            <h2>Activities</h2>
                        </div>
                        <div class="activities-content">
                            <?php if (!empty($activities)): ?>
                                <ul class="activities-list">
                                    <?php foreach ($activities as $activity): ?>
                                        <li>
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="9 11 12 14 22 4"></polyline>
                                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                                            </svg>
                                            <?php echo htmlspecialchars($activity); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="no-activities">No activities listed at the moment.</p>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- Gallery Section -->
                    <section class="club-gallery">
                        <div class="section-header">
                            <h2>Gallery</h2>
                        </div>
                        <div class="gallery-content">
                            <?php if (!empty($gallery)): ?>
                                <div class="gallery-grid">
                                    <?php foreach ($gallery as $image): ?>
                                        <div class="gallery-item">
                                            <img src="<?php echo BASE_URL; ?>/assets/images/clubs/<?php echo $image; ?>" alt="<?php echo htmlspecialchars($clubDetails['club_name'] ?? 'Club'); ?> image">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="no-images">No gallery images available.</p>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/js/profile-dropdown.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/dashboard.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/join-club.js"></script>

    <?php include 'templates/cca-application-form.php'; ?>
</body>

</html>
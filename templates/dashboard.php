<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL);
    exit();
}

$isDark = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT dark_mode FROM users WHERE user_id = ?");
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
    <title>Enrichment Point Passport - Dashboard</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/settings.css">

</head>

<body class="<?php echo $isDark ? 'dark' : ''; ?>">

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
                <div class="tab-navigation" role="navigation" aria-label="Main navigation">
                    <a href="<?php echo BASE_URL; ?>/dashboard" class="tab-item active">Dashboard</a>
                    <a href="<?php echo BASE_URL; ?>/ep" class="tab-item">Enrichment Point</a>
                    <a href="<?php echo BASE_URL; ?>/events" class="tab-item">Events</a>
                    <a href="<?php echo BASE_URL; ?>/cca" class="tab-item">CCAs</a>
                    <a href="<?php echo BASE_URL; ?>/history" class="tab-item">History</a>
                </div>

                <!-- Welcome Card Section - Make it consistent with EP page -->
                <section class="welcome-card">
                    <div class="welcome-content">
                        <h1>Welcome, <?php echo explode(' ', $_SESSION['full_name'])[0]; ?>!</h1>
                        <p>Track your enrichment journey and discover new opportunities.</p>
                        <a href="<?php echo BASE_URL; ?>/events" class="primary-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 5l7 7-7 7M5 12h14"></path>
                            </svg>
                            Explore Activities
                        </a>
                    </div>
                    <div class="points-overview">
                        <div class="circular-progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <svg viewBox="0 0 36 36" class="circular-chart">
                                <path d="M18 2.0845
                        a 15.9155 15.9155 0 0 1 0 31.831
                        a 15.9155 15.9155 0 0 1 0 -31.831"
                                    fill="none"
                                    stroke="#eee"
                                    stroke-width="2.5" />
                                <path class="progress-circle"
                                    d="M18 2.0845
                        a 15.9155 15.9155 0 0 1 0 31.831
                        a 15.9155 15.9155 0 0 1 0 -31.831"
                                    fill="none"
                                    stroke="#c0a43d"
                                    stroke-width="2.5"
                                    stroke-dasharray="0, 100" />
                            </svg>
                            <div class="percentage">
                                <span class="points"><?php echo intval($_SESSION['enrichment_point']); ?></span>
                                <span class="total">/64</span>
                            </div>
                        </div>
                        <div class="points-info">
                            <h3>Total Points</h3>
                            <p><span class="percentage-text">0</span>% of target achieved</p>
                        </div>
                    </div>
                </section>
                
                <!-- Quick Stats Section -->
                <section class="quick-stats" aria-label="Overview">
                    <!-- ... rest of the content ... -->
                </section>

                <!-- Dashboard Grid -->
                <div class="dashboard-grid">
                    <!-- <section class="activity-categories" aria-labelledby="categories-heading">
                        <div class="section-header">
                            <h3 id="categories-heading">Categories</h3>
                        </div> -->
                        <!-- <div class="category-grid">
                            <a href="/activities/academic" class="category-card academic">
                                <div class="category-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M12 2l10 6.5v7L12 22 2 15.5v-7L12 2z"></path>
                                    </svg>
                                </div>
                                <h4>Academic</h4>
                                <p>15 points</p>
                            </a>
                            <a href="/activities/leadership" class="category-card leadership">
                                <div class="category-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                </div>
                                <h4>Leadership</h4>
                                <p>10 points</p>
                            </a>
                            <a href="/activities/sports" class="category-card sports">
                                <div class="category-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                                        <line x1="9" y1="9" x2="9.01" y2="9"></line>
                                        <line x1="15" y1="9" x2="15.01" y2="9"></line>
                                    </svg>
                                </div>
                                <h4>Sports</h4>
                                <p>12 points</p>
                            </a>
                            <a href="/activities/community" class="category-card community">
                                <div class="category-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                </div>
                                <h4>Community Service</h4>
                                <p>7 points</p>
                            </a>
                        </div> -->
                    <!-- </section> -->

                    <!-- Recent Activities -->
                    <section class="recent-activities" aria-labelledby="recent-activities-heading">
                        <div class="section-header">
                            <h3 id="recent-activities-heading">Recent Activities</h3>
                            <a href="<?php echo BASE_URL; ?>/ep" class="view-all">View All</a>
                        </div>
                        <div class="activity-list">
                            <div class="activity-item">
                                <div class="activity-icon sports">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                                        <line x1="9" y1="9" x2="9.01" y2="9"></line>
                                        <line x1="15" y1="9" x2="15.01" y2="9"></line>
                                    </svg>
                                </div>
                                <div class="activity-details">
                                    <h4>Sports Carnival</h4>
                                    <p>Completed on Jan 20, 2025</p>
                                </div>
                                <span class="points-badge" aria-label="Earned 75 points">+75 pts</span>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon academic">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M12 2l10 6.5v7L12 22 2 15.5v-7L12 2z"></path>
                                    </svg>
                                </div>
                                <div class="activity-details">
                                    <h4>Academic Workshop</h4>
                                    <p>Completed on Jan 15, 2025</p>
                                </div>
                                <span class="points-badge" aria-label="Earned 50 points">+50 pts</span>
                            </div>
                        </div>
                    </section>

                    <!-- Calendar Section -->
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

                    <!-- Upcoming Events -->
                    <section class="upcoming-events" aria-labelledby="upcoming-events-heading">
                        <div class="section-header">
                            <h3 id="upcoming-events-heading">Upcoming Events</h3>
                            <a href="/events" class="view-all">View All</a>
                        </div>
                        <div class="event-list">
                            <div class="event-item">
                                <div class="event-date" aria-label="February 15">
                                    <span class="day">15</span>
                                    <span class="month">FEB</span>
                                </div>
                                <div class="event-details">
                                    <h4>Cultural Exchange Program</h4>
                                    <p>9:00 AM - Main Hall</p>
                                </div>
                                <button class="register-btn" data-event-id="1" aria-label="Register for Cultural Exchange Program">
                                    Register
                                </button>
                            </div>
                            <div class="event-item">
                                <div class="event-date" aria-label="February 22">
                                    <span class="day">22</span>
                                    <span class="month">FEB</span>
                                </div>
                                <div class="event-details">
                                    <h4>Innovation Challenge</h4>
                                    <p>2:00 PM - Innovation Lab</p>
                                </div>
                                <button class="register-btn" data-event-id="2" aria-label="Register for Innovation Challenge">
                                    Register
                                </button>
                            </div>
                            <div class="event-item">
                                <div class="event-date" aria-label="March 1">
                                    <span class="day">1</span>
                                    <span class="month">MAR</span>
                                </div>
                                <div class="event-details">
                                    <h4>Leadership Workshop</h4>
                                    <p>10:00 AM - Conference Room</p>
                                </div>
                                <button class="register-btn" data-event-id="3" aria-label="Register for Leadership Workshop">
                                    Register
                                </button>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/js/dashboard.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/profile-dropdown.js"></script>
</body>

</html>
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

// Get recent activities data for the current user
$activityDetails = [];
if (isset($_SESSION['student_id'])) {
    // Get the enrichment point repository
    require_once 'repositories/enrichment-point-repository.php';
    $epRepository = new EnrichmentPointRepository($ccaDB, $profilesDB);

    // Get user's recent activities (limit to 2)
    $activityDetails = $epRepository->getActivityDetails($_SESSION['student_id']);
    $activityDetails = array_slice($activityDetails, 0, 2);
}

// Get user's joined clubs
$joinedClubs = [];
if (isset($_SESSION['student_id'])) {
    require_once 'repositories/club-repository.php';
    $clubRepo = new clubRepository($ccaDB, $profilesDB);

    // Get user club memberships
    $userMemberships = $clubRepo->getUserMemberships($_SESSION['student_id']);

    // Get details for each club
    foreach ($userMemberships as $clubId) {
        $clubDetails = $clubRepo->getClubDetailsById($clubId);
        if ($clubDetails) {
            $joinedClubs[] = $clubDetails;
        }
    }
}

// Helper function for badges - use the same as in EP page
function getBadgeClass($type)
{
    switch ($type) {
        case 'Academic':
            return 'academic';
        case 'Sports':
            return 'leadership';
        case 'Service':
            return 'service';
        case 'Arts':
            return 'professional';
        default:
            return 'academic';
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Politeknik Brunei</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/settings.css">
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
                    <!-- Recent Activities -->
                    <section class="recent-activities" aria-labelledby="recent-activities-heading">
                        <div class="section-header">
                            <h3 id="recent-activities-heading">Recent Activities</h3>
                            <a href="<?php echo BASE_URL; ?>/ep" class="view-all">View All</a>
                        </div>
                        <div class="activity-list">
                            <?php if (empty($activityDetails)): ?>
                                <div class="no-data-message">
                                    <p>No recent activities found. Start participating to earn points!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($activityDetails as $activity): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon <?php echo getBadgeClass($activity['type']); ?>">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <?php if ($activity['type'] == 'Sports'): ?>
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                                                    <line x1="9" y1="9" x2="9.01" y2="9"></line>
                                                    <line x1="15" y1="9" x2="15.01" y2="9"></line>
                                                <?php elseif ($activity['type'] == 'Academic'): ?>
                                                    <path d="M12 2l10 6.5v7L12 22 2 15.5v-7L12 2z"></path>
                                                <?php elseif ($activity['type'] == 'Service'): ?>
                                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="9" cy="7" r="4"></circle>
                                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                                <?php else: ?>
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                                <?php endif; ?>
                                            </svg>
                                        </div>
                                        <div class="activity-details">
                                            <h4><?php echo htmlspecialchars($activity['activity']); ?></h4>
                                            <p>Completed on <?php echo htmlspecialchars($activity['completion_date']); ?></p>
                                        </div>
                                        <span class="points-badge" aria-label="Earned <?php echo $activity['points']; ?> points">+<?php echo $activity['points']; ?> pts</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- Joined Clubs Section -->
                    <section class="joined-clubs" aria-labelledby="joined-clubs-heading">
                        <div class="section-header">
                            <h3 id="joined-clubs-heading">My Clubs</h3>
                            <a href="<?php echo BASE_URL; ?>/cca" class="view-all">View All</a>
                        </div>
                        <div class="club-list">
                            <?php if (empty($joinedClubs)): ?>
                                <div class="no-data-message">
                                    <p>You haven't joined any clubs yet. <a href="<?php echo BASE_URL; ?>/cca">Explore clubs</a> to join!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($joinedClubs as $club): ?>
                                    <div class="club-item">
                                        <div class="club-icon">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="9" cy="7" r="4"></circle>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                            </svg>
                                        </div>
                                        <div class="club-details">
                                            <h4><?php echo htmlspecialchars($club['club_name']); ?></h4>
                                            <p><?php echo htmlspecialchars($club['category']); ?></p>
                                        </div>
                                        <?php
                                        $clubName = $club['club_name'];
                                        $nameParts = explode(' ', $clubName);
                                        if (end($nameParts) === 'Club') {
                                            array_pop($nameParts);
                                        }
                                        $nameBase = implode(' ', $nameParts);
                                        $clubSlug = strtolower(
                                            str_replace(
                                                ' ',
                                                '-',
                                                preg_replace('/[^\p{L}\p{N}\s-]/u', '', $nameBase)
                                            )
                                        );
                                        ?>
                                        <a href="<?php echo BASE_URL; ?>/cca/<?php echo $clubSlug; ?>" class="view-club-btn">View Club</a>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- News Section -->
                    <section class="news-section" aria-labelledby="news-heading">
                        <div class="section-header">
                            <h3 id="news-heading">News</h3>
                            <a href="https://pb.edu.bn" class="view-all" target="_blank">View All</a>
                        </div>
                        <div class="news-slider">
                            <div class="news-slider-container">
                                <a href="https://pb.edu.bn/2025/03/24/majlis-khatam-al-quran-dan-tahlil-bagi-pegawai-pegawai-kakitangan-dan-pelajar-pelajar-politeknik-brunei/" class="news-item" target="_blank">
                                    <div class="news-image">
                                        <img src="https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com/majlis-khatam-al-quran.jpg" alt="Majlis Khatam Al Quran">
                                    </div>
                                    <div class="news-content">
                                        <div class="news-date">24/03/2025</div>
                                        <h4>Majlis Khatam Al Quran dan Tahlil Bagi Pegawai-Pegawai, Kakitangan dan Pelajar-Pelajar Politeknik Brunei</h4>
                                    </div>
                                </a>
                                <a href="https://pb.edu.bn/2025/03/04/bon-voyage-to-politeknik-bruneis-diploma-in-chemical-engineering-batch-3-students/" class="news-item" target="_blank">
                                    <div class="news-image">
                                        <img src="https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com/bon-voyage.jpg" alt="Bon Voyage">
                                    </div>
                                    <div class="news-content">
                                        <div class="news-date">04/03/2025</div>
                                        <h4>Bon voyage to Politeknik Brunei's Diploma in Chemical Engineering (Batch 3) students!</h4>
                                    </div>
                                </a>
                                <a href="https://pb.edu.bn/2025/02/26/bound-for-china/" class="news-item" target="_blank">
                                    <div class="news-image">
                                        <img src="https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com/hengyi-industries.jpg" alt="Bound for China">
                                    </div>
                                    <div class="news-content">
                                        <div class="news-date">26/02/2025</div>
                                        <h4>Bound for China!</h4>
                                    </div>
                                </a>
                                <a href="https://pb.edu.bn/2025/02/22/pb-students-win-runner-up-at-iptc-2025/" class="news-item" target="_blank">
                                    <div class="news-image">
                                        <img src="https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com/runner-up.jpeg" alt="PB Students Win Runner-Up">
                                    </div>
                                    <div class="news-content">
                                        <div class="news-date">22/02/2025</div>
                                        <h4>PB Students Win Runner-Up at IPTC 2025</h4>
                                    </div>
                                </a>
                                <a href="https://pb.edu.bn/2025/02/20/acara-menaikkan-bendera-negara-brunei-darussalam-sempena-hari-kebangsaan-negara-brunei-darussalam-ke-41-tahun-2025/" class="news-item" target="_blank">
                                    <div class="news-image">
                                        <img src="https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com/acara-menaikkan-bendera.jpg" alt="Acara Menaikkan Bendera">
                                    </div>
                                    <div class="news-content">
                                        <div class="news-date">20/02/2025</div>
                                        <h4>Acara Menaikkan Bendera Negara Brunei Darussalam Sempena Hari Kebangsaan Negara Brunei Darussalam ke-41 tahun 2025</h4>
                                    </div>
                                </a>
                                <a href="https://pb.edu.bn/2025/01/16/kemasukan-sesi-akademik-2025-2026-secara-dalam-talian-bagi-kemasukan-ke-politeknik-brunei-pb/" class="news-item" target="_blank">
                                    <div class="news-image">
                                        <img src="https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com/open-day.jpg" alt="Kemasukan Sesi Akademik">
                                    </div>
                                    <div class="news-content">
                                        <div class="news-date">16/01/2025</div>
                                        <h4>Kemasukan Sesi Akademik 2025/2026 secara dalam talian bagi kemasukan ke Politeknik Brunei (PB)</h4>
                                    </div>
                                </a>
                            </div>
                            <div class="slider-controls">
                                <button class="slider-arrow prev" aria-label="Previous news">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M15 18l-6-6 6-6"></path>
                                    </svg>
                                </button>
                                <button class="slider-arrow next" aria-label="Next news">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 18l6-6-6-6"></path>
                                    </svg>
                                </button>
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
                        <div class="calendar-grid">
                            <!-- Calendar days would go here -->
                        </div>
                    </section>
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

    <script src="<?php echo BASE_URL; ?>/assets/js/dashboard.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/profile-dropdown.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/search.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/burger.js"></script>
</body>

</html>
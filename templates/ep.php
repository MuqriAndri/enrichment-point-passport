<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL);
    exit();
}

// Dark mode logic (same as dashboard.php)
$isDark = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT dark_mode FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $isDark = $user && $user['dark_mode'];
}

// Ensure we have database connections
if (!isset($ccaDB) || !isset($profilesDB)) {
    error_log("EP Template: Database connections not available");
    echo "Database connection error. Please try again later.";
    exit();
}

// Load the controller
require_once 'controllers/enrichment-point-handler.php';

// Get EP data with error handling
try {
    $epData = getEnrichmentPointData($ccaDB, $profilesDB, $_SESSION['student_id']);

    // Extract all variables for easier access in the template
    extract($epData);

    // Debug the targetExceeded variable
    error_log("After extract - Target Exceeded: " . ($targetExceeded ? 'true' : 'false') . ", Total EP: $totalEP, Target EP: $targetEP");
} catch (Exception $e) {
    error_log("EP Template: Error getting EP data: " . $e->getMessage());
    $totalEP = 0;
    $targetEP = 64;
    $completionPercentage = 0;
    $remainingEP = 64;
    $epPerSemester = [];
    $cumulativeEP = [];
    $activityDetails = [];
    $epDistribution = [];
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrichment Point Passport - Tracker</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/ep.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/burger.css">

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
                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
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
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
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
                    <a href="<?php echo BASE_URL; ?>/dashboard" class="tab-item">Dashboard</a>
                    <a href="<?php echo BASE_URL; ?>/ep" class="tab-item active">Enrichment Point</a>
                    <a href="<?php echo BASE_URL; ?>/events" class="tab-item">Events</a>
                    <a href="<?php echo BASE_URL; ?>/cca" class="tab-item">CCAs</a>
                    <a href="<?php echo BASE_URL; ?>/history" class="tab-item">History</a>
                </div>

                <!-- Quick Stats Section -->
                <?php if ($targetExceeded): ?>
                    <div class="target-achieved-message">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        Congratulations! You've reached your EP target.
                    </div>
                <?php endif; ?>
                <!--
                <section class="quick-stats" aria-label="Overview">
                    <div class="welcome-card">
                        <div class="welcome-content">
                            <h1>Welcome, <?php echo explode(' ', $_SESSION['full_name'])[0]; ?>!</h1>
                            <p>Track your enrichment points progress across your academic timeline.</p>
                            <?php if (!$targetExceeded): ?>
                                <a href="<?php echo BASE_URL; ?>/events" class="primary-btn">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 5l7 7-7 7M5 12h14"></path>
                                    </svg>
                                    Explore Activities
                                </a>
                            <?php else: ?>
                                <div class="target-achieved-message">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                    Congratulations! You've reached your EP target.
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="points-overview">
                            <div class="circular-progress" role="progressbar" aria-valuenow="<?php echo $completionPercentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                <svg viewBox="0 0 36 36" class="circular-chart">
                                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                        fill="none" stroke="#eee" stroke-width="2.5" />
                                    <path class="progress-circle <?php echo $targetExceeded ? 'exceeded' : ''; ?>"
                                        d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                        fill="none" stroke="<?php echo $targetExceeded ? '#28a745' : '#c0a43d'; ?>" stroke-width="2.5" stroke-dasharray="<?php echo $completionPercentage; ?>, 100" />
                                </svg>
                                <div class="percentage">
                                    <span class="points"><?php echo $totalEP; ?></span>
                                    <span class="total">/<?php echo $targetEP; ?></span>
                                </div>
                            </div>
                            <div class="points-info">
                                <h3>Total Points</h3>
                                <p>
                                    <?php if ($targetExceeded): ?>
                                        <span class="percentage-text">100</span>% of target achieved
                                        <span class="target-exceeded">(+<?php echo $totalEP - $targetEP; ?> points)</span>
                                    <?php else: ?>
                                        <span class="percentage-text"><?php echo $completionPercentage; ?></span>% of target achieved
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
                -->

                <!-- Student Info -->
                <div class="student-info">
                    <div class="user-avatar">
                        <?php if (isset($_SESSION['profile_picture'])): ?>
                            <img src="<?php echo $_SESSION['profile_picture']; ?>" alt="Profile Picture">
                        <?php else: ?>
                            <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div class="student-details">
                        <h3><?php echo $_SESSION['full_name']; ?></h3>
                        <p>Student ID: <?php echo $_SESSION['student_id']; ?> | Programme: <?php echo $_SESSION['programme']; ?></p>
                    </div>
                </div>

                <!-- Dashboard Grid -->
                <div class="dashboard-grid">
                    <!-- EP Progress by Semester -->
                    <div class="card">
                        <div class="card-header">
                            <h3>EP Progress by Current Semester</h3>
                        </div>
                        <table class="ep-table">
                            <thead>
                                <tr>
                                    <th>Semester</th>
                                    <th>EP Earned</th>
                                    <th>Cumulative</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($epPerSemester as $semester): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($semester['semester']); ?></td>
                                        <td><?php echo htmlspecialchars($semester['points_earned']); ?></td>
                                        <td><?php echo htmlspecialchars($cumulativeEP[$semester['semester']]); ?></td>
                                        <td>
                                            <?php if ($cumulativeEP[$semester['semester']] >= $targetEP): ?>
                                                <span class="status-badge success">Target Reached</span>
                                            <?php else: ?>
                                                <span class="status-badge success">Within Limit</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- EP Summary -->
                    <div class="card">
                        <div class="card-header">
                            <h3>EP Summary</h3>
                        </div>
                        <div class="ep-summary">
                            <div class="summary-item">
                                <span class="label">Total EP Earned</span>
                                <span class="value highlight"><?php echo $totalEP; ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="label">EP Limit</span>
                                <span class="value"><?php echo $targetEP; ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="label">Remaining EP</span>
                                <?php if ($targetExceeded): ?>
                                    <span class="value success">Target Achieved</span>
                                <?php else: ?>
                                    <span class="value"><?php echo $remainingEP; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="summary-item">
                                <span class="label">Completion Status</span>
                                <?php if ($targetExceeded): ?>
                                    <span class="value success">100%</span>
                                <?php else: ?>
                                    <span class="value"><?php echo $completionPercentage; ?>%</span>
                                <?php endif; ?>
                            </div>
                            <div class="progress-bar-container">
                                <span class="label">Progress</span>
                                <div class="progress-bar">
                                    <div class="progress-fill <?php echo $targetExceeded ? 'exceeded' : ''; ?>" style="width: <?php echo min(100, $completionPercentage); ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Details -->
                    <div class="card" style="grid-column: span 2;">
                        <div class="card-header">
                            <h3>Activity Details</h3>
                        </div>
                        <table class="ep-table">
                            <thead>
                                <tr>
                                    <th>Activity</th>
                                    <th>Type</th>
                                    <th>Semester</th>
                                    <th>Completion Date</th>
                                    <th>EP Earned</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($activityDetails)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No activities found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($activityDetails as $activity): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($activity['activity']); ?></td>
                                            <td><span class="activity-badge <?php echo getBadgeClass($activity['type']); ?>"><?php echo htmlspecialchars($activity['type']); ?></span></td>
                                            <td><?php echo htmlspecialchars($activity['semester']); ?></td>
                                            <td><?php echo htmlspecialchars($activity['completion_date']); ?></td>
                                            <td><?php echo htmlspecialchars($activity['points']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- EP Distribution by Activity Type -->
                    <div class="card">
                        <div class="card-header">
                            <h3>EP Distribution by Activity Type</h3>
                        </div>
                        <div class="chart-container">
                            <table class="ep-table">
                                <thead>
                                    <tr>
                                        <th>Activity Type</th>
                                        <th>EP Earned</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($epDistribution)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center">No distribution data available</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($epDistribution as $distribution): ?>
                                            <tr>
                                                <td><span class="activity-badge <?php echo getBadgeClass($distribution['type']); ?>"><?php echo htmlspecialchars($distribution['type']); ?></span></td>
                                                <td><?php echo htmlspecialchars($distribution['points']); ?></td>
                                                <td><?php echo htmlspecialchars(round($distribution['percentage'], 1)); ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Recommendations -->
                    <?php if (!$targetExceeded): ?>
                        <div class="card">
                            <div class="card-header">
                                <h3>Recommendations</h3>
                            </div>
                            <div class="summary-content">
                                <div class="summary-item">
                                    <span class="summary-label">Current EP Status</span>
                                    <span class="summary-value"><?php echo $totalEP; ?>/<?php echo $targetEP; ?> Points</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Needed for Completion</span>
                                    <span class="summary-value"><?php echo $remainingEP; ?> more points</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Suggested Activities</span>
                                </div>
                                <ul style="padding-left: 1.5rem; margin-top: 0.5rem;">
                                    <li>International Experience (Study Abroad) - 20 points</li>
                                    <li>Academic Conference - 8 points</li>
                                    <li>Academic Publication - 10 points</li>
                                    <li>Community Service - 5 points</li>
                                </ul>
                                <div style="margin-top: 1rem;">
                                    <a href="<?php echo BASE_URL; ?>/events" class="primary-btn" style="background: var(--primary-color); display: block; text-align: center;">
                                        View All Available Activities
                                    </a>
                                </div>
                            </div>
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

    <script src="<?php echo BASE_URL; ?>/assets/js/profile-dropdown.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/search.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/burger.js"></script>

    <!-- Mobile Optimization Script -->
    <script>
        // Handle table responsiveness
        document.addEventListener('DOMContentLoaded', function() {
            // Make tables horizontally scrollable on small screens
            const tables = document.querySelectorAll('.ep-table');

            tables.forEach(table => {
                const wrapper = document.createElement('div');
                wrapper.style.overflowX = 'auto';
                wrapper.style.width = '100%';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            });

            // Fix tab navigation scroll position
            const activeTab = document.querySelector('.tab-item.active');
            const tabNav = document.querySelector('.tab-navigation');

            if (activeTab && tabNav && window.innerWidth < 768) {
                setTimeout(() => {
                    const tabWidth = activeTab.offsetWidth;
                    const tabLeft = activeTab.offsetLeft;
                    const containerWidth = tabNav.offsetWidth;

                    // Center the active tab
                    tabNav.scrollLeft = tabLeft - (containerWidth / 2) + (tabWidth / 2);
                }, 100);
            }
        });
    </script>
    <!-- Mobile Optimization Script -->
    <script>
        // Handle table responsiveness
        document.addEventListener('DOMContentLoaded', function() {
            // Make tables horizontally scrollable on small screens
            const tables = document.querySelectorAll('.ep-table');

            tables.forEach(table => {
                const wrapper = document.createElement('div');
                wrapper.style.overflowX = 'auto';
                wrapper.style.width = '100%';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            });

            // Fix tab navigation scroll position
            const activeTab = document.querySelector('.tab-item.active');
            const tabNav = document.querySelector('.tab-navigation');

            if (activeTab && tabNav && window.innerWidth < 768) {
                setTimeout(() => {
                    const tabWidth = activeTab.offsetWidth;
                    const tabLeft = activeTab.offsetLeft;
                    const containerWidth = tabNav.offsetWidth;

                    // Center the active tab
                    tabNav.scrollLeft = tabLeft - (containerWidth / 2) + (tabWidth / 2);
                }, 100);
            }
        });
    </script>
</body>

</html>
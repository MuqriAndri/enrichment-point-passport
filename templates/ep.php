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
</head>

<body class="<?php echo $isDark ? 'dark' : ''; ?>">
    <div class="dashboard-container">
        <!-- Top Navigation Bar -->
        <nav class="top-nav">
            <div class="nav-left">
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
                <section class="quick-stats" aria-label="Overview">
                    <div class="welcome-card">
                        <div class="welcome-content">
                            <h1>Welcome, <?php echo explode(' ', $_SESSION['full_name'])[0]; ?>!</h1>
                            <p>Track your enrichment points progress across your academic timeline.</p>
                            <a href="<?php echo BASE_URL; ?>/events" class="primary-btn">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 5l7 7-7 7M5 12h14"></path>
                                </svg>
                                Explore Activities
                            </a>
                        </div>
                        <div class="points-overview">
                            <div class="circular-progress" role="progressbar" aria-valuenow="<?php echo $completionPercentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                <svg viewBox="0 0 36 36" class="circular-chart">
                                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                        fill="none" stroke="#eee" stroke-width="2.5" />
                                    <path class="progress-circle"
                                        d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                        fill="none" stroke="#c0a43d" stroke-width="2.5" stroke-dasharray="<?php echo $completionPercentage; ?>, 100" />
                                </svg>
                                <div class="percentage">
                                    <span class="points"><?php echo $totalEP; ?></span>
                                    <span class="total">/<?php echo $targetEP; ?></span>
                                </div>
                            </div>
                            <div class="points-info">
                                <h3>Total Points</h3>
                                <p><span class="percentage-text"><?php echo $completionPercentage; ?></span>% of target achieved</p>
                            </div>
                        </div>
                    </div>
                </section>

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
                            <h3>EP Progress by Semester</h3>
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
                                        <td><span class="status-badge success">Within Limit</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- EP Summary -->
                    <!-- EP Summary -->
                    <div class="card">
                        <div class="card-header">
                            <h3>EP Summary</h3>
                        </div>
                        <div class="summary-content">
                            <div class="summary-item">
                                <span class="summary-label">Total EP Earned</span>
                                <span class="summary-value highlight"><?php echo $totalEP; ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">EP Limit</span>
                                <span class="summary-value"><?php echo $targetEP; ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Remaining EP</span>
                                <span class="summary-value"><?php echo $remainingEP; ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Completion Status</span>
                                <span class="summary-value"><?php echo $completionPercentage; ?>%</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Progress</span>
                                <div style="width: 100%;">
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: <?php echo $completionPercentage; ?>%"></div>
                                    </div>
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
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/js/dashboard.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/profile-dropdown.js"></script>
</body>

</html>
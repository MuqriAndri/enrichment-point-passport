<?php
session_start();
include '../config.php'; // Adjust if needed for BASE_URL
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Settings</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/settings.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
</head>

<body>
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
                        <div class="profile-trigger" tabindex="0" role="button">
                            <div class="user-avatar">
                                <?php if (isset($_SESSION['profile_picture'])): ?>
                                    <img src="<?php echo $_SESSION['profile_picture']; ?>" alt="Profile Picture">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <span class="user-name"><?php echo explode(' ', $_SESSION['full_name'])[0]; ?></span>
                        </div>
                        <div class="dropdown-menu">
                            <a href="<?php echo BASE_URL; ?>/profile" class="dropdown-item">My Profile</a>
                            <a href="<?php echo BASE_URL; ?>/settings" class="dropdown-item">Settings</a>
                            <div class="dropdown-divider"></div>
                            <a href="<?php echo BASE_URL; ?>/logout" class="dropdown-item">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Tabs -->
        <div class="main-content">
            <div class="main-wrapper">
                <div class="tab-navigation">
                    <a href="<?php echo BASE_URL; ?>/dashboard" class="tab-item">Dashboard</a>
                    <a href="<?php echo BASE_URL; ?>/ep" class="tab-item">Enrichment Point</a>
                    <a href="<?php echo BASE_URL; ?>/events" class="tab-item">Events</a>
                    <a href="<?php echo BASE_URL; ?>/cca" class="tab-item">CCAs</a>
                    <a href="<?php echo BASE_URL; ?>/history" class="tab-item">History</a>
                </div>

                <!-- Settings Section -->
                <div class="settings-page">
                    <div class="settings-header">
                        <h1>Settings</h1>
                    </div>



                    <div class="container">
                        <!-- USER ACCOUNT -->
                        <div class="box">
                            <h3><i class="icon">👤</i> User Account</h3>
                            <ul>
                                <li><a href="<?php echo BASE_URL; ?>/templates/change-password.php">Change Password</a></li>
                                <li><a href="<?php echo BASE_URL; ?>/templates/notification-pref.php">Notification Preference</a></li>
                                <li><a href="<?php echo BASE_URL; ?>/templates/display-pref.php">Display Preference</a></li>
                            </ul>
                        </div>

                        <!-- GUIDE -->
                        <div class="box">
                            <h3><i class="icon">📘</i> Guide</h3>
                            <ul>
                                <li><a href="<?php echo BASE_URL; ?>/templates/help.php">Help</a></li>
                                <li><a href="<?php echo BASE_URL; ?>/templates/faqs.php">FAQs</a></li>
                            </ul>
                        </div>

                        <!-- ABOUT US -->
                        <div class="box">
                            <h3><i class="icon">📞</i> About Us</h3>
                            <ul>
                                <li><a href="<?php echo BASE_URL; ?>/templates/contact.php">Contact Us</a></li>
                            </ul>
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
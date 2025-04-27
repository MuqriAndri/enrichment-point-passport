<?php
session_start();
include '../config.php'; // Adjust if needed for BASE_URL

// Database connection
require_once 'config/database.php';

$message = "";

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $user_id = $_SESSION['user_id'];
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        $message = "New passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if ($user && password_verify($current, $user['password'])) {
            $hashed_password = password_hash($new, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $update->execute([$hashed_password, $user_id]);
            $message = "Password changed successfully.";
        } else {
            $message = "Current password is incorrect.";
        }
    }
}

// Save notification preferences
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_notification'])) {
    $user_id = $_SESSION['user_id'];
    $email_pref = isset($_POST['email_notification']) ? 1 : 0;
    $sms_pref = isset($_POST['sms_notification']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE users SET email_notification = ?, sms_notification = ? WHERE user_id = ?");
    $stmt->execute([$email_pref, $sms_pref, $user_id]);
    $message = "Notification preferences updated successfully.";
}

// Save display preferences
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_display'])) {
    $user_id = $_SESSION['user_id'];
    $dark_mode = isset($_POST['dark_mode']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE users SET dark_mode = ? WHERE user_id = ?");
    $stmt->execute([$dark_mode, $user_id]);
    $_SESSION['dark_mode'] = $dark_mode ? true : false;
    $message = "Display preferences saved.";
}

// Load user preferences
$stmt = $pdo->prepare("SELECT dark_mode, email_notification, sms_notification FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$preferences = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Politeknik Brunei</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/settings.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/burger.css">
    <link rel="icon" href="https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com/logo/politeknik-brunei-logo-2.png" type="image/png">
</head>

<body class="<?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark' : ''; ?>">
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

                    <?php if (!empty($message)) : ?>
                        <div class="settings-message"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>

                    <div class="container">
                        <!-- USER ACCOUNT -->
                        <div class="box">
                            <h3>
                                <svg class="icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                User Account
                            </h3>
                            <ul>
                                <li>
                                    <a href="#" class="modal-trigger" data-modal="password-modal">
                                        Change Password
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="modal-trigger" data-modal="notification-modal">
                                        Notification Preference
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="modal-trigger" data-modal="display-modal">
                                        Display Preference
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- GUIDE -->
                        <div class="box">
                            <h3>
                                <svg class="icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                </svg>
                                Guide
                            </h3>
                            <ul>
                                <li>
                                    <a href="#" class="modal-trigger" data-modal="help-modal">
                                        Help & Documentation
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- ABOUT US -->
                        <div class="box">
                            <h3>
                                <svg class="icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                                General
                            </h3>
                            <ul>
                                <li>
                                    <a href="<?php echo BASE_URL; ?>/about">
                                        About Us
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo BASE_URL; ?>/contact">
                                        Contact Us
                                    </a>
                                </li>
                            </ul>
                        </div>
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

    <!-- Modal Overlays -->
    <div class="modal-overlay"></div>

    <!-- Password Change Modal -->
    <div id="password-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Change Password</h2>
                <button class="modal-close" aria-label="Close">&times;</button>
            </div>
            <form method="POST" action="" class="modal-form password-form">
                <div class="form-group">
                    <label for="current_password">Current Password:</label>
                    <input type="password" name="current_password" id="current_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" name="new_password" id="new_password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel modal-close">Cancel</button>
                    <button type="submit" name="change_password" class="btn-submit">Change Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notification Preferences Modal -->
    <div id="notification-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Notification Preferences</h2>
                <button class="modal-close" aria-label="Close">&times;</button>
            </div>
            <form method="POST" class="modal-form password-form">
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="email_notification" value="1" <?php echo ($preferences && $preferences['email_notification']) ? 'checked' : ''; ?>>
                        Enable Email Notifications
                    </label>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="sms_notification" value="1" <?php echo ($preferences && $preferences['sms_notification']) ? 'checked' : ''; ?>>
                        Enable SMS Notifications
                    </label>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel modal-close">Cancel</button>
                    <button type="submit" name="save_notification" class="btn-submit">Save Preferences</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Display Preferences Modal -->
    <div id="display-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Display Preferences</h2>
                <button class="modal-close" aria-label="Close">&times;</button>
            </div>
            <form method="POST" class="modal-form password-form">
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="dark_mode" value="1" <?php echo ($preferences && $preferences['dark_mode']) ? 'checked' : ''; ?>>
                        Enable Dark Mode
                    </label>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel modal-close">Cancel</button>
                    <button type="submit" name="save_display" class="btn-submit">Save Preferences</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Help Modal -->
    <div id="help-modal" class="modal">
        <div class="modal-content help-modal-content">
            <div class="modal-header">
                <h2>Help & Documentation</h2>
                <button class="modal-close" aria-label="Close">&times;</button>
            </div>
            <div class="modal-form help-content">
                <div class="help-section">
                    <h3>Getting Started</h3>
                    <p>Welcome to the Enrichment Point Passport system, your central hub for tracking all enrichment activities and points. This guide will help you navigate the platform and make the most of its features.</p>
                </div>

                <div class="help-section">
                    <h3>Frequently Asked Questions</h3>
                    
                    <div class="faq-item">
                        <h4>How do I update my password?</h4>
                        <p>You can change your password by going to Settings > User Account > Change Password. You'll need to enter your current password for verification before setting a new one.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h4>Why am I not receiving email notifications?</h4>
                        <p>This could be due to several reasons:</p>
                        <ul class="faq-list">
                            <li>Your notification preferences may be disabled. Check Settings > User Account > Notification Preference</li>
                            <li>Your email address may be incorrect. Verify this in your profile settings</li>
                            <li>Our emails might be going to your spam folder. Add our domain to your safe senders list</li>
                        </ul>
                    </div>
                    
                    <div class="faq-item">
                        <h4>How can I update my account details?</h4>
                        <p>To update your account information, navigate to the Profile page by clicking on your avatar in the top-right corner and selecting "My Profile". There you can edit your personal details and contact information.</p>
                    </div>
                </div>

                <div class="help-section">
                    <h3>Need More Assistance?</h3>
                    <p>If you're still experiencing issues or have questions not covered here, please don't hesitate to reach out to our support team.</p>
                    <div class="help-buttons">
                        <a href="<?php echo BASE_URL; ?>/contact" class="help-contact-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                            </svg>
                            Contact Us
                        </a>
                        <a href="https://drive.google.com/file/d/1o-sdJM6ipubxmw0fxnLiOqaTjR5nZkvO/view?usp=drive_link" target="_blank" class="help-manual-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            User Manual
                        </a>
                    </div>
                    <div class="manual-note">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span>The user manual will open in a new tab and can be downloaded from Google Drive</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/js/dashboard.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/profile-dropdown.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/search.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/burger.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal functionality
            const modalTriggers = document.querySelectorAll('.modal-trigger');
            const modalOverlay = document.querySelector('.modal-overlay');
            const modalCloseButtons = document.querySelectorAll('.modal-close');
            const modals = document.querySelectorAll('.modal');

            // Open modal
            modalTriggers.forEach(trigger => {
                trigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    const modalId = this.dataset.modal;
                    const modal = document.getElementById(modalId);

                    modalOverlay.classList.add('active');
                    modal.classList.add('active');
                    document.body.classList.add('modal-open');
                });
            });

            // Close modal function
            function closeAllModals() {
                modalOverlay.classList.remove('active');
                modals.forEach(modal => {
                    modal.classList.remove('active');
                });
                document.body.classList.remove('modal-open');
            }

            // Close with buttons
            modalCloseButtons.forEach(button => {
                button.addEventListener('click', closeAllModals);
            });

            // Close with overlay
            modalOverlay.addEventListener('click', closeAllModals);

            // Close with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeAllModals();
                }
            });

            // Prevent closing when clicking inside modal content
            document.querySelectorAll('.modal-content').forEach(content => {
                content.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });
        });
    </script>
</body>

</html>
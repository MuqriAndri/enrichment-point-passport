<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrichment Point Passport - Profile</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/profile.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Top Navigation Bar -->
        <nav class="top-nav">
            <div class="nav-left">
                <img src="<?php echo BASE_URL; ?>/assets/images/politeknik-brunei-logo.png" alt="PB Logo" class="nav-logo">
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
                            <a href="/profile" class="dropdown-item active" role="menuitem">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                My Profile
                            </a>
                            <a href="/settings" class="dropdown-item" role="menuitem">
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
            <!-- Tab Navigation -->
            <div class="tab-navigation" role="navigation" aria-label="Main navigation">
                <a href="/dashboard" class="tab-item">Dashboard</a>
                <a href="/activities" class="tab-item">Enrichment Point</a>
                <a href="/events" class="tab-item">Events</a>
                <a href="/achievements" class="tab-item">CCAs</a>
                <a href="/reports" class="tab-item">History</a>
            </div>

            <div class="main-wrapper">
                <!-- Profile Header Section -->
                <section class="profile-header">
                    <div class="profile-cover">
                        <div class="profile-avatar-wrapper">
                            <div class="profile-avatar">
                                <div class="avatar-image-container">
                                    <?php if (isset($_SESSION['profile_picture'])): ?>
                                        <img src="<?php echo $_SESSION['profile_picture']; ?>" alt="Profile Picture">
                                    <?php else: ?>
                                        <span class="avatar-placeholder">
                                            <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <button class="avatar-upload-btn" aria-label="Upload profile picture">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                        <circle cx="12" cy="13" r="4"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="profile-info">
                        <div class="profile-info-main">
                            <h1><?php echo $_SESSION['full_name']; ?></h1>
                            <p class="student-id"><?php echo $_SESSION['student_id']; ?></p>
                        </div>
                        <button class="edit-profile-btn" id="editProfileBtn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            Edit Profile
                        </button>
                    </div>
                </section>

                <!-- Profile Content Grid -->
                <div class="profile-grid">
                    <section class="profile-section" aria-labelledby="profile-info-heading">
                        <div class="section-header">
                            <h2 id="profile-info-heading">Profile Information</h2>
                        </div>
                        <div class="section-content">
                            <form id="profileInfoForm" class="profile-form">
                                <!-- Personal Information -->
                                <div class="form-section">
                                    <h3 class="subsection-title">Personal Information</h3>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="email">Email Address</label>
                                            <input type="email" id="email" name="user_email"
                                                value="<?php echo $_SESSION['user_email']; ?>"
                                                disabled>
                                        </div>
                                        <div class="form-group">
                                            <label for="studentId">Student ID</label>
                                            <input type="text" id="studentId" name="student_id"
                                                value="<?php echo $_SESSION['student_id']; ?>"
                                                disabled>
                                        </div>
                                        <div class="form-group">
                                            <label for="fullName">Full Name</label>
                                            <input type="text" id="fullName" name="full_name"
                                                value="<?php echo $_SESSION['full_name']; ?>"
                                                disabled>
                                        </div>
                                        <div class="form-group">
                                            <label for="role">Role</label>
                                            <input type="text" id="role" name="role"
                                                value="<?php echo ucfirst($_SESSION['role']); ?>"
                                                disabled>
                                        </div>
                                    </div>
                                </div>

                                <!-- Academic Information (Only shown for students) -->
                                <?php if ($_SESSION['role'] === 'student'): ?>
                                    <div class="form-section">
                                        <h3 class="subsection-title">Academic Information</h3>
                                        <div class="form-grid">
                                            <div class="form-group">
                                                <label for="studentId">Group Code</label>
                                                <input type="text" id="groupCode" name="group_code"
                                                    value="<?php echo $_SESSION['group_code']; ?>"
                                                    disabled>
                                            </div>
                                            <div class="form-group">
                                                <label for="school">School</label>
                                                <input type="text" id="school" name="school"
                                                    value="<?php echo $_SESSION['school']; ?>"
                                                    disabled>
                                            </div>
                                            <div class="form-group">
                                                <label for="programme">Programme</label>
                                                <input type="text" id="programme" name="programme"
                                                    value="<?php echo $_SESSION['programme']; ?>"
                                                    disabled>
                                            </div>
                                            <div class="form-group">
                                                <label for="intake">Intake</label>
                                                <input type="text" id="intake" name="intake"
                                                    value="<?php echo $_SESSION['intake']; ?>"
                                                    disabled>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="form-section">
                                        <h3 class="subsection-title">Department Information</h3>
                                        <div class="form-grid">
                                            <div class="form-group">
                                                <label for="school">School</label>
                                                <input type="text" id="school" name="school"
                                                    value="<?php echo $_SESSION['school']; ?>"
                                                    disabled>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </section>

                    <!-- Activity Summary Section -->
                    <section class="profile-section" aria-labelledby="activity-summary-heading">
                        <div class="section-header">
                            <h2 id="activity-summary-heading">Activity Summary</h2>
                        </div>
                        <div class="section-content">
                            <div class="summary-stats">
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                                        </svg>
                                    </div>
                                    <div class="stat-info">
                                        <span class="stat-label">Total Points</span>
                                        <span class="stat-value">1,250</span>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                    </div>
                                    <div class="stat-info">
                                        <span class="stat-label">Activities Completed</span>
                                        <span class="stat-value">24</span>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                    </div>
                                    <div class="stat-info">
                                        <span class="stat-label">Leadership Roles</span>
                                        <span class="stat-value">3</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Activities -->
                            <div class="recent-activities">
                                <h3>Recent Activities</h3>
                                <div class="activity-list">
                                    <div class="activity-item">
                                        <div class="activity-icon sports">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                                        <span class="points-badge">+75 pts</span>
                                    </div>
                                    <div class="activity-item">
                                        <div class="activity-icon academic">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 2l10 6.5v7L12 22 2 15.5v-7L12 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="activity-details">
                                            <h4>Academic Workshop</h4>
                                            <p>Completed on Jan 15, 2025</p>
                                        </div>
                                        <span class="points-badge">+50 pts</span>
                                    </div>
                                    <div class="activity-item">
                                        <div class="activity-icon leadership">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="9" cy="7" r="4"></circle>
                                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                            </svg>
                                        </div>
                                        <div class="activity-details">
                                            <h4>Leadership Seminar</h4>
                                            <p>Completed on Jan 10, 2025</p>
                                        </div>
                                        <span class="points-badge">+100 pts</span>
                                    </div>
                                </div>
                                <a href="/activities" class="view-all-link">View All Activities</a>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/js/dashboard.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/profile.js"></script>
</body>

</html>
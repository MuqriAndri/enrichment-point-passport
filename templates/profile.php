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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/profile.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar (Same as dashboard) -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="<?php echo BASE_URL; ?>/assets/images/politeknik-brunei-logo.png" alt="PB Logo" class="sidebar-logo">
                <h2>Enrichment Point Passport</h2>
            </div>

            <nav class="sidebar-nav">
                <a href="#overview" class="active">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    Overview
                </a>
                <a href="#activities">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 20V10"></path>
                        <path d="M18 20V4"></path>
                        <path d="M6 20v-4"></path>
                    </svg>
                    Activities
                </a>
                <a href="#achievements">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 15l-2-2-2 2V4h4v11z"></path>
                        <path d="M18 21H6a2 2 0 01-2-2V5"></path>
                    </svg>
                    Achievements
                </a>
                <a href="#certificates">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <path d="M12 8v8"></path>
                        <path d="M8 12h8"></path>
                    </svg>
                    Certificates
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <h1>Profile</h1>
                <div class="header-actions">
                    <button class="edit-profile-btn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                        </svg>
                        Edit Profile
                    </button>
                </div>
            </header>

            <div class="profile-container">
                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="profile-cover">
                        <img src="/api/placeholder/1200/300" alt="Profile Cover" class="cover-image">
                    </div>
                    <div class="profile-info">
                        <div class="profile-avatar">
                            <img src="/api/placeholder/150/150" alt="Profile Picture">
                        </div>
                        <div class="profile-details">
                            <h2><?php echo htmlspecialchars($_SESSION['full_name']); ?></h2>
                            <p class="student-id"><?php echo htmlspecialchars($_SESSION['user_ic']); ?></p>
                            <div class="profile-stats">
                                <div class="stat-item">
                                    <span class="stat-value">44</span>
                                    <span class="stat-label">Total Points</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value">12</span>
                                    <span class="stat-label">Activities</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value">5</span>
                                    <span class="stat-label">Certificates</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Content -->
                <div class="profile-content">
                    <!-- Personal Information -->
                    <div class="profile-section">
                        <h3>Personal Information</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Full Name</label>
                                <p><?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
                            </div>
                            <div class="info-item">
                                <label>Student ID</label>
                                <p><?php echo htmlspecialchars($_SESSION['user_ic']); ?></p>
                            </div>
                            <div class="info-item">
                                <label>School</label>
                                <p>School of Information and Communication Technology</p>
                            </div>
                            <div class="info-item">
                                <label>Programme</label>
                                <p>Level 5 Diploma in Cloud and Networking</p>
                            </div>
                            <div class="info-item">
                                <label>Email</label>
                                <p><?php echo htmlspecialchars($_SESSION['user_email'] ?? 'Not provided'); ?></p>
                            </div>
                            <div class="info-item">
                                <label>Phone</label>
                                <p>+673 723 9910</p>
                            </div>
                        </div>
                    </div>

                    <!-- Achievements -->
                    <div class="profile-section">
                        <h3>Recent Achievements</h3>
                        <div class="achievements-grid">
                            <div class="achievement-card">
                                <div class="achievement-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 15l-2-2-2 2V4h4v11z"></path>
                                    </svg>
                                </div>
                                <h4>Leadership Excellence</h4>
                                <p>Completed Leadership Workshop Series</p>
                                <span class="achievement-date">January 2025</span>
                            </div>
                            <div class="achievement-card">
                                <div class="achievement-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                                    </svg>
                                </div>
                                <h4>Sports Champion</h4>
                                <p>Winner of Inter-School Sports Competition</p>
                                <span class="achievement-date">December 2024</span>
                            </div>
                            <div class="achievement-card">
                                <div class="achievement-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2l10 6.5v7L12 22 2 15.5v-7L12 2z"></path>
                                    </svg>
                                </div>
                                <h4>Academic Excellence</h4>
                                <p>Dean's List Award</p>
                                <span class="achievement-date">November 2024</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
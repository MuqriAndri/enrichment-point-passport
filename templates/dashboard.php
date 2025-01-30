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
    <title>Enrichment Point Passport - Dashboard</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
    <!-- Include Chart.js for statistics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
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

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <h4><?php echo htmlspecialchars($_SESSION['full_name']); ?></h4>
                        <p><?php echo htmlspecialchars($_SESSION['user_ic']); ?></p>
                    </div>
                </div>
                <a href="<?php echo BASE_URL; ?>/logout" class="logout-btn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <h1>Welcome back, <?php echo explode(' ', $_SESSION['full_name'])[0]; ?>!</h1>
                <div class="header-actions">
                    <button class="action-btn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                    </button>
                </div>
            </header>

            <div class="dashboard-grid">
                <!-- Points Overview Card -->
                <div class="dashboard-card points-card">
                    <h3>Total Points</h3>
                    <div class="points-display">
                        <span class="points">44</span>
                        <span class="points-label">/ 64 points</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress" style="width: 75%"></div>
                    </div>
                </div>

                <!-- Activities Card -->
                <div class="dashboard-card activities-card">
                    <h3>Recent Activities</h3>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon academic">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2l10 6.5v7L12 22 2 15.5v-7L12 2z"></path>
                                </svg>
                            </div>
                            <div class="activity-details">
                                <h4>Leadership Workshop</h4>
                                <p>Completed on Jan 25, 2025</p>
                            </div>
                            <span class="points-badge">+50 pts</span>
                        </div>
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
                    </div>
                </div>

                <!-- Statistics Card -->
                <div class="dashboard-card stats-card">
                    <h3>Points Distribution</h3>
                    <canvas id="pointsChart"></canvas>
                </div>

                <!-- Upcoming Events Card -->
                <div class="dashboard-card events-card">
                    <h3>Upcoming Events</h3>
                    <div class="event-list">
                        <div class="event-item">
                            <div class="event-date">
                                <span class="day">15</span>
                                <span class="month">FEB</span>
                            </div>
                            <div class="event-details">
                                <h4>Cultural Exchange Program</h4>
                                <p>9:00 AM - Main Hall</p>
                            </div>
                            <button class="register-btn">Register</button>
                        </div>
                        <div class="event-item">
                            <div class="event-date">
                                <span class="day">22</span>
                                <span class="month">FEB</span>
                            </div>
                            <div class="event-details">
                                <h4>Innovation Challenge</h4>
                                <p>2:00 PM - Innovation Lab</p>
                            </div>
                            <button class="register-btn">Register</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Initialize Points Distribution Chart
        const ctx = document.getElementById('pointsChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Academic', 'Leadership', 'Sports', 'Community Service', 'Arts & Culture'],
                datasets: [{
                    data: [300, 150, 200, 50, 50],
                    backgroundColor: [
                        '#4C51BF',
                        '#48BB78',
                        '#ED8936',
                        '#667EEA',
                        '#F56565'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>
<?php
session_start();

// Access control check - only admin or committee members can access this page
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'committee')) {
    header("Location: " . BASE_URL);
    exit();
}

// Force-clear all modal variables on page load if they're in the URL
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // If visiting with GET request, clear modal session data 
    // only if we don't have specific instructions to show a modal
    $clearSessions = true;

    // If edit_event session exists, keep it to show edit modal
    if (isset($_SESSION['edit_event']) && $_SESSION['edit_event']) {
        $clearSessions = false;
    }

    // If participants session exists, keep it to show participants modal
    if (
        isset($_SESSION['participants']) && isset($_SESSION['view_event']) &&
        $_SESSION['participants'] && $_SESSION['view_event']
    ) {
        $clearSessions = false;
    }

    // Clear sessions if no specific modal should be shown
    if ($clearSessions) {
        if (isset($_SESSION['edit_event'])) unset($_SESSION['edit_event']);
        if (isset($_SESSION['participants'])) unset($_SESSION['participants']);
        if (isset($_SESSION['view_event'])) unset($_SESSION['view_event']);
    }
}

// Dark mode check
$isDark = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $profilesDB->prepare("SELECT dark_mode FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $isDark = $user && $user['dark_mode'];
}

// Initialize event repository
require_once 'repositories/event-repository.php';
$eventRepo = new EventRepository($eventsDB, $profilesDB);

// Get all events
$events = $eventRepo->getAllEvents();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrichment Point Passport - Events Management</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/events.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/events-management.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/burger.css">
    <style>
        .temp-alert {
            transition: opacity 0.5s ease-in-out;
            position: relative;
            z-index: 100;
        }
        
        /* Loading indicator styles */
        .loading {
            position: relative;
            opacity: 0.7;
        }
        
        .loading::after {
            content: '';
            position: absolute;
            right: -20px;
            top: 50%;
            width: 16px;
            height: 16px;
            margin-top: -8px;
            border-radius: 50%;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-top-color: #3498db;
            animation: spin 1s infinite linear;
        }
        
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
 
            100% {
                transform: rotate(360deg);
            }
        }
        
        /* Modal loading state */
        #participants-list .loading {
            padding: 20px;
            text-align: center;
            font-style: italic;
            color: #666;
        }
        
        /* Error message styling */
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        /* Improved Participants Modal Styles */
        #participants-modal .modal-content {
            width: 90%;
            max-width: 1200px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            padding: 28px;
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        #participants-modal h2 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 16px;
            margin-bottom: 24px;
            font-size: 1.8rem;
            text-align: center;
        }
        
        /* Improved Table Styles */
        .participants-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 24px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.08);
        }
        
        .participants-table thead {
            background-color: #3498db;
            color: white;
        }
        
        .participants-table th {
            text-align: left;
            padding: 15px 18px;
            font-weight: 600;
            letter-spacing: 0.5px;
            font-size: 14px;
            text-transform: uppercase;
        }
        
        /* Set specific widths for columns */
        .participants-table th:nth-child(1) { width: 14%; } /* Name */
        .participants-table th:nth-child(2) { width: 10%; } /* Student ID */
        .participants-table th:nth-child(3) { width: 15%; } /* Email */
        .participants-table th:nth-child(4) { width: 10%; } /* Phone */
        .participants-table th:nth-child(5) { width: 14%; } /* Registration Date */
        .participants-table th:nth-child(6) { width: 12%; } /* Attendance Status */
        .participants-table th:nth-child(7) { width: 12%; } /* Registration Status */
        .participants-table th:nth-child(8) { width: 13%; } /* Actions */
        
        .participants-table td {
            padding: 14px 18px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: middle;
            transition: all 0.2s ease;
        }
        
        .participants-table tbody tr {
            transition: background-color 0.2s ease;
        }
        
        .participants-table tbody tr:hover {
            background-color: #f0f7ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .participants-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        /* Status badge improvements */
        .status-badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            min-width: 100px;
            text-align: center;
            transition: all 0.2s;
        }
        
        .status-badge.registered {
            background-color: #e5f4fd;
            color: #3498db;
            border: 1px solid rgba(52, 152, 219, 0.2);
        }
        
        .status-badge.confirmed {
            background-color: #e3f9e5;
            color: #27ae60;
            border: 1px solid rgba(39, 174, 96, 0.2);
        }
        
        .status-badge.attended {
            background-color: #dff3e3;
            color: #219653;
            border: 1px solid rgba(33, 150, 83, 0.2);
        }
        
        .status-badge.cancelled {
            background-color: #fdeeee;
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.2);
        }
        
        .status-badge.pending {
            background-color: #fef7e1;
            color: #f39c12;
            border: 1px solid rgba(243, 156, 18, 0.2);
        }
        
        .status-badge.approved {
            background-color: #e3f9e5;
            color: #27ae60;
            border: 1px solid rgba(39, 174, 96, 0.2);
        }
        
        /* Status select improvements */
        .status-select {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background-color: white;
            width: 140px; /* Fixed width instead of 100% */
            font-size: 14px;
            transition: all 0.3s;
            cursor: pointer;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%233498db' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
            padding-right: 35px;
        }
        
        .status-select:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.25);
        }
        
        .status-select:hover {
            border-color: #3498db;
        }
        
        /* Modal footer improvements */
        .modal-footer {
            border-top: 1px solid #eee;
            padding-top: 20px;
            margin-top: 10px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        
        .modal-footer button {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .modal-footer button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        #refresh-participants {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        #refresh-participants::before {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2'%3E%3Cpolyline points='1 4 1 10 7 10'/%3E%3Cpolyline points='23 20 23 14 17 14'/%3E%3Cpath d='M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
        }
        
        /* For empty state */
        .participants-table tbody tr td.no-data {
            text-align: center;
            padding: 50px;
            color: #7f8c8d;
            font-style: italic;
            background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='48' height='48' viewBox='0 0 24 24' fill='none' stroke='%23ecf0f1' stroke-width='1'%3E%3Cpath d='M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2'/%3E%3Ccircle cx='9' cy='7' r='4'/%3E%3Cpath d='M23 21v-2a4 4 0 0 0-3-3.87'/%3E%3Cpath d='M16 3.13a4 4 0 0 1 0 7.75'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center 20px;
            background-size: 48px;
            padding-top: 80px;
        }
        
        /* Dark mode adjustments */
        .dark #participants-modal .modal-content {
            background-color: #1e2b38;
            color: #ecf0f1;
        }
        
        .dark #participants-modal h2 {
            color: #ecf0f1;
            border-bottom-color: #3498db;
        }
        
        .dark .participants-table thead {
            background-color: #2c3e50;
        }
        
        .dark .participants-table td {
            border-bottom-color: #2c3e50;
        }
        
        .dark .participants-table tbody tr:hover {
            background-color: #2c3e50;
        }
        
        .dark .status-select {
            background-color: #1e2b38;
            border-color: #34495e;
            color: #ecf0f1;
        }
        
        .dark .modal-footer {
            border-top-color: #2c3e50;
        }
    </style>
    <!-- Include Google Maps API using callback pattern -->
    <script>
        // Initialize Google Maps API with callback
        function initGoogleMapsAPI() {
            const script = document.createElement('script');
            script.src = "https://maps.googleapis.com/maps/api/js?key=AIzaSyBXXh-Lwbrw-UKAC9YsrBq09vyKNmG0Lzo&libraries=places&callback=initMapsCallback";
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
        }

        // Callback function for when maps API loads
        function initMapsCallback() {
            console.log("Google Maps API loaded successfully");
            // Initialize map if modal is already open
            if (document.getElementById('event-modal').style.display === 'flex') {
                initializeMap();
            }
        }

        // Load Maps API when page loads
        window.addEventListener('DOMContentLoaded', initGoogleMapsAPI);
    </script>
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
                    <input type="text" placeholder="Search events..." aria-label="Search events">
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
                    <input type="text" placeholder="Search events..." aria-label="Search events">
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
                <div class="tab-navigation">
                    <a href="<?php echo BASE_URL; ?>/dashboard" class="tab-item">Dashboard</a>
                    <a href="<?php echo BASE_URL; ?>/ep" class="tab-item">Enrichment Point</a>
                    <a href="<?php echo BASE_URL; ?>/events" class="tab-item active">Events</a>
                    <a href="<?php echo BASE_URL; ?>/cca" class="tab-item">CCAs</a>
                    <a href="<?php echo BASE_URL; ?>/history" class="tab-item">History</a>
                </div>

                <!-- Page Header with Actions -->
                <div class="events-management-header">
                    <h1>Events Management</h1>
                    <div class="event-actions">
                        <button id="add-event-btn" class="btn btn-primary">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Add New Event
                        </button>
                        <a href="<?php echo BASE_URL; ?>/events" class="btn btn-secondary">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 17l-5-5 5-5M18 17l-5-5 5-5"></path>
                            </svg>
                            Back to Events
                        </a>
                    </div>
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

                <!-- Events List -->
                <div class="events-list-container">
                    <div class="events-filter">
                        <input type="text" id="event-search" placeholder="Search events..." class="search-input">
                        <select id="status-filter" class="filter-select">
                            <option value="all">All Statuses</option>
                            <option value="Scheduled">Scheduled</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>

                    <table class="events-table">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Date & Time</th>
                                <th>Location</th>
                                <th>Participants</th>
                                <th>Status</th>
                                <th>EP Points</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($events)): ?>
                                <tr>
                                    <td colspan="7" class="no-data">No events found. Create your first event!</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($events as $event): ?>
                                    <tr data-status="<?php echo htmlspecialchars($event['status']); ?>">
                                        <td><?php echo htmlspecialchars($event['event_name']); ?></td>
                                        <td>
                                            <?php
                                            $date = new DateTime($event['event_date']);
                                            echo $date->format('d M Y');

                                            if ($event['start_time'] && $event['end_time']) {
                                                $startTime = date('g:i A', strtotime($event['start_time']));
                                                $endTime = date('g:i A', strtotime($event['end_time']));
                                                echo '<br>' . $startTime . ' - ' . $endTime;
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($event['event_location']); ?></td>
                                        <td><?php echo htmlspecialchars($event['event_participants']); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo strtolower($event['status']); ?>">
                                                <?php echo htmlspecialchars($event['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($event['enrichment_points_awarded']); ?></td>
                                        <td class="action-buttons">
                                            <button class="edit-event-btn" data-id="<?php echo $event['event_id']; ?>">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                                Edit
                                            </button>
                                            <button class="delete-event-btn" data-id="<?php echo $event['event_id']; ?>">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                                </svg>
                                                Delete
                                            </button>
                                            <button class="view-participants-btn" data-id="<?php echo $event['event_id']; ?>">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="9" cy="7" r="4"></circle>
                                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                                </svg>
                                                Participants
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Add/Edit Event Modal -->
                <div id="event-modal" class="modal">
                    <div class="modal-content">
                        <span class="close-btn">&times;</span>
                        <h2 id="modal-title"><?php echo isset($_SESSION['edit_event']) ? 'Edit Event' : 'Add New Event'; ?></h2>

                        <form id="event-form" action="<?php echo BASE_URL; ?>/events-management" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="operation" id="operation" value="<?php echo isset($_SESSION['edit_event']) ? 'update_event' : 'add_event'; ?>">
                            <input type="hidden" name="event_id" id="event_id" value="<?php echo isset($_SESSION['edit_event']) ? $_SESSION['edit_event']['event_id'] : ''; ?>">

                            <div class="form-group">
                                <label for="event_name">Event Name <span class="required">*</span></label>
                                <input type="text" id="event_name" name="event_name" value="<?php echo isset($_SESSION['edit_event']) ? htmlspecialchars($_SESSION['edit_event']['event_name']) : ''; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="event_description">Description</label>
                                <textarea id="event_description" name="event_description" rows="4"><?php echo isset($_SESSION['edit_event']) ? htmlspecialchars($_SESSION['edit_event']['event_description']) : ''; ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="event_date">Date <span class="required">*</span></label>
                                    <input type="date" id="event_date" name="event_date" value="<?php echo isset($_SESSION['edit_event']) ? htmlspecialchars(date('Y-m-d', strtotime($_SESSION['edit_event']['event_date']))) : ''; ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="start_time">Start Time <span class="required">*</span></label>
                                    <input type="time" id="start_time" name="start_time" value="<?php echo isset($_SESSION['edit_event']) ? htmlspecialchars($_SESSION['edit_event']['start_time']) : ''; ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="end_time">End Time <span class="required">*</span></label>
                                    <input type="time" id="end_time" name="end_time" value="<?php echo isset($_SESSION['edit_event']) ? htmlspecialchars($_SESSION['edit_event']['end_time']) : ''; ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="event_location">Location <span class="required">*</span></label>
                                <input type="text" id="event_location" name="event_location" value="<?php echo isset($_SESSION['edit_event']) ? htmlspecialchars($_SESSION['edit_event']['event_location']) : ''; ?>" required>
                            </div>

                            <div class="form-group location-search-box">
                                <label for="location-search">Search Location</label>
                                <input type="text" id="location-search" placeholder="Search for a location...">
                                <div id="location-map"></div>
                                <p class="form-hint">Click on the map to select a location</p>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="event_participants">Max Participants <span class="required">*</span></label>
                                    <input type="number" id="event_participants" name="event_participants" min="1" value="<?php echo isset($_SESSION['edit_event']) ? htmlspecialchars($_SESSION['edit_event']['event_participants']) : ''; ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="enrichment_points_awarded">EP Points <span class="required">*</span></label>
                                    <input type="number" id="enrichment_points_awarded" name="enrichment_points_awarded" min="0" value="<?php echo isset($_SESSION['edit_event']) ? htmlspecialchars($_SESSION['edit_event']['enrichment_points_awarded']) : ''; ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="status">Status <span class="required">*</span></label>
                                    <select id="status" name="status" required>
                                        <option value="Scheduled" <?php echo isset($_SESSION['edit_event']) && $_SESSION['edit_event']['status'] === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                        <option value="Ongoing" <?php echo isset($_SESSION['edit_event']) && $_SESSION['edit_event']['status'] === 'Ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                        <option value="Completed" <?php echo isset($_SESSION['edit_event']) && $_SESSION['edit_event']['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="Cancelled" <?php echo isset($_SESSION['edit_event']) && $_SESSION['edit_event']['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="organizer">Organizer</label>
                                <input type="text" id="organizer" name="organizer" value="<?php echo isset($_SESSION['edit_event']) ? htmlspecialchars($_SESSION['edit_event']['organizer']) : htmlspecialchars($_SESSION['full_name'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="event_image">Event Image</label>
                                <input type="file" id="event_image" name="event_image" accept="image/*">
                                <div id="image-preview" class="image-preview">
                                    <?php if (isset($_SESSION['edit_event']) && !empty($_SESSION['edit_event']['events_images'])): ?>
                                        <img src="<?php echo htmlspecialchars($_SESSION['edit_event']['events_images']); ?>" alt="Event Image">
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="form-buttons">
                                <button type="submit" class="btn btn-primary">Save Event</button>
                                <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Participants Modal -->
                <div id="participants-modal" class="modal">
                    <div class="modal-content">
                        <h2>Event Participants: <?php echo isset($_SESSION['view_event']) ? htmlspecialchars($_SESSION['view_event']['event_name']) : 'Event'; ?></h2>

                        <div id="participants-list">
                            <table class="participants-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Student ID</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Registration Date</th>
                                        <th>Attendance Status</th>
                                        <th>Registration Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="participants-data">
                                    <?php if (isset($_SESSION['participants'])): ?>
                                        <?php if (empty($_SESSION['participants'])): ?>
                                            <tr>
                                                <td colspan="8">No participants have registered for this event yet.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($_SESSION['participants'] as $participant): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($participant['participant_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($participant['student_id'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($participant['participant_email']); ?></td>
                                                    <td><?php echo htmlspecialchars($participant['participant_phone'] ?? 'N/A'); ?></td>
                                                    <td>
                                                        <?php
                                                        $regDate = new DateTime($participant['registration_date']);
                                                        echo $regDate->format('d M Y g:i A');
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <span class="status-badge <?php echo strtolower($participant['attendance_status']); ?>">
                                                            <?php echo htmlspecialchars($participant['attendance_status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="status-badge <?php echo strtolower($participant['status']); ?>">
                                                            <?php echo htmlspecialchars($participant['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <form method="POST" action="<?php echo BASE_URL; ?>/events-management" class="status-update-form">
                                                            <input type="hidden" name="operation" value="update_participant_status">
                                                            <input type="hidden" name="participant_id" value="<?php echo $participant['participant_id']; ?>">
                                                            <select name="status" class="status-select" data-id="<?php echo $participant['participant_id']; ?>">
                                                                <option value="Registered" <?php echo $participant['attendance_status'] === 'Registered' ? 'selected' : ''; ?>>Registered</option>
                                                                <option value="Confirmed" <?php echo $participant['attendance_status'] === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                                <option value="Attended" <?php echo $participant['attendance_status'] === 'Attended' ? 'selected' : ''; ?>>Attended</option>
                                                                <option value="Cancelled" <?php echo $participant['attendance_status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                            </select>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Modal footer with buttons -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id="refresh-participants">Refresh Data</button>
                            <button type="button" class="btn btn-secondary close-modal">Close</button>
                        </div>
                    </div>
                </div>

                <!-- Delete Confirmation Modal -->
                <div id="confirm-modal" class="modal">
                    <div class="modal-content confirm-content">
                        <h2>Confirm Deletion</h2>
                        <p>Are you sure you want to delete this event? This action cannot be undone.</p>

                        <form id="delete-form" action="<?php echo BASE_URL; ?>/events-management" method="POST">
                            <input type="hidden" name="operation" value="delete_event">
                            <input type="hidden" name="event_id" id="delete-event-id" value="">

                            <div class="form-buttons">
                                <button type="submit" class="btn btn-danger">Delete</button>
                                <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                            </div>
                        </form>
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

    <script src="<?php echo BASE_URL; ?>/assets/js/profile-dropdown.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/search.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/burger.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/events-management.js"></script>

    <script>
        // Define BASE_URL for JavaScript from PHP
        const BASE_URL = "<?php echo BASE_URL; ?>";

        // Google Maps integration - initialize map function in global scope
        let map, marker;

        function initializeMap(address = null) {
            // Default to a central location (e.g., Politeknik Brunei)
            const defaultLocation = {
                lat: 4.9431,
                lng: 114.9425
            };

            const mapOptions = {
                center: defaultLocation,
                zoom: 15,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };

            const mapElement = document.getElementById('location-map');
            if (!mapElement) {
                console.error("Map element not found");
                return;
            }

            map = new google.maps.Map(mapElement, mapOptions);

            marker = new google.maps.Marker({
                position: defaultLocation,
                map: map,
                draggable: true,
                title: 'Event Location'
            });

            // If an address is provided and not empty, try to geocode it
            if (address && address.trim() !== '') {
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({
                    address: address
                }, function(results, status) {
                    if (status === 'OK' && results[0]) {
                        map.setCenter(results[0].geometry.location);
                        marker.setPosition(results[0].geometry.location);
                    } else {
                        console.warn("Geocoding failed for address: " + address + ", status: " + status);
                    }
                });
            }

            // Add click listener to map
            google.maps.event.addListener(map, 'click', function(event) {
                marker.setPosition(event.latLng);

                // Reverse geocode to get address
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({
                    'location': event.latLng
                }, function(results, status) {
                    if (status === 'OK' && results[0]) {
                        document.getElementById('event_location').value = results[0].formatted_address;
                    }
                });
            });

            // Add listener for marker drag
            google.maps.event.addListener(marker, 'dragend', function() {
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({
                    'location': marker.getPosition()
                }, function(results, status) {
                    if (status === 'OK' && results[0]) {
                        document.getElementById('event_location').value = results[0].formatted_address;
                    }
                });
            });

            // Setup location search autocomplete
            const locationSearch = document.getElementById('location-search');
            if (locationSearch) {
                const autocomplete = new google.maps.places.Autocomplete(locationSearch);
                autocomplete.addListener('place_changed', function() {
                    const place = autocomplete.getPlace();

                    if (!place.geometry || !place.geometry.location) {
                        alert('No location details available for this search');
                        return;
                    }

                    // Update map and marker
                    map.setCenter(place.geometry.location);
                    map.setZoom(15);
                    marker.setPosition(place.geometry.location);

                    // Update location input field
                    document.getElementById('event_location').value = place.formatted_address || place.name;
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Close any open modals first
            document.getElementById('event-modal').style.display = 'none';
            document.getElementById('participants-modal').style.display = 'none';
            document.getElementById('confirm-modal').style.display = 'none';

            // Variables
            const addEventBtn = document.getElementById('add-event-btn');
            const eventModal = document.getElementById('event-modal');
            const participantsModal = document.getElementById('participants-modal');
            const confirmModal = document.getElementById('confirm-modal');
            const closeButtons = document.querySelectorAll('.close-btn, .close-modal');
            const eventSearch = document.getElementById('event-search');
            const statusFilter = document.getElementById('status-filter');

            // Only proceed to show a modal if specifically told to by server-side code
            <?php if (isset($_SESSION['edit_event'])): ?>
                console.log('Showing edit modal from session data');
                eventModal.style.display = 'flex';
                participantsModal.style.display = 'none';
                <?php
                // Clear this session variable immediately
                unset($_SESSION['edit_event']);
                ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['participants']) && isset($_SESSION['view_event'])): ?>
                console.log('Showing participants modal from session data');
                participantsModal.style.display = 'flex';
                eventModal.style.display = 'none';
                <?php
                // Clear these session variables immediately
                unset($_SESSION['participants']);
                unset($_SESSION['view_event']);
                ?>
            <?php endif; ?>

            // Add event listeners to all status-select dropdowns for AJAX updates
            document.querySelectorAll('.status-select').forEach(select => {
                // Remove any existing form submission behavior
                if (select.form) {
                    select.form.onsubmit = (e) => e.preventDefault();
                }

                // Add change event listener to use the AJAX method
                select.addEventListener('change', function() {
                    // Get participant ID from data attribute
                    const participantId = this.dataset.id;
                    // Get selected status
                    const status = this.value;
                    // Call the updateParticipantStatus function
                    updateParticipantStatus(participantId, status);
                });
            });

            // Add event listener for the event form submission to use AJAX
            const eventForm = document.getElementById('event-form');
            if (eventForm) {
                eventForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitEventForm(this);
                });
            }

            // Add event listener for the delete form submission to use AJAX
            const deleteForm = document.getElementById('delete-form');
            if (deleteForm) {
                deleteForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitDeleteForm(this);
                });
            }

            // Event listeners for modals
            addEventBtn.addEventListener('click', function() {
                // Reset form for a new event
                eventForm.reset();
                document.getElementById('operation').value = 'add_event';
                document.getElementById('event_id').value = '';
                document.getElementById('modal-title').textContent = 'Add New Event';
                document.getElementById('image-preview').innerHTML = '';
                showModal(eventModal);

                // Initialize Google Maps if available
                if (typeof google !== 'undefined') {
                    initializeMap();
                }
            });

            // Close modals when clicking close button or cancel
            closeButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    console.log('Close button clicked, hiding modals');
                    e.preventDefault();
                    eventModal.style.display = 'none';
                    participantsModal.style.display = 'none';
                    confirmModal.style.display = 'none';
                });
            });

            // Close modals when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === eventModal || event.target === participantsModal || event.target === confirmModal) {
                    console.log('Clicked outside modal, hiding modals');
                    eventModal.style.display = 'none';
                    participantsModal.style.display = 'none';
                    confirmModal.style.display = 'none';
                }
            });

            // Close modals with ESC key
            window.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' || event.keyCode === 27) {
                    console.log('ESC key pressed, hiding modals');
                    eventModal.style.display = 'none';
                    participantsModal.style.display = 'none';
                    confirmModal.style.display = 'none';
                }
            });

            // Event listeners for edit buttons
            document.querySelectorAll('.edit-event-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = this.dataset.id;
                    console.log("Edit button clicked for event ID:", eventId);

                    // Create a form to fetch event data by POST instead of AJAX
                    // Note: The server has been modified to redirect to GET after processing
                    // This prevents form resubmission issues on page refresh
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `${BASE_URL}/events-management`;
                    form.style.display = 'none';

                    const operationInput = document.createElement('input');
                    operationInput.type = 'hidden';
                    operationInput.name = 'operation';
                    operationInput.value = 'edit_form';

                    const eventIdInput = document.createElement('input');
                    eventIdInput.type = 'hidden';
                    eventIdInput.name = 'event_id';
                    eventIdInput.value = eventId;

                    form.appendChild(operationInput);
                    form.appendChild(eventIdInput);
                    document.body.appendChild(form);

                    // Use direct form submission
                    form.submit();
                });
            });

            // Event listeners for delete buttons
            document.querySelectorAll('.delete-event-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = this.dataset.id;
                    document.getElementById('delete-event-id').value = eventId;
                    showModal(confirmModal);
                });
            });

            // Event listeners for view participants buttons
            document.querySelectorAll('.view-participants-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    const eventId = this.dataset.id;
                    console.log("View participants button clicked for event ID:", eventId);
                    
                    // Use debug function if Shift key is held
                    if (e.shiftKey) {
                        console.log("Shift key detected, using debug function");
                        debugViewParticipants(eventId);
                        return;
                    }
                    
                    // Get a reference to the participants modal
                    const participantsModal = document.getElementById('participants-modal');

                    // Update the modal title with the event name (if available)
                    const eventName = this.closest('tr').querySelector('td:first-child').textContent;
                    const modalTitle = participantsModal.querySelector('h2');
                    if (modalTitle) {
                        modalTitle.textContent = `Event Participants: ${eventName}`;
                    }

                    // Show the modal immediately with a loading message
                    const participantsList = document.getElementById('participants-list');
                    participantsList.innerHTML = '<div class="loading">Loading participants data...</div>';
                    participantsModal.style.display = 'flex';

                    // Use the direct events-management URL with POST request
                    const url = `${BASE_URL}/events-management`;
                    console.log("Fetching participants for event ID:", eventId);

                    // Create form data to send in POST request
                    const formData = new FormData();
                    formData.append('operation', 'view_participants');
                    formData.append('event_id', eventId);

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        })
                        .then(response => {
                            console.log("Response status:", response.status);
                            console.log("Response headers:", [...response.headers].map(h => `${h[0]}: ${h[1]}`).join(', '));

                            if (!response.ok) {
                                throw new Error(`HTTP error! Status: ${response.status}`);
                            }

                            // Try to parse as JSON
                            return response.text().then(text => {
                                console.log("Raw response:", text);
                                try {
                                    return JSON.parse(text);
                                } catch (e) {
                                    console.error("Error parsing JSON:", e);
                                    throw new Error("Invalid JSON response: " + text);
                                }
                            });
                        })
                        .then(data => {
                            console.log("Received participants data:", data);
                            
                            if (data.success) {
                                // Get the parent container for the entire participants list
                                const participantsList = document.getElementById('participants-list');
                                
                                // Clear existing content and rebuild the entire table with headers
                                participantsList.innerHTML = `
                                    <table class="participants-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Student ID</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Registration Date</th>
                                                <th>Attendance Status</th>
                                                <th>Registration Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="participants-data">
                                        </tbody>
                                    </table>
                                `;
                                
                                // Get the newly created tbody
                                const participantsTable = document.getElementById('participants-data');
                                
                                if (!data.participants || data.participants.length === 0) {
                                    console.log("No participants found for this event");
                                    participantsTable.innerHTML = '<tr><td colspan="8">No participants have registered for this event yet.</td></tr>';
                                } else {
                                    console.log(`Processing ${data.participants.length} participants`);
                                    data.participants.forEach((participant, index) => {
                                        console.log(`Processing participant ${index + 1}:`, participant);
                                        const row = document.createElement('tr');
                                        
                                        // Format the registration date
                                        const regDate = new Date(participant.registration_date);
                                        const formattedDate = regDate.toLocaleDateString() + ' ' + regDate.toLocaleTimeString();
                                        
                                        // Ensure all fields exist with fallbacks
                                        const participantName = participant.participant_name || participant.full_name || 'N/A';
                                        const studentId = participant.student_id || 'N/A';
                                        const email = participant.participant_email || participant.email || 'N/A';
                                        const phone = participant.participant_phone || 'N/A';
                                        const attendanceStatus = participant.attendance_status || 'Registered';
                                        const status = participant.status || 'Pending';
                                        
                                        row.innerHTML = `
                                            <td>${participantName}</td>
                                            <td>${studentId}</td>
                                            <td>${email}</td>
                                            <td>${phone}</td>
                                            <td>${formattedDate}</td>
                                            <td>
                                                <span class="status-badge ${attendanceStatus.toLowerCase()}">
                                                    ${attendanceStatus}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge ${status.toLowerCase()}">
                                                    ${status}
                                                </span>
                                            </td>
                                            <td>
                                                <form method="POST" action="<?php echo BASE_URL; ?>/events-management" class="status-update-form">
                                                    <input type="hidden" name="operation" value="update_participant_status">
                                                    <input type="hidden" name="participant_id" value="${participant.participant_id}">
                                                    <select name="status" class="status-select" data-id="${participant.participant_id}">
                                                        <option value="Registered" ${attendanceStatus === 'Registered' ? 'selected' : ''}>Registered</option>
                                                        <option value="Confirmed" ${attendanceStatus === 'Confirmed' ? 'selected' : ''}>Confirmed</option>
                                                        <option value="Attended" ${attendanceStatus === 'Attended' ? 'selected' : ''}>Attended</option>
                                                        <option value="Cancelled" ${attendanceStatus === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                                                    </select>
                                                </form>
                                            </td>`;
                                        
                                        participantsTable.appendChild(row);
                                    });
                                    
                                    // Add event listeners for status changes
                                    document.querySelectorAll('.status-select').forEach(select => {
                                        select.addEventListener('change', function() {
                                            updateParticipantStatus(this.dataset.id, this.value);
                                        });
                                    });
                                }
                                
                                // Add event listeners to the dynamically created close buttons
                                document.querySelectorAll('.close-modal').forEach(button => {
                                    button.addEventListener('click', function() {
                                        participantsModal.style.display = 'none';
                                    });
                                });
                            } else {
                                // Display error in the participants list
                                const participantsList = document.getElementById('participants-list');
                                participantsList.innerHTML = `<div class="alert alert-error">Failed to load participants: ${data.message || 'Unknown error'}</div>`;
                            }
                        })
                        .catch(error => {
                            console.error("Fetch error:", error.message);

                            // Display error in the participants list
                            const participantsList = document.getElementById('participants-list');
                            participantsList.innerHTML = `<div class="alert alert-error">Error: ${error.message}</div>`;

                            // If automatic AJAX approach fails, offer manual form submission as a fallback
                            const retryButton = document.createElement('button');
                            retryButton.className = 'btn btn-primary';
                            retryButton.textContent = 'Retry with Traditional Form Submission';
                            retryButton.addEventListener('click', function() {
                                // Create and submit a traditional form
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action = `${BASE_URL}/events-management`;

                                const operationInput = document.createElement('input');
                                operationInput.type = 'hidden';
                                operationInput.name = 'operation';
                                operationInput.value = 'view_participants';

                                const eventIdInput = document.createElement('input');
                                eventIdInput.type = 'hidden';
                                eventIdInput.name = 'event_id';
                                eventIdInput.value = eventId;

                                form.appendChild(operationInput);
                                form.appendChild(eventIdInput);
                                document.body.appendChild(form);

                                // Hide the modal before submitting
                                participantsModal.style.display = 'none';

                                // Submit the form
                                form.submit();
                            });

                            // Add the retry button to the participants list
                            participantsList.appendChild(retryButton);
                        });
                });
            });

            // Image preview
            const imageInput = document.getElementById('event_image');
            const imagePreview = document.getElementById('image-preview');

            imageInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        imagePreview.innerHTML = `<img src="${e.target.result}" alt="Event Image Preview">`;
                    };

                    reader.readAsDataURL(this.files[0]);
                }
            });

            // Search functionality
            eventSearch.addEventListener('input', filterEvents);
            statusFilter.addEventListener('change', filterEvents);

            // Filter events based on search input and status filter
            function filterEvents() {
                const searchTerm = eventSearch.value.toLowerCase();
                const statusValue = statusFilter.value;
                const rows = document.querySelectorAll('.events-table tbody tr');

                rows.forEach(row => {
                    const eventName = row.cells[0].textContent.toLowerCase();
                    const eventLocation = row.cells[2].textContent.toLowerCase();
                    const eventStatus = row.dataset.status;

                    const matchesSearch = eventName.includes(searchTerm) || eventLocation.includes(searchTerm);
                    const matchesStatus = statusValue === 'all' || eventStatus === statusValue;

                    row.style.display = matchesSearch && matchesStatus ? '' : 'none';
                });
            }

            // Show modal helper
            function showModal(modal) {
                modal.style.display = 'flex';

                // If this is the event modal, initialize the map after modal is displayed
                if (modal === eventModal && typeof google !== 'undefined') {
                    // Slight delay to ensure modal is fully visible
                    setTimeout(() => {
                        // Trigger resize event to make sure map renders correctly within modal
                        window.dispatchEvent(new Event('resize'));
                        // Initialize map with current address if editing
                        const currentAddress = document.getElementById('event_location').value;
                        initializeMap(currentAddress);
                    }, 100);
                }
            }

            // Edit event function
            function editEvent(eventId) {
                // Based on routing in index.php
                // URL should be /controllers/events.php for API endpoints
                const url = `${BASE_URL}/controllers/events.php?operation=get_event&event_id=${eventId}`;
                console.log("Fetching event from URL:", url);

                // Try to access the API endpoint
                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log("Received data:", data);

                        if (data.success) {
                            const event = data.event;

                            // Populate form fields
                            document.getElementById('operation').value = 'update_event';
                            document.getElementById('event_id').value = event.event_id;
                            document.getElementById('event_name').value = event.event_name;
                            document.getElementById('event_description').value = event.event_description;
                            document.getElementById('event_date').value = formatDateForInput(event.event_date);
                            document.getElementById('start_time').value = event.start_time;
                            document.getElementById('end_time').value = event.end_time;
                            document.getElementById('event_location').value = event.event_location;
                            document.getElementById('event_participants').value = event.event_participants;
                            document.getElementById('enrichment_points_awarded').value = event.enrichment_points_awarded;
                            document.getElementById('status').value = event.status;
                            document.getElementById('organizer').value = event.organizer || '';

                            // Show image preview if available
                            if (event.events_images) {
                                document.getElementById('image-preview').innerHTML = `<img src="${event.events_images}" alt="Event Image">`;
                            } else {
                                document.getElementById('image-preview').innerHTML = '';
                            }

                            // Update modal title
                            document.getElementById('modal-title').textContent = 'Edit Event';

                            // Show modal
                            showModal(eventModal);

                            // Initialize Google Maps if available
                            if (typeof google !== 'undefined') {
                                initializeMap(event.event_location);
                            }
                        } else {
                            alert('Failed to load event details: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error("Fetch error:", error.message);
                        alert('Error fetching event details: ' + error.message);
                    });
            }

            // View participants function
            function viewParticipants(eventId) {
                // Use the direct events-management URL with POST request
                const url = `${BASE_URL}/events-management`;
                console.log("Fetching participants for event ID:", eventId);

                // Create form data to send in POST request
                const formData = new FormData();
                formData.append('operation', 'view_participants');
                formData.append('event_id', eventId);

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log("Received participants data:", data);

                        if (data.success) {
                            // Get the parent container for the entire participants list
                            const participantsList = document.getElementById('participants-list');

                            // Clear existing content and rebuild the entire table with headers
                            participantsList.innerHTML = `
                            <table class="participants-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Student ID</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Registration Date</th>
                                        <th>Attendance Status</th>
                                        <th>Registration Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="participants-data">
                                </tbody>
                            </table>
                        `;

                            // Get the newly created tbody
                            const participantsTable = document.getElementById('participants-data');

                            if (data.participants.length === 0) {
                                participantsTable.innerHTML = '<tr><td colspan="8">No participants have registered for this event yet.</td></tr>';
                            } else {
                                data.participants.forEach(participant => {
                                    const row = document.createElement('tr');

                                    // Format the registration date
                                    const regDate = new Date(participant.registration_date);
                                    const formattedDate = regDate.toLocaleDateString() + ' ' + regDate.toLocaleTimeString();

                                    row.innerHTML = `
                                    <td>${participant.participant_name}</td>
                                    <td>${participant.student_id || 'N/A'}</td>
                                    <td>${participant.participant_email}</td>
                                    <td>${participant.participant_phone || 'N/A'}</td>
                                    <td>${formattedDate}</td>
                                    <td>
                                        <span class="status-badge ${participant.attendance_status.toLowerCase()}">
                                            ${participant.attendance_status}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge ${participant.status.toLowerCase()}">
                                            ${participant.status}
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="<?php echo BASE_URL; ?>/events-management" class="status-update-form">
                                            <input type="hidden" name="operation" value="update_participant_status">
                                            <input type="hidden" name="participant_id" value="${participant.participant_id}">
                                            <select name="status" class="status-select" data-id="${participant.participant_id}">
                                                <option value="Registered" ${participant.attendance_status === 'Registered' ? 'selected' : ''}>Registered</option>
                                                <option value="Confirmed" ${participant.attendance_status === 'Confirmed' ? 'selected' : ''}>Confirmed</option>
                                                <option value="Attended" ${participant.attendance_status === 'Attended' ? 'selected' : ''}>Attended</option>
                                                <option value="Cancelled" ${participant.attendance_status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>`;

                                    participantsTable.appendChild(row);
                                });

                                // Add event listeners for status changes
                                document.querySelectorAll('.status-select').forEach(select => {
                                    select.addEventListener('change', function() {
                                        updateParticipantStatus(this.dataset.id, this.value);
                                    });
                                });
                            }

                            // Show modal
                            showModal(participantsModal);
                        } else {
                            alert('Failed to load participants: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error("Fetch error:", error.message);

                        // If AJAX fails, use server-side approach as fallback
                        console.log("Falling back to server-side approach");
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `${BASE_URL}/events-management`;
                        form.style.display = 'none';

                        const operationInput = document.createElement('input');
                        operationInput.type = 'hidden';
                        operationInput.name = 'operation';
                        operationInput.value = 'view_participants';

                        const eventIdInput = document.createElement('input');
                        eventIdInput.type = 'hidden';
                        eventIdInput.name = 'event_id';
                        eventIdInput.value = eventId;

                        form.appendChild(operationInput);
                        form.appendChild(eventIdInput);
                        document.body.appendChild(form);

                        form.submit();
                    });
            }

            // Update participant status function
            function updateParticipantStatus(participantId, status) {
                console.log(`Updating participant ${participantId} to status ${status}`);

                // Show feedback to user immediately
                const select = document.querySelector(`.status-select[data-id="${participantId}"]`);
                const row = select ? select.closest('tr') : null;
                const statusBadge = row ? row.querySelector('.status-badge') : null;

                if (statusBadge) {
                    // Add loading indicator to the badge
                    statusBadge.classList.add('loading');
                    statusBadge.setAttribute('data-original-text', statusBadge.textContent);
                    statusBadge.textContent = 'Updating...';
                }

                const formData = new FormData();
                formData.append('operation', 'update_participant_status');
                formData.append('participant_id', participantId);
                formData.append('status', status);

                // Use events-management URL instead of direct controller
                const url = `${BASE_URL}/events-management`;
                console.log("Updating participant status at URL:", url, {
                    participantId,
                    status
                });

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }

                        // Parse as text first to check for valid JSON
                        return response.text().then(text => {
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.error("Invalid JSON response:", text);
                                throw new Error("Server returned an invalid response");
                            }
                        });
                    })
                    .then(data => {
                        console.log("Update response:", data);

                        // If we have a select element and row, update the UI
                        if (select && row && statusBadge) {
                            if (data.success) {
                                // Remove loading state
                                statusBadge.classList.remove('loading');

                                // Update badge class and text
                                statusBadge.className = `status-badge ${status.toLowerCase()}`;
                                statusBadge.textContent = status;

                                // Show success message
                                const alertContainer = document.createElement('div');
                                alertContainer.className = 'alert alert-success temp-alert';
                                alertContainer.textContent = data.message || 'Participant status updated successfully.';

                                // Add to page
                                const mainWrapper = document.querySelector('.main-wrapper');
                                mainWrapper.insertBefore(alertContainer, mainWrapper.firstChild);

                                // Remove after 3 seconds
                                setTimeout(() => {
                                    alertContainer.style.opacity = '0';
                                    setTimeout(() => alertContainer.remove(), 500);
                                }, 3000);
                            } else {
                                // Reset the select to the previous value
                                const options = Array.from(select.options);
                                const previousStatus = statusBadge.getAttribute('data-original-text');
                                const originalOption = options.find(opt => opt.textContent === previousStatus);

                                if (originalOption) {
                                    select.value = originalOption.value;
                                }

                                // Reset badge
                                statusBadge.classList.remove('loading');
                                statusBadge.textContent = statusBadge.getAttribute('data-original-text') || status;

                                // Show error message
                                const alertContainer = document.createElement('div');
                                alertContainer.className = 'alert alert-error temp-alert';
                                alertContainer.textContent = data.message || 'Failed to update participant status.';

                                // Add to page
                                const mainWrapper = document.querySelector('.main-wrapper');
                                mainWrapper.insertBefore(alertContainer, mainWrapper.firstChild);

                                // Remove after 5 seconds
                                setTimeout(() => {
                                    alertContainer.style.opacity = '0';
                                    setTimeout(() => alertContainer.remove(), 500);
                                }, 5000);
                            }
                        }

                        return data;
                    })
                    .catch(error => {
                        console.error('Error updating participant status:', error);

                        // Reset UI if we have the elements
                        if (statusBadge) {
                            statusBadge.classList.remove('loading');
                            statusBadge.textContent = statusBadge.getAttribute('data-original-text') || 'Unknown';
                        }

                        if (select) {
                            // Try to reset the select value
                            const options = Array.from(select.options);
                            const previousStatus = statusBadge ? statusBadge.getAttribute('data-original-text') : null;
                            const originalOption = previousStatus ? options.find(opt => opt.textContent === previousStatus) : null;

                            if (originalOption) {
                                select.value = originalOption.value;
                            }
                        }

                        // Show error alert
                        const alertContainer = document.createElement('div');
                        alertContainer.className = 'alert alert-error temp-alert';
                        alertContainer.textContent = 'An error occurred while updating participant status: ' + error.message;

                        // Add to page
                        const mainWrapper = document.querySelector('.main-wrapper');
                        if (mainWrapper) {
                            mainWrapper.insertBefore(alertContainer, mainWrapper.firstChild);

                            // Remove after 5 seconds
                            setTimeout(() => {
                                alertContainer.style.opacity = '0';
                                setTimeout(() => alertContainer.remove(), 500);
                            }, 5000);
                        } else {
                            alert('Error updating status: ' + error.message);
                        }
                    });
            }

            // Format date for date input (YYYY-MM-DD)
            function formatDateForInput(dateString) {
                const date = new Date(dateString);
                return date.toISOString().split('T')[0];
            }

            // Function to submit event form via AJAX
            function submitEventForm(form) {
                const formData = new FormData(form);

                // Use form's actual action URL
                fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }

                        // Close the modal
                        eventModal.style.display = 'none';

                        // Show success message
                        const alertContainer = document.createElement('div');
                        alertContainer.className = 'alert alert-success temp-alert';
                        alertContainer.textContent = 'Event saved successfully.';

                        // Add to page
                        const mainWrapper = document.querySelector('.main-wrapper');
                        mainWrapper.insertBefore(alertContainer, mainWrapper.firstChild);

                        // Reload the page to show updated event list
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);

                        return true;
                    })
                    .catch(error => {
                        console.error('Error submitting form:', error);
                        alert('An error occurred while submitting the form: ' + error.message);
                    });
            }

            // Function to submit delete form via AJAX
            function submitDeleteForm(form) {
                const formData = new FormData(form);

                // Use form's actual action URL
                fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }

                        // Close the modal
                        confirmModal.style.display = 'none';

                        // Show success message
                        const alertContainer = document.createElement('div');
                        alertContainer.className = 'alert alert-success temp-alert';
                        alertContainer.textContent = 'Event deleted successfully.';

                        // Add to page
                        const mainWrapper = document.querySelector('.main-wrapper');
                        mainWrapper.insertBefore(alertContainer, mainWrapper.firstChild);

                        // Reload the page to show updated event list
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);

                        return true;
                    })
                    .catch(error => {
                        console.error('Error deleting event:', error);
                        alert('An error occurred while deleting the event: ' + error.message);
                    });
            }

            // Helper function for debugging - can be removed in production
            function debugViewParticipants(eventId) {
                console.log("Debug: Creating sample participant data for testing");
                
                // Get a reference to the participants modal
                const participantsModal = document.getElementById('participants-modal');
                
                // Show the modal immediately with a loading message
                const participantsList = document.getElementById('participants-list');
                participantsList.innerHTML = '<div class="loading">Loading sample data...</div>';
                participantsModal.style.display = 'flex';
                
                // Create sample data
                const sampleData = {
                    success: true,
                    event: {
                        event_id: eventId,
                        event_name: "Sample Event"
                    },
                    participants: [
                        {
                            participant_id: 1,
                            event_id: eventId,
                            user_id: 1,
                            participant_name: "John Smith",
                            participant_email: "john@example.com",
                            participant_phone: "123-456-7890",
                            registration_date: new Date().toISOString(),
                            attendance_status: "Registered",
                            status: "Pending",
                            student_id: "22ABC1234"
                        },
                        {
                            participant_id: 2,
                            event_id: eventId,
                            user_id: 2,
                            participant_name: "Jane Doe",
                            participant_email: "jane@example.com",
                            participant_phone: "987-654-3210",
                            registration_date: new Date().toISOString(),
                            attendance_status: "Confirmed",
                            status: "Approved",
                            student_id: "22ABC5678"
                        }
                    ]
                };
                
                setTimeout(() => {
                    // Get the parent container for the entire participants list
                    const participantsList = document.getElementById('participants-list');
                    
                    // Clear existing content and rebuild the entire table with headers
                    participantsList.innerHTML = `
                        <table class="participants-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Student ID</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Registration Date</th>
                                    <th>Attendance Status</th>
                                    <th>Registration Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="participants-data">
                            </tbody>
                        </table>
                    `;
                    
                    // Get the newly created tbody
                    const participantsTable = document.getElementById('participants-data');
                    
                    if (sampleData.participants.length === 0) {
                        participantsTable.innerHTML = '<tr><td colspan="8">No participants have registered for this event yet.</td></tr>';
                    } else {
                        sampleData.participants.forEach(participant => {
                            const row = document.createElement('tr');
                            
                            // Format the registration date
                            const regDate = new Date(participant.registration_date);
                            const formattedDate = regDate.toLocaleDateString() + ' ' + regDate.toLocaleTimeString();
                            
                            row.innerHTML = `
                                <td>${participant.participant_name}</td>
                                <td>${participant.student_id}</td>
                                <td>${participant.participant_email}</td>
                                <td>${participant.participant_phone}</td>
                                <td>${formattedDate}</td>
                                <td>
                                    <span class="status-badge ${participant.attendance_status.toLowerCase()}">
                                        ${participant.attendance_status}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge ${participant.status.toLowerCase()}">
                                        ${participant.status}
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="<?php echo BASE_URL; ?>/events-management" class="status-update-form">
                                        <input type="hidden" name="operation" value="update_participant_status">
                                        <input type="hidden" name="participant_id" value="${participant.participant_id}">
                                        <select name="status" class="status-select" data-id="${participant.participant_id}">
                                            <option value="Registered" ${participant.attendance_status === 'Registered' ? 'selected' : ''}>Registered</option>
                                            <option value="Confirmed" ${participant.attendance_status === 'Confirmed' ? 'selected' : ''}>Confirmed</option>
                                            <option value="Attended" ${participant.attendance_status === 'Attended' ? 'selected' : ''}>Attended</option>
                                            <option value="Cancelled" ${participant.attendance_status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                            `;
                            
                            participantsTable.appendChild(row);
                        });
                        
                        // Add event listeners for status changes
                        document.querySelectorAll('.status-select').forEach(select => {
                            select.addEventListener('change', function() {
                                updateParticipantStatus(this.dataset.id, this.value);
                            });
                        });
                    }
                    
                    // Add a button to switch between empty and sample data views
                    const toggleButton = document.createElement('button');
                    toggleButton.className = 'btn btn-primary';
                    toggleButton.textContent = 'Toggle Empty View';
                    toggleButton.addEventListener('click', function() {
                        if (participantsTable.innerHTML.includes('No participants')) {
                            // Replace with sample data
                            participantsTable.innerHTML = '';
                            sampleData.participants.forEach(participant => {
                                const row = document.createElement('tr');
                                const regDate = new Date(participant.registration_date);
                                const formattedDate = regDate.toLocaleDateString() + ' ' + regDate.toLocaleTimeString();
                                
                                row.innerHTML = `
                                    <td>${participant.participant_name}</td>
                                    <td>${participant.student_id}</td>
                                    <td>${participant.participant_email}</td>
                                    <td>${participant.participant_phone}</td>
                                    <td>${formattedDate}</td>
                                    <td>
                                        <span class="status-badge ${participant.attendance_status.toLowerCase()}">
                                            ${participant.attendance_status}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge ${participant.status.toLowerCase()}">
                                            ${participant.status}
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="<?php echo BASE_URL; ?>/events-management" class="status-update-form">
                                            <input type="hidden" name="operation" value="update_participant_status">
                                            <input type="hidden" name="participant_id" value="${participant.participant_id}">
                                            <select name="status" class="status-select" data-id="${participant.participant_id}">
                                                <option value="Registered" ${participant.attendance_status === 'Registered' ? 'selected' : ''}>Registered</option>
                                                <option value="Confirmed" ${participant.attendance_status === 'Confirmed' ? 'selected' : ''}>Confirmed</option>
                                                <option value="Attended" ${participant.attendance_status === 'Attended' ? 'selected' : ''}>Attended</option>
                                                <option value="Cancelled" ${participant.attendance_status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>
                                `;
                                
                                participantsTable.appendChild(row);
                            });
                            toggleButton.textContent = 'Show Empty View';
                        } else {
                            // Show empty state
                            participantsTable.innerHTML = '<tr><td colspan="8">No participants have registered for this event yet.</td></tr>';
                            toggleButton.textContent = 'Show Sample Data';
                        }
                    });
                    
                    // Add the button to the modal
                    const modalFooter = participantsModal.querySelector('.modal-footer');
                    modalFooter.prepend(toggleButton);
                    
                    // Add event listeners to the close button
                    document.querySelectorAll('.close-modal').forEach(button => {
                        button.addEventListener('click', function() {
                            participantsModal.style.display = 'none';
                        });
                    });
                }, 500); // Small delay to simulate loading
            }
        });
    </script>
</body>

</html>
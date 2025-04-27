<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL);
    exit();
}

// Check for dark mode
$isDark = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT dark_mode FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $isDark = $user && $user['dark_mode'];
}

// Get club location data from query parameters
$clubId = $_GET['club_id'] ?? '';
$locationName = $_GET['location'] ?? 'Unknown Location';
$destLat = $_GET['lat'] ?? 4.8856000;
$destLng = $_GET['lng'] ?? 114.9370000;

// Extract club slug from URL
$clubSlug = '';
if (preg_match('/^([a-z0-9-]+)-location$/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'), $matches)) {
    $clubSlug = $matches[1];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Check-in - Politeknik Brunei</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/cca-details.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/cca-location.css">
    <link rel="icon" href="https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com/logo/politeknik-brunei-logo-2.png" type="image/png">
    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBXXh-Lwbrw-UKAC9YsrBq09vyKNmG0Lzo&libraries=places,geometry" async defer></script>
    
    <!-- Custom styles to ensure button text stays white -->
    <style>
        /* Dark mode styles for notification modal */
        body.dark .notification-content {
            background-color: #1e293b;
            color: #e6edf3;
        }
        
        body.dark #notification-message {
            color: #a3adc2;
        }
        
        body.dark .notification-close {
            color: #a3adc2;
        }
        
        body.dark .notification-close:hover {
            color: #e6edf3;
        }
        
        body.dark .alert-success {
            color: #a7f3d0;
            background-color: #064e3b;
            border-color: #10b981;
        }
        
        body.dark .alert-error {
            color: #fecdd3;
            background-color: #7f1d1d;
            border-color: #f43f5e;
        }
        
        body.dark .alert-info {
            color: #a5f3fc;
            background-color: #0e7490;
            border-color: #06b6d4;
        }
        
        .white-text-button,
        .white-text-button:hover,
        .white-text-button:active,
        .white-text-button:focus {
            color: white !important;
        }
        
        .white-text-button span,
        .white-text-button:hover span {
            color: white !important;
        }
        
        .white-text-button svg,
        .white-text-button:hover svg {
            color: white !important;
            stroke: white !important;
        }
        
        /* Extra specific rule to override any other styles */
        button#directions-btn.primary-btn.white-text-button,
        button#directions-btn.primary-btn.white-text-button:hover {
            color: white !important;
        }
        
        button#directions-btn.primary-btn.white-text-button span,
        button#directions-btn.primary-btn.white-text-button:hover span {
            color: white !important;
        }
        
        button#directions-btn.primary-btn.white-text-button svg,
        button#directions-btn.primary-btn.white-text-button:hover svg {
            color: white !important;
            stroke: white !important;
        }
        
        /* Alert message styling */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .alert-info {
            color: #0c5460;
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }
        
        /* Notification Modal Styles */
        .notification-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s;
        }
        
        .notification-content {
            position: relative;
            background-color: #fff;
            margin: 15% auto;
            padding: 30px;
            width: 90%;
            max-width: 400px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            animation: slideIn 0.3s;
        }
        
        .notification-close {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            color: #aaa;
        }
        
        .notification-close:hover {
            color: #555;
        }
        
        .notification-icon {
            margin-bottom: 20px;
        }
        
        .notification-icon svg {
            width: 64px;
            height: 64px;
            stroke: #28a745;
        }
        
        .notification-icon.error svg {
            stroke: #dc3545;
        }
        
        .notification-icon.warning svg {
            stroke: #ffc107;
        }
        
        .notification-icon.info svg {
            stroke: #17a2b8;
        }
        
        #notification-title {
            margin-bottom: 10px;
            font-size: 24px;
        }
        
        #notification-message {
            margin-bottom: 20px;
            color: #555;
        }
        
        #notification-ok-btn {
            padding: 10px 30px;
            font-size: 16px;
            margin: 0 auto;
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        /* Spinner animation */
        .spinner {
            animation: spin 1.5s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
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
                <div class="tab-navigation">
                    <a href="<?php echo BASE_URL; ?>/dashboard" class="tab-item">Dashboard</a>
                    <a href="<?php echo BASE_URL; ?>/ep" class="tab-item">Enrichment Point</a>
                    <a href="<?php echo BASE_URL; ?>/events" class="tab-item">Events</a>
                    <a href="<?php echo BASE_URL; ?>/cca" class="tab-item active">CCAs</a>
                    <a href="<?php echo BASE_URL; ?>/history" class="tab-item">History</a>
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

                <?php if (isset($_SESSION['info'])): ?>
                    <div class="alert alert-info">
                        <?php
                        echo $_SESSION['info'];
                        unset($_SESSION['info']);
                        ?>
                    </div>
                <?php endif; ?>

                <div class="back-link">
                    <a href="<?php echo BASE_URL; ?>/cca/<?php echo $clubSlug; ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 12H5M12 19l-7-7 7-7" />
                        </svg>
                        Back to Club Details
                    </a>
                </div>

                <div class="location-container">
                    <div class="location-header">
                        <h1>Attendance Check-in</h1>
                        <p>Checking in at: <strong><?php echo htmlspecialchars($locationName); ?></strong></p>
                    </div>

                    <div class="location-content">
                        <div class="map-container">
                            <div id="map"></div>
                            <div id="check-in-overlay" class="check-in-overlay" style="display: none;">
                                <div class="check-in-card">
                                    <div class="check-in-icon success">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                    </div>
                                    <div class="check-in-message">
                                        <h3>You've arrived!</h3>
                                        <p>You're within the club's location radius</p>
                                    </div>
                                    <form id="check-in-form" method="POST" action="<?php echo BASE_URL; ?>/attendance/check-in">
                                        <input type="hidden" name="club_id" value="<?php echo htmlspecialchars($clubId); ?>">
                                        <input type="hidden" name="lat" id="user-lat">
                                        <input type="hidden" name="lng" id="user-lng">
                                        <button type="submit" class="check-in-btn" id="check-in-submit-btn">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M9 11l3 3L22 4"></path>
                                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                                            </svg>
                                            Check In
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="location-info">
                            <div class="info-card">
                                <h3>Your Location</h3>
                                <p id="current-location">Detecting your location...</p>
                                <div class="location-status" id="location-status">
                                    <div class="status-icon pending">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                    </div>
                                    <span>Waiting for GPS signal...</span>
                                </div>
                            </div>

                            <div class="info-card">
                                <h3>Destination</h3>
                                <p><?php echo htmlspecialchars($locationName); ?></p>
                                <div class="coordinates">
                                    <span>Latitude: <?php echo $destLat; ?></span>
                                    <span>Longitude: <?php echo $destLng; ?></span>
                                </div>
                            </div>

                            <div class="info-card">
                                <h3>Check-in Status</h3>
                                <div class="check-in-status pending" id="check-in-status">
                                    <div class="status-icon pending">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                    </div>
                                    <span>Checking distance to location...</span>
                                </div>
                                <div class="distance-info" id="distance-info">
                                    <div class="distance-value">--</div>
                                    <div class="distance-label">meters away</div>
                                </div>
                            </div>

                            <div class="button-container">
                                <button id="directions-btn" class="primary-btn white-text-button" disabled style="color: white !important;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="color: white !important;">
                                        <polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>
                                    </svg>
                                    <span style="color: white !important;">Get Directions</span>
                                </button>
                                <button id="clear-route-btn" class="secondary-btn" style="display: none;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="15" y1="9" x2="9" y2="15"></line>
                                        <line x1="9" y1="9" x2="15" y2="15"></line>
                                    </svg>
                                    Clear Route
                                </button>
                                <button id="use-current-location" class="secondary-btn">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    Refresh Location
                                </button>
                                <button id="request-permission" class="secondary-btn permission-btn">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                    Request Location Permission
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Floating check-in button -->
    <button id="floating-check-in-btn" class="floating-check-in-btn" style="display: none;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 11l3 3L22 4"></path>
            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
        </svg>
    </button>
    
    <!-- Popup notification for check-in results -->
    <div id="notification-modal" class="notification-modal" style="display: none;">
        <div class="notification-content">
            <span class="notification-close">&times;</span>
            <div class="notification-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <h3 id="notification-title">Success!</h3>
            <p id="notification-message">Your attendance has been recorded successfully.</p>
            <button id="notification-ok-btn" class="primary-btn">OK</button>
        </div>
    </div>
    
    <script>
        // Define constants
        const CHECK_IN_RADIUS = 100; // meters - distance within which users can check in
        
        // Define destination coordinates from PHP
        const destinationLat = <?php echo $destLat; ?>;
        const destinationLng = <?php echo $destLng; ?>;
        const destinationName = "<?php echo addslashes(htmlspecialchars($locationName)); ?>";
        
        // Get DOM elements
        const locationStatus = document.getElementById('location-status');
        const currentLocationText = document.getElementById('current-location');
        const distanceInfo = document.getElementById('distance-info');
        const directionsBtn = document.getElementById('directions-btn');
        const useCurrentLocationBtn = document.getElementById('use-current-location');
        const checkInStatus = document.getElementById('check-in-status');
        const checkInOverlay = document.getElementById('check-in-overlay');
        const userLatInput = document.getElementById('user-lat');
        const userLngInput = document.getElementById('user-lng');
        
        // Variables to store user location data
        let userLat, userLng;
        let map, userMarker, destMarker;
        let locationRadiusCircle;
        let directionsService, directionsRenderer;
        let watchId; // For continuous location tracking
        
        // Initialize the map and related services
        function initMap() {
            // Create a new map centered on destination
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: destinationLat, lng: destinationLng },
                zoom: 16,
                mapTypeId: 'roadmap',
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true,
                zoomControl: true,
                styles: [
                    {
                        "featureType": "poi",
                        "elementType": "labels.icon",
                        "stylers": [
                            {
                                "visibility": "simplified"
                            }
                        ]
                    },
                    {
                        "featureType": "poi.business",
                        "stylers": [
                            {
                                "visibility": "off"
                            }
                        ]
                    },
                    {
                        "featureType": "poi.park",
                        "elementType": "labels.text",
                        "stylers": [
                            {
                                "visibility": "simplified"
                            }
                        ]
                    },
                    {
                        "featureType": "water",
                        "elementType": "geometry.fill",
                        "stylers": [
                            {
                                "color": "#d3e0f4"
                            }
                        ]
                    }
                ]
            });
            
            // Create destination marker
            destMarker = new google.maps.Marker({
                position: { lat: destinationLat, lng: destinationLng },
                map: map,
                title: destinationName,
                animation: google.maps.Animation.DROP,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: '#FF6B6B',
                    fillOpacity: 1,
                    strokeColor: '#FFF',
                    strokeWeight: 2,
                    scale: 10
                }
            });
            
            // Add info window to destination marker
            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div class="map-info-window">
                        <div class="info-window-header">${destinationName}</div>
                        <div class="info-window-content">Club meeting location</div>
                    </div>
                `
            });
            
            destMarker.addListener('click', function() {
                infoWindow.open(map, destMarker);
            });
            
            // Create a circle to show check-in radius
            locationRadiusCircle = new google.maps.Circle({
                strokeColor: '#FF6B6B',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#FF6B6B',
                fillOpacity: 0.1,
                map: map,
                center: { lat: destinationLat, lng: destinationLng },
                radius: CHECK_IN_RADIUS
            });
            
            // Initialize the Directions service
            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({
                suppressMarkers: true,
                polylineOptions: {
                    strokeColor: '#4285F4',
                    strokeWeight: 5,
                    strokeOpacity: 0.7
                }
            });
            directionsRenderer.setMap(map);
            
            // Start tracking user location with a slight delay to ensure the map is loaded
            setTimeout(getUserLocation, 1000);
        }
        
        // Function to get and continuously track user location
        function getUserLocation() {
            // Update UI to show location is being fetched
            showLocationStatus('pending', 'Detecting your location...');
            showCheckInStatus('pending', 'Checking distance to location...');
            
            // Clear previous watch if it exists
            if (watchId) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }
            
            if (navigator.geolocation) {
                // First test if we have permission by getting a single position
                try {
                    // Set a timeout to detect if we're not getting a prompt response
                    let permissionCheckTimeout = setTimeout(function() {
                        // If we reach this point without the clearTimeout being called,
                        // it likely means the permission prompt is being shown but user hasn't responded
                        showLocationStatus('pending', 'Please allow location access in the browser prompt');
                    }, 500);
                    
                    navigator.geolocation.getCurrentPosition(
                        // Success callback - we have permission
                        function(position) {
                            clearTimeout(permissionCheckTimeout);
                            updatePosition(position);
                            
                            // Now that we have permission, set up continuous tracking
                            watchId = navigator.geolocation.watchPosition(
                                position => updatePosition(position),
                                error => handleLocationError(error),
                                { 
                                    enableHighAccuracy: true, 
                                    timeout: 10000, 
                                    maximumAge: 0 
                                }
                            );
                        },
                        // Error callback
                        function(error) {
                            clearTimeout(permissionCheckTimeout);
                            handleLocationError(error);
                            
                            // If it's a permission error, show a prompt explaining how to enable location
                            if (error.code === error.PERMISSION_DENIED) {
                                console.log("Location permission denied");
                                // Show custom message with instructions based on browser
                                const browser = detectBrowser();
                                showPermissionInstructions(browser);
                            }
                        },
                        // Options
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0
                        }
                    );
                } catch (e) {
                    // Handle any exceptions that might occur
                    console.error("Error accessing geolocation:", e);
                    showLocationStatus('error', 'Error accessing location services');
                    showCheckInStatus('error', 'Cannot determine if you can check in');
                }
            } else {
                showLocationStatus('error', 'Geolocation is not supported by your browser');
                showCheckInStatus('error', 'Unable to determine your position');
            }
        }
        
        // Helper function to detect browser for permission instructions
        function detectBrowser() {
            const userAgent = navigator.userAgent.toLowerCase();
            
            if (userAgent.indexOf('opr/') > -1 || userAgent.indexOf('opera/') > -1) {
                return 'opera';
            } else if (userAgent.indexOf('chrome') > -1) {
                return 'chrome';
            } else if (userAgent.indexOf('firefox') > -1) {
                return 'firefox';
            } else if (userAgent.indexOf('safari') > -1) {
                return 'safari';
            } else if (userAgent.indexOf('edge') > -1 || userAgent.indexOf('edg') > -1) {
                return 'edge';
            } else {
                return 'unknown';
            }
        }
        
        // Show browser-specific instructions for enabling location
        function showPermissionInstructions(browser) {
            let instructions = '';
            
            switch(browser) {
                case 'chrome':
                    instructions = `
                        <div class="permission-instructions">
                            <h4>Enable Location in Chrome</h4>
                            <ol>
                                <li>Click the lock/info icon in the address bar</li>
                                <li>Find "Location" and select "Allow"</li>
                                <li>Refresh this page</li>
                            </ol>
                        </div>
                    `;
                    break;
                case 'opera':
                    instructions = `
                        <div class="permission-instructions">
                            <h4>Enable Location in Opera/OperaGX</h4>
                            <ol>
                                <li>Click the lock/shield icon in the address bar</li>
                                <li>Find "Site settings" and click on it</li>
                                <li>Under "Permissions", set "Location" to "Allow"</li>
                                <li>Refresh this page</li>
                            </ol>
                        </div>
                    `;
                    break;
                case 'firefox':
                    instructions = `
                        <div class="permission-instructions">
                            <h4>Enable Location in Firefox</h4>
                            <ol>
                                <li>Click the lock/shield icon in the address bar</li>
                                <li>Go to "Permissions" and enable "Access Your Location"</li>
                                <li>Refresh this page</li>
                            </ol>
                        </div>
                    `;
                    break;
                case 'safari':
                    instructions = `
                        <div class="permission-instructions">
                            <h4>Enable Location in Safari</h4>
                            <ol>
                                <li>Open Safari Preferences</li>
                                <li>Go to "Websites" tab and find "Location"</li>
                                <li>Allow this website to access your location</li>
                                <li>Refresh this page</li>
                            </ol>
                        </div>
                    `;
                    break;
                case 'edge':
                    instructions = `
                        <div class="permission-instructions">
                            <h4>Enable Location in Edge</h4>
                            <ol>
                                <li>Click the lock/info icon in the address bar</li>
                                <li>Find "Location" permission and select "Allow"</li>
                                <li>Refresh this page</li>
                            </ol>
                        </div>
                    `;
                    break;
                default:
                    instructions = `
                        <div class="permission-instructions">
                            <h4>Enable Location Access</h4>
                            <ol>
                                <li>Check your browser settings to allow location access</li>
                                <li>Look for location permissions in the address bar or site settings</li>
                                <li>Refresh this page after enabling location</li>
                            </ol>
                        </div>
                    `;
            }
            
            // Remove any existing permission dialog first
            const existingDialog = document.querySelector('.permission-dialog');
            if (existingDialog) {
                existingDialog.remove();
            }
            
            // Create a permission dialog to show
            const permissionDialog = document.createElement('div');
            permissionDialog.className = 'permission-dialog';
            permissionDialog.innerHTML = `
                <div class="permission-dialog-content">
                    <div class="permission-dialog-header">
                        <h3>Location Access Required</h3>
                        <p>To check in, we need access to your location. Your browser has blocked this permission.</p>
                    </div>
                    ${instructions}
                    <div class="permission-dialog-footer">
                        <button id="refresh-page-btn" class="primary-btn">Refresh Page</button>
                        <button id="try-again-btn" class="secondary-btn">Try Again</button>
                        <button id="dismiss-dialog-btn" class="secondary-btn">Dismiss</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(permissionDialog);
            
            // Add event listeners to buttons
            document.getElementById('refresh-page-btn').addEventListener('click', function() {
                window.location.reload();
            });
            
            document.getElementById('try-again-btn').addEventListener('click', function() {
                permissionDialog.remove();
                getUserLocation();
            });
            
            document.getElementById('dismiss-dialog-btn').addEventListener('click', function() {
                permissionDialog.remove();
            });
        }
        
        // Function to update position data
        function updatePosition(position) {
            userLat = position.coords.latitude;
            userLng = position.coords.longitude;
            
            // Store in hidden inputs for form submission
            if (userLatInput) {
                userLatInput.value = userLat;
                console.log("Set userLatInput value to:", userLat);
            } else {
                console.error("userLatInput element not found");
            }
            
            if (userLngInput) {
                userLngInput.value = userLng;
                console.log("Set userLngInput value to:", userLng);
            } else {
                console.error("userLngInput element not found");
            }
            
            // Update status and UI
            showLocationStatus('success', 'Location found');
            
            // Update location text
            currentLocationText.textContent = `Lat: ${userLat.toFixed(6)}, Lng: ${userLng.toFixed(6)}`;
            
            // Update map
            updateUserLocationOnMap(userLat, userLng, position.coords.accuracy);
            
            // Check distance and update check-in status
            checkDistanceForCheckIn(userLat, userLng);
            
            // Enable directions button
            directionsBtn.removeAttribute('disabled');
        }
        
        // Function to handle location errors
        function handleLocationError(error) {
            let errorMessage = 'Unable to retrieve your location';
            
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage = 'Location access denied. Please check your browser permissions.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage = 'Location information unavailable at this time.';
                    break;
                case error.TIMEOUT:
                    errorMessage = 'Location request timed out. Please try again.';
                    break;
            }
            
            showLocationStatus('error', errorMessage);
            showCheckInStatus('error', 'Cannot determine if you can check in');
        }
        
        // Function to update user's location on map
        function updateUserLocationOnMap(lat, lng, accuracy) {
            // Ensure lat and lng are valid numbers
            lat = parseFloat(lat);
            lng = parseFloat(lng);
            accuracy = parseFloat(accuracy);
            
            if (isNaN(lat) || isNaN(lng)) {
                console.error('Invalid coordinates provided to updateUserLocationOnMap:', lat, lng);
                return;
            }
            
            console.log('Updating user location on map:', lat, lng, accuracy);
            
            const userLatLng = new google.maps.LatLng(lat, lng);
            
            // If marker already exists, update its position
            if (userMarker) {
                userMarker.setPosition(userLatLng);
            } else {
                // Create a custom HTML marker for user location
                const markerElement = document.createElement('div');
                markerElement.className = 'user-location-marker';
                markerElement.innerHTML = `
                    <div class="marker-pulse"></div>
                    <div class="marker-dot"></div>
                `;
                
                // Create new marker
                userMarker = new google.maps.Marker({
                    position: userLatLng,
                    map: map,
                    title: 'Your Location',
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        fillColor: '#4285F4',
                        fillOpacity: 1,
                        strokeColor: '#FFF',
                        strokeWeight: 2,
                        scale: 8
                    },
                    animation: google.maps.Animation.DROP
                });
                
                // Add info window to user marker
                const userInfoWindow = new google.maps.InfoWindow({
                    content: `
                        <div class="map-info-window">
                            <div class="info-window-header">Your Location</div>
                            <div class="info-window-content">Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}</div>
                        </div>
                    `
                });
                
                userMarker.addListener('click', function() {
                    userInfoWindow.open(map, userMarker);
                });
                
                // Add an accuracy circle around the user marker
                const accuracyCircle = new google.maps.Circle({
                    strokeColor: '#4285F4',
                    strokeOpacity: 0.2,
                    strokeWeight: 1,
                    fillColor: '#4285F4',
                    fillOpacity: 0.1,
                    map: map,
                    center: userLatLng,
                    radius: accuracy,
                    zIndex: 1
                });
            }
            
            // Fit map to show both markers
            const bounds = new google.maps.LatLngBounds();
            bounds.extend(userLatLng);
            bounds.extend(new google.maps.LatLng(destinationLat, destinationLng));
            map.fitBounds(bounds);
            
            // If zoom is too high, limit it
            const listener = google.maps.event.addListenerOnce(map, 'idle', function() {
                if (map.getZoom() > 16) {
                    map.setZoom(16);
                }
            });
        }
        
        // Function to check distance and update check-in status
        function checkDistanceForCheckIn(lat, lng) {
            const userLatLng = new google.maps.LatLng(lat, lng);
            const destLatLng = new google.maps.LatLng(destinationLat, destinationLng);
            
            // Calculate distance in meters
            const distance = google.maps.geometry.spherical.computeDistanceBetween(userLatLng, destLatLng);
            
            // Update distance display
            distanceInfo.innerHTML = `
                <div class="distance-value">${Math.round(distance)}</div>
                <div class="distance-label">meters away</div>
            `;
            
            // Check if user is within check-in radius
            if (distance <= CHECK_IN_RADIUS) {
                // User can check in
                showCheckInStatus('success', 'You can check in now!');
                
                // Show check-in overlay
                checkInOverlay.style.display = 'flex';
                
                // Also enable floating check-in button for mobile users who may scroll away
                const floatingCheckInBtn = document.getElementById('floating-check-in-btn');
                if (floatingCheckInBtn) {
                    floatingCheckInBtn.style.display = 'flex';
                    
                    // Add click event to the floating button
                    floatingCheckInBtn.addEventListener('click', function() {
                        // Scroll to the check-in overlay
                        document.querySelector('.map-container').scrollIntoView({ 
                            behavior: 'smooth' 
                        });
                    });
                }
                
                // Show a helpful notification if it's the first time the overlay appears
                if (sessionStorage.getItem('checkInOverlayShown') !== 'true') {
                    setTimeout(() => {
                        showNotification('success', 'You\'ve Arrived!', 
                            'You\'re within the check-in radius. Click the "Check In" button to record your attendance.');
                        sessionStorage.setItem('checkInOverlayShown', 'true');
                    }, 1000);
                }
            } else {
                // User needs to get closer
                const remainingDistance = Math.round(distance - CHECK_IN_RADIUS);
                showCheckInStatus('warning', `Get ${remainingDistance}m closer to check in`);
                
                // Hide check-in overlay
                checkInOverlay.style.display = 'none';
                
                // Hide floating check-in button
                const floatingCheckInBtn = document.getElementById('floating-check-in-btn');
                if (floatingCheckInBtn) {
                    floatingCheckInBtn.style.display = 'none';
                }
                
                // If they're very far away (more than 500m), show a helpful notification
                // but only once per page load 
                if (distance > 500 && sessionStorage.getItem('farAwayNotificationShown') !== 'true') {
                    setTimeout(() => {
                        showNotification('info', 'You\'re Too Far Away', 
                            `You are ${Math.round(distance)}m from the check-in location. ` + 
                            `You need to be within ${CHECK_IN_RADIUS}m to check in. Use the "Get Directions" button to find your way.`);
                        sessionStorage.setItem('farAwayNotificationShown', 'true');
                    }, 1500);
                }
            }
        }
        
        // Function to show location status
        function showLocationStatus(type, message) {
            if (!locationStatus) return;
            
            let icon;
            switch(type) {
                case 'pending':
                    icon = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>`;
                    break;
                case 'success':
                    icon = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>`;
                    break;
                case 'error':
                    icon = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>`;
                    break;
            }
            
            locationStatus.innerHTML = `
                <div class="status-icon ${type}">
                    ${icon}
                </div>
                <span>${message}</span>
            `;
        }
        
        // Function to show check-in status
        function showCheckInStatus(type, message) {
            if (!checkInStatus) return;
            
            let icon;
            switch(type) {
                case 'pending':
                    icon = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>`;
                    break;
                case 'success':
                    icon = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>`;
                    break;
                case 'warning':
                    icon = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>`;
                    break;
                case 'info':
                    icon = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>`;
                    break;
                case 'error':
                    icon = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>`;
                    break;
            }
            
            checkInStatus.innerHTML = `
                <div class="status-icon ${type}">
                    ${icon}
                </div>
                <span>${message}</span>
            `;
            
            // Also update class for styling
            checkInStatus.className = 'check-in-status';
            checkInStatus.classList.add(type);
        }
        
        // Function to get directions
        function getDirections() {
            if (!userLat || !userLng) return;
            
            const userLatLng = new google.maps.LatLng(userLat, userLng);
            const destLatLng = new google.maps.LatLng(destinationLat, destinationLng);
            
            // Show loading state
            showCheckInStatus('info', 'Calculating best route...');
            
            const request = {
                origin: userLatLng,
                destination: destLatLng,
                travelMode: google.maps.TravelMode.WALKING
            };
            
            directionsService.route(request, function(result, status) {
                if (status == 'OK') {
                    // Display the route on the map
                    directionsRenderer.setMap(map);
                    directionsRenderer.setDirections(result);
                    
                    // Extract and display duration and distance
                    const duration = result.routes[0].legs[0].duration.text;
                    const distance = result.routes[0].legs[0].distance.text;
                    
                    // Update status with walking time information
                    showCheckInStatus('info', `Walking time: ${duration} (${distance})`);
                    
                    // Show the clear route button
                    const clearRouteBtn = document.getElementById('clear-route-btn');
                    if (clearRouteBtn) {
                        clearRouteBtn.style.display = 'block';
                    }
                } else {
                    showCheckInStatus('error', 'Could not calculate directions');
                }
            });
        }
        
        // Function to clear the route from the map
        function clearRoute() {
            directionsRenderer.setMap(null);
            const clearRouteBtn = document.getElementById('clear-route-btn');
            if (clearRouteBtn) {
                clearRouteBtn.style.display = 'none';
            }
            
            // Reset the check-in status
            if (userLat && userLng) {
                checkDistanceForCheckIn(userLat, userLng);
            } else {
                showCheckInStatus('pending', 'Checking distance to location...');
            }
        }
        
        // Event listeners
        if (useCurrentLocationBtn) {
            useCurrentLocationBtn.addEventListener('click', getUserLocation);
        }
        
        const requestPermissionBtn = document.getElementById('request-permission');
        if (requestPermissionBtn) {
            requestPermissionBtn.addEventListener('click', function() {
                // Try to force the permission dialog by explicitly requesting location
                if (navigator.geolocation) {
                    showLocationStatus('pending', 'Requesting location permission...');
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            // Success - we have permission
                            updatePosition(position);
                            // Hide the permission button as it's no longer needed
                            requestPermissionBtn.style.display = 'none';
                        },
                        function(error) {
                            // Still no permission, show the instructions
                            handleLocationError(error);
                            if (error.code === error.PERMISSION_DENIED) {
                                const browser = detectBrowser();
                                showPermissionInstructions(browser);
                            }
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0
                        }
                    );
                } else {
                    alert('Geolocation is not supported by your browser');
                }
            });
        }
        
        if (directionsBtn) {
            directionsBtn.addEventListener('click', function() {
                if (userLat && userLng) {
                    // Make sure text stays white
                    this.style.color = 'white';
                    this.querySelector('span').style.color = 'white';
                    this.querySelector('svg').style.color = 'white';
                    
                    // Get directions on the map
                    getDirections();
                    
                    // Show a tooltip with option to open in Google Maps
                    const directionsTooltip = document.createElement('div');
                    directionsTooltip.className = 'directions-tooltip';
                    directionsTooltip.innerHTML = `
                        <p>Route displayed on map. Open in Google Maps for turn-by-turn navigation?</p>
                        <div class="tooltip-actions">
                            <button id="open-google-maps" class="primary-btn">Open Google Maps</button>
                            <button id="close-tooltip" class="secondary-btn">No Thanks</button>
                        </div>
                    `;
                    
                    // Add tooltip to page
                    document.body.appendChild(directionsTooltip);
                    
                    // Show tooltip with animation
                    setTimeout(() => {
                        directionsTooltip.classList.add('visible');
                    }, 100);
                    
                    // Handle tooltip buttons
                    document.getElementById('open-google-maps').addEventListener('click', function() {
                        const url = `https://www.google.com/maps/dir/?api=1&origin=${userLat},${userLng}&destination=${destinationLat},${destinationLng}&travelmode=walking`;
                        window.open(url, '_blank');
                        directionsTooltip.remove();
                    });
                    
                    document.getElementById('close-tooltip').addEventListener('click', function() {
                        directionsTooltip.classList.remove('visible');
                        setTimeout(() => {
                            directionsTooltip.remove();
                        }, 300);
                    });
                    
                    // Auto-hide tooltip after 15 seconds
                    setTimeout(() => {
                        if (document.body.contains(directionsTooltip)) {
                            directionsTooltip.classList.remove('visible');
                            setTimeout(() => {
                                if (document.body.contains(directionsTooltip)) {
                                    directionsTooltip.remove();
                                }
                            }, 300);
                        }
                    }, 15000);
                }
            });
        }
        
        // Clear route button
        const clearRouteBtn = document.getElementById('clear-route-btn');
        if (clearRouteBtn) {
            clearRouteBtn.addEventListener('click', clearRoute);
        }
        
        // Profile dropdown toggle
        const profileTrigger = document.querySelector('.profile-trigger');
        const dropdownMenu = document.querySelector('.dropdown-menu');
        
        if (profileTrigger && dropdownMenu) {
            profileTrigger.addEventListener('click', function() {
                dropdownMenu.classList.toggle('show');
                this.setAttribute('aria-expanded', dropdownMenu.classList.contains('show'));
            });
            
            document.addEventListener('click', function(event) {
                if (!profileTrigger.contains(event.target) && !dropdownMenu.contains(event.target)) {
                    dropdownMenu.classList.remove('show');
                    profileTrigger.setAttribute('aria-expanded', 'false');
                }
            });
        }
        
        // Initialize map when the page loads
        window.onload = function() {
            // Clear any potential "undefined" text by setting initial status
            showCheckInStatus('pending', 'Waiting for location...');
            
            // Set initial distance display to prevent layout shifts
            distanceInfo.innerHTML = `
                <div class="distance-value">--</div>
                <div class="distance-label">meters away</div>
            `;
            
            // Initialize the map
            initMap();
        };
        
        // Function to show notification popup
        function showNotification(type, title, message) {
            const modal = document.getElementById('notification-modal');
            const iconDiv = modal.querySelector('.notification-icon');
            const titleEl = document.getElementById('notification-title');
            const messageEl = document.getElementById('notification-message');
            
            // Reset classes
            iconDiv.className = 'notification-icon';
            
            // Set icon and colors based on type
            if (type === 'success') {
                iconDiv.innerHTML = '<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
            } else if (type === 'error') {
                iconDiv.className += ' error';
                iconDiv.innerHTML = '<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
            } else if (type === 'warning') {
                iconDiv.className += ' warning';
                iconDiv.innerHTML = '<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#ffc107" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
            } else if (type === 'info') {
                iconDiv.className += ' info';
                iconDiv.innerHTML = '<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#17a2b8" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
            }
            
            titleEl.textContent = title;
            messageEl.textContent = message;
            
            // Show modal
            modal.style.display = 'block';
            
            // Add event listeners for closing
            const closeBtn = document.querySelector('.notification-close');
            const okBtn = document.getElementById('notification-ok-btn');
            
            closeBtn.onclick = function() {
                modal.style.display = 'none';
            };
            
            okBtn.onclick = function() {
                modal.style.display = 'none';
            };
            
            // Close when clicking outside the modal
            window.onclick = function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            };
        }
    </script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get the check in form
            const checkInForm = document.querySelector('#check-in-form');
            const floatingCheckInBtn = document.getElementById('floating-check-in-btn');
            
            if (checkInForm) {
                checkInForm.addEventListener('submit', function(e) {
                    e.preventDefault(); // Prevent normal form submission
                    
                    // Prevent form submission if coordinates are not set
                    if (!userLatInput.value || !userLngInput.value) {
                        showNotification('error', 'Error', 'Could not get your location. Please try refreshing your location.');
                        return false;
                    }
                    
                    // Disable the submit button to prevent double submission
                    const submitBtn = document.getElementById('check-in-submit-btn');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = `
                            <svg class="spinner" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0z"></path>
                            </svg>
                            Checking In...
                        `;
                    }
                    
                    // Get form data
                    const formData = new FormData(checkInForm);
                    
                    // Make AJAX request
                    fetch(checkInForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text();
                    })
                    .then(html => {
                        // Success - show a popup
                        showNotification('success', 'Success!', 'Your attendance has been recorded successfully.');
                        
                        // Hide the check-in overlay
                        if (checkInOverlay) {
                            checkInOverlay.style.display = 'none';
                        }
                        
                        // Hide floating button
                        if (floatingCheckInBtn) {
                            floatingCheckInBtn.style.display = 'none';
                        }
                        
                        // Update the check-in status
                        showCheckInStatus('success', 'You have checked in successfully!');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('error', 'Error', 'Failed to record attendance. Please try again.');
                    })
                    .finally(() => {
                        // Re-enable the submit button
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = `
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 11l3 3L22 4"></path>
                                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                                </svg>
                                Check In
                            `;
                        }
                    });
                });
            }
            
            // Make the floating check-in button trigger the form submission
            if (floatingCheckInBtn) {
                floatingCheckInBtn.addEventListener('click', function() {
                    // Scroll to the check-in overlay
                    document.querySelector('.map-container').scrollIntoView({ 
                        behavior: 'smooth' 
                    });
                    
                    // Trigger the form submission after a short delay
                    setTimeout(() => {
                        const submitBtn = document.getElementById('check-in-submit-btn');
                        if (submitBtn) {
                            submitBtn.click();
                        }
                    }, 500);
                });
            }
            
            // Check for success/error/info messages on page load
            <?php if (isset($_SESSION['success'])): ?>
                showNotification('success', 'Success!', '<?php echo addslashes($_SESSION['success']); ?>');
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                showNotification('error', 'Error', '<?php echo addslashes($_SESSION['error']); ?>');
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['info'])): ?>
                showNotification('info', 'Information', '<?php echo addslashes($_SESSION['info']); ?>');
                <?php unset($_SESSION['info']); ?>
            <?php endif; ?>
        });
    </script>

    <!-- Add this right before the closing body tag -->
    <script>
        // Check if dark mode was set in sessionStorage by other pages
        document.addEventListener('DOMContentLoaded', function() {
            const body = document.body;
            if (sessionStorage.getItem('darkMode') === 'true' && !body.classList.contains('dark')) {
                body.classList.add('dark');
            }
            
            // Listen for dark mode changes from other pages
            window.addEventListener('storage', function(e) {
                if (e.key === 'darkMode') {
                    if (e.newValue === 'true') {
                        body.classList.add('dark');
                    } else {
                        body.classList.remove('dark');
                    }
                }
            });
        });
    </script>
</body>

</html> 
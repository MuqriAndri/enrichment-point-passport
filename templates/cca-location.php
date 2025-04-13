<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL);
    exit();
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
    <title>Enrichment Point Passport - Location Tracker</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/cca-details.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/cca-location.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
</head>

<body>
    <div class="dashboard-container">
        <!-- Top Navigation Bar -->
        <nav class="top-nav">
            <div class="nav-left">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo/politeknik-brunei-logo.png" alt="PB Logo" class="nav-logo">
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
                        <h1>Location Tracker</h1>
                        <p>Finding your way to: <strong><?php echo htmlspecialchars($locationName); ?></strong></p>
                    </div>

                    <div class="location-content">
                        <div class="map-container">
                            <div id="map"></div>
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
                                <h3>Distance</h3>
                                <div class="distance-info" id="distance-info">
                                    <div class="distance-value">--</div>
                                    <div class="distance-label">kilometers away</div>
                                </div>
                            </div>

                            <div class="button-container">
                                <button id="directions-btn" class="primary-btn" disabled>
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>
                                    </svg>
                                    Get Directions
                                </button>
                                <button id="refresh-location-btn" class="secondary-btn">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M23 4v6h-6"></path>
                                        <path d="M1 20v-6h6"></path>
                                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10"></path>
                                        <path d="M20.49 15a9 9 0 0 1-14.85 3.36L1 14"></path>
                                    </svg>
                                    Refresh Location
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
        // Define destination coordinates from PHP
        const destinationLat = <?php echo $destLat; ?>;
        const destinationLng = <?php echo $destLng; ?>;
        const destinationName = "<?php echo addslashes(htmlspecialchars($locationName)); ?>";
        
        // Initialize map centered on Brunei
        const map = L.map('map').setView([4.8856000, 114.9370000], 13);
        
        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Add destination marker
        const destinationMarker = L.marker([destinationLat, destinationLng]).addTo(map);
        destinationMarker.bindPopup(`<b>${destinationName}</b><br>Your destination`).openPopup();
        
        // Variables to store user location data
        let userLat, userLng;
        let userLocationMarker;
        let locationCircle;
        let polyline;
        
        // Get user's location
        const locationStatus = document.getElementById('location-status');
        const currentLocationText = document.getElementById('current-location');
        const distanceInfo = document.getElementById('distance-info');
        const directionsBtn = document.getElementById('directions-btn');
        const refreshLocationBtn = document.getElementById('refresh-location-btn');
        
        // Function to calculate distance between two points
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Radius of the earth in km
            const dLat = deg2rad(lat2 - lat1);
            const dLon = deg2rad(lon2 - lon1);
            const a = 
                Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
                Math.sin(dLon/2) * Math.sin(dLon/2); 
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
            const distance = R * c; // Distance in km
            return distance;
        }
        
        function deg2rad(deg) {
            return deg * (Math.PI/180);
        }
        
        // Function to get user's location
        function getUserLocation() {
            // Update UI to show location is being fetched
            locationStatus.innerHTML = `
                <div class="status-icon pending">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <span>Detecting your location...</span>
            `;
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    // Success callback
                    function(position) {
                        userLat = position.coords.latitude;
                        userLng = position.coords.longitude;
                        
                        // Update status
                        locationStatus.innerHTML = `
                            <div class="status-icon success">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                            </div>
                            <span>Location found</span>
                        `;
                        
                        // Update location text
                        currentLocationText.textContent = `Lat: ${userLat.toFixed(6)}, Lng: ${userLng.toFixed(6)}`;
                        
                        // Update map
                        updateUserLocationOnMap(userLat, userLng, position.coords.accuracy);
                        
                        // Calculate and display distance
                        const distance = calculateDistance(userLat, userLng, destinationLat, destinationLng);
                        distanceInfo.innerHTML = `
                            <div class="distance-value">${distance.toFixed(2)}</div>
                            <div class="distance-label">kilometers away</div>
                        `;
                        
                        // Enable directions button
                        directionsBtn.removeAttribute('disabled');
                    },
                    // Error callback
                    function(error) {
                        locationStatus.innerHTML = `
                            <div class="status-icon error">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                            </div>
                            <span>Error: ${getLocationErrorMessage(error)}</span>
                        `;
                    },
                    // Options
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            } else {
                locationStatus.innerHTML = `
                    <div class="status-icon error">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <span>Geolocation is not supported by your browser</span>
                `;
            }
        }
        
        // Function to get readable error message
        function getLocationErrorMessage(error) {
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    return "User denied the request for geolocation";
                case error.POSITION_UNAVAILABLE:
                    return "Location information is unavailable";
                case error.TIMEOUT:
                    return "The request to get user location timed out";
                case error.UNKNOWN_ERROR:
                    return "An unknown error occurred";
                default:
                    return "Error getting location";
            }
        }
        
        // Function to update user's location on map
        function updateUserLocationOnMap(lat, lng, accuracy) {
            // If marker already exists, update its position
            if (userLocationMarker) {
                userLocationMarker.setLatLng([lat, lng]);
            } else {
                // Create new marker
                userLocationMarker = L.marker([lat, lng], {
                    icon: L.divIcon({
                        className: 'user-location-marker',
                        html: '<div class="marker-dot"></div><div class="marker-pulse"></div>',
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    })
                }).addTo(map);
                userLocationMarker.bindPopup('<b>Your Location</b>');
            }
            
            // Update accuracy circle
            if (locationCircle) {
                locationCircle.setLatLng([lat, lng]);
                locationCircle.setRadius(accuracy);
            } else {
                locationCircle = L.circle([lat, lng], {
                    radius: accuracy,
                    color: 'rgba(66, 133, 244, 0.2)',
                    fillColor: 'rgba(66, 133, 244, 0.1)',
                    fillOpacity: 0.5
                }).addTo(map);
            }
            
            // Draw line between user and destination
            if (polyline) {
                map.removeLayer(polyline);
            }
            
            polyline = L.polyline([[lat, lng], [destinationLat, destinationLng]], {
                color: '#4285F4',
                weight: 3,
                opacity: 0.7,
                dashArray: '5, 10'
            }).addTo(map);
            
            // Fit map to show both markers
            const bounds = L.latLngBounds([
                [lat, lng],
                [destinationLat, destinationLng]
            ]);
            map.fitBounds(bounds, { padding: [50, 50] });
        }
        
        // Initialize location tracking
        getUserLocation();
        
        // Event listeners
        refreshLocationBtn.addEventListener('click', getUserLocation);
        
        directionsBtn.addEventListener('click', function() {
            if (userLat && userLng) {
                // Open Google Maps for directions
                const url = `https://www.google.com/maps/dir/?api=1&origin=${userLat},${userLng}&destination=${destinationLat},${destinationLng}`;
                window.open(url, '_blank');
            }
        });
        
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
    </script>
</body>

</html> 
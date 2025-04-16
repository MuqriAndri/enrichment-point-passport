/**
 * Location Check-in Functionality
 * Handles GPS tracking, distance calculation, and check-in features
 */

// Variables to be initialized by the PHP
let CHECK_IN_RADIUS; 
let destinationLat;
let destinationLng;
let destinationName;

// DOM elements
let locationStatus;
let currentLocationText;
let distanceInfo;
let directionsBtn;
let useCurrentLocationBtn;
let checkInStatus;
let checkInOverlay;
let userLatInput;
let userLngInput;

// State variables
let userLat, userLng;
let map, userMarker, destMarker;
let locationRadiusCircle;
let directionsService, directionsRenderer;
let watchId; // For continuous location tracking

/**
 * Initialize the location check-in functionality
 * @param {Object} config - Configuration parameters
 */
function initLocationCheckIn(config) {
    // Set up configuration
    CHECK_IN_RADIUS = config.checkInRadius || 100;
    destinationLat = config.destinationLat || 4.8856000;
    destinationLng = config.destinationLng || 114.9370000;
    destinationName = config.destinationName || 'Destination';
    
    // Get DOM elements
    locationStatus = document.getElementById('location-status');
    currentLocationText = document.getElementById('current-location');
    distanceInfo = document.getElementById('distance-info');
    directionsBtn = document.getElementById('directions-btn');
    useCurrentLocationBtn = document.getElementById('use-current-location');
    checkInStatus = document.getElementById('check-in-status');
    checkInOverlay = document.getElementById('check-in-overlay');
    userLatInput = document.getElementById('user-lat');
    userLngInput = document.getElementById('user-lng');
    
    // Set initial status
    showCheckInStatus('pending', 'Waiting for location...');
    
    // Set initial distance display to prevent layout shifts
    if (distanceInfo) {
        distanceInfo.innerHTML = `
            <div class="distance-value">--</div>
            <div class="distance-label">meters away</div>
        `;
    }
    
    // Initialize the map
    initMap();
    
    // Set up event listeners
    setupEventListeners();
}

/**
 * Initialize the map and related services
 */
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

/**
 * Function to get and continuously track user location
 */
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

/**
 * Helper function to detect browser for permission instructions
 * @returns {string} Browser name
 */
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

/**
 * Function to update position data
 * @param {GeolocationPosition} position - Position object from geolocation API
 */
function updatePosition(position) {
    userLat = position.coords.latitude;
    userLng = position.coords.longitude;
    
    // Store in hidden inputs for form submission
    if (userLatInput) userLatInput.value = userLat;
    if (userLngInput) userLngInput.value = userLng;
    
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

/**
 * Function to handle location errors
 * @param {GeolocationPositionError} error - Error from geolocation API
 */
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

/**
 * Set up all event listeners
 */
function setupEventListeners() {
    // Refresh location button
    if (useCurrentLocationBtn) {
        useCurrentLocationBtn.addEventListener('click', getUserLocation);
    }
    
    // Request permission button
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
    
    // Directions button
    if (directionsBtn) {
        directionsBtn.addEventListener('click', function() {
            if (userLat && userLng) {
                // Make sure text stays white
                this.style.color = 'white';
                if (this.querySelector('span')) this.querySelector('span').style.color = 'white';
                if (this.querySelector('svg')) this.querySelector('svg').style.color = 'white';
                
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
    
    // Floating check-in button
    const floatingCheckInBtn = document.getElementById('floating-check-in-btn');
    if (floatingCheckInBtn) {
        floatingCheckInBtn.addEventListener('click', function() {
            // Scroll to the check-in overlay
            document.querySelector('.map-container').scrollIntoView({ 
                behavior: 'smooth' 
            });
        });
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
}

/**
 * Function to update user's location on map
 * @param {number} lat - Latitude
 * @param {number} lng - Longitude
 * @param {number} accuracy - Accuracy in meters
 */
function updateUserLocationOnMap(lat, lng, accuracy) {
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

/**
 * Function to check distance and update check-in status
 * @param {number} lat - Latitude
 * @param {number} lng - Longitude
 */
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
    }
}

/**
 * Function to get directions
 */
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

/**
 * Function to clear the route from the map
 */
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

/**
 * Function to show location status
 * @param {string} type - Status type (pending, success, error, warning)
 * @param {string} message - Status message
 */
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
        case 'warning':
            icon = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
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

/**
 * Function to show check-in status
 * @param {string} type - Status type (pending, success, error, warning, info)
 * @param {string} message - Status message
 */
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

/**
 * Show browser-specific instructions for enabling location
 * @param {string} browser - Browser name
 */
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

// Export functions for use in other scripts
window.LocationCheckIn = {
    init: initLocationCheckIn,
    getUserLocation: getUserLocation,
    getDirections: getDirections,
    clearRoute: clearRoute
}; 
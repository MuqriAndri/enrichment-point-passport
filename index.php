<?php
session_start();

// Load configurations
$appConfig = require_once 'config/app.php';
define('BASE_URL', $appConfig['base_url']);
$protected_pages = $appConfig['protected_pages'];

// Load database configuration - now provides $profilesDB and $ccaDB
require_once 'config/database.php';

// Load repositories
require_once 'repositories/user-repository.php';
require_once 'repositories/club-repository.php';
require_once 'repositories/enrichment-point-repository.php';

// Get the path from URL
$request_uri = str_replace($appConfig['path_prefix'], '', $_SERVER['REQUEST_URI']);
$path = trim(parse_url($request_uri, PHP_URL_PATH), '/');
$params = explode('/', $path);

// Default to login if no path
$page = $params[0] ?: $appConfig['default_page'];

// Check if this is a club location page (format: club-slug-location)
$isClubLocation = false;
if (preg_match('/^([a-z0-9-]+)-location$/', $page, $matches)) {
    $clubSlug = $matches[1];
    $isClubLocation = true;
}

// Check if this is a club management page (format: club-slug-management)
$isClubManagement = false;
if (isset($params[0]) && $params[0] === 'cca' && isset($params[1]) && preg_match('/^([a-z0-9-]+)-management$/', $params[1], $matches)) {
    $clubSlug = $matches[1];
    $isClubManagement = true;
}

// Redirect to dashboard if already logged in and trying to access login page
if ($page === 'login' && isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/dashboard");
    exit();
}

// Check authentication for protected pages
if (in_array($page, $protected_pages) && !isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL);
    exit();
}

// Handle POST login separately
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["user_ic"]) && isset($_POST["password"])) {
        require_once 'controllers/auth.php';
        handleLogin($profilesDB); // User info is in profiles database
    } 
    elseif (isset($_POST['action']) && $_POST['action'] === 'cca') {
        require_once 'controllers/cca.php';
        handleClubAction($ccaDB, $profilesDB); // Pass both database connections
    }
    elseif (isset($_POST['action']) && $_POST['action'] === 'cca_manage') {
        require_once 'controllers/cca.php';
        handleClubManagement($ccaDB, $profilesDB); // Handle club management actions
    }
}

// Prepare page data
$pageData = [];

if ($page === 'cca') {
    $clubMapping = require_once 'config/club-mapping.php';

    try {
        // Use ccaDB for club repository
        $clubRepo = new clubRepository($ccaDB, $profilesDB);

        // Check if this is a club management page request
        if (isset($params[1]) && preg_match('/^([a-z0-9-]+)-management$/', $params[1], $matches)) {
            $clubSlug = $matches[1];
            
            // Find the club based on the slug
            $clubFound = false;
            $clubName = '';
            $clubCategory = '';
            
            foreach ($clubMapping as $category => $clubs) {
                foreach ($clubs as $name => $slug) {
                    if ($slug === $clubSlug) {
                        $clubFound = true;
                        $clubName = $name;
                        $clubCategory = $category;
                        break 2;
                    }
                }
            }
            
            if ($clubFound) {
                // Get club details
                $clubDetails = $clubRepo->getClubDetails($clubName);
                
                if ($clubDetails) {
                    // Check if user has permission to edit this club
                    $isOfficer = false;
                    if (isset($_SESSION['user_id'])) {
                        $isOfficer = $clubRepo->isClubOfficer($_SESSION['user_id'], $clubDetails['club_id']);
                    }
                    
                    if ($isOfficer) {
                        // Fetch additional data needed for club management
                        $gallery = $clubRepo->getClubGallery($clubDetails['club_id']);
                        $activities = $clubRepo->getClubActivities($clubDetails['club_id']);
                        $locations = $clubRepo->getClubLocations($clubDetails['club_id']);
                        
                        // User has permission, show management page
                        $pageData = [
                            'details' => $clubDetails,
                            'is_officer' => true,
                            'gallery' => $gallery,
                            'activities' => $activities,
                            'locations' => $locations,
                            'clubMapping' => $clubMapping,
                            'clubSlug' => $clubSlug // Add the slug for back navigation
                        ];
                        include 'templates/cca-edit.php';
                        exit();
                    }
                }
            }
            
            // No permission, club not found, or invalid slug - redirect to CCA page
            $_SESSION['error'] = 'You do not have permission to manage this club.';
            header("Location: " . BASE_URL . "/cca");
            exit();
        }
        
        // Handle regular club detail pages
        if (isset($params[1])) {
            $requestedClub = $params[1];
            $clubFound = false;
            $clubCategory = '';
            $clubName = '';

            // Find club data in the mapping
            foreach ($clubMapping as $category => $clubs) {
                foreach ($clubs as $name => $slug) {
                    if ($requestedClub === $slug) {
                        $clubFound = true;
                        $clubCategory = $category;
                        $clubName = $name;
                        break 2;
                    }
                }
            }

            if ($clubFound) {
                $clubDetails = $clubRepo->getClubDetails($clubName);

                if ($clubDetails) {
                    // Check if user is a member of this club
                    $isMember = false;
                    if (isset($_SESSION['student_id'])) {
                        $isMember = $clubRepo->isUserMemberOfClub($_SESSION['student_id'], $clubDetails['club_id']);
                    }
                    
                    // Check if user is an officer of this club
                    $isOfficer = false;
                    if (isset($_SESSION['user_id'])) {
                        $isOfficer = $clubRepo->isClubOfficer($_SESSION['user_id'], $clubDetails['club_id']);
                    }
                    
                    // Fetch club gallery images
                    $gallery = $clubRepo->getClubGallery($clubDetails['club_id']);

                    $pageData = [
                        'details' => $clubDetails,
                        'isMember' => $isMember,
                        'is_officer' => $isOfficer,
                        'upcoming_events' => [], // Placeholder for events data
                        'activities' => [],      // Placeholder for activities data
                        'gallery' => $gallery    // Gallery data
                    ];

                    include 'templates/cca-details.php';
                } else {
                    header("HTTP/1.0 404 Not Found");
                    include 'templates/404.php';
                }
                exit();
            } else {
                header("HTTP/1.0 404 Not Found");
                include 'templates/404.php';
                exit();
            }
        } else {
            // Main CCA page
            $clubs = $clubRepo->getAllActiveClubs();

            // Group clubs by category
            $clubsByCategory = [];
            foreach ($clubs as $club) {
                $clubsByCategory[$club['category']][] = $club;
            }

            // Get user's current memberships
            $userMemberships = [];
            if (isset($_SESSION['student_id'])) {
                $userMemberships = $clubRepo->getUserMemberships($_SESSION['student_id']);
            }

            $pageData = [
                'clubsByCategory' => $clubsByCategory,
                'userMemberships' => $userMemberships,
                'clubMapping' => $clubMapping
            ];
        }
    } catch (PDOException $e) {
        error_log("Error fetching CCAs: " . $e->getMessage());
        $pageData = [
            'clubsByCategory' => [],
            'userMemberships' => [],
            'error' => 'Failed to load CCA data'
        ];
    }
}

// Route to appropriate template
switch ($page) {
    case 'about':
        include 'templates/about.php';
        break;
    case 'cca':
        include 'templates/cca.php';
        break;
    case 'contact':
        include 'templates/contact.php';
        break;
    case 'dashboard':
        include 'templates/dashboard.php';
        break;
    case 'ep':
        include 'templates/ep.php';
        break;
    case 'events':
        include 'templates/events.php';
        break;
    case 'history':
        include 'templates/history.php';
        break;
    case 'login':
        include 'templates/login.php';
        break;
    case 'logout':
        session_destroy();
        header("Location: " . BASE_URL);
        exit();
        break;
    case 'profile':
        include 'templates/profile.php';
        break;
    case 'settings':
        include 'templates/settings.php';
        break;
    default:
        // Check if this is a club location page
        if ($isClubLocation) {
            include 'templates/cca-location.php';
            break;
        }
        
        header("HTTP/1.0 404 Not Found");
        include 'templates/404.php';
        break;
}
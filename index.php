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
require_once 'repositories/event-repository.php';
require_once 'repositories/history-repository.php';
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
    } elseif (isset($_POST['action']) && $_POST['action'] === 'cca') {
        require_once 'controllers/cca.php';
        handleClubAction($ccaDB, $profilesDB); // Pass both database connections
    } elseif (isset($_POST['action']) && $_POST['action'] === 'cca_manage') {
        require_once 'controllers/cca.php';
        handleClubManagement($ccaDB, $profilesDB); // Handle club management actions
    } elseif (isset($_POST['club_id']) && isset($_POST['lat']) && isset($_POST['lng'])) {
        require_once 'controllers/attendance.php';
        handleAttendanceCheckIn($ccaDB, $profilesDB); // Handle attendance check-in
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
                        $applications = $clubRepo->getPendingApplications($clubDetails['club_id']);
                        $members = $clubRepo->getClubMembers($clubDetails['club_id']);

                        // User has permission, show management page
                        $pageData = [
                            'details' => $clubDetails,
                            'is_officer' => true,
                            'gallery' => $gallery,
                            'activities' => $activities,
                            'locations' => $locations,
                            'applications' => $applications,
                            'members' => $members,
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

                    // Fetch club activities (Planned and Ongoing)
                    $activities = [];
                    $allActivities = $clubRepo->getClubActivities($clubDetails['club_id']);

                    // Log for debugging
                    error_log("Club ID: " . $clubDetails['club_id']);
                    error_log("All activities count: " . count($allActivities));
                    error_log("All activities: " . print_r($allActivities, true));

                    // Check each activity for title field
                    foreach ($allActivities as $index => $activity) {
                        error_log("Activity $index - Title: " . ($activity['title'] ?? 'MISSING') . ", Status: " . ($activity['status'] ?? 'MISSING'));
                    }

                    // Filter for Planned and Ongoing activities only
                    foreach ($allActivities as $activity) {
                        if ($activity['status'] === 'Planned' || $activity['status'] === 'Ongoing') {
                            // Store both title and status, and activity_id for the link
                            $activities[] = [
                                'title' => $activity['title'],
                                'status' => $activity['status'],
                                'id' => $activity['activity_id']
                            ];
                        }
                    }

                    // Add a manual entry for testing
                    if (empty($activities)) {
                        // Force an activity for testing
                        $activities[] = [
                            'title' => "Weekly Club Meeting",
                            'status' => "Planned",
                            'id' => 0
                        ];
                        $activities[] = [
                            'title' => "Member Training Session",
                            'status' => "Ongoing",
                            'id' => 0
                        ];
                    }

                    $pageData = [
                        'details' => $clubDetails,
                        'isMember' => $isMember,
                        'is_officer' => $isOfficer,
                        'upcoming_events' => [], // Placeholder for events data
                        'activities' => $activities,
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
        if (isset($params[1]) && $params[1] === 'details' && isset($params[2]) && is_numeric($params[2])) {
            // Use the correct database for events
            $pdo = $eventsDB;
            // Set event_id in GET for the details page
            $_GET['id'] = $params[2];
            include 'templates/events-details.php';
            break;
        }
        // Use the correct database for events
        $pdo = $eventsDB;
        include 'templates/events.php';
        break;
    case 'events-management':
        // Check if user has admin or committee privileges
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'committee')) {
            $_SESSION['error'] = 'You do not have permission to access this page';
            header("Location: " . BASE_URL . "/events");
            exit();
        }

        // Handle POST requests for event operations
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Special handling for edit_form operation
            if (isset($_POST['operation']) && $_POST['operation'] === 'edit_form' && isset($_POST['event_id'])) {
                require_once 'repositories/event-repository.php';
                $eventRepo = new EventRepository($eventsDB, $profilesDB);

                // Fetch the event
                $eventId = $_POST['event_id'];
                $_SESSION['edit_event'] = $eventRepo->getEventById($eventId);

                if (!$_SESSION['edit_event']) {
                    $_SESSION['error'] = 'Event not found';
                }

                // Redirect to the same page but with GET instead of POST to prevent form resubmission
                header("Location: " . BASE_URL . "/events-management");
                exit();
            }
            // Special handling for view_participants operation
            else if (isset($_POST['operation']) && $_POST['operation'] === 'view_participants' && isset($_POST['event_id'])) {
                require_once 'repositories/event-repository.php';
                $eventRepo = new EventRepository($eventsDB, $profilesDB);

                // Fetch the event and participants
                $eventId = $_POST['event_id'];
                $event = $eventRepo->getEventById($eventId);

                // Check if this is an AJAX request
                $isAjax = false;
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    $isAjax = true;
                } elseif (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                    $isAjax = true;
                }

                if ($isAjax) {
                    // For AJAX requests, return JSON response
                    header('Content-Type: application/json');

                    if ($event) {
                        $participants = $eventRepo->getEventParticipants($eventId);

                        // Log the data for debugging
                        error_log("AJAX Participants Response: Event ID: " . $eventId);
                        error_log("AJAX Participants Response: Participant Count: " . count($participants));

                        // Return the data as JSON
                        echo json_encode([
                            'success' => true,
                            'event' => $event,
                            'participants' => $participants
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Event not found'
                        ]);
                    }
                    exit();
                } else {
                    // For regular form submissions, use the session-based approach
                    if ($event) {
                        $_SESSION['view_event'] = $event;
                        $_SESSION['participants'] = $eventRepo->getEventParticipants($eventId);

                        // Redirect to the same page but with GET instead of POST to prevent form resubmission
                        header("Location: " . BASE_URL . "/events-management");
                        exit();
                    } else {
                        $_SESSION['error'] = 'Event not found';
                        header("Location: " . BASE_URL . "/events-management");
                        exit();
                    }
                }
            } else {
                // Handle other operations
                require_once 'controllers/events.php';
                handleEventOperations($eventsDB, $profilesDB);
                exit();
            }
        }

        // Use the correct database for events
        $pdo = $eventsDB;
        include 'templates/events-management.php';
        break;
    case 'help':
        include 'templates/help.php';
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
    case 'controllers':
        // Debug information
        error_log("Controllers route triggered. params[1]=" . ($params[1] ?? 'not set'));

        if (isset($params[1]) && $params[1] === 'events.php') {
            error_log("Events controller detected. Method: " . $_SERVER['REQUEST_METHOD'] . ", operation: " . ($_GET['operation'] ?? 'not set'));

            if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['operation'])) {
                require_once 'controllers/events.php';
                handleEventAPI($eventsDB, $profilesDB);
                exit();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                require_once 'controllers/events.php';
                handleEventOperations($eventsDB, $profilesDB);
                exit();
            }
        }

        // If not a handled controller or operation, show 404
        header("HTTP/1.0 404 Not Found");
        include 'templates/404.php';
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

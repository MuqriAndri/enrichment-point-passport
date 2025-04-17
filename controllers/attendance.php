<?php
function handleAttendanceCheckIn($ccaDB, $profilesDB) {
    // Enable error logging
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_log("Attendance check-in handler started");
    
    // Check if this is an AJAX request
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    // Create a response array for debugging and AJAX responses
    $response = [
        'status' => 'processing',
        'time' => date('Y-m-d H:i:s'),
        'post_data' => $_POST,
        'session' => [
            'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set',
            'student_id' => isset($_SESSION['student_id']) ? $_SESSION['student_id'] : 'not set'
        ]
    ];

    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['student_id'])) {
        $errorMsg = 'You must be logged in to check in.';
        $response['status'] = 'error';
        $response['message'] = 'Not logged in';
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        
        $_SESSION['error'] = $errorMsg;
        // Debug mode: Output directly to browser and exit
        if (isset($_GET['debug']) && $_GET['debug'] == 1) {
            header('Content-Type: application/json');
            echo json_encode($response, JSON_PRETTY_PRINT);
            exit;
        }
        
        header("Location: " . BASE_URL . "/cca");
        exit();
    }

    // Check if required parameters are set
    if (!isset($_POST['club_id']) || !isset($_POST['lat']) || !isset($_POST['lng'])) {
        $errorMsg = 'Invalid request parameters.';
        $response['status'] = 'error';
        $response['message'] = 'Missing required parameters';
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        
        $_SESSION['error'] = $errorMsg;
        if (isset($_GET['debug']) && $_GET['debug'] == 1) {
            header('Content-Type: application/json');
            echo json_encode($response, JSON_PRETTY_PRINT);
            exit;
        }
        
        header("Location: " . BASE_URL . "/cca");
        exit();
    }

    $clubId = $_POST['club_id'];
    $userId = $_SESSION['user_id'];
    $studentId = $_SESSION['student_id'];
    $latitude = $_POST['lat'];
    $longitude = $_POST['lng'];
    
    $response['club_id'] = $clubId;
    $response['user_id'] = $userId;
    $response['student_id'] = $studentId;
    $response['latitude'] = $latitude;
    $response['longitude'] = $longitude;

    // Validate club_id
    if (!is_numeric($clubId)) {
        $errorMsg = 'Invalid club ID.';
        $response['status'] = 'error';
        $response['message'] = 'Invalid club ID';
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        
        $_SESSION['error'] = $errorMsg;
        if (isset($_GET['debug']) && $_GET['debug'] == 1) {
            header('Content-Type: application/json');
            echo json_encode($response, JSON_PRETTY_PRINT);
            exit;
        }
        
        header("Location: " . BASE_URL . "/cca");
        exit();
    }

    // Load club repository with both database connections
    $clubRepo = new clubRepository($ccaDB, $profilesDB);
    
    // Get club details for confirmation message
    $clubDetails = $clubRepo->getClubDetailsById($clubId);
    $response['club_details'] = $clubDetails;
    
    if (!$clubDetails) {
        $errorMsg = 'Club not found.';
        $response['status'] = 'error';
        $response['message'] = 'Club not found';
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        
        $_SESSION['error'] = $errorMsg;
        if (isset($_GET['debug']) && $_GET['debug'] == 1) {
            header('Content-Type: application/json');
            echo json_encode($response, JSON_PRETTY_PRINT);
            exit;
        }
        
        header("Location: " . BASE_URL . "/cca");
        exit();
    }

    // Check if user is a member of this club
    if (!$clubRepo->isUserMemberOfClub($studentId, $clubId)) {
        $errorMsg = 'You must be a member of this club to check in.';
        $response['status'] = 'error';
        $response['message'] = 'Not a member';
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        
        $_SESSION['error'] = $errorMsg;
        if (isset($_GET['debug']) && $_GET['debug'] == 1) {
            header('Content-Type: application/json');
            echo json_encode($response, JSON_PRETTY_PRINT);
            exit;
        }
        
        // Get club mapping for redirects
        $clubMapping = require_once __DIR__ . '/../config/club-mapping.php';
        
        // Redirect to club details page if available
        if (isset($clubDetails['category']) && isset($clubDetails['club_name']) && 
            isset($clubMapping[$clubDetails['category']][$clubDetails['club_name']])) {
            $clubSlug = $clubMapping[$clubDetails['category']][$clubDetails['club_name']];
            header("Location: " . BASE_URL . "/cca/" . $clubSlug);
        } else {
            // Fallback to main CCA page
            header("Location: " . BASE_URL . "/cca");
        }
        exit();
    }

    try {
        // Get browser/device info
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Device';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        
        // Calculate accuracy (simulated here)
        $accuracy = mt_rand(3, 10); // Random accuracy between 3-10 meters
        
        // Record attendance using the club repository
        $result = $clubRepo->recordAttendance(
            $clubId,
            $studentId,
            $latitude,
            $longitude,
            $accuracy,
            $userAgent,
            $ipAddress
        );
        
        if ($result['success']) {
            $successMsg = 'Check-in successful. Your attendance has been recorded.';
            $response['status'] = 'success';
            $response['message'] = $result['message'];
            
            if ($isAjax) {
                $_SESSION['success'] = $successMsg; // Store for page refreshes
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
            
            $_SESSION['success'] = $successMsg;
        } else {
            // If it's already checked in today, display an info message
            if (strpos($result['message'], 'already recorded for today') !== false) {
                $infoMsg = 'You have already checked in to this club today.';
                $response['status'] = 'info';
                $response['message'] = $result['message'];
                
                if ($isAjax) {
                    $_SESSION['info'] = $infoMsg; // Store for page refreshes
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }
                
                $_SESSION['info'] = $infoMsg;
            } else {
                $errorMsg = 'Failed to record attendance: ' . $result['message'];
                $response['status'] = 'error';
                $response['message'] = $result['message'];
                
                if ($isAjax) {
                    $_SESSION['error'] = $errorMsg; // Store for page refreshes
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }
                
                $_SESSION['error'] = $errorMsg;
            }
        }
        
        if (isset($_GET['debug']) && $_GET['debug'] == 1) {
            header('Content-Type: application/json');
            echo json_encode($response, JSON_PRETTY_PRINT);
            exit;
        }
        
        // Get club mapping for redirects
        $clubMapping = require_once __DIR__ . '/../config/club-mapping.php';
        
        // Redirect to club details page if available
        if (isset($clubDetails['category']) && isset($clubDetails['club_name']) && 
            isset($clubMapping[$clubDetails['category']][$clubDetails['club_name']])) {
            $clubSlug = $clubMapping[$clubDetails['category']][$clubDetails['club_name']];
            header("Location: " . BASE_URL . "/cca/" . $clubSlug);
        } else {
            // Fallback to main CCA page
            header("Location: " . BASE_URL . "/cca");
        }
        exit();
        
    } catch (Exception $e) {
        // Catch any other exceptions
        error_log("General Exception in attendance check-in: " . $e->getMessage());
        
        $errorMsg = 'An unexpected error occurred: ' . $e->getMessage();
        $response['status'] = 'error';
        $response['message'] = 'General error: ' . $e->getMessage();
        
        if ($isAjax) {
            $_SESSION['error'] = $errorMsg; // Store for page refreshes
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        
        $_SESSION['error'] = $errorMsg;
        if (isset($_GET['debug']) && $_GET['debug'] == 1) {
            header('Content-Type: application/json');
            echo json_encode($response, JSON_PRETTY_PRINT);
            exit;
        }
        
        // Redirect back to location page
        header("Location: " . $_SERVER['HTTP_REFERER'] ?? BASE_URL . "/cca");
        exit();
    }
} 
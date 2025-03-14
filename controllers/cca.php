<?php
function handleClubAction($ccaDB, $profilesDB) {
    // Enable error logging
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_log("Club action handler started");
    
    // For debugging - create a response array
    $debugResponse = [
        'status' => 'processing',
        'time' => date('Y-m-d H:i:s'),
        'post_data' => $_POST,
        'session' => [
            'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set',
            'student_id' => isset($_SESSION['student_id']) ? $_SESSION['student_id'] : 'not set'
        ]
    ];

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = 'You must be logged in to perform this action.';
        $debugResponse['status'] = 'error';
        $debugResponse['message'] = 'Not logged in';
        
        // Debug mode: Output directly to browser and exit
        if (isset($_GET['debug']) && $_GET['debug'] == 1) {
            header('Content-Type: application/json');
            echo json_encode($debugResponse, JSON_PRETTY_PRINT);
            exit;
        }
        
        header("Location: " . BASE_URL . "/cca");
        exit();
    }

    // Check if required parameters are set
    if (!isset($_POST['operation']) || !isset($_POST['club_id'])) {
        $_SESSION['error'] = 'Invalid request parameters.';
        $debugResponse['status'] = 'error';
        $debugResponse['message'] = 'Missing operation or club_id';
        
        if (isset($_GET['debug']) && $_GET['debug'] == 1) {
            header('Content-Type: application/json');
            echo json_encode($debugResponse, JSON_PRETTY_PRINT);
            exit;
        }
        
        header("Location: " . BASE_URL . "/cca");
        exit();
    }

    $operation = $_POST['operation'];
    $clubId = $_POST['club_id'];
    $userId = $_SESSION['user_id'];
    $studentId = $_POST['student_id'] ?? $_SESSION['student_id'] ?? '';
    
    $debugResponse['operation'] = $operation;
    $debugResponse['club_id'] = $clubId;
    $debugResponse['user_id'] = $userId;
    $debugResponse['student_id'] = $studentId;

    // Validate club_id
    if (!is_numeric($clubId)) {
        $_SESSION['error'] = 'Invalid club ID.';
        $debugResponse['status'] = 'error';
        $debugResponse['message'] = 'Invalid club ID';
        
        if (isset($_GET['debug']) && $_GET['debug'] == 1) {
            header('Content-Type: application/json');
            echo json_encode($debugResponse, JSON_PRETTY_PRINT);
            exit;
        }
        
        header("Location: " . BASE_URL . "/cca");
        exit();
    }

    // Load club repository with both database connections
    $clubRepo = new clubRepository($ccaDB, $profilesDB);
    
    // Get club details for confirmation message
    $clubDetails = $clubRepo->getClubDetailsById($clubId);
    $debugResponse['club_details'] = $clubDetails;
    
    if (!$clubDetails) {
        $_SESSION['error'] = 'Club not found.';
        $debugResponse['status'] = 'error';
        $debugResponse['message'] = 'Club not found';
        
        if (isset($_GET['debug']) && $_GET['debug'] == 1) {
            header('Content-Type: application/json');
            echo json_encode($debugResponse, JSON_PRETTY_PRINT);
            exit;
        }
        
        header("Location: " . BASE_URL . "/cca");
        exit();
    }

    // Process based on operation
    switch ($operation) {
        case 'join':
            // Check if user is already a member
            $isMember = $clubRepo->isUserMemberOfClub($studentId, $clubId);
            $debugResponse['is_member'] = $isMember;
            
            if ($isMember) {
                $_SESSION['error'] = 'You are already a member of this club.';
                $debugResponse['status'] = 'error';
                $debugResponse['message'] = 'Already a member';
            } else {
                // Get student information from the form
                $studentInfo = [
                    'full_name' => $_POST['full_name'] ?? '',
                    'student_id' => $_POST['student_id'] ?? '',
                    'student_email' => $_POST['student_email'] ?? '',
                    'school' => $_POST['school'] ?? '',
                    'course' => $_POST['course'] ?? '',
                    'group_code' => $_POST['group_code'] ?? '',
                    'intake' => $_POST['intake'] ?? '',
                    'phone_number' => $_POST['phone_number'] ?? ''
                ];
                
                $debugResponse['student_info'] = $studentInfo;
                
                // Log the received data for debugging
                error_log('Join club request received: ' . json_encode([
                    'clubId' => $clubId,
                    'userId' => $userId,
                    'studentId' => $studentId,
                    'studentInfo' => $studentInfo
                ]));
                
                // Try to submit the club application
                try {
                    $joined = $clubRepo->submitClubApplication($clubId, $userId, $studentId, $studentInfo);
                    $debugResponse['joined'] = $joined;
                    
                    if ($joined) {
                        $_SESSION['success'] = 'Your application for ' . $clubDetails['club_name'] . ' has been submitted for approval.';
                        $debugResponse['status'] = 'success';
                        $debugResponse['message'] = 'Application submitted';
                    } else {
                        $_SESSION['error'] = 'Failed to submit your application. Please try again.';
                        $debugResponse['status'] = 'error';
                        $debugResponse['message'] = 'Failed to submit';
                    }
                } catch (Exception $e) {
                    error_log("Exception in handleClubAction: " . $e->getMessage());
                    error_log("Stack trace: " . $e->getTraceAsString());
                    
                    $debugResponse['status'] = 'error';
                    $debugResponse['message'] = 'Exception: ' . $e->getMessage();
                    $debugResponse['error_trace'] = $e->getTraceAsString();
                    $_SESSION['error'] = 'An error occurred: ' . $e->getMessage();
                }
            }
            break;
        
        default:
            $_SESSION['error'] = 'Invalid operation.';
            $debugResponse['status'] = 'error';
            $debugResponse['message'] = 'Invalid operation';
            break;
    }

    // If debug mode is enabled, output the debug information and exit
    if (isset($_GET['debug']) && $_GET['debug'] == 1) {
        header('Content-Type: application/json');
        echo json_encode($debugResponse, JSON_PRETTY_PRINT);
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
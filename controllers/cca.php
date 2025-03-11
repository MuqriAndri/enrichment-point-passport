<?php
function handleClubAction($pdo) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = 'You must be logged in to perform this action.';
        header("Location: " . BASE_URL . "/cca");
        exit();
    }

    // Check if required parameters are set
    if (!isset($_POST['operation']) || !isset($_POST['club_id'])) {
        $_SESSION['error'] = 'Invalid request parameters.';
        header("Location: " . BASE_URL . "/cca");
        exit();
    }

    $operation = $_POST['operation'];
    $clubId = $_POST['club_id'];
    $userId = $_SESSION['user_id'];
    $studentId = $_SESSION['student_id'] ?? '';

    // Validate club_id
    if (!is_numeric($clubId)) {
        $_SESSION['error'] = 'Invalid club ID.';
        header("Location: " . BASE_URL . "/cca");
        exit();
    }

    // Load club repository
    $clubRepo = new clubRepository($pdo);
    
    // Get club details for confirmation message
    $clubDetails = $clubRepo->getClubDetailsById($clubId);
    
    if (!$clubDetails) {
        $_SESSION['error'] = 'Club not found.';
        header("Location: " . BASE_URL . "/cca");
        exit();
    }

    // Process based on operation
    switch ($operation) {
        case 'join':
            // Check if user is already a member
            if ($clubRepo->isUserMemberOfClub($studentId, $clubId)) {
                $_SESSION['error'] = 'You are already a member of this club.';
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
                
                // Log the received data for debugging
                error_log('Join club request received: ' . json_encode([
                    'clubId' => $clubId,
                    'userId' => $userId,
                    'studentId' => $studentId,
                    'studentInfo' => $studentInfo
                ]));
                
                // Try to submit the club application
                $joined = $clubRepo->submitClubApplication($clubId, $userId, $studentId, $studentInfo);
                
                if ($joined) {
                    $_SESSION['success'] = 'Your application for ' . $clubDetails['club_name'] . ' has been submitted for approval.';
                } else {
                    $_SESSION['error'] = 'Failed to submit your application. Please try again.';
                }
            }
            break;
        
        default:
            $_SESSION['error'] = 'Invalid operation.';
            break;
    }

    // Generate the redirect URL
    $clubMapping = require_once 'config/club-mapping.php';
    
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
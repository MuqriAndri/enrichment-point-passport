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

function handleClubManagement($ccaDB, $profilesDB) {
    // Enable error logging
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    error_log("Club management handler started");
    
    // For debugging - create a response array
    $debugResponse = [
        'status' => 'processing',
        'time' => date('Y-m-d H:i:s'),
        'post_data' => $_POST,
        'files_data' => $_FILES,
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
    
    $debugResponse['operation'] = $operation;
    $debugResponse['club_id'] = $clubId;
    $debugResponse['user_id'] = $userId;

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

    // Load club repository
    $clubRepo = new clubRepository($ccaDB, $profilesDB);
    
    // Get club details for confirmation
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

    // Check if user is an officer of this club
    $isOfficer = $clubRepo->isClubOfficer($userId, $clubId);
    $debugResponse['is_officer'] = $isOfficer;
    
    if (!$isOfficer) {
        $_SESSION['error'] = 'You do not have permission to manage this club.';
        $debugResponse['status'] = 'error';
        $debugResponse['message'] = 'Not an officer';
        
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
        case 'update_info':
            // Update basic club information
            try {
                $updateData = [];
                $allowedFields = ['description', 'advisor', 'president', 'contact_email', 'membership_fee', 'meeting_schedule'];
                
                // Handle all fields, setting empty values to NULL
                foreach ($allowedFields as $field) {
                    // Check if the field is provided in the form
                    if (isset($_POST[$field])) {
                        // If field is empty string, set to NULL, otherwise use the value
                        $updateData[$field] = $_POST[$field] === '' ? null : $_POST[$field];
                    }
                }
                
                if (!empty($updateData)) {
                    $sql = "UPDATE clubs SET ";
                    $updates = [];
                    $params = [];
                    
                    foreach ($updateData as $field => $value) {
                        $updates[] = "$field = :$field";
                        $params[$field] = $value;
                    }
                    
                    $sql .= implode(', ', $updates);
                    $sql .= " WHERE club_id = :club_id";
                    $params['club_id'] = $clubId;
                    
                    $stmt = $ccaDB->prepare($sql);
                    foreach ($params as $key => $value) {
                        // Bind as NULL if value is null, otherwise bind as string
                        if ($value === null) {
                            $stmt->bindValue(":$key", $value, PDO::PARAM_NULL);
                        } else {
                            $stmt->bindValue(":$key", $value);
                        }
                    }
                    
                    $stmt->execute();
                    
                    if ($stmt->rowCount() > 0) {
                        $_SESSION['success'] = 'Club information updated successfully.';
                    } else {
                        $_SESSION['info'] = 'No changes were made to the club information.';
                    }
                } else {
                    $_SESSION['info'] = 'No data provided for update.';
                }
            } catch (PDOException $e) {
                error_log("Error updating club info: " . $e->getMessage());
                $_SESSION['error'] = 'Failed to update club information. Please try again.';
            }
            break;
            
        case 'add_gallery':
            // Handle gallery image upload
            if (isset($_FILES['gallery_image']) && $_FILES['gallery_image']['error'] == 0) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $maxSize = 5 * 1024 * 1024; // 5MB
                
                $fileType = $_FILES['gallery_image']['type'];
                $fileSize = $_FILES['gallery_image']['size'];
                $fileTmp = $_FILES['gallery_image']['tmp_name'];
                
                // Validate file type and size
                if (!in_array($fileType, $allowedTypes)) {
                    $_SESSION['error'] = 'Invalid file type. Only JPEG, PNG, and GIF are allowed.';
                    break;
                }
                
                if ($fileSize > $maxSize) {
                    $_SESSION['error'] = 'File size exceeds the maximum limit of 5MB.';
                    break;
                }

                // Create base directories if they don't exist
                $baseImagePath = __DIR__ . '/../assets/images';
                $galleryDir = $baseImagePath . '/gallery';
                $clubsDir = $galleryDir . '/clubs';
                
                // Create directory structure if it doesn't exist
                foreach ([$galleryDir, $clubsDir] as $dir) {
                    if (!file_exists($dir)) {
                        if (!mkdir($dir, 0777, true)) {
                            error_log("Failed to create directory: " . $dir);
                            $_SESSION['error'] = 'Failed to create upload directory.';
                            break 2;
                        }
                    }
                }
                
                // Load club mapping
                $clubMapping = require_once __DIR__ . '/../config/club-mapping.php';
                
                // Get club slug
                $clubSlug = '';
                foreach ($clubMapping as $category => $clubs) {
                    foreach ($clubs as $name => $slug) {
                        if (strcasecmp($name, $clubDetails['club_name']) === 0) {
                            $clubSlug = $slug;
                            break 2;
                        }
                    }
                }
                
                if (empty($clubSlug)) {
                    error_log("Could not find club slug for: " . $clubDetails['club_name']);
                    $_SESSION['error'] = 'Failed to determine club directory.';
                    break;
                }
                
                // Create club-specific directory
                $clubSpecificDir = 'gallery/clubs/' . $clubSlug;
                $uploadDir = $baseImagePath . '/' . $clubSpecificDir;
                
                if (!file_exists($uploadDir)) {
                    if (!mkdir($uploadDir, 0777, true)) {
                        error_log("Failed to create club specific directory: " . $uploadDir);
                        $_SESSION['error'] = 'Failed to create club directory.';
                        break;
                    }
                }
                
                // Generate a unique filename
                $extension = strtolower(pathinfo($_FILES['gallery_image']['name'], PATHINFO_EXTENSION));
                $newFilename = uniqid('gallery_') . '.' . $extension;
                $uploadPath = $uploadDir . '/' . $newFilename;
                
                // Move the uploaded file
                if (move_uploaded_file($fileTmp, $uploadPath)) {
                    try {
                        // Save to database with relative path
                        $imagePath = $clubSpecificDir . '/' . $newFilename;
                        $imageTitle = isset($_POST['image_title']) ? $_POST['image_title'] : null;
                        $imageDescription = isset($_POST['image_description']) ? $_POST['image_description'] : null;
                        
                        $sql = "INSERT INTO club_gallery (club_id, image_path, image_title, image_description, created_at) 
                                VALUES (:club_id, :image_path, :image_title, :image_description, NOW())";
                        $stmt = $ccaDB->prepare($sql);
                        $stmt->bindParam(':club_id', $clubId);
                        $stmt->bindParam(':image_path', $imagePath);
                        $stmt->bindParam(':image_title', $imageTitle);
                        $stmt->bindParam(':image_description', $imageDescription);
                        $stmt->execute();
                        
                        $_SESSION['success'] = 'Image added to gallery successfully.';
                    } catch (PDOException $e) {
                        error_log("Error adding gallery image to database: " . $e->getMessage());
                        $_SESSION['error'] = 'Failed to save image to database. Please try again.';
                        // Delete the uploaded file if database insert fails
                        if (file_exists($uploadPath)) {
                            unlink($uploadPath);
                        }
                    }
                } else {
                    $uploadError = error_get_last();
                    error_log("Failed to move uploaded file. Error: " . json_encode($uploadError));
                    $_SESSION['error'] = 'Failed to upload image. Please try again.';
                }
            } else {
                $errorCode = isset($_FILES['gallery_image']) ? $_FILES['gallery_image']['error'] : 'No file uploaded';
                error_log("Gallery upload error: " . $errorCode);
                $_SESSION['error'] = 'No image selected or upload error occurred.';
            }
            break;
            
        case 'delete_gallery':
            // Handle deletion of gallery image
            if (isset($_POST['image_id']) && is_numeric($_POST['image_id'])) {
                $imageId = (int)$_POST['image_id'];
                
                try {
                    // First get the image path
                    $sql = "SELECT image_path FROM club_gallery WHERE image_id = :image_id AND club_id = :club_id";
                    $stmt = $ccaDB->prepare($sql);
                    $stmt->bindParam(':image_id', $imageId);
                    $stmt->bindParam(':club_id', $clubId);
                    $stmt->execute();
                    
                    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $imagePath = $row['image_path'];
                        
                        // Delete from database
                        $sql = "DELETE FROM club_gallery WHERE image_id = :image_id AND club_id = :club_id";
                        $stmt = $ccaDB->prepare($sql);
                        $stmt->bindParam(':image_id', $imageId);
                        $stmt->bindParam(':club_id', $clubId);
                        $stmt->execute();
                        
                        // Delete the file from disk
                        $filePath = __DIR__ . '/../assets/images/' . $imagePath;
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                        
                        $_SESSION['success'] = 'Gallery image deleted successfully.';
                    } else {
                        $_SESSION['error'] = 'Image not found or you do not have permission to delete it.';
                    }
                } catch (PDOException $e) {
                    error_log("Error deleting gallery image: " . $e->getMessage());
                    $_SESSION['error'] = 'Failed to delete gallery image. Please try again.';
                }
            } else {
                $_SESSION['error'] = 'Invalid image ID provided.';
            }
            break;
            
        case 'add_activity':
            // Add new club activity
            try {
                // Validation
                $requiredFields = ['title', 'start_datetime', 'end_datetime'];
                $missingFields = [];
                
                foreach ($requiredFields as $field) {
                    if (!isset($_POST[$field]) || empty($_POST[$field])) {
                        $missingFields[] = $field;
                    }
                }
                
                if (!empty($missingFields)) {
                    $_SESSION['error'] = 'Missing required fields: ' . implode(', ', $missingFields);
                    break;
                }
                
                // Prepare activity data
                $activityData = [
                    'title' => $_POST['title'],
                    'description' => $_POST['description'] ?? null,
                    'location_name' => $_POST['location'] ?? null,
                    'activity_type' => $_POST['activity_type'] ?? 'Regular Session',
                    'start_datetime' => $_POST['start_datetime'],
                    'end_datetime' => $_POST['end_datetime'],
                    'points_awarded' => isset($_POST['points']) && !empty($_POST['points']) ? $_POST['points'] : 2
                ];
                
                try {
                    $sql = "INSERT INTO club_activities (
                        club_id,
                        title,
                        description,
                        activity_type,
                        start_datetime,
                        end_datetime,
                        location_name,
                        points_awarded,
                        status,
                        created_at
                    ) VALUES (
                        :club_id,
                        :title,
                        :description,
                        :activity_type,
                        :start_datetime,
                        :end_datetime,
                        :location_name,
                        :points_awarded,
                        :status,
                        NOW()
                    )";
                    
                    $stmt = $ccaDB->prepare($sql);
                    $stmt->bindParam(':club_id', $clubId);
                    $stmt->bindParam(':title', $activityData['title']);
                    $stmt->bindParam(':description', $activityData['description'], PDO::PARAM_STR);
                    $stmt->bindParam(':activity_type', $activityData['activity_type']);
                    $stmt->bindParam(':location_name', $activityData['location_name'], PDO::PARAM_STR);
                    $stmt->bindParam(':start_datetime', $activityData['start_datetime']);
                    $stmt->bindParam(':end_datetime', $activityData['end_datetime']);
                    $stmt->bindParam(':points_awarded', $activityData['points_awarded']);
                    
                    $status = 'Planned';
                    $stmt->bindParam(':status', $status);
                    
                    $stmt->execute();
                    $activityId = $ccaDB->lastInsertId();
                    
                    $_SESSION['success'] = 'Activity added successfully.';
                } catch (PDOException $e) {
                    error_log("Error adding activity: " . $e->getMessage());
                    $_SESSION['error'] = 'Failed to add activity: ' . $e->getMessage();
                }
            } catch (Exception $e) {
                error_log("Error adding activity: " . $e->getMessage());
                $_SESSION['error'] = 'Failed to add activity. Please try again.';
            }
            break;
            
        case 'delete_activity':
            // Delete club activity
            if (isset($_POST['activity_id']) && is_numeric($_POST['activity_id'])) {
                $activityId = (int)$_POST['activity_id'];
                
                try {
                    // First check if the activity exists and belongs to this club
                    $checkSql = "SELECT club_id FROM club_activities WHERE activity_id = :activity_id AND club_id = :club_id";
                    $checkStmt = $ccaDB->prepare($checkSql);
                    $checkStmt->bindParam(':activity_id', $activityId);
                    $checkStmt->bindParam(':club_id', $clubId);
                    $checkStmt->execute();
                    
                    if ($checkStmt->rowCount() === 0) {
                        $_SESSION['error'] = 'Activity not found or you do not have permission to delete it.';
                        break;
                    }
                    
                    // Check if there's attendance records
                    $attendanceSql = "SELECT COUNT(*) FROM club_attendance WHERE club_id = :club_id";
                    $attendanceStmt = $ccaDB->prepare($attendanceSql);
                    $attendanceStmt->bindParam(':club_id', $clubId);
                    $attendanceStmt->execute();
                    $attendanceCount = $attendanceStmt->fetchColumn();
                    
                    if ($attendanceCount > 0) {
                        // If there's attendance, just mark it as cancelled instead of deleting
                        $updateSql = "UPDATE club_activities SET status = 'Cancelled', last_modified_at = NOW() WHERE activity_id = :activity_id AND club_id = :club_id";
                        $updateStmt = $ccaDB->prepare($updateSql);
                        $updateStmt->bindParam(':activity_id', $activityId);
                        $updateStmt->bindParam(':club_id', $clubId);
                        $updateStmt->execute();
                        
                        $_SESSION['success'] = 'Activity marked as cancelled due to existing attendance records.';
                    } else {
                        // If no attendance, delete the activity
                        $deleteSql = "DELETE FROM club_activities WHERE activity_id = :activity_id AND club_id = :club_id";
                        $deleteStmt = $ccaDB->prepare($deleteSql);
                        $deleteStmt->bindParam(':activity_id', $activityId);
                        $deleteStmt->bindParam(':club_id', $clubId);
                        $deleteStmt->execute();
                        
                        $_SESSION['success'] = 'Activity deleted successfully.';
                    }
                } catch (PDOException $e) {
                    error_log("Error deleting activity: " . $e->getMessage());
                    $_SESSION['error'] = 'Failed to delete activity: ' . $e->getMessage();
                }
            } else {
                $_SESSION['error'] = 'Invalid activity ID provided.';
            }
            break;
            
        case 'update_location':
            // Update club location
            try {
                if (isset($_POST['location'], $_POST['latitude'], $_POST['longitude'])) {
                    $location = $_POST['location'];
                    $latitude = $_POST['latitude'];
                    $longitude = $_POST['longitude'];
                    
                    // Update the clubs table with the new location
                    $sql = "UPDATE clubs SET location = :location, latitude = :latitude, longitude = :longitude WHERE club_id = :club_id";
                    $stmt = $ccaDB->prepare($sql);
                    $stmt->bindParam(':location', $location);
                    $stmt->bindParam(':latitude', $latitude);
                    $stmt->bindParam(':longitude', $longitude);
                    $stmt->bindParam(':club_id', $clubId);
                    $stmt->execute();
                    
                    if ($stmt->rowCount() > 0) {
                        $_SESSION['success'] = 'Club location updated successfully.';
                    } else {
                        $_SESSION['info'] = 'No changes were made to the club location.';
                    }
                } else {
                    $_SESSION['error'] = 'Missing location data.';
                }
            } catch (PDOException $e) {
                error_log("Error updating location: " . $e->getMessage());
                $_SESSION['error'] = 'Failed to update location. Please try again.';
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
    
    // Find the club slug
    $clubSlug = '';
    if (isset($clubDetails['category']) && isset($clubDetails['club_name'])) {
        $category = strtolower($clubDetails['category']);
        // Handle special case for 'martial arts' category
        if ($category === 'martial arts') {
            $category = 'martial-arts';
        }
        
        if (isset($clubMapping[$category][$clubDetails['club_name']])) {
            $clubSlug = $clubMapping[$category][$clubDetails['club_name']];
        } else {
            // Try to find it by iterating through all mappings
            foreach ($clubMapping as $catName => $clubs) {
                foreach ($clubs as $name => $slug) {
                    if ($name === $clubDetails['club_name']) {
                        $clubSlug = $slug;
                        break 2;
                    }
                }
            }
        }
    }
    
    // Check if there was a specific redirect request
    $redirectToDetails = false;
    if (isset($_POST['redirect_to_details']) && $_POST['redirect_to_details'] === '1') {
        $redirectToDetails = true;
    }
    
    // Redirect based on the request, with fallbacks if needed
    if ($redirectToDetails && !empty($clubSlug)) {
        // Redirect to club details page
        header("Location: " . BASE_URL . "/cca/" . $clubSlug);
    } else if (!empty($clubSlug)) {
        // Default: redirect to club management page
        header("Location: " . BASE_URL . "/cca/" . $clubSlug . "-management");
    } else {
        // Fallback to main CCA page if no slug is found
        header("Location: " . BASE_URL . "/cca");
    }
    exit();
}
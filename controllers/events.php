<?php

// Include event repository
require_once __DIR__ . '/../repositories/event-repository.php';

// Define S3 constants if not already defined
if (!defined('AWS_ACCESS_KEY_ID')) {
    define('AWS_ACCESS_KEY_ID', 'AKIA4SYAMLXWG443EJG2');
}

if (!defined('AWS_SECRET_ACCESS_KEY')) {
    define('AWS_SECRET_ACCESS_KEY', 'tTsOrY1XG1m2CAZNmOJcu0TbAOj+0QcbFUyWWoyv');
}

if (!defined('S3_BUCKET_NAME')) {
    define('S3_BUCKET_NAME', 'enrichment-point-passport-bucket');
}

if (!defined('S3_BASE_URL')) {
    define('S3_BASE_URL', 'https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com');
}

if (!defined('S3_ENABLED')) {
    define('S3_ENABLED', true);
}

// Helper functions for file uploads
function uploadToS3WithSignature($sourceFile, $key) {
    if (!file_exists($sourceFile)) {
        error_log("Source file does not exist: " . $sourceFile);
        return ['success' => false, 'message' => 'Source file does not exist'];
    }
    
    // Check if AWS SDK is available
    if (!class_exists('Aws\S3\S3Client')) {
        error_log("AWS SDK not available. Using direct upload method instead.");
        return uploadToS3Direct($sourceFile, $key);
    }
    
    try {
        $s3Client = new Aws\S3\S3Client([
            'version' => 'latest',
            'region'  => 'ap-southeast-1',
            'credentials' => [
                'key'    => AWS_ACCESS_KEY_ID,
                'secret' => AWS_SECRET_ACCESS_KEY,
            ],
        ]);
        
        $command = $s3Client->getCommand('PutObject', [
            'Bucket' => S3_BUCKET_NAME,
            'Key'    => $key,
            'Body'   => fopen($sourceFile, 'r'),
            'ACL'    => 'public-read'
        ]);
        
        $s3Client->execute($command);
        
        return [
            'success' => true,
            'url' => S3_BASE_URL . '/' . $key
        ];
    } catch (Exception $e) {
        error_log("S3 upload error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function uploadToS3Direct($sourceFile, $key) {
    if (!file_exists($sourceFile)) {
        error_log("Source file does not exist: " . $sourceFile);
        return ['success' => false, 'message' => 'Source file does not exist'];
    }
    
    $fileData = file_get_contents($sourceFile);
    if ($fileData === false) {
        error_log("Failed to read file: " . $sourceFile);
        return ['success' => false, 'message' => 'Failed to read file'];
    }
    
    $s3Url = S3_BASE_URL;
    $bucket = S3_BUCKET_NAME;
    $contentType = mime_content_type($sourceFile);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $s3Url . '/' . $key);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_PUT, 1);
    curl_setopt($ch, CURLOPT_UPLOAD, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: ' . $contentType,
        'x-amz-acl: public-read'
    ]);
    
    $fp = fopen('php://temp', 'r+');
    fwrite($fp, $fileData);
    rewind($fp);
    curl_setopt($ch, CURLOPT_INFILE, $fp);
    curl_setopt($ch, CURLOPT_INFILESIZE, strlen($fileData));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    fclose($fp);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return [
            'success' => true,
            'url' => $s3Url . '/' . $key
        ];
    } else {
        error_log("S3 upload failed with status: " . $httpCode . " - " . $response);
        return [
            'success' => false,
            'message' => 'S3 upload failed with status: ' . $httpCode
        ];
    }
}

/**
 * Handle event operations
 *
 * @param PDO $eventsDB Events database connection
 * @param PDO $profilesDB Profiles database connection
 * @return void
 */
function handleEventOperations($eventsDB, $profilesDB) {
    // Initialize repository
    $eventRepo = new EventRepository($eventsDB, $profilesDB);
    
    // Determine the operation
    $operation = $_POST['operation'] ?? '';
    
    // Initialize response for debugging
    $debugResponse = [
        'status' => 'success',
        'operation' => $operation,
        'message' => 'Operation processed successfully'
    ];
    
    // Start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    switch ($operation) {
        case 'add_event':
            // Handle adding a new event
            $requiredFields = ['event_name', 'event_location', 'event_participants', 'event_date', 'start_time', 'end_time', 'status', 'enrichment_points_awarded'];
            $missingFields = [];
            
            // Check for missing required fields
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                $_SESSION['error'] = 'Missing required fields: ' . implode(', ', $missingFields);
                break;
            }
            
            // Prepare event data
            $eventData = [
                'event_name' => $_POST['event_name'],
                'event_description' => $_POST['event_description'] ?? '',
                'event_location' => $_POST['event_location'],
                'event_participants' => $_POST['event_participants'],
                'event_date' => $_POST['event_date'],
                'start_time' => $_POST['start_time'],
                'end_time' => $_POST['end_time'],
                'status' => $_POST['status'],
                'events_images' => '',
                'enrichment_points_awarded' => $_POST['enrichment_points_awarded'],
                'organizer' => $_POST['organizer'] ?? $_SESSION['full_name'] ?? 'System Administrator'
            ];
            
            // Handle image upload if present
            if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
                $fileTmp = $_FILES['event_image']['tmp_name'];
                $fileName = $_FILES['event_image']['name'];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                // Validate file extension
                $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($fileExt, $allowedExt)) {
                    $_SESSION['error'] = 'Invalid image type. Allowed types: ' . implode(', ', $allowedExt);
                    break;
                }
                
                // Generate a unique file name
                $newFileName = 'event_' . uniqid() . '.' . $fileExt;
                $s3Key = 'events/' . $newFileName;
                
                // Upload to S3
                if (S3_ENABLED) {
                    try {
                        $uploadResult = uploadToS3Direct($fileTmp, $s3Key);
                        
                        if (!$uploadResult['success']) {
                            error_log("Failed to upload to S3: " . ($uploadResult['message'] ?? 'Unknown error'));
                            $_SESSION['error'] = 'Failed to upload image to cloud storage: ' . ($uploadResult['message'] ?? 'Unknown error');
                            break;
                        }
                        
                        // Get the full S3 URL
                        $imagePath = $uploadResult['url'];
                        
                        // Make sure we store the full URL, not a relative path
                        if (strpos($imagePath, 'http') !== 0) {
                            $imagePath = S3_BASE_URL . '/' . $s3Key;
                        }
                        
                        $eventData['events_images'] = $imagePath;
                    } catch (Exception $e) {
                        error_log("Exception during S3 upload: " . $e->getMessage());
                        $_SESSION['error'] = 'Error uploading image: ' . $e->getMessage();
                        break;
                    }
                } else {
                    // Local file storage (fallback)
                    $uploadDir = __DIR__ . '/../assets/images/events/';
                    
                    if (!file_exists($uploadDir)) {
                        if (!mkdir($uploadDir, 0777, true)) {
                            error_log("Failed to create directory: " . $uploadDir);
                            $_SESSION['error'] = 'Failed to create upload directory.';
                            break;
                        }
                    }
                    
                    $uploadPath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($fileTmp, $uploadPath)) {
                        $eventData['events_images'] = BASE_URL . '/assets/images/events/' . $newFileName;
                    } else {
                        error_log("Failed to move uploaded file to: " . $uploadPath);
                        $_SESSION['error'] = 'Failed to store uploaded image.';
                        break;
                    }
                }
            }
            
            // Add event to database
            $result = $eventRepo->addEvent($eventData);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Event added successfully.';
            } else {
                $_SESSION['error'] = 'Failed to add event: ' . $result['message'];
            }
            break;
            
        case 'update_event':
            // Handle updating an existing event
            if (!isset($_POST['event_id']) || empty($_POST['event_id'])) {
                $_SESSION['error'] = 'Event ID is required';
                break;
            }
            
            $eventId = $_POST['event_id'];
            
            // Get the existing event first
            $existingEvent = $eventRepo->getEventById($eventId);
            
            if (!$existingEvent) {
                $_SESSION['error'] = 'Event not found';
                break;
            }
            
            // Prepare updated data
            $eventData = [
                'event_name' => $_POST['event_name'] ?? $existingEvent['event_name'],
                'event_description' => $_POST['event_description'] ?? $existingEvent['event_description'],
                'event_location' => $_POST['event_location'] ?? $existingEvent['event_location'],
                'event_participants' => $_POST['event_participants'] ?? $existingEvent['event_participants'],
                'event_date' => $_POST['event_date'] ?? $existingEvent['event_date'],
                'start_time' => $_POST['start_time'] ?? $existingEvent['start_time'],
                'end_time' => $_POST['end_time'] ?? $existingEvent['end_time'],
                'status' => $_POST['status'] ?? $existingEvent['status'],
                'enrichment_points_awarded' => $_POST['enrichment_points_awarded'] ?? $existingEvent['enrichment_points_awarded'],
                'organizer' => $_POST['organizer'] ?? $existingEvent['organizer']
            ];
            
            // Handle image upload if present
            if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
                $fileTmp = $_FILES['event_image']['tmp_name'];
                $fileName = $_FILES['event_image']['name'];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                // Validate file extension
                $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($fileExt, $allowedExt)) {
                    $_SESSION['error'] = 'Invalid image type. Allowed types: ' . implode(', ', $allowedExt);
                    break;
                }
                
                // Generate a unique file name
                $newFileName = 'event_' . uniqid() . '.' . $fileExt;
                $s3Key = 'events/' . $newFileName;
                
                // Upload to S3
                if (S3_ENABLED) {
                    try {
                        $uploadResult = uploadToS3Direct($fileTmp, $s3Key);
                        
                        if (!$uploadResult['success']) {
                            error_log("Failed to upload to S3: " . ($uploadResult['message'] ?? 'Unknown error'));
                            $_SESSION['error'] = 'Failed to upload image to cloud storage: ' . ($uploadResult['message'] ?? 'Unknown error');
                            break;
                        }
                        
                        // Get the full S3 URL
                        $imagePath = $uploadResult['url'];
                        
                        // Make sure we store the full URL, not a relative path
                        if (strpos($imagePath, 'http') !== 0) {
                            $imagePath = S3_BASE_URL . '/' . $s3Key;
                        }
                        
                        $eventData['events_images'] = $imagePath;
                    } catch (Exception $e) {
                        error_log("Exception during S3 upload: " . $e->getMessage());
                        $_SESSION['error'] = 'Error uploading image: ' . $e->getMessage();
                        break;
                    }
                } else {
                    // Local file storage (fallback)
                    $uploadDir = __DIR__ . '/../assets/images/events/';
                    
                    if (!file_exists($uploadDir)) {
                        if (!mkdir($uploadDir, 0777, true)) {
                            error_log("Failed to create directory: " . $uploadDir);
                            $_SESSION['error'] = 'Failed to create upload directory.';
                            break;
                        }
                    }
                    
                    $uploadPath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($fileTmp, $uploadPath)) {
                        $eventData['events_images'] = BASE_URL . '/assets/images/events/' . $newFileName;
                    } else {
                        error_log("Failed to move uploaded file to: " . $uploadPath);
                        $_SESSION['error'] = 'Failed to store uploaded image.';
                        break;
                    }
                }
            }
            
            // Update event in database
            $result = $eventRepo->updateEvent($eventId, $eventData);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Event updated successfully.';
            } else {
                $_SESSION['error'] = 'Failed to update event: ' . $result['message'];
            }
            break;
            
        case 'delete_event':
            // Handle deleting an event
            if (!isset($_POST['event_id']) || empty($_POST['event_id'])) {
                $_SESSION['error'] = 'Event ID is required';
                break;
            }
            
            $eventId = $_POST['event_id'];
            
            // Delete the event
            $result = $eventRepo->deleteEvent($eventId);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Event deleted successfully.';
            } else {
                $_SESSION['error'] = 'Failed to delete event: ' . $result['message'];
            }
            break;
            
        case 'update_participant_status':
            // Handle updating participant status
            if (!isset($_POST['participant_id']) || empty($_POST['participant_id']) || !isset($_POST['status']) || empty($_POST['status'])) {
                $_SESSION['error'] = 'Participant ID and status are required';
                break;
            }
            
            $participantId = $_POST['participant_id'];
            $status = $_POST['status'];
            
            // Update participant status
            $result = $eventRepo->updateParticipantStatus($participantId, $status);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Participant status updated successfully.';
            } else {
                $_SESSION['error'] = 'Failed to update participant status: ' . $result['message'];
            }
            break;
            
        case 'update_location':
            // Handle updating event location
            if (!isset($_POST['event_id']) || empty($_POST['event_id']) || !isset($_POST['location']) || empty($_POST['location'])) {
                $_SESSION['error'] = 'Event ID and location are required';
                break;
            }
            
            $eventId = $_POST['event_id'];
            $location = $_POST['location'];
            
            // Get the existing event first
            $existingEvent = $eventRepo->getEventById($eventId);
            
            if (!$existingEvent) {
                $_SESSION['error'] = 'Event not found';
                break;
            }
            
            // Prepare updated data with just the location
            $eventData = $existingEvent;
            $eventData['event_location'] = $location;
            
            // Update event in database
            $result = $eventRepo->updateEvent($eventId, $eventData);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Event location updated successfully.';
            } else {
                $_SESSION['error'] = 'Failed to update event location: ' . $result['message'];
            }
            break;
            
        default:
            $_SESSION['error'] = 'Invalid operation';
            $debugResponse['status'] = 'error';
            $debugResponse['message'] = 'Invalid operation';
            break;
    }
    
    // Debug mode handling
    if (isset($_GET['debug']) && $_GET['debug'] == 1) {
        header('Content-Type: application/json');
        echo json_encode($debugResponse, JSON_PRETTY_PRINT);
        exit;
    }
    
    // Redirect to the events management page
    header("Location: " . BASE_URL . "/events-management");
    exit();
}

/**
 * Handle event API requests
 *
 * @param PDO $eventsDB Events database connection
 * @param PDO $profilesDB Profiles database connection
 * @return void
 */
function handleEventAPI($eventsDB, $profilesDB) {
    // Initialize repository
    $eventRepo = new EventRepository($eventsDB, $profilesDB);
    
    // Check session
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check permissions - only admin or committee can access
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'committee')) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized'
        ]);
        exit();
    }
    
    // Determine the operation
    $operation = $_GET['operation'] ?? '';
    
    switch ($operation) {
        case 'get_event':
            // Get event details
            if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Event ID is required'
                ]);
                exit();
            }
            
            $eventId = $_GET['event_id'];
            $event = $eventRepo->getEventById($eventId);
            
            if ($event) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'event' => $event
                ]);
            } else {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Event not found'
                ]);
            }
            break;
            
        case 'get_participants':
            // Get participants for an event
            if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Event ID is required'
                ]);
                exit();
            }
            
            $eventId = $_GET['event_id'];
            $participants = $eventRepo->getEventParticipants($eventId);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'participants' => $participants
            ]);
            break;
            
        default:
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid operation'
            ]);
            break;
    }
    
    exit();
} 
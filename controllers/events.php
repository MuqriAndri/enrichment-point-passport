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

if (!defined('S3_REGION')) {
    define('S3_REGION', 'ap-southeast-1');
}

/**
 * Upload a file to S3 using AWS Signature V4 authentication
 * 
 * @param string $sourceFile Path to local file
 * @param string $key The S3 key (path in bucket)
 * @return array Result with success status and URL/message
 */
function uploadToS3WithSignature($sourceFile, $key) {
    error_log('Attempting S3 upload with auth signature for: ' . $key);
    
    try {
        // Prepare URL and basic setup
        $url = "https://" . S3_BUCKET_NAME . ".s3." . S3_REGION . ".amazonaws.com/" . $key;
        $contentType = mime_content_type($sourceFile);
        $fileContent = file_get_contents($sourceFile);
        
        // Generate AWS Signature V4
        $now = gmdate('Ymd\THis\Z');
        $date = substr($now, 0, 8);
        $contentSha256 = hash('sha256', $fileContent);
        
        // Canonical Request
        $canonicalRequest = "PUT\n" .
                           "/$key\n" .
                           "\n" .
                           "content-type:$contentType\n" .
                           "host:" . S3_BUCKET_NAME . ".s3." . S3_REGION . ".amazonaws.com\n" .
                           "x-amz-content-sha256:$contentSha256\n" .
                           "x-amz-date:$now\n" .
                           "\n" .
                           "content-type;host;x-amz-content-sha256;x-amz-date\n" .
                           "$contentSha256";

        $canonicalRequestHash = hash('sha256', $canonicalRequest);

        // String to Sign
        $stringToSign = "AWS4-HMAC-SHA256\n" .
                       "$now\n" .
                       "$date/" . S3_REGION . "/s3/aws4_request\n" .
                       "$canonicalRequestHash";

        // Signing Key
        $kDate = hash_hmac('sha256', $date, 'AWS4' . AWS_SECRET_ACCESS_KEY, true);
        $kRegion = hash_hmac('sha256', S3_REGION, $kDate, true);
        $kService = hash_hmac('sha256', 's3', $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

        // Signature
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        // Authorization Header
        $authorizationHeader = "AWS4-HMAC-SHA256 " .
                              "Credential=" . AWS_ACCESS_KEY_ID . "/$date/" . S3_REGION . "/s3/aws4_request, " .
                              "SignedHeaders=content-type;host;x-amz-content-sha256;x-amz-date, " .
                              "Signature=$signature";
        
        // Initialize curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for testing
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Disable SSL host verification for testing
        
        // Set headers with authentication
        $curlHeaders = [
            'Content-Type: ' . $contentType,
            'Authorization: ' . $authorizationHeader,
            'x-amz-date: ' . $now,
            'x-amz-content-sha256: ' . $contentSha256,
            'x-amz-acl: public-read'
        ];
        
        error_log("S3 request headers: " . print_r($curlHeaders, true));
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
        
        // Enable verbose debugging
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_STDERR, $verbose);
        
        // Execute curl
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        // Get verbose information
        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        fclose($verbose);
        
        // Log detailed information for debugging
        error_log('S3 upload details: HTTP Code: ' . $httpCode . ', Error: ' . $error);
        
        // Clean up
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'url' => S3_BASE_URL . '/' . $key
            ];
        } else {
            // Try alternate method with direct upload
            error_log('S3 upload failed, trying with direct upload approach');
            return uploadToS3Direct($sourceFile, $key);
        }
    } catch (Exception $e) {
        error_log('S3 upload exception: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Exception during S3 upload: ' . $e->getMessage()
        ];
    }
}

function uploadToS3Direct($sourceFile, $key) {
    error_log('Attempting direct S3 upload for: ' . $key);
    
    try {
        // Prepare URL and basic setup
        $url = "https://" . S3_BUCKET_NAME . ".s3." . S3_REGION . ".amazonaws.com/" . $key;
        $contentType = mime_content_type($sourceFile);
        $fileContent = file_get_contents($sourceFile);
        
        // Initialize curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        // Set simple headers without complex AWS authentication
        $curlHeaders = [
            'Content-Type: ' . $contentType,
            'x-amz-acl: public-read'
        ];
        
        error_log("S3 direct upload headers: " . print_r($curlHeaders, true));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
        
        // Enable verbose debugging
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_STDERR, $verbose);
        
        // Execute curl
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        // Get verbose information
        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        fclose($verbose);
        
        // Log detailed information for debugging
        error_log('S3 direct upload details: HTTP Code: ' . $httpCode . ', Error: ' . $error);
        error_log('S3 direct upload verbose log: ' . $verboseLog);
        
        // Clean up
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'url' => S3_BASE_URL . '/' . $key
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to upload to S3: HTTP ' . $httpCode . ' - ' . $error
            ];
        }
    } catch (Exception $e) {
        error_log('S3 direct upload exception: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Exception during S3 direct upload: ' . $e->getMessage()
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
            error_log("Starting add_event process");
            $requiredFields = ['event_name', 'event_location', 'event_participants', 'event_date', 'start_time', 'end_time', 'status', 'enrichment_points_awarded'];
            $missingFields = [];
            
            // Check for missing required fields
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {
                    $missingFields[] = $field;
                    error_log("Missing required field: $field");
                }
            }
            
            if (!empty($missingFields)) {
                error_log("Missing required fields: " . implode(', ', $missingFields));
                $_SESSION['error'] = 'Missing required fields: ' . implode(', ', $missingFields);
                header("Location: " . BASE_URL . "/events-management");
                exit();
            }
            
            // Log POST data for debugging
            error_log("POST data for add_event: " . print_r($_POST, true));
            if (isset($_FILES['event_image'])) {
                error_log("File data: " . print_r($_FILES['event_image'], true));
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
                error_log("Processing image upload");
                $fileTmp = $_FILES['event_image']['tmp_name'];
                $fileName = $_FILES['event_image']['name'];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                if (!file_exists($fileTmp)) {
                    error_log("Uploaded file not found at temp location: $fileTmp");
                    $_SESSION['error'] = 'Uploaded file not found on server';
                    header("Location: " . BASE_URL . "/events-management");
                    exit();
                }
                
                // Validate file extension
                $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($fileExt, $allowedExt)) {
                    error_log("Invalid file extension: $fileExt");
                    $_SESSION['error'] = 'Invalid image type. Allowed types: ' . implode(', ', $allowedExt);
                    header("Location: " . BASE_URL . "/events-management");
                    exit();
                }
                
                // Generate a unique file name
                $newFileName = 'event_' . uniqid() . '.' . $fileExt;
                $s3Key = 'events/' . $newFileName;
                error_log("Generated S3 key: $s3Key");
                
                // Upload to S3
                if (S3_ENABLED) {
                    try {
                        // Use the signature V4 upload method
                        $uploadResult = uploadToS3WithSignature($fileTmp, $s3Key);
                        error_log("S3 upload result: " . print_r($uploadResult, true));
                        
                        if (!$uploadResult['success']) {
                            error_log("Failed to upload to S3: " . ($uploadResult['message'] ?? 'Unknown error'));
                            $_SESSION['error'] = 'Failed to upload image to cloud storage: ' . ($uploadResult['message'] ?? 'Unknown error');
                            header("Location: " . BASE_URL . "/events-management");
                            exit();
                        }
                        
                        // Get the full S3 URL
                        $imagePath = $uploadResult['url'];
                        error_log("S3 upload successful, image URL: $imagePath");
                        
                        // Make sure we store the full URL
                        $eventData['events_images'] = $imagePath;
                    } catch (Exception $e) {
                        error_log("Exception during S3 upload: " . $e->getMessage());
                        $_SESSION['error'] = 'Error uploading image: ' . $e->getMessage();
                        header("Location: " . BASE_URL . "/events-management");
                        exit();
                    }
                } else {
                    // Local file storage (fallback)
                    error_log("S3 is disabled, using local file storage");
                    $uploadDir = __DIR__ . '/../assets/images/events/';
                    
                    if (!file_exists($uploadDir)) {
                        if (!mkdir($uploadDir, 0777, true)) {
                            error_log("Failed to create directory: " . $uploadDir);
                            $_SESSION['error'] = 'Failed to create upload directory.';
                            header("Location: " . BASE_URL . "/events-management");
                            exit();
                        }
                    }
                    
                    $uploadPath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($fileTmp, $uploadPath)) {
                        $eventData['events_images'] = BASE_URL . '/assets/images/events/' . $newFileName;
                        error_log("Local file upload successful: " . $eventData['events_images']);
                    } else {
                        error_log("Failed to move uploaded file to: " . $uploadPath);
                        $_SESSION['error'] = 'Failed to store uploaded image.';
                        header("Location: " . BASE_URL . "/events-management");
                        exit();
                    }
                }
            } else if (isset($_FILES['event_image'])) {
                $errorCode = $_FILES['event_image']['error'];
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive',
                    UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive',
                    UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
                ];
                $errorMessage = isset($errorMessages[$errorCode]) ? $errorMessages[$errorCode] : 'Unknown upload error';
                error_log("File upload error: $errorMessage (Code: $errorCode)");
            }
            
            // Add event to database
            error_log("Adding event to database with data: " . print_r($eventData, true));
            
            try {
                // Start transaction
                $eventsDB->beginTransaction();
                
                // Build SQL query
                $sql = "INSERT INTO events (
                    event_name, 
                    event_description, 
                    event_location, 
                    event_participants, 
                    event_date, 
                    start_time, 
                    end_time, 
                    status, 
                    events_images, 
                    enrichment_points_awarded,
                    organizer,
                    created_at
                ) VALUES (
                    :event_name, 
                    :event_description, 
                    :event_location, 
                    :event_participants, 
                    :event_date, 
                    :start_time, 
                    :end_time, 
                    :status, 
                    :events_images, 
                    :enrichment_points_awarded,
                    :organizer,
                    NOW()
                )";
                
                // Prepare and execute statement
                $stmt = $eventsDB->prepare($sql);
                
                if (!$stmt) {
                    $error = $eventsDB->errorInfo();
                    error_log("Statement preparation failed: " . ($error[2] ?? 'Unknown error'));
                    throw new PDOException("Failed to prepare SQL statement");
                }
                
                // Bind parameters
                $stmt->bindParam(':event_name', $eventData['event_name']);
                $stmt->bindParam(':event_description', $eventData['event_description']);
                $stmt->bindParam(':event_location', $eventData['event_location']);
                $stmt->bindParam(':event_participants', $eventData['event_participants']);
                $stmt->bindParam(':event_date', $eventData['event_date']);
                $stmt->bindParam(':start_time', $eventData['start_time']);
                $stmt->bindParam(':end_time', $eventData['end_time']);
                $stmt->bindParam(':status', $eventData['status']);
                $stmt->bindParam(':events_images', $eventData['events_images']);
                $stmt->bindParam(':enrichment_points_awarded', $eventData['enrichment_points_awarded']);
                $stmt->bindParam(':organizer', $eventData['organizer']);
                
                // Execute statement
                $result = $stmt->execute();
                
                if (!$result) {
                    $error = $stmt->errorInfo();
                    error_log("Database error: " . ($error[2] ?? 'Unknown error'));
                    throw new PDOException("Failed to execute SQL: " . ($error[2] ?? 'Unknown error'));
                }
                
                $eventId = $eventsDB->lastInsertId();
                error_log("Event successfully inserted with ID: $eventId");
                
                // Commit transaction
                $eventsDB->commit();
                
                $_SESSION['success'] = 'Event added successfully!';
            } catch (Exception $e) {
                // Rollback on error
                if ($eventsDB->inTransaction()) {
                    $eventsDB->rollBack();
                    error_log("Transaction rolled back due to: " . $e->getMessage());
                }
                
                error_log("Error adding event: " . $e->getMessage());
                $_SESSION['error'] = 'Failed to add event: ' . $e->getMessage();
            }
            
            // Redirect back after operation
            header("Location: " . BASE_URL . "/events-management");
            exit();
            
        case 'update_event':
            // Handle updating an existing event
            if (!isset($_POST['event_id']) || empty($_POST['event_id'])) {
                $_SESSION['error'] = 'Event ID is required';
                header("Location: " . BASE_URL . "/events-management");
                exit();
            }
            
            $eventId = $_POST['event_id'];
            
            // Get the existing event first
            $existingEvent = $eventRepo->getEventById($eventId);
            
            if (!$existingEvent) {
                $_SESSION['error'] = 'Event not found';
                header("Location: " . BASE_URL . "/events-management");
                exit();
            }
            
            // Prepare updated data - start with existing data
            $eventData = $existingEvent;
            
            // Update with new values
            $eventData['event_name'] = $_POST['event_name'] ?? $existingEvent['event_name'];
            $eventData['event_description'] = $_POST['event_description'] ?? $existingEvent['event_description'];
            $eventData['event_location'] = $_POST['event_location'] ?? $existingEvent['event_location'];
            $eventData['event_participants'] = $_POST['event_participants'] ?? $existingEvent['event_participants'];
            $eventData['event_date'] = $_POST['event_date'] ?? $existingEvent['event_date'];
            $eventData['start_time'] = $_POST['start_time'] ?? $existingEvent['start_time'];
            $eventData['end_time'] = $_POST['end_time'] ?? $existingEvent['end_time'];
            $eventData['status'] = $_POST['status'] ?? $existingEvent['status'];
            $eventData['enrichment_points_awarded'] = $_POST['enrichment_points_awarded'] ?? $existingEvent['enrichment_points_awarded'];
            $eventData['organizer'] = $_POST['organizer'] ?? $existingEvent['organizer'];
            
            // Handle image upload if present
            if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
                $fileTmp = $_FILES['event_image']['tmp_name'];
                $fileName = $_FILES['event_image']['name'];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                // Validate file extension
                $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($fileExt, $allowedExt)) {
                    $_SESSION['error'] = 'Invalid image type. Allowed types: ' . implode(', ', $allowedExt);
                    header("Location: " . BASE_URL . "/events-management");
                    exit();
                }
                
                // Generate a unique file name
                $newFileName = 'event_' . uniqid() . '.' . $fileExt;
                $s3Key = 'events/' . $newFileName;
                
                error_log("Updating event $eventId - uploading new image: $s3Key");
                
                // Upload to S3
                if (S3_ENABLED) {
                    try {
                        $uploadResult = uploadToS3WithSignature($fileTmp, $s3Key);
                        
                        if (!$uploadResult['success']) {
                            error_log("Failed to upload to S3: " . ($uploadResult['message'] ?? 'Unknown error'));
                            $_SESSION['error'] = 'Failed to upload image to cloud storage: ' . ($uploadResult['message'] ?? 'Unknown error');
                            header("Location: " . BASE_URL . "/events-management");
                            exit();
                        }
                        
                        // Get the full S3 URL
                        $imagePath = $uploadResult['url'];
                        error_log("Successfully uploaded new image for event $eventId: $imagePath");
                        
                        // Save the new image URL
                        $eventData['events_images'] = $imagePath;
                    } catch (Exception $e) {
                        error_log("Exception during S3 upload: " . $e->getMessage());
                        $_SESSION['error'] = 'Error uploading image: ' . $e->getMessage();
                        header("Location: " . BASE_URL . "/events-management");
                        exit();
                    }
                } else {
                    // Local file storage (fallback)
                    $uploadDir = __DIR__ . '/../assets/images/events/';
                    
                    if (!file_exists($uploadDir)) {
                        if (!mkdir($uploadDir, 0777, true)) {
                            error_log("Failed to create directory: " . $uploadDir);
                            $_SESSION['error'] = 'Failed to create upload directory.';
                            header("Location: " . BASE_URL . "/events-management");
                            exit();
                        }
                    }
                    
                    $uploadPath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($fileTmp, $uploadPath)) {
                        $eventData['events_images'] = BASE_URL . '/assets/images/events/' . $newFileName;
                    } else {
                        error_log("Failed to move uploaded file to: " . $uploadPath);
                        $_SESSION['error'] = 'Failed to store uploaded image.';
                        header("Location: " . BASE_URL . "/events-management");
                        exit();
                    }
                }
            } else if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                // There was an upload error other than no file selected
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                    UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
                    UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
                ];
                
                $errorMessage = $uploadErrors[$_FILES['event_image']['error']] ?? 'Unknown upload error';
                error_log("Upload error for event $eventId: " . $errorMessage);
                $_SESSION['error'] = 'Image upload error: ' . $errorMessage;
                header("Location: " . BASE_URL . "/events-management");
                exit();
            }
            
            // Update event in database
            error_log("Updating event ID $eventId with data: " . print_r($eventData, true));
            $result = $eventRepo->updateEvent($eventId, $eventData);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Event updated successfully.';
            } else {
                $_SESSION['error'] = 'Failed to update event: ' . $result['message'];
            }
            
            // Redirect back
            header("Location: " . BASE_URL . "/events-management");
            exit();
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
            
            // Detect if this is an AJAX request
            $isAjax = false;
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                $isAjax = true;
            } elseif (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                $isAjax = true;
            }
            
            // If AJAX request, return JSON response
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => $result['success'],
                    'message' => $result['success'] ? 'Event deleted successfully.' : $result['message']
                ]);
                exit();
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
            
            // Detect if this is an AJAX request by checking for content-type header or X-Requested-With
            $isAjax = false;
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                $isAjax = true;
            } elseif (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                $isAjax = true;
            }
            
            // If AJAX request, return JSON response
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => $result['success'],
                    'message' => $result['success'] ? 'Participant status updated successfully.' : $result['message']
                ]);
                exit();
            }
            
            // Otherwise, we'll continue and let the default redirect happen at the end
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
            
        case 'update_participant_status':
            // Handle updating participant status via AJAX
            if (!isset($_POST['participant_id']) || empty($_POST['participant_id']) || !isset($_POST['status']) || empty($_POST['status'])) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Participant ID and status are required'
                ]);
                exit();
            }
            
            $participantId = $_POST['participant_id'];
            $status = $_POST['status'];
            
            // Log the request
            error_log("API: Update participant status request - ID: $participantId, Status: $status");
            
            // Update participant status
            $result = $eventRepo->updateParticipantStatus($participantId, $status);
            
            // Log the result
            error_log("API: Update result - Success: " . ($result['success'] ? 'true' : 'false') . ", Message: " . $result['message']);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $result['success'],
                'message' => $result['success'] ? 'Participant status updated successfully' : $result['message']
            ]);
            break;
            
        case 'register_event':
            // Handle event registration
            if (!isset($_POST['event_id']) || empty($_POST['event_id'])) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Event ID is required'
                ]);
                exit();
            }
            
            $eventId = $_POST['event_id'];
            $userId = $_SESSION['user_id'];
            
            // Check if already registered
            $stmt = $eventsDB->prepare("SELECT * FROM events_participants WHERE event_id = ? AND user_id = ?");
            $stmt->execute([$eventId, $userId]);
            if ($stmt->fetch()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'You are already registered for this event'
                ]);
                exit();
            }
            
            // Get user details
            $stmt = $profilesDB->prepare("SELECT full_name, email, phone FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'User not found'
                ]);
                exit();
            }
            
            try {
                // Begin transaction
                $eventsDB->beginTransaction();
                
                // Insert into events_participants table
                $stmt = $eventsDB->prepare("INSERT INTO events_participants 
                    (event_id, user_id, participant_name, participant_email, participant_phone, registration_date, status) 
                    VALUES (?, ?, ?, ?, ?, NOW(), 'Pending')");
                $stmt->execute([
                    $eventId,
                    $userId,
                    $user['full_name'],
                    $user['email'],
                    $user['phone'] ?? ''
                ]);
                
                // Update participant count
                $stmt = $eventsDB->prepare("UPDATE events SET event_participants = event_participants + 1 WHERE event_id = ?");
                $stmt->execute([$eventId]);
                
                // Commit the transaction
                $eventsDB->commit();
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'You have successfully registered for this event'
                ]);
            } catch (PDOException $e) {
                // Rollback the transaction on error
                $eventsDB->rollBack();
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Registration failed: ' . $e->getMessage()
                ]);
            }
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
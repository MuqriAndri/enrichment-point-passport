<?php
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
header('Content-Type: application/json');

// S3 Configuration
define('S3_BUCKET', 'enrichment-point-passport-bucket');
define('S3_REGION', 'ap-southeast-1');
define('S3_PROFILE_PATH', 'uploads/profile');
define('S3_BASE_URL', 'https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com');

// AWS Credentials
define('AWS_ACCESS_KEY', 'AKIA4SYAMLXWG443EJG2');
define('AWS_SECRET_KEY', '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');

function sendResponse($success, $data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'data' => $data
    ]);
    exit();
}

function logError($message, $context = []) {
    error_log("Upload Error - " . $message . " Context: " . json_encode($context));
}

function generateUniqueFilename($userId, $extension) {
    // Format: userID_YYYYMMDD_HHmmss_random
    $timestamp = date('Ymd_His');
    $random = substr(md5(uniqid()), 0, 8);
    return sprintf('profile_%s_%s_%s.%s', $userId, $timestamp, $random, $extension);
}

function getSignedHeaders($httpVerb, $path, $contentType, $contentSha256 = 'UNSIGNED-PAYLOAD') {
    $now = gmdate('Ymd\THis\Z');
    $date = substr($now, 0, 8);

    // Canonical request
    $canonicalRequest = "$httpVerb\n" .
                        "/$path\n" .
                        "\n" .
                        "content-type:$contentType\n" .
                        "host:" . S3_BUCKET . ".s3." . S3_REGION . ".amazonaws.com\n" .
                        "x-amz-content-sha256:$contentSha256\n" .
                        "x-amz-date:$now\n" .
                        "\n" .
                        "content-type;host;x-amz-content-sha256;x-amz-date\n" .
                        "$contentSha256";

    $canonicalRequestHash = hash('sha256', $canonicalRequest);

    // String to sign
    $stringToSign = "AWS4-HMAC-SHA256\n" .
                   "$now\n" .
                   "$date/" . S3_REGION . "/s3/aws4_request\n" .
                   "$canonicalRequestHash";

    // Signing key
    $kDate = hash_hmac('sha256', $date, 'AWS4' . AWS_SECRET_KEY, true);
    $kRegion = hash_hmac('sha256', S3_REGION, $kDate, true);
    $kService = hash_hmac('sha256', 's3', $kRegion, true);
    $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

    // Signature
    $signature = hash_hmac('sha256', $stringToSign, $kSigning);

    // Authorization header
    $authorizationHeader = "AWS4-HMAC-SHA256 " .
                          "Credential=" . AWS_ACCESS_KEY . "/$date/" . S3_REGION . "/s3/aws4_request, " .
                          "SignedHeaders=content-type;host;x-amz-content-sha256;x-amz-date, " .
                          "Signature=$signature";

    return [
        'Content-Type' => $contentType,
        'Authorization' => $authorizationHeader,
        'x-amz-date' => $now,
        'x-amz-content-sha256' => $contentSha256,
        'x-amz-acl' => 'public-read'
    ];
}

/**
 * Upload a file to S3 using AWS Signature V4 authentication
 * 
 * @param string $sourceFile Path to local file
 * @param string $key The S3 key (path in bucket)
 * @return array Result with success status and URL/message
 */
function uploadToS3($sourceFile, $key) {
    logError('Attempting S3 upload with auth', ['key' => $key]);
    
    try {
        // Check if path is profile and can use simplified upload
        $isProfilePath = (strpos($key, S3_PROFILE_PATH) === 0);
        
        // Prepare URL and basic setup
        $url = "https://" . S3_BUCKET . ".s3." . S3_REGION . ".amazonaws.com/" . $key;
        $contentType = mime_content_type($sourceFile);
        $fileContent = file_get_contents($sourceFile);
        
        // Initialize curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);
        
        // Get headers with AWS Signature V4 authentication
        if (!$isProfilePath) {
            // For paths outside profile uploads that need authentication
            $contentSha256 = hash('sha256', $fileContent);
            $headers = getSignedHeaders('PUT', $key, $contentType, $contentSha256);
            
            // Format headers for CURL
            $curlHeaders = [];
            foreach ($headers as $name => $value) {
                $curlHeaders[] = "$name: $value";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
        } else {
            // For profile uploads, simplified headers since bucket policy allows public write
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: ' . $contentType,
                'x-amz-acl: public-read'
            ]);
        }
        
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
        logError('S3 upload details', [
            'http_code' => $httpCode,
            'curl_error' => $error,
            'verbose_log' => $verboseLog,
            'response' => $response,
            'is_profile_path' => $isProfilePath
        ]);
        
        // Clean up
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'url' => S3_BASE_URL . '/' . $key
            ];
        } else {
            // Try alternate method
            return uploadToS3WithTempFile($sourceFile, $key);
        }
    } catch (Exception $e) {
        logError('S3 upload exception', ['error' => $e->getMessage()]);
        return [
            'success' => false,
            'message' => 'Exception during S3 upload: ' . $e->getMessage()
        ];
    }
}

/**
 * Alternative upload method using a temp file approach with AWS signatures
 */
function uploadToS3WithTempFile($sourceFile, $key) {
    logError('Attempting temp file S3 upload fallback', ['key' => $key]);
    
    try {
        // Check if path is profile and can use simplified upload
        $isProfilePath = (strpos($key, S3_PROFILE_PATH) === 0);
        
        // Prepare URL and content type
        $url = "https://" . S3_BUCKET . ".s3." . S3_REGION . ".amazonaws.com/" . $key;
        $contentType = mime_content_type($sourceFile);
        
        // Initialize curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_PUT, true);
        
        // Set headers with authentication if needed
        if (!$isProfilePath) {
            // For non-profile paths, use UNSIGNED-PAYLOAD since we're streaming from file
            $headers = getSignedHeaders('PUT', $key, $contentType, 'UNSIGNED-PAYLOAD');
            
            // Format headers for CURL
            $curlHeaders = [];
            foreach ($headers as $name => $value) {
                $curlHeaders[] = "$name: $value";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
        } else {
            // For profile uploads, simplified headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: ' . $contentType,
                'x-amz-acl: public-read'
            ]);
        }
        
        // Use the file directly
        $fh = fopen($sourceFile, 'r');
        curl_setopt($ch, CURLOPT_INFILE, $fh);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($sourceFile));
        
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
        logError('S3 temp file upload details', [
            'http_code' => $httpCode,
            'curl_error' => $error,
            'verbose_log' => $verboseLog,
            'response' => $response,
            'is_profile_path' => $isProfilePath
        ]);
        
        // Clean up
        fclose($fh);
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
        logError('S3 temp file upload exception', ['error' => $e->getMessage()]);
        return [
            'success' => false,
            'message' => 'Exception during S3 temp file upload: ' . $e->getMessage()
        ];
    }
}

// Check if we're dealing with a CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Handle CORS preflight request
    header('Access-Control-Allow-Origin: http://52.221.56.103');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
    header('Access-Control-Max-Age: 86400');  // Cache preflight response for 24 hours
    exit();
}

// Add CORS headers for the actual request
header('Access-Control-Allow-Origin: http://52.221.56.103');
header('Access-Control-Allow-Methods: POST');

// Validate session
if (!isset($_SESSION['user_id'])) {
    sendResponse(false, ['error' => 'Unauthorized'], 401);
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, ['error' => 'Method not allowed'], 405);
}

try {
    // Include database connection
    require_once __DIR__ . '/../config/database.php';
    
    // Make sure we have $profilesDB defined from database.php
    if (!isset($profilesDB)) {
        // Try with $pdo instead if profilesDB is not available
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }
        $profilesDB = $pdo;
    }
    
    // Validate file upload
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        $errorCode = $_FILES['avatar']['error'] ?? 'No file uploaded';
        logError('File upload error', ['code' => $errorCode]);
        sendResponse(false, ['error' => 'File upload failed', 'code' => $errorCode], 400);
    }

    $file = $_FILES['avatar'];
    logError('File upload received', [
        'name' => $file['name'],
        'size' => $file['size'],
        'tmp_name' => $file['tmp_name']
    ]);
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $file['tmp_name']);
    finfo_close($fileInfo);

    if (!in_array($mimeType, $allowedTypes)) {
        sendResponse(false, ['error' => 'Invalid file type. Only JPG, PNG and GIF are allowed.'], 400);
    }

    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        sendResponse(false, ['error' => 'File too large. Maximum size is 5MB.'], 400);
    }

    // Generate unique S3 key for the file
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $s3Key = S3_PROFILE_PATH . '/' . generateUniqueFilename($_SESSION['user_id'], $extension);
    
    // Upload directly to S3
    $uploadResult = uploadToS3($file['tmp_name'], $s3Key);
    
    if (!$uploadResult['success']) {
        logError('S3 upload failed', ['error' => $uploadResult['message']]);
        sendResponse(false, ['error' => 'Failed to upload to cloud storage: ' . $uploadResult['message']], 500);
    }
    
    // Get the full S3 URL
    $avatarUrl = $uploadResult['url'];

    // Update database
    $sql = "UPDATE users SET profile_picture = :profile_picture WHERE user_id = :user_id";
    try {
        $stmt = $profilesDB->prepare($sql);
        $result = $stmt->execute([
            ':profile_picture' => $avatarUrl,
            ':user_id' => $_SESSION['user_id']
        ]);
        
        if (!$result) {
            logError('Database update failed', [
                'user_id' => $_SESSION['user_id'],
                'error' => $stmt->errorInfo()
            ]);
            sendResponse(false, ['error' => 'Failed to update profile picture in database'], 500);
        }
    } catch (PDOException $e) {
        logError('Database PDO exception', [
            'user_id' => $_SESSION['user_id'],
            'error' => $e->getMessage()
        ]);
        sendResponse(false, ['error' => 'Database error: ' . $e->getMessage()], 500);
    }

    // Update session
    $_SESSION['profile_picture'] = $avatarUrl;

    // Return success
    sendResponse(true, [
        'message' => 'Profile picture updated successfully',
        'avatarUrl' => $avatarUrl
    ]);

} catch (Exception $e) {
    logError('Unexpected error', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    sendResponse(false, ['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
}
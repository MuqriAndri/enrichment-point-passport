<?php
// Set error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
header('Content-Type: application/json');

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

function cleanOldProfilePictures($userId, $uploadDir) {
    $pattern = $uploadDir . '/profile_' . $userId . '_*';
    $existingFiles = glob($pattern);
    
    foreach ($existingFiles as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
}

// Validate session
if (!isset($_SESSION['user_id'])) {
    sendResponse(false, ['error' => 'Unauthorized'], 401);
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, ['error' => 'Method not allowed'], 405);
}

try {
    // Validate file upload
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        $errorCode = $_FILES['avatar']['error'] ?? 'No file uploaded';
        logError('File upload error', ['code' => $errorCode]);
        sendResponse(false, ['error' => 'File upload failed', 'code' => $errorCode], 400);
    }

    $file = $_FILES['avatar'];
    
    // Basic path setup
    // $baseDir = '/var/www/html/enrichment-point-passport';
    $baseDir = __DIR__ . '/..';
    $uploadDir = $baseDir . '/assets/images/uploads/profile';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0775, true)) {
            logError('Failed to create directory', ['path' => $uploadDir]);
            sendResponse(false, ['error' => 'Server configuration error'], 500);
        }
        chmod($uploadDir, 0775);
    }

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

    // Clean up old profile pictures before uploading new one
    cleanOldProfilePictures($_SESSION['user_id'], $uploadDir);

    // Generate unique filename
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = generateUniqueFilename($_SESSION['user_id'], $extension);
    $filepath = $uploadDir . '/' . $filename;

    // Attempt to move the file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        $error = error_get_last();
        logError('Move uploaded file failed', [
            'from' => $file['tmp_name'],
            'to' => $filepath,
            'error' => $error
        ]);
        sendResponse(false, ['error' => 'Failed to save file'], 500);
    }

    // Set file permissions
    chmod($filepath, 0664);

    // Update relative path for database and frontend
    $relativePath = '/enrichment-point-passport/assets/images/uploads/profile/' . $filename;

    // Update database
    $sql = "UPDATE profiles.users SET profile_picture = :profile_picture WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    
    if (!$stmt->execute([
        ':profile_picture' => $relativePath,
        ':user_id' => $_SESSION['user_id']
    ])) {
        unlink($filepath);
        logError('Database update failed', ['user_id' => $_SESSION['user_id']]);
        sendResponse(false, ['error' => 'Failed to update profile picture in database'], 500);
    }

    // Update session
    $_SESSION['profile_picture'] = $relativePath;

    // Return success
    sendResponse(true, [
        'message' => 'Profile picture updated successfully',
        'avatarUrl' => $relativePath
    ]);

} catch (Exception $e) {
    logError('Unexpected error', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    sendResponse(false, ['error' => 'An unexpected error occurred'], 500);
}
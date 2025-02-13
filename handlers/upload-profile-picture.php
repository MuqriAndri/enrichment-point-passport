<?php
session_start();
require_once '../database/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // Check if file was uploaded
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error occurred');
    }

    $file = $_FILES['avatar'];
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $file['tmp_name']);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPG, PNG and GIF are allowed.');
    }

    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('File too large. Maximum size is 5MB.');
    }

    // Define upload directory path
    $uploadDir = __DIR__ . '/../assets/images/uploads/profile/';

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $_SESSION['user_id'] . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to save file');
    }

    // Delete old avatar if exists
    if (isset($_SESSION['profile_picture']) && !empty($_SESSION['profile_picture'])) {
        $oldFile = __DIR__ . '/..' . $_SESSION['profile_picture'];
        if (file_exists($oldFile) && is_file($oldFile)) {
            unlink($oldFile);
        }
    }

    // Set the relative path for database and frontend
    $relativePath = '/enrichment-point-passport/assets/images/uploads/profile/' . $filename;

    // Update database
    $sql = "UPDATE profiles.users SET profile_picture = :profile_picture WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':profile_picture' => $relativePath,
        ':user_id' => $_SESSION['user_id']
    ]);

    // Update session
    $_SESSION['profile_picture'] = $relativePath;

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Profile picture updated successfully',
        'avatarUrl' => $relativePath
    ]);

} catch (Exception $e) {
    error_log('Profile picture upload error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>
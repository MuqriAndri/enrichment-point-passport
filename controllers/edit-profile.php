<?php
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

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
    // Include database connection
    require_once __DIR__ . '/../config/database.php';
    // Make sure we have $pdo defined from database.php
    if (!isset($pdo)) {
        throw new Exception('Database connection not available');
    }

    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Log received data for debugging
    error_log('Edit profile request received: ' . json_encode($data));
    
    // Basic validation
    if (empty($data['user_email']) || !filter_var($data['user_email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }

    // Prepare base SQL for all users - adjust table name if needed based on your DB setup
    $sql = "UPDATE users SET 
            user_email = :user_email,
            school = :school
            WHERE user_id = :user_id";
    
    $params = [
        ':user_email' => $data['user_email'],
        ':school' => $data['school'],
        ':user_id' => $_SESSION['user_id']
    ];

    // Additional fields for students
    if ($_SESSION['role'] === 'student') {
        $sql = "UPDATE users SET 
                user_email = :user_email,
                school = :school,
                programme = :programme,
                intake = :intake,
                group_code = :group_code
                WHERE user_id = :user_id";
        
        $params[':programme'] = $data['programme'] ?? '';
        $params[':intake'] = $data['intake'] ?? '';
        $params[':group_code'] = $data['group_code'] ?? '';
    }

    // Log SQL and parameters
    error_log('SQL: ' . $sql);
    error_log('Params: ' . json_encode($params));

    // Execute update
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($params);
    
    if (!$result) {
        error_log('Update failed: ' . json_encode($pdo->errorInfo()));
        throw new Exception('Failed to update profile');
    }

    if ($stmt->rowCount() > 0) {
        // Update session data
        $_SESSION['user_email'] = $data['user_email'];
        $_SESSION['school'] = $data['school'];
        
        if ($_SESSION['role'] === 'student') {
            $_SESSION['programme'] = $data['programme'] ?? $_SESSION['programme'];
            $_SESSION['intake'] = $data['intake'] ?? $_SESSION['intake'];
            $_SESSION['group_code'] = $data['group_code'] ?? $_SESSION['group_code'];
        }

        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'No changes were made'
        ]);
    }

} catch (PDOException $e) {
    error_log('PDO Error in edit-profile.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred. Please try again later.'
    ]);
} catch (Exception $e) {
    error_log('Error in edit-profile.php: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
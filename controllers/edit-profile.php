<?php
session_start();
require_once '../config/database.php';

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
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Basic validation
    if (empty($data['user_email']) || !filter_var($data['user_email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }

    // Prepare base SQL for all users
    $sql = "UPDATE profiles.users SET 
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
        $sql = "UPDATE profiles.users SET 
                user_email = :user_email,
                school = :school,
                programme = :programme,
                intake = :intake,
                group_code = :group_code
                WHERE user_id = :user_id";
        
        $params[':programme'] = $data['programme'];
        $params[':intake'] = $data['intake'];
        $params[':group_code'] = $data['group_code'];
    }

    // Execute update
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        // Update session data
        $_SESSION['user_email'] = $data['user_email'];
        $_SESSION['school'] = $data['school'];
        
        if ($_SESSION['role'] === 'student') {
            $_SESSION['programme'] = $data['programme'];
            $_SESSION['intake'] = $data['intake'];
            $_SESSION['group_code'] = $data['group_code'];
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

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>
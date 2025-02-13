<?php
session_start();

define('BASE_URL', 'http://13.213.31.195/enrichment-point-passport');

// Add database configuration
require_once 'database/config.php';

// Get the path from URL
$request_uri = str_replace('/enrichment-point-passport', '', $_SERVER['REQUEST_URI']);
$path = trim(parse_url($request_uri, PHP_URL_PATH), '/');

// Default to login if no path
$page = $path ?: 'login';

// Redirect to dashboard if already logged in and trying to access login page
if ($page === 'login' && isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/dashboard");
    exit();
}

// List of pages that require authentication
$protected_pages = ['dashboard', 'profile']; // Added 'profile' to protected pages

// Check authentication for protected pages
if (in_array($page, $protected_pages) && !isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL);
    exit();
}

// Handle POST login separately
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_ic = trim($_POST["user_ic"] ?? '');
    $password = trim($_POST["password"] ?? '');
        
    try {
        $sql = "SELECT user_id, user_ic, full_name, password_hash, user_email FROM profiles.users WHERE user_ic = :user_ic";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":user_ic", $user_ic, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($password === $row["password_hash"]) {
                session_regenerate_id(true);
                $_SESSION["user_id"] = $row["user_id"];
                $_SESSION["user_ic"] = $row["user_ic"];
                $_SESSION["full_name"] = $row["full_name"];
                $_SESSION["user_email"] = $row["user_email"];
                
                header("Location: " . BASE_URL . "/dashboard");
                exit();
            }
        }
        
        $_SESSION["error"] = "Invalid IC number or password.";
        header("Location: " . BASE_URL);
        exit();
        
    } catch(PDOException $e) {
        $_SESSION["error"] = "Something went wrong. Please try again later.";
        header("Location: " . BASE_URL);
        exit();
    }
}

// Handle page routing
switch ($page) {
    case 'login':
        include 'templates/login-page.php';
        break;
    case 'dashboard':
        include 'templates/dashboard.php';
        break;
    case 'profile':
        include 'templates/profile.php';
        break;
    case 'logout':
        session_destroy();
        header("Location: " . BASE_URL);
        exit();
        break;
    case 'about':
        include 'templates/about.php';
        break;
    case 'contact':
        include 'templates/contact.php';
        break;
    default:
        header("HTTP/1.0 404 Not Found");
        include 'templates/404.php';
        break;
}
?>
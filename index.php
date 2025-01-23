<?php
session_start();

define('BASE_URL', 'http://18.142.237.123/enrichment-point-passport');

$page = isset($_GET['page']) ? $_GET['page'] : 'login';

switch ($page) {
    case 'login':
        include 'templates/login-page.php';
        break;
    case 'dashboard':
        include 'templates/dashboard.php';
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
<?php
define('DB_HOST', 'enrichment-point-passport-database.cte6so84691c.ap-southeast-1.rds.amazonaws.com');
define('DB_USER', 'admin');
define('DB_PASS', 'epadminpb');
define('DB_NAME', 'profiles');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
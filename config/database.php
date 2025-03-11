<?php
// Define database connection constants
define('DB_HOST', 'enrichment-point-passport-database.cte6so84691c.ap-southeast-1.rds.amazonaws.com');
define('DB_USER', 'admin');
define('DB_PASS', 'epadminpb');
define('DB_PROFILES', 'profiles');
define('DB_CCA', 'cca');

// Create connection to profiles database
try {
    $profilesDB = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_PROFILES, DB_USER, DB_PASS);
    $profilesDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Profiles database connection established successfully");
} catch(PDOException $e) {
    error_log("Profiles database connection failed: " . $e->getMessage());
    die("Profiles database connection failed: " . $e->getMessage());
}

// Create connection to cca database
try {
    $ccaDB = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_CCA, DB_USER, DB_PASS);
    $ccaDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("CCA database connection established successfully");
} catch(PDOException $e) {
    error_log("CCA database connection failed: " . $e->getMessage());
    die("CCA database connection failed: " . $e->getMessage());
}

// For backward compatibility, set $pdo to one of the connections
// Choose which one should be the default based on your application needs
$pdo = $profilesDB;
?>
<?php
// Define database connection constants
define('DB_HOST', 'enrichment-point-passport-database.cte6so84691c.ap-southeast-1.rds.amazonaws.com');
define('DB_USER', 'admin');
define('DB_PASS', 'epadminpb');
define('DB_PROFILES', 'profiles');
define('DB_CCA', 'cca');
define('DB_EVENTS', 'events');

// Improved error logging
function logDatabaseError($message, $exception = null) {
    $errorInfo = $exception ? ' Error: ' . $exception->getMessage() : '';
    error_log('Database Connection: ' . $message . $errorInfo);
}

// Create connection to profiles database
try {
    $profilesDB = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_PROFILES . ";charset=utf8mb4", 
        DB_USER, 
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    logDatabaseError("Profiles database connection established successfully");
} catch(PDOException $e) {
    logDatabaseError("Profiles database connection failed", $e);
    // Use a softer approach than die() to allow fallback behaviors
    $profilesDB = null;
}

// Create connection to cca database
try {
    $ccaDB = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_CCA . ";charset=utf8mb4", 
        DB_USER, 
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    logDatabaseError("CCA database connection established successfully");
} catch(PDOException $e) {
    logDatabaseError("CCA database connection failed", $e);
    $ccaDB = null;
}

// Create connection to events database
$eventsDB = null; // Initialize to null
$eventsRetryCount = 0;
$maxRetries = 2;

while ($eventsDB === null && $eventsRetryCount <= $maxRetries) {
    try {
        $eventsDB = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_EVENTS . ";charset=utf8mb4", 
            DB_USER, 
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                // Add connection timeout settings
                PDO::ATTR_TIMEOUT => 5, // 5 second timeout
            ]
        );
        
        // Verify connection works with basic query
        $testStmt = $eventsDB->query("SELECT 1");
        if (!$testStmt) {
            throw new PDOException("Database connection test failed");
        }
        
        logDatabaseError("Events database connection established successfully (attempt " . ($eventsRetryCount + 1) . ")");
    } catch(PDOException $e) {
        logDatabaseError("Events database connection failed (attempt " . ($eventsRetryCount + 1) . ")", $e);
        $eventsDB = null;
        
        // Sleep briefly before retry
        if ($eventsRetryCount < $maxRetries) {
            usleep(500000); // 500ms delay between attempts
        }
    }
    
    $eventsRetryCount++;
}

// If events database still failed after retries, log a critical error
if ($eventsDB === null) {
    logDatabaseError("Critical: Events database connection failed after {$maxRetries} attempts");
}

// Check if we have at least one valid connection
if ($profilesDB === null && $ccaDB === null && $eventsDB === null) {
    logDatabaseError("All database connections failed");
    header('HTTP/1.1 503 Service Unavailable');
    echo "Database connection error. Please try again later.";
    exit;
}

// For backward compatibility, set $pdo to one of the connections
// Choose profiles DB first if available, otherwise use CCA
$pdo = $profilesDB !== null ? $profilesDB : ($ccaDB !== null ? $ccaDB : $eventsDB);
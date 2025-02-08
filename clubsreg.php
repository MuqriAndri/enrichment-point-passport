<?php
// Database configuration
$host = 'localhost'; // database host
$dbname = 'database_name'; // database name
$username = 'username'; //  database username
$password = 'password'; //  database password

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare and execute the SQL statement
    $stmt = $pdo->prepare("SELECT id, name FROM clubs WHERE available = 1");
    $stmt->execute();

    // Fetch all available clubs
    $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if there are available clubs
    if (count($clubs) > 0) {
        echo "<h2>Available Clubs for Registration</h2>";
        echo "<form action='register.php' method='post'>";
        echo "<select name='club_id'>";

        // Loop through the clubs and create an option for each
        foreach ($clubs as $club) {
            echo "<option value='" . htmlspecialchars($club['id']) . "'>" . htmlspecialchars($club['name']) . "</option>";
        }

        echo "</select>";
        echo "<input type='submit' value='Register'>";
        echo "</form>";
    } else {
        echo "<p>No clubs are currently available for registration.</p>";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$pdo = null;
?>

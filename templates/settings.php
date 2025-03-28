<?php
session_start();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update dark mode setting in session
    if (isset($_POST['dark_mode'])) {
        $_SESSION['dark_mode'] = true;
    } else {
        $_SESSION['dark_mode'] = false;
    }
}

// Check if dark mode is enabled
$darkMode = isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] === true;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/settings.css">
</head>
<body class="<?php echo $darkMode ? 'dark-mode' : ''; ?>">
    <div class="container">
        <h2>Settings</h2>
        
        <form action="settings.php" method="POST">
            <div class="form-group">
                <label for="dark_mode">Enable Dark Mode</label>
                <input type="checkbox" id="dark_mode" name="dark_mode" <?php echo $darkMode ? 'checked' : ''; ?>>
            </div>
            
            <button type="submit">Save Settings</button>
        </form>
    </div>
</body>
</html>

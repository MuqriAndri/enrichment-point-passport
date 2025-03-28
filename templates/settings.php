<?php
// Start the session to access session variables
session_start();

// Default settings
$dark_mode = isset($_SESSION['dark_mode']) ? $_SESSION['dark_mode'] : 'disabled';
$email_notifications = isset($_SESSION['email_notifications']) ? $_SESSION['email_notifications'] : 'enabled';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_settings'])) {
    // Save dark mode setting
    if (isset($_POST['dark_mode'])) {
        $_SESSION['dark_mode'] = 'enabled';
    } else {
        $_SESSION['dark_mode'] = 'disabled';
    }

    // Save email notifications setting
    if (isset($_POST['email_notifications'])) {
        $_SESSION['email_notifications'] = 'enabled';
    } else {
        $_SESSION['email_notifications'] = 'disabled';
    }

    // Redirect to refresh the page after saving settings
    header("Location: settings.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Settings</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/settings.css">
</head>
<body class="<?php echo $dark_mode == 'enabled' ? 'dark-mode' : ''; ?>">
    <div class="container">
        <h2>User Settings</h2>
        
        <!-- Settings Form -->
        <form method="POST" action="settings.php">
            <div class="form-group">
                <label for="dark_mode">Enable Dark Mode</label>
                <input type="checkbox" name="dark_mode" id="dark_mode" <?php echo $dark_mode == 'enabled' ? 'checked' : ''; ?>>
            </div>

            <div class="form-group">
                <label for="email_notifications">Enable Email Notifications</label>
                <input type="checkbox" name="email_notifications" id="email_notifications" <?php echo $email_notifications == 'enabled' ? 'checked' : ''; ?>>
            </div>

            <button type="submit" name="save_settings">Save Settings</button>
        </form>

        <!-- Danger Zone Section -->
        <div class="danger-zone">
            <h3>Danger Zone</h3>
            <p>This will permanently delete your account. Please proceed with caution.</p>
            <button class="deactivate-btn">Deactivate Account</button>
        </div>
    </div>

    <script src="assets/js/settings.js"></script>
</body>
</html>

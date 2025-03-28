<?php
session_start();

// Save settings when form is submitted
if (isset($_POST['save_settings'])) {
    // Dark Mode Setting
    if (isset($_POST['dark_mode']) && $_POST['dark_mode'] == 'on') {
        $_SESSION['dark_mode'] = 'enabled';
    } else {
        $_SESSION['dark_mode'] = 'disabled';
    }

    // Email Notifications Setting
    if (isset($_POST['email_notifications']) && $_POST['email_notifications'] == 'on') {
        $_SESSION['email_notifications'] = 'enabled';
    } else {
        $_SESSION['email_notifications'] = 'disabled';
    }

    // Other settings logic can be added here

    // Save settings and refresh the page
    header("Location: settings.php");
    exit;
}

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
<body class="<?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 'enabled' ? 'dark-mode' : ''; ?>">

    <div class="container">
        <h2>Account Settings</h2>
        <form method="POST" action="settings.php">
            <!-- Dark Mode Setting -->
            <div class="form-group">
                <label for="dark_mode">Enable Dark Mode</label>
                <input type="checkbox" name="dark_mode" id="dark_mode" <?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 'enabled' ? 'checked' : ''; ?>>
            </div>

            <!-- Email Notifications Setting -->
            <div class="form-group">
                <label for="email_notifications">Enable Email Notifications</label>
                <input type="checkbox" name="email_notifications" id="email_notifications" <?php echo isset($_SESSION['email_notifications']) && $_SESSION['email_notifications'] == 'enabled' ? 'checked' : ''; ?>>
            </div>

            <!-- Add other settings here -->
            <!-- Example: Changing password -->
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password">
            </div>

            <!-- Save Settings Button -->
            <button type="submit" name="save_settings">Save Settings</button>
        </form>
    </div>

</body>
</html>

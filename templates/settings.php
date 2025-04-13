<?php
// Start the session
session_start();

// Redirect to the home page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL);
    exit();
}

// Fetch user data from the session
$user_data = $_SESSION['user']; // Assuming user data is stored in session as 'user'

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle the form submissions (e.g., update password, language, and dark mode)
    if (isset($_POST['change_password'])) {
        // Change password logic here
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password == $confirm_password) {
            // Update password in the database
            // Example: updatePassword($user_data['id'], $new_password);
            // You can write your function to update the password here.
        } else {
            $error_message = "Passwords do not match!";
        }
    }

    if (isset($_POST['change_language'])) {
        // Change language logic here
        $language = $_POST['language'];
        // Update user language preference (store it in the session or database)
        $_SESSION['language'] = $language;
    }

    if (isset($_POST['toggle_dark_mode'])) {
        // Toggle dark mode logic
        $dark_mode = $_POST['dark_mode'] == 'on' ? 'on' : 'off';
        // Store dark mode preference in the session or database
        $_SESSION['dark_mode'] = $dark_mode;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="path/to/settings.css">
</head>
<body class="<?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 'on' ? 'dark-mode' : ''; ?>">
    <div class="container">
        <h2>Settings</h2>

        <!-- Change Password Section -->
        <div class="settings-section">
            <h3>Change Password</h3>
            <?php if (isset($error_message)) { echo "<p class='error'>$error_message</p>"; } ?>
            <form action="settings.php" method="POST">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" name="change_password">Save Password</button>
            </form>
        </div>

        <!-- Change Language Section -->
        <div class="settings-section">
            <h3>Change Language</h3>
            <form action="settings.php" method="POST">
                <div class="form-group">
                    <label for="language">Select Language</label>
                    <select name="language" id="language" required>
                        <option value="en" <?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'en') ? 'selected' : ''; ?>>English</option>
                        <option value="ms" <?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'ms') ? 'selected' : ''; ?>>Malay</option>
                    </select>
                </div>
                <button type="submit" name="change_language">Save Language</button>
            </form>
        </div>

        <!-- Dark Mode Section -->
        <div class="settings-section">
            <h3>Dark Mode</h3>
            <form action="settings.php" method="POST">
                <div class="form-group">
                    <input type="checkbox" name="dark_mode" id="dark_mode" value="on" <?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 'on' ? 'checked' : ''; ?>>
                    <label for="dark_mode">Enable Dark Mode</label>
                </div>
                <button type="submit" name="toggle_dark_mode">Save Dark Mode</button>
            </form>
        </div>
    </div>
</body>
</html>
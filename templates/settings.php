<?php
session_start();

// Simulating some user data, you should replace it with actual data from your database.
$user = [
    'username' => 'johndoe',
    'email' => 'johndoe@example.com',
];

// Handle form submissions (e.g., updating username, email, or password).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'])) {
        // Process the username change.
        $user['username'] = htmlspecialchars($_POST['username']);
    }
    if (isset($_POST['email'])) {
        // Process the email change.
        $user['email'] = htmlspecialchars($_POST['email']);
    }
    if (isset($_POST['password']) && !empty($_POST['password'])) {
        // Process the password change.
        // Here, you would hash the password and update it in the database.
        $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        // Save the new password in the database.
    }

    // You could also handle other settings like dark mode toggle here.
    if (isset($_POST['dark_mode'])) {
        $_SESSION['dark_mode'] = $_POST['dark_mode'] == 'on' ? true : false;
    }
}

// Set the dark mode class based on session data.
$dark_mode_class = isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark-mode' : '';
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
<body class="<?php echo $dark_mode_class; ?>">
    <div class="container">
        <h2>User Settings</h2>
        
        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password">
                <small>Leave blank if you don't want to change your password.</small>
            </div>
            
            <div class="form-group">
                <label for="dark_mode">Enable Dark Mode</label>
                <input type="checkbox" id="dark_mode" name="dark_mode" <?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'checked' : ''; ?>>
            </div>

            <button type="submit">Save Changes</button>
        </form>
        
        <!-- Danger Zone for account deactivation -->
        <div class="danger-zone">
            <h3>Danger Zone</h3>
            <p>Be careful when deactivating your account.</p>
            <button class="deactivate-btn">Deactivate Account</button>
        </div>
    </div>
</body>
</html>

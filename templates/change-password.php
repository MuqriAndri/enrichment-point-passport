<?php
session_start();

// Optional: Toggle dark mode from this page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_dark_mode'])) {
    $_SESSION['dark_mode'] = isset($_POST['dark_mode']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '/enrichment-point-passport');
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL);
    exit();
}

// Database connection
require_once '../config/database.php';

$message = "";

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $user_id = $_SESSION['user_id'];
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        $message = "New passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if ($user && $current === $user['password']) {
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $update->execute([$new, $user_id]);
            $message = "Password changed successfully.";
        } else {
            $message = "Current password is incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/settings.css">
</head>
<body class="<?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark' : ''; ?>">
    <div class="form-wrapper">
        <a class="back" href="<?php echo BASE_URL; ?>/settings">&#8592; Back to Settings</a>
        <h1>Change Password</h1>

        <?php if (!empty($message)) : ?>
            <p class="form-message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="POST" action="" class="password-form">
            <div class="form-group">
                <label for="current_password">Current Password:</label>
                <input type="password" name="current_password" id="current_password" required>
            </div>

            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" id="new_password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>

            <div class="form-group">
                <label>
                <input type="checkbox" name="dark_mode" <?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'checked' : ''; ?>>
                Enable Dark Mode
                </label>
            </div>
            <button type="submit" name="toggle_dark_mode" class="btn-submit">Save Theme</button>
            <button type="submit" name="change_password" class="btn-submit">Change Password</button>
        </form>
    </div>
</body>
</html>

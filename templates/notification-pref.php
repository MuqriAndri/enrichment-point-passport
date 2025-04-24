<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!defined('BASE_URL')) {
    define('BASE_URL', '/enrichment-point-passport');
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL);
    exit();
}

require_once '../config/database.php';

$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_notification'])) {
    $user_id = $_SESSION['user_id'];
    $email_pref = isset($_POST['email_notification']) ? 1 : 0;
    $sms_pref = isset($_POST['sms_notification']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE users SET email_notification = ?, sms_notification = ? WHERE user_id = ?");
    $stmt->execute([$email_pref, $sms_pref, $user_id]);
    $message = "Preferences updated successfully.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notification Preferences</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/settings.css">
</head>
<body class="<?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark' : ''; ?>">
    <div class="form-wrapper">
        <a class="back" href="<?php echo BASE_URL; ?>/settings">&#8592; Back to Settings</a>
        <h1>Notification Preferences</h1>

        <?php if (!empty($message)) : ?>
            <p class="form-message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="POST" class="password-form">
            <div class="form-group">
                <label>
                    <input type="checkbox" name="email_notification" value="1">
                    Enable Email Notifications
                </label>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="sms_notification" value="1">
                    Enable SMS Notifications
                </label>
            </div>

            <button type="submit" name="save_notification" class="btn-submit">Save Preferences</button>
        </form>
    </div>
</body>
</html>
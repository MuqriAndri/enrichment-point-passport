<?php
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

// Save preferences
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_display'])) {
    $user_id = $_SESSION['user_id'];
    $dark_mode = isset($_POST['dark_mode']) ? 1 : 0;
    $language = $_POST['language'];

    $stmt = $pdo->prepare("UPDATE users SET dark_mode = ?, language_pref = ? WHERE user_id = ?");
    $stmt->execute([$dark_mode, $language, $user_id]);
    $message = "Display preferences saved.";
}

// Load preferences for the current user
$stmt = $pdo->prepare("SELECT dark_mode, language_pref FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$pref = $stmt->fetch();

// Determine if dark mode is enabled
$isDark = $pref && $pref['dark_mode'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Display Preferences</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/settings.css">
</head>
<body class="<?php echo $isDark ? 'dark' : ''; ?>">
    <div class="form-wrapper">
        <a class="back" href="<?php echo BASE_URL; ?>/settings">&#8592; Back to Settings</a>
        <h1>Display Preferences</h1>

        <?php if (!empty($message)) : ?>
            <p class="form-message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="POST" class="password-form">
            <div class="form-group">
                <label>
                    <input type="checkbox" name="dark_mode" value="1" <?php if ($pref && $pref['dark_mode']) echo 'checked'; ?>>
                    Enable Dark Mode
                </label>
            </div>

            <div class="form-group">
                <label for="language">Language:</label>
                <select name="language" id="language" required>
                    <option value="en" <?php if ($pref && $pref['language_pref'] === 'en') echo 'selected'; ?>>English</option>
                    <option value="ms" <?php if ($pref && $pref['language_pref'] === 'ms') echo 'selected'; ?>>Malay</option>
                </select>
            </div>

            <button type="submit" name="save_display" class="btn-submit">Save Preferences</button>
        </form>
    </div>
</body>
</html>

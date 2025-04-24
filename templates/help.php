<?php
session_start();

if (!defined('BASE_URL')) {
    define('BASE_URL', '/enrichment-point-passport');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Help</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/settings.css">
</head>
<body class="<?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'dark' : ''; ?>">
    <div class="form-wrapper">
        <a class="back" href="<?php echo BASE_URL; ?>/settings">&#8592; Back to Settings</a>
        <h1>Help & Support</h1>

        <p>If you're experiencing issues or have questions about how to use the Enrichment Point Passport system, feel free to reach out or explore the FAQs.</p>

        <ul style="margin-top: 1.5rem; line-height: 1.7;">
            <li>🔹 Having trouble changing your password?</li>
            <li>🔹 Not receiving email notifications?</li>
            <li>🔹 Need to update your account details?</li>
        </ul>

        <p style="margin-top: 1.5rem;">Please check the <a href="<?php echo BASE_URL; ?>/templates/faqs.php">FAQs</a> or contact support via the <a href="<?php echo BASE_URL; ?>/templates/contact.php">Contact Us</a> page.</p>
    </div>
</body>
</html>
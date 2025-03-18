<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Enrichment Point Passport</title>
    <link rel="stylesheet" href="settings.css">
</head>
<body>
    <div class="settings-container">
        <h2>Settings</h2>
<div class="container">
    <div class="history-title">Enrichment Points History</div>
    <!-- Example static data for testing -->
    <div class="history-grid">
        <div class="history-card">
            <div class="activity-name">Workshop on AI</div>
            <div class="ep-points">10 EP</div>
            <div class="date-range">2024-03-10</div>
        </div>
        <div class="history-card">
            <div class="activity-name">Coding Bootcamp</div>
            <div class="ep-points">15 EP</div>
            <div class="date-range">2024-02-15</div>
        </div>
        <div class="history-card">
            <div class="activity-name">Research Presentation</div>
            <div class="ep-points">20 EP</div>
            <div class="date-range">2024-01-22</div>
        </div>
    </div>
    <!-- If no data, show this message -->
    <div class="no-history">No history available for this user.</div>
    </div>
</body>
</html>

        <form method="POST" class="settings-form">
            <h3>Profile Information</h3>
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" required>

            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>

            <button type="submit" name="update_profile">Update Profile</button>
        </form>

        <form method="POST" class="settings-form">
            <h3>Change Password</h3>
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" required>

            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" required>

            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit" name="update_password">Change Password</button>
        </form>
    </div>
</body>
</html>
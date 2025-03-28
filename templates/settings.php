<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Settings</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/settings.css">
    <script>
        function toggleDarkMode() {
            document.body.classList.toggle("dark-mode");
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Account Settings</h2>

        <form action="settings_update.php" method="POST" enctype="multipart/form-data">
            
            <!-- Profile Picture Upload -->
            <div class="form-group">
                <label for="profile-picture">Profile Picture</label>
                <input type="file" id="profile-picture" name="profile_picture" accept="image/*">
            </div>

            <!-- Full Name -->
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Enter your full name" required>
            </div>

            <!-- Email Address -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>

            <!-- Password Update -->
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" placeholder="Enter a new password">
            </div>

            <div class="form-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm new password">
            </div>

            <!-- Notification Preferences -->
            <div class="form-group">
                <label>Notification Preferences</label>
                <input type="checkbox" id="email-notifications" name="email_notifications">
                <label for="email-notifications">Receive Email Notifications</label><br>

                <input type="checkbox" id="sms-notifications" name="sms_notifications">
                <label for="sms-notifications">Receive SMS Notifications</label>
            </div>

            <!-- Dark Mode Toggle -->
            <div class="form-group">
                <label>Dark Mode</label>
                <input type="checkbox" id="dark-mode-toggle" onclick="toggleDarkMode()">
                <label for="dark-mode-toggle">Enable Dark Mode</label>
            </div>

            <!-- Two-Factor Authentication -->
            <div class="form-group">
                <label for="2fa">Two-Factor Authentication (2FA)</label>
                <select id="2fa" name="two_factor">
                    <option value="disabled">Disabled</option>
                    <option value="email">Enable via Email</option>
                    <option value="sms">Enable via SMS</option>
                </select>
            </div>

            <button type="submit">Save Changes</button>
        </form>

        <!-- Account Deactivation -->
        <div class="danger-zone">
            <h3>Danger Zone</h3>
            <p>If you wish to deactivate your account, click the button below.</p>
            <button class="deactivate-btn" onclick="confirmDeactivation()">Deactivate Account</button>
        </div>
    </div>

    <script>
        function confirmDeactivation() {
            if (confirm("Are you sure you want to deactivate your account? This action cannot be undone.")) {
                window.location.href = "deactivate_account.php";
            }
        }
    </script>
</body>
</html>

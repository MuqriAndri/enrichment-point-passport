<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover, height=device-height">
    <title>Enrichment Point Passport</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/login.css">
    <link rel="icon" href="https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com/logo/politeknik-brunei-logo-2.png" type="image/png">
</head>

<body>
    <nav class="nav">
        <a href="https://lms.pb.edu.bn" target="_blank" rel="noopener noreferrer">PB LMS</a>
        <a href="https://pb.edu.bn" target="_blank" rel="noopener noreferrer">PB Website</a>
        <a href="<?php echo BASE_URL; ?>/about">About</a>
        <a href="<?php echo BASE_URL; ?>/contact">Contact</a>
    </nav>

    <div class="container">
        <div class="left-content">
            <img src="https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com/logo/politeknik-brunei-logo.png" alt="Politeknik Brunei Logo" class="logo">
            <h1 class="title">POLITEKNIK BRUNEI<br>ENRICHMENT POINT<br>PASSPORT</h1>
            <p class="subtitle">Enriching experiences, evaluating your success.</p>
        </div>

        <div class="login-box">
            <form action="<?php echo BASE_URL; ?>/index.php" method="POST">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message">
                        <?php
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <div>
                    <label for="user_ic">IC Number</label>
                    <input type="text" id="user_ic" name="user_ic" placeholder="Enter your IC Number" required>
                </div>
                <div>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="login-btn">Log In</button>
                <a href="<?php echo BASE_URL; ?>/forgot-password" class="forgot-link">Forgot your password?</a>
            </form>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-icons">
                <div class="icon-container">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                    </svg>
                    <span>Track Progress</span>
                </div>
                <div class="icon-container">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                    </svg>
                    <span>Learn & Grow</span>
                </div>
                <div class="icon-container">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 15C8.7 15 6 12.3 6 9V4.5C6 3.1 7.1 2 8.5 2H15.5C16.9 2 18 3.1 18 4.5V9C18 12.3 15.3 15 12 15Z" />
                        <path d="M20 20H4C3.4 20 3 19.6 3 19V18C3 14.7 5.7 12 9 12H15C18.3 12 21 14.7 21 18V19C21 19.6 20.6 20 20 20Z" />
                    </svg>
                    <span>Achieve Excellence</span>
                </div>
            </div>
            <p>Empowering students through enrichment and achievement tracking</p>
        </div>
    </footer>
</body>

</html>
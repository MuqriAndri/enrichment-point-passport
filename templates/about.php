<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/enrichment-point-passport');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover, height=device-height">
    <title>About - Politeknik Brunei Enrichment Point Passport</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/settings.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/about.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com/logo/politeknik-brunei-logo-2.png" type="image/png">
</head>

<body>
    <nav class="nav">
        <a href="https://pb.edu.bn" target="_blank" rel="noopener noreferrer">PB Website</a>
        <a href="<?php echo BASE_URL; ?>/about">About</a>
        <a href="<?php echo BASE_URL; ?>/contact">Contact</a>
    </nav>

    <div class="main-container">
        <img src="https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com/logo/politeknik-brunei-logo.png" alt="Politeknik Brunei Logo" class="logo">

        <div class="content-wrapper">
            <h1 class="page-title">About Us</h1>

            <div class="about-content">
                <p>Enrichment Point Passport is a student-made project that helps make CCAs more exciting and easy to track! From checking in to events with your location to viewing your enrichment points in one place — this platform is built to help students stay involved and keep things simple for everyone.</p>

                <div class="about-section">
                    <h3><i class="fas fa-eye"></i> Vision</h3>
                    <p>To become a leading digital solution that empowers students and institutions through seamless, transparent, and technology-driven co-curricular engagement.</p>
                </div>

                <div class="about-section">
                    <h3><i class="fas fa-bullseye"></i> Our Mission</h3>
                    <p>Modernize and simplify the tracking of student participation in co-curricular activities (CCAs) through smart automation.</p>
                </div>

                <div class="about-section">
                    <h3><i class="fas fa-lightbulb"></i> Key Features</h3>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i> Location-based attendance verification</li>
                        <li><i class="fas fa-medal"></i> Automated enrichment point tracking</li>
                        <li><i class="fas fa-calendar-alt"></i> Upcoming event notifications</li>
                        <li><i class="fas fa-chart-line"></i> Personal participation insights</li>
                    </ul>
                </div>

                <div class="about-section">
                    <h3><i class="fas fa-users"></i> Our Team</h3>
                    <p>Enrichment Point Passport was developed as a final year project by students at Politeknik Brunei, combining technical innovation with real campus needs.</p>
                </div>

                <div class="button-container">
                    <a href="<?php echo BASE_URL; ?>" class="back-button">Return to Homepage</a>
                </div>
            </div>
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
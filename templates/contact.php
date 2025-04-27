<?php
session_start();
$isDark = isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'];

if (!defined('BASE_URL')) {
    define('BASE_URL', '/enrichment-point-passport');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Politeknik Brunei</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/settings.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/contact.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com/logo/politeknik-brunei-logo-2.png" type="image/png">
</head>

<body class="<?php echo $isDark ? 'dark' : ''; ?>">
    <nav class="nav">
        <a href="https://pb.edu.bn" target="_blank" rel="noopener noreferrer">PB Website</a>
        <a href="<?php echo BASE_URL; ?>/about">About</a>
        <a href="<?php echo BASE_URL; ?>/contact">Contact</a>
        <button id="darkModeToggle" class="dark-mode-toggle" aria-label="Toggle dark mode">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="5"></circle>
                <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"></path>
            </svg>
        </button>
    </nav>

    <div class="main-container">
        <img src="https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com/logo/politeknik-brunei-logo.png" alt="Politeknik Brunei Logo" class="logo">

        <div class="content-wrapper">
            <h1 class="page-title">Contact Us</h1>

            <div class="contact-content">
                <p class="intro-text">Have questions, feedback, or need assistance with the Enrichment Point Passport platform? We're here to help! Reach out using any of the methods below.</p>

                <div class="contact-section">
                    <h3><i class="fas fa-envelope"></i> Email Us</h3>
                    <p>For general inquiries or technical support:</p>
                    <p>Tel: +673 2234630 / 2234650</p>
                    <p>Fax: 2234469 / 2234449</p>
                    <a href="mailto:contact@pb.edu.bn" class="contact-link">contact@pb.edu.bn</a>
                </div>

                <div class="contact-section">
                    <h3><i class="fas fa-comment-alt"></i> Send a Message</h3>
                    <form class="contact-form">
                        <div class="form-group">
                            <label for="name">Your Name</label>
                            <input type="text" id="name" name="name" placeholder="Enter your name" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Your Email</label>
                            <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                        </div>

                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" placeholder="Enter message subject" required>
                        </div>

                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5" placeholder="Type your message here..." required></textarea>
                        </div>

                        <button type="submit" class="btn-submit">Send Message</button>
                    </form>
                </div>

                <div class="contact-section">
                    <h3><i class="fas fa-map-marker-alt"></i> Find Us</h3>
                    <p>Politeknik Brunei Campus</p>
                    <p>Block 2E, Ong Sum Ping</p>
                    <p>Condominium, Jalan Ong Sum Ping</p>
                    <p>Bandar Seri Begawan, BA1311</p>
                    <p>Brunei Darussalam</p>
                </div>

                <div class="contact-section">
                    <h3><i class="fas fa-clock"></i> Operating Hours</h3>
                    <p>Monday - Saturday: 8:00 AM - 4:30 PM</p>
                    <p>Friday: 8:00 AM - 12:00 PM, 2:00 PM - 4:30 PM</p>
                    <p>Sunday: Closed</p>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const darkModeToggle = document.getElementById('darkModeToggle');
            const body = document.body;
            
            darkModeToggle.addEventListener('click', function() {
                body.classList.toggle('dark');
                const isDark = body.classList.contains('dark');
                
                // Save preference to sessionStorage for now
                sessionStorage.setItem('darkMode', isDark ? 'true' : 'false');
            });
            
            // Check if dark mode was previously set
            if (sessionStorage.getItem('darkMode') === 'true') {
                body.classList.add('dark');
            }
        });
    </script>
</body>

</html>

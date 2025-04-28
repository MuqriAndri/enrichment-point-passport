<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover, height=device-height, minimum-scale=1.0">
    <meta name="orientation" content="portrait">
    <title>Enrichment Point Passport</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/login.css">
    <link rel="icon" href="https://enrichment-point-passport-bucket.s3.ap-southeast-1.amazonaws.com/logo/politeknik-brunei-logo-2.png" type="image/png">
</head>

<body>
    <nav class="nav">
        <div class="nav-left">
            <button class="dark-mode-toggle" id="dark-mode-toggle" aria-label="Toggle dark mode">
                <svg class="moon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
                <svg class="sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="5"></circle>
                    <line x1="12" y1="1" x2="12" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="23"></line>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                    <line x1="1" y1="12" x2="3" y2="12"></line>
                    <line x1="21" y1="12" x2="23" y2="12"></line>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                </svg>
            </button>
        </div>
        <div class="nav-links">
            <a href="https://lms.pb.edu.bn" target="_blank" rel="noopener noreferrer">PB LMS</a>
            <a href="https://pb.edu.bn" target="_blank" rel="noopener noreferrer">PB Website</a>
            <a href="<?php echo BASE_URL; ?>/about">About</a>
            <a href="<?php echo BASE_URL; ?>/contact">Contact</a>
        </div>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const darkModeToggle = document.getElementById('dark-mode-toggle');
            const body = document.body;
            
            // Check for saved user preference and set initial state
            const darkMode = localStorage.getItem('darkMode');
            
            // If dark mode was previously enabled, apply it
            if (darkMode === 'enabled') {
                body.classList.add('dark');
            }
            
            darkModeToggle.addEventListener('click', function() {
                // Toggle dark mode
                body.classList.toggle('dark');
                
                // Save user preference to localStorage
                if (body.classList.contains('dark')) {
                    localStorage.setItem('darkMode', 'enabled');
                } else {
                    localStorage.setItem('darkMode', null);
                }
            });
        });
    </script>
</body>

</html>
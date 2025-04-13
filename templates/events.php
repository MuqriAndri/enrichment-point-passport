<<<<<<< HEAD
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Events</title>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/events.css">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
        <script src="<?php echo BASE_URL; ?>/assets/js/events.js" defer></script> <!-- Added events.js -->
    </head>

    <body>
        <div class="dashboard-container">
            <nav class="top-nav">
                <div class="nav-left">
                    <img src="<?php echo BASE_URL; ?>/assets/images/logo/politeknik-brunei-logo.png" alt="PB Logo" class="nav-logo">
                    <h2>Enrichment Point Passport</h2>
                </div>
                <div class="nav-right">
                    <div class="search-bar">
                        <input type="text" placeholder="Search events..." aria-label="Search activities">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                    <div class="nav-actions">
                        <button class="notification-btn" aria-label="Notifications">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span class="notification-badge" aria-label="3 notifications">3</span>
                        </button>
                        <div class="profile-dropdown">
                            <div class="profile-trigger" tabindex="0" role="button" aria-haspopup="true" aria-expanded="false">
                                <div class="user-avatar">
                                    <?php if (isset($_SESSION['profile_picture'])): ?>
                                        <img src="<?php echo $_SESSION['profile_picture']; ?>" alt="Profile Picture">
                                    <?php else: ?>
                                        <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                                    <?php endif; ?>
                                </div>
                                <span class="user-name"><?php echo explode(' ', $_SESSION['full_name'])[0]; ?></span>
                            </div>
                            <div class="dropdown-menu" role="menu">
                                <a href="<?php echo BASE_URL; ?>/profile" class="dropdown-item" role="menuitem">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    My Profile
                                </a>
                                <a href="<?php echo BASE_URL; ?>/settings" class="dropdown-item" role="menuitem">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="3"></circle>
                                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06-.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                    </svg>
                                    Settings
                                </a>
                                <div class="dropdown-divider" role="separator"></div>
                                <a href="<?php echo BASE_URL; ?>/logout" class="dropdown-item" role="menuitem">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" y1="12" x2="9" y2="12"></line>
                                    </svg>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="main-content">
                <div class="main-wrapper">
                    <div class="tab-navigation">
                        <a href="<?php echo BASE_URL; ?>/dashboard" class="tab-item">Dashboard</a>
                        <a href="<?php echo BASE_URL; ?>/ep" class="tab-item">Enrichment Point</a>
                        <a href="<?php echo BASE_URL; ?>/events" class="tab-item active">Events</a>
                        <a href="<?php echo BASE_URL; ?>/cca" class="tab-item">CCAs</a>
                        <a href="<?php echo BASE_URL; ?>/history" class="tab-item">History</a>
                    </div>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-error">
                            <?php
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>
                    <div class="container">
                        <h1>Upcoming and Available Events</h1>
                        <div class="event-slider">
                            <?php
                            $events = [
                                [
                                    "name" => "Event A",
                                    "description" => "New description for event A.",
                                    "date" => "2025-07-01",
                                    "time" => "09:00 AM",
                                    "location" => "New Location A",
                                    "enrichment_points" => 20,
                                    "images" => [
                                        "event-a-1.jpg",
                                        "event-a-2.jpg",
                                        "event-a-3.jpg"
                                    ]
                                ],
                                [
                                    "name" => "Event B",
                                    "description" => "New description for event B.",
                                    "date" => "2025-08-15",
                                    "time" => "10:30 AM",
                                    "location" => "New Location B",
                                    "enrichment_points" => 25,
                                    "images" => [
                                        "event-b-1.jpg",
                                        "event-b-2.jpg",
                                        "event-b-3.jpg"
                                    ]
                                ],
                                [
                                    "name" => "Event C",
                                    "description" => "New description for event C.",
                                    "date" => "2025-09-20",
                                    "time" => "02:00 PM",
                                    "location" => "New Location C",
                                    "enrichment_points" => 35,
                                    "images" => [
                                        "event-c-1.jpg",
                                        "event-c-2.jpg",
                                        "event-c-3.jpg"
                                    ]
                                ]
                            ];

                            foreach ($events as $event) {
                                echo "<div class='event-slide'>";
                                echo "<h2>" . $event['name'] . "</h2>";
                                echo "<div class='image-slider'>";
                                echo "<div class='image-box'>";
                                echo "<div class='image-container'>";
                                foreach ($event['images'] as $index => $image) {
                                    $display = $index === 0 ? "block" : "none";
                                    echo "<img src='" . BASE_URL . "/assets/images/events/$image' alt='" . $event['name'] . " Image' class='event-image' style='display: $display;'>";
                                }
                                echo "</div>";
                                echo "<button class='nav-button left' aria-label='Previous Image'>&lt;</button>";
                                echo "<button class='nav-button right' aria-label='Next Image'>&gt;</button>";
                                echo "</div>";
                                echo "<div class='image-indicators'>";
                                foreach ($event['images'] as $index => $image) {
                                    $activeClass = $index === 0 ? "active" : "";
                                    echo "<span class='indicator $activeClass' data-index='$index'></span>";
                                }
                                echo "</div>";
                                echo "</div>";
                                echo "<p class='short-description'>" . substr($event['description'], 0, 50) . "...</p>";
                                echo "<button class='btn btn-secondary learn-more-btn small-btn'>Learn More</button>";
                                echo "<div class='full-description' style='display: none;'>";
                                echo "<p>" . $event['description'] . "</p>";
                                echo "<p>Date: " . $event['date'] . "</p>";
                                echo "<p>Time: " . $event['time'] . "</p>";
                                echo "<p>Location: " . $event['location'] . "</p>";
                                echo "<p>Enrichment Points: " . $event['enrichment_points'] . "</p>";
                                echo "<button class='btn btn-primary'>Register</button>";
                                echo "</div>";
                                echo "</div>";
                            }
                            ?>
                        </div>
                        <script>
                            document.querySelectorAll('.learn-more-btn').forEach(button => {
                                button.addEventListener('click', () => {
                                    const fullDescription = button.nextElementSibling;
                                    if (fullDescription.style.display === 'none') {
                                        fullDescription.style.display = 'block';
                                        button.textContent = 'Show Less';
                                    } else {
                                        fullDescription.style.display = 'none';
                                        button.textContent = 'Learn More';
                                    }
                                });
                            });

                            document.querySelectorAll('.image-slider').forEach(slider => {
                                const images = slider.querySelectorAll('.event-image');
                                const indicators = slider.querySelectorAll('.indicator');
                                const leftButton = slider.querySelector('.nav-button.left');
                                const rightButton = slider.querySelector('.nav-button.right');
                                let currentIndex = 0;

                                // Allow manual switching using indicators
                                indicators.forEach(indicator => {
                                    indicator.addEventListener('click', () => {
                                        images[currentIndex].style.display = 'none';
                                        indicators[currentIndex].classList.remove('active');
                                        currentIndex = parseInt(indicator.getAttribute('data-index'));
                                        images[currentIndex].style.display = 'block';
                                        indicators[currentIndex].classList.add('active');
                                    });
                                });

                                // Allow navigation using buttons
                                leftButton.addEventListener('click', () => {
                                    images[currentIndex].style.display = 'none';
                                    indicators[currentIndex].classList.remove('active');
                                    currentIndex = (currentIndex - 1 + images.length) % images.length;
                                    images[currentIndex].style.display = 'block';
                                    indicators[currentIndex].classList.add('active');
                                });

                                rightButton.addEventListener('click', () => {
                                    images[currentIndex].style.display = 'none';
                                    indicators[currentIndex].classList.remove('active');
                                    currentIndex = (currentIndex + 1) % images.length;
                                    images[currentIndex].style.display = 'block';
                                    indicators[currentIndex].classList.add('active');
                                });
                            });
                        </script>
                        <script src="<?php echo BASE_URL; ?>/assets/js/events.js"></script>
                        <script src="<?php echo BASE_URL; ?>/assets/js/profile-dropdown.js"></script>
                    </div>
    </body>

    </html>
    =======
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Events</title>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/events.css">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
        <script src="<?php echo BASE_URL; ?>/assets/js/events.js" defer></script> <!-- Added events.js -->
    </head>

    <body>
        <div class="dashboard-container">
            <nav class="top-nav">
                <div class="nav-left">
                    <img src="<?php echo BASE_URL; ?>/assets/images/logo/politeknik-brunei-logo.png" alt="PB Logo" class="nav-logo">
                    <h2>Enrichment Point Passport</h2>
                </div>
                <div class="nav-right">
                    <div class="search-bar">
                        <input type="text" placeholder="Search events..." aria-label="Search activities">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                    <div class="nav-actions">
                        <button class="notification-btn" aria-label="Notifications">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span class="notification-badge" aria-label="3 notifications">3</span>
                        </button>
                        <div class="profile-dropdown">
                            <div class="profile-trigger" tabindex="0" role="button" aria-haspopup="true" aria-expanded="false">
                                <div class="user-avatar">
                                    <?php if (isset($_SESSION['profile_picture'])): ?>
                                        <img src="<?php echo $_SESSION['profile_picture']; ?>" alt="Profile Picture">
                                    <?php else: ?>
                                        <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                                    <?php endif; ?>
                                </div>
                                <span class="user-name"><?php echo explode(' ', $_SESSION['full_name'])[0]; ?></span>
                            </div>
                            <div class="dropdown-menu" role="menu">
                                <a href="<?php echo BASE_URL; ?>/profile" class="dropdown-item" role="menuitem">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    My Profile
                                </a>
                                <a href="<?php echo BASE_URL; ?>/settings" class="dropdown-item" role="menuitem">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="3"></circle>
                                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06-.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                    </svg>
                                    Settings
                                </a>
                                <div class="dropdown-divider" role="separator"></div>
                                <a href="<?php echo BASE_URL; ?>/logout" class="dropdown-item" role="menuitem">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" y1="12" x2="9" y2="12"></line>
                                    </svg>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="main-content">
                <div class="tab-navigation">
                    <a href="<?php echo BASE_URL; ?>/dashboard" class="tab-item">Dashboard</a>
                    <a href="<?php echo BASE_URL; ?>/ep" class="tab-item">Enrichment Point</a>
                    <a href="<?php echo BASE_URL; ?>/events" class="tab-item active">Events</a>
                    <a href="<?php echo BASE_URL; ?>/cca" class="tab-item">CCAs</a>
                    <a href="<?php echo BASE_URL; ?>/history" class="tab-item">History</a>
                </div>

                <div class="main-wrapper">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-error">
                            <?php
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>
                    <div class="container">
                        <h1>Upcoming and Available Events</h1>
                        <div class="event-slider">
                            <?php
                            $events = [
                                [
                                    "name" => "Event A",
                                    "description" => "New description for event A.",
                                    "date" => "2025-07-01",
                                    "time" => "09:00 AM",
                                    "location" => "New Location A",
                                    "enrichment_points" => 20,
                                    "images" => [
                                        "event-a-1.jpg",
                                        "event-a-2.jpg",
                                        "event-a-3.jpg"
                                    ]
                                ],
                                [
                                    "name" => "Event B",
                                    "description" => "New description for event B.",
                                    "date" => "2025-08-15",
                                    "time" => "10:30 AM",
                                    "location" => "New Location B",
                                    "enrichment_points" => 25,
                                    "images" => [
                                        "event-b-1.jpg",
                                        "event-b-2.jpg",
                                        "event-b-3.jpg"
                                    ]
                                ],
                                [
                                    "name" => "Event C",
                                    "description" => "New description for event C.",
                                    "date" => "2025-09-20",
                                    "time" => "02:00 PM",
                                    "location" => "New Location C",
                                    "enrichment_points" => 35,
                                    "images" => [
                                        "event-c-1.jpg",
                                        "event-c-2.jpg",
                                        "event-c-3.jpg"
                                    ]
                                ]
                            ];

                            foreach ($events as $event) {
                                echo "<div class='event-slide'>";
                                echo "<h2>" . $event['name'] . "</h2>";
                                echo "<div class='image-slider'>";
                                echo "<div class='image-box'>";
                                echo "<div class='image-container'>";
                                foreach ($event['images'] as $index => $image) {
                                    $display = $index === 0 ? "block" : "none";
                                    echo "<img src='" . BASE_URL . "/assets/images/events/$image' alt='" . $event['name'] . " Image' class='event-image' style='display: $display;'>";
                                }
                                echo "</div>";
                                echo "<button class='nav-button left' aria-label='Previous Image'>&lt;</button>";
                                echo "<button class='nav-button right' aria-label='Next Image'>&gt;</button>";
                                echo "</div>";
                                echo "<div class='image-indicators'>";
                                foreach ($event['images'] as $index => $image) {
                                    $activeClass = $index === 0 ? "active" : "";
                                    echo "<span class='indicator $activeClass' data-index='$index'></span>";
                                }
                                echo "</div>";
                                echo "</div>";
                                echo "<p class='short-description'>" . substr($event['description'], 0, 50) . "...</p>";
                                echo "<button class='btn btn-secondary learn-more-btn small-btn'>Learn More</button>";
                                echo "<div class='full-description' style='display: none;'>";
                                echo "<p>" . $event['description'] . "</p>";
                                echo "<p>Date: " . $event['date'] . "</p>";
                                echo "<p>Time: " . $event['time'] . "</p>";
                                echo "<p>Location: " . $event['location'] . "</p>";
                                echo "<p>Enrichment Points: " . $event['enrichment_points'] . "</p>";
                                echo "<button class='btn btn-primary'>Register</button>";
                                echo "</div>";
                                echo "</div>";
                            }
                            ?>
                        </div>
                        <script>
                            document.querySelectorAll('.learn-more-btn').forEach(button => {
                                button.addEventListener('click', () => {
                                    const fullDescription = button.nextElementSibling;
                                    if (fullDescription.style.display === 'none') {
                                        fullDescription.style.display = 'block';
                                        button.textContent = 'Show Less';
                                    } else {
                                        fullDescription.style.display = 'none';
                                        button.textContent = 'Learn More';
                                    }
                                });
                            });

                            document.querySelectorAll('.image-slider').forEach(slider => {
                                const images = slider.querySelectorAll('.event-image');
                                const indicators = slider.querySelectorAll('.indicator');
                                const leftButton = slider.querySelector('.nav-button.left');
                                const rightButton = slider.querySelector('.nav-button.right');
                                let currentIndex = 0;

                                // Allow manual switching using indicators
                                indicators.forEach(indicator => {
                                    indicator.addEventListener('click', () => {
                                        images[currentIndex].style.display = 'none';
                                        indicators[currentIndex].classList.remove('active');
                                        currentIndex = parseInt(indicator.getAttribute('data-index'));
                                        images[currentIndex].style.display = 'block';
                                        indicators[currentIndex].classList.add('active');
                                    });
                                });

                                // Allow navigation using buttons
                                leftButton.addEventListener('click', () => {
                                    images[currentIndex].style.display = 'none';
                                    indicators[currentIndex].classList.remove('active');
                                    currentIndex = (currentIndex - 1 + images.length) % images.length;
                                    images[currentIndex].style.display = 'block';
                                    indicators[currentIndex].classList.add('active');
                                });

                                rightButton.addEventListener('click', () => {
                                    images[currentIndex].style.display = 'none';
                                    indicators[currentIndex].classList.remove('active');
                                    currentIndex = (currentIndex + 1) % images.length;
                                    images[currentIndex].style.display = 'block';
                                    indicators[currentIndex].classList.add('active');
                                });
                            });
                        </script>
                        <script src="<?php echo BASE_URL; ?>/assets/js/events.js"></script>
                        <script src="<?php echo BASE_URL; ?>/assets/js/profile-dropdown.js"></script>
                    </div>
    </body>

    </html>
    >>>>>>> 60c853e (Update)
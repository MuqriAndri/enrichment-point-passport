<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrichment Points History</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/history.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">

</head>
<body>

    <!-- Navbar -->
    <header class="navbar">
        <div class="navbar-left">
            <img src="pb-logo.png" alt="PB Logo" class="logo">
            <span class="ep-title">Enrichment Point Passport</span>
        </div>
        <div class="navbar-center">
            <input type="text" placeholder="Search activities..." class="search-box">
            <button class="search-btn">&#128269;</button>
        </div>
        <div class="navbar-right">
            <div class="notif">
                <span class="bell">&#128276;</span>
                <span class="notif-badge">3</span>
            </div>
            <div class="profile-circle">M</div>
            <span class="username">Muhammad</span>
        </div>
    </header>

    <!-- Navigation Tabs -->
    <nav class="nav-tabs">
        <a href="#" class="tab">Dashboard</a>
        <a href="#" class="tab">Enrichment Point</a>
        <a href="#" class="tab">Events</a>
        <a href="#" class="tab">CCAs</a>
        <a href="#" class="tab active">History</a>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <a href="#" class="back-arrow">&#8592;</a>

        <h1 class="title">HISTORY</h1>

        <div class="semester-select">
            <button class="dropdown-btn">SEMESTER 1 &#x25B2;</button>
            <div class="dropdown-content">
                <a href="#">SEMESTER 2</a>
                <a href="#">SEMESTER 3</a>
                <a href="#">SEMESTER 4</a>
                <a href="#">SEMESTER 5</a>
                <a href="#">SEMESTER 6</a>
            </div>
        </div>

        <div class="history-table">
            <h2>SEMESTER 1 <span>JAN - JUNE</span></h2>
            <table>
                <tr>
                    <th>Club Name</th>
                    <td>Frisbee</td>
                    <td>Netball</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
                <tr>
                    <th>EP Earned</th>
                    <td>45</td>
                    <td>67</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>Completed</td>
                    <td>Completed</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
                <tr>
                    <th>Role</th>
                    <td>President</td>
                    <td>Member</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
                <tr>
                    <th>Overall Points</th>
                    <td colspan="4" class="overall">112</td>
                </tr>
            </table>
        </div>

    </div>
    <script src="<?php echo BASE_URL; ?>/assets/js/dashboard.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/profile-dropdown.js"></script>
</body>
</html>

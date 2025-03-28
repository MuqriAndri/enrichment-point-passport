<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrichment Points History</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/settings.css">
</head>
<body>
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

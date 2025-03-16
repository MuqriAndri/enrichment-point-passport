<?php
// Database connection
$host = 'localhost';
$dbname = 'ep_passport';
$username = 'admin';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . htmlspecialchars($e->getMessage()));
}

// Fetch history data for a specific user (assuming user_id is passed via GET request)
$user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT) ?? 1; // Default to user ID 1 if not provided
$stmt = $pdo->prepare("SELECT * FROM history WHERE user_id = :user_id ORDER BY id DESC");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div class="history-title">Enrichment Points History</div>
    <?php if ($history): ?>
        <div class="history-grid">
            <?php foreach ($history as $entry): ?>
                <div class="history-card">
                    <div class="activity-name"> <?= htmlspecialchars($entry['activity_name']) ?> </div>
                    <div class="ep-points"> <?= htmlspecialchars($entry['enrichment_points']) ?> EP</div>
                    <div class="date-range"> <?= htmlspecialchars($entry['date_range']) ?> </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-history">No history available for this user.</div>
    <?php endif; ?>
</div>

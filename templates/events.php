
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="events.css">
</head>
<body>
    <div class="container">
        <h1>Upcoming and Available Events</h1>
        <?php
        $events = [
            ["name" => "Event 1", "description" => "Description for event 1.", "date" => "2025-04-01"],
            ["name" => "Event 2", "description" => "Description for event 2.", "date" => "2025-05-15"],
            ["name" => "Event 3", "description" => "Description for event 3.", "date" => "2025-06-20"]
        ];

        foreach ($events as $event) {
            echo "<div class='event-item'>";
            echo "<h2>" . $event['name'] . "</h2>";
            echo "<p>" . $event['description'] . "</p>";
            echo "<p>Date: " . $event['date'] . "</p>";
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>

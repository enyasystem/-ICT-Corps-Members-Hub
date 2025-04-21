<?php
ob_start();
require_once __DIR__ . '/../config/database.php';
// Fetch top 6 events
$sql = "SELECT id, title, description, event_date, image FROM events ORDER BY event_date DESC LIMIT 6";
$result = $conn->query($sql);
$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - ICT Corps Members Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Upcoming Events</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <?php foreach ($events as $event): ?>
            <div class="bg-white rounded shadow p-4 flex flex-col">
                <?php if ($event['image']): ?>
                    <img src="<?= htmlspecialchars($event['image']) ?>" alt="Event Image" class="w-full h-40 object-cover rounded mb-2">
                <?php endif; ?>
                <div class="font-bold text-lg mb-1"> <?= htmlspecialchars($event['title']) ?> </div>
                <div class="text-sm text-gray-600 mb-1"> <?= date('F j, Y', strtotime($event['event_date'])) ?> </div>
                <div class="text-gray-700 mb-2 line-clamp-3"> <?= htmlspecialchars($event['description']) ?> </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center">
            <a href="?all=1" class="bg-green-700 text-white px-6 py-2 rounded hover:bg-green-800 transition">View More</a>
        </div>
        <?php
        // Show all events if ?all=1 is set
        if (isset($_GET['all'])) {
            $result = $conn->query("SELECT id, title, description, event_date, image FROM events ORDER BY event_date DESC");
            echo '<div class=\'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-8\'>';
            while ($event = $result->fetch_assoc()) {
                echo '<div class="bg-white rounded shadow p-4 flex flex-col">';
                if ($event['image']) {
                    echo '<img src="' . htmlspecialchars($event['image']) . '" alt="Event Image" class="w-full h-40 object-cover rounded mb-2">';
                }
                echo '<div class="font-bold text-lg mb-1">' . htmlspecialchars($event['title']) . '</div>';
                echo '<div class="text-sm text-gray-600 mb-1">' . date('F j, Y', strtotime($event['event_date'])) . '</div>';
                echo '<div class="text-gray-700 mb-2">' . htmlspecialchars($event['description']) . '</div>';
                echo '</div>';
            }
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>

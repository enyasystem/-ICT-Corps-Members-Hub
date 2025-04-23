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
<?php include __DIR__ . '/navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - ICT Corps Members Hub</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4 display-6 fw-bold">Upcoming Events</h1>
        <div class="row g-4 mb-4">
            <?php foreach ($events as $event): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <?php if ($event['image']): ?>
                        <img src="<?= htmlspecialchars($event['image']) ?>" alt="Event Image" class="card-img-top" style="height: 220px; object-fit: cover;">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-2"> <?= htmlspecialchars($event['title']) ?> </h5>
                        <h6 class="card-subtitle mb-2 text-muted"> <?= date('F j, Y', strtotime($event['event_date'])) ?> </h6>
                        <p class="card-text mb-2" style="overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;"> <?= htmlspecialchars($event['description']) ?> </p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mb-4">
            <a href="?all=1" class="btn btn-success px-4 py-2">View More</a>
        </div>
        <?php
        // Show all events if ?all=1 is set
        if (isset($_GET['all'])) {
            $result = $conn->query("SELECT id, title, description, event_date, image FROM events ORDER BY event_date DESC");
            echo '<div class="row g-4 mt-4">';
            while ($event = $result->fetch_assoc()) {
                echo '<div class="col-12 col-md-6 col-lg-4">';
                echo '<div class="card h-100 shadow-sm">';
                if ($event['image']) {
                    echo '<img src="' . htmlspecialchars($event['image']) . '" alt="Event Image" class="card-img-top" style="height: 220px; object-fit: cover;">';
                }
                echo '<div class="card-body d-flex flex-column">';
                echo '<h5 class="card-title mb-2">' . htmlspecialchars($event['title']) . '</h5>';
                echo '<h6 class="card-subtitle mb-2 text-muted">' . date('F j, Y', strtotime($event['event_date'])) . '</h6>';
                echo '<p class="card-text mb-2">' . htmlspecialchars($event['description']) . '</p>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>
<//?php include __DIR__ . '/footer.php'; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>

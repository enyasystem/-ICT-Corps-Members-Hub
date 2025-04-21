<?php
ob_start();
require_once __DIR__ . '/../config/database.php';
// Fetch top 3 resources
$sql = "SELECT id, title, description, link, image FROM resources ORDER BY id DESC LIMIT 3";
$result = $conn->query($sql);
$resources = [];
while ($row = $result->fetch_assoc()) {
    $resources[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resources - ICT Corps Members Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Learn Resources</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <?php foreach ($resources as $resource): ?>
                <?php 
                $image = $resource['image'];
                if ($image && strpos($image, '/public/uploads/') === 0) {
                    $image = '/ICT-Corps-Members-Hub' . $image;
                }
                ?>
                <div class="bg-white rounded shadow p-4 flex flex-col">
                    <?php if ($image): ?>
                        <img src="<?= htmlspecialchars($image) ?>" alt="Resource Image" class="w-full h-32 object-cover rounded mb-2">
                    <?php endif; ?>
                    <div class="font-bold text-lg mb-1"> <?= htmlspecialchars($resource['title']) ?> </div>
                    <div class="text-gray-700 mb-2 line-clamp-3"> <?= htmlspecialchars($resource['description']) ?> </div>
                    <a href="<?= htmlspecialchars($resource['link']) ?>" target="_blank" class="text-green-700 underline mt-auto">View Resource</a>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center">
            <a href="?all=1" class="bg-green-700 text-white px-6 py-2 rounded hover:bg-green-800 transition">View More</a>
        </div>
        <?php
        // Show all resources if ?all=1 is set
        if (isset($_GET['all'])) {
            $result = $conn->query("SELECT id, title, description, link, image FROM resources ORDER BY id DESC");
            echo '<div class=\'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-8\'>';
            while ($resource = $result->fetch_assoc()) {
                $image = $resource['image'];
                if ($image && strpos($image, '/public/uploads/') === 0) {
                    $image = '/ICT-Corps-Members-Hub' . $image;
                }
                echo '<div class="bg-white rounded shadow p-4 flex flex-col">';
                if ($image) {
                    echo '<img src="' . htmlspecialchars($image) . '" alt="Resource Image" class="w-full h-32 object-cover rounded mb-2">';
                }
                echo '<div class="font-bold text-lg mb-1">' . htmlspecialchars($resource['title']) . '</div>';
                echo '<div class="text-gray-700 mb-2">' . htmlspecialchars($resource['description']) . '</div>';
                echo '<a href="' . htmlspecialchars($resource['link']) . '" target="_blank" class="text-green-700 underline mt-auto">View Resource</a>';
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

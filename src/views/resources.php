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
<?php include __DIR__ . '/navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resources - ICT Corps Members Hub</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body class="bg-light min-vh-100">
    
    <div class="container py-4">
        <h1 class="h3 fw-bold mb-4 d-flex align-items-center gap-2">
          <i class="fa-solid fa-book-open-reader text-success"></i>
          Learn Resources
        </h1>
        <div class="row g-4 mb-6">
            <?php foreach ($resources as $resource): ?>
                <?php 
                $image = $resource['image'];
                if ($image && strpos($image, '/public/uploads/') === 0) {
                    $image = '/ICT-Corps-Members-Hub' . $image;
                }
                ?>
                <div class="col-12 col-md-6 col-lg-4">
                  <div class="card h-100 shadow-sm">
                    <?php if ($image): ?>
                        <img src="<?= htmlspecialchars($image) ?>" alt="Resource Image" class="card-img-top object-fit-cover" style="height: 180px;">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                      <div class="fw-bold h5 mb-2 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-link text-success"></i>
                        <?= htmlspecialchars($resource['title']) ?>
                      </div>
                      <div class="text-secondary mb-3 flex-grow-1"> <?= htmlspecialchars($resource['description']) ?> </div>
                      <a href="<?= htmlspecialchars($resource['link']) ?>" target="_blank" class="btn btn-outline-success mt-auto d-inline-flex align-items-center gap-1">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> View Resource
                      </a>
                    </div>
                  </div>
                </div>
            <?php endforeach; ?>
        </div><br><br>
        <div class="text-center mb-4">
            <a href="?all=1" class="btn btn-success px-4 py-2 d-inline-flex align-items-center gap-2">
              <i class="fa-solid fa-list"></i> View More
            </a>
        </div>
        <?php
        // Show all resources if ?all=1 is set
        if (isset($_GET['all'])) {
            $result = $conn->query("SELECT id, title, description, link, image FROM resources ORDER BY id DESC");
            echo '<div class="row g-4 mt-4">';
            while ($resource = $result->fetch_assoc()) {
                $image = $resource['image'];
                if ($image && strpos($image, '/public/uploads/') === 0) {
                    $image = '/ICT-Corps-Members-Hub' . $image;
                }
                echo '<div class="col-12 col-md-6 col-lg-4">';
                echo '<div class="card h-100 shadow-sm">';
                if ($image) {
                    echo '<img src="' . htmlspecialchars($image) . '" alt="Resource Image" class="card-img-top object-fit-cover" style="height: 180px;">';
                }
                echo '<div class="card-body d-flex flex-column">';
                echo '<div class="fw-bold h5 mb-2 d-flex align-items-center gap-2"><i class="fa-solid fa-link text-success"></i> ' . htmlspecialchars($resource['title']) . '</div>';
                echo '<div class="text-secondary mb-3 flex-grow-1">' . htmlspecialchars($resource['description']) . '</div>';
                echo '<a href="' . htmlspecialchars($resource['link']) . '" target="_blank" class="btn btn-outline-success mt-auto d-inline-flex align-items-center gap-1"><i class="fa-solid fa-arrow-up-right-from-square"></i> View Resource</a>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        }
        ?>
    </div>
<//?php include __DIR__ . '/footer.php'; ?>

    <!-- Bootstrap JS -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> -->
</body>
</html>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>

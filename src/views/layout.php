<?php

// Load site settings for logo and colors
$siteSettings = [];
$settingsPath = __DIR__ . '/../../public/uploads/site_settings.json';
if (file_exists($settingsPath)) {
    $siteSettings = json_decode(file_get_contents($settingsPath), true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT Corps Members Hub</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
      body { font-family: 'Inter', 'Segoe UI', Arial, sans-serif; }
      .section-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: <?= isset($siteSettings['primary_color']) ? htmlspecialchars($siteSettings['primary_color']) : '#008751' ?>;
        /* margin-bottom: 2rem; */
      }
      .member-card {
        transition: transform 0.2s, box-shadow 0.2s;
      }
      .member-card:hover {
        transform: translateY(-8px) scale(1.03);
        box-shadow: 0 8px 32px <?= isset($siteSettings['primary_color']) ? htmlspecialchars($siteSettings['primary_color']) : '#008751' ?>19!important;
      }
      .fade-in {
        animation: fade-in 1s cubic-bezier(.4,0,.2,1) both;
      }
      @keyframes fade-in {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: none; }
      }
      .nysc-gradient {
        background: linear-gradient(90deg, <?= isset($siteSettings['primary_color']) ? htmlspecialchars($siteSettings['primary_color']) : '#008751' ?> 0%, <?= isset($siteSettings['secondary_color']) ? htmlspecialchars($siteSettings['secondary_color']) : '#00b16a' ?> 100%);
      }
    </style>
</head>
<body class="min-h-screen d-flex flex-column" style="background:transparent!important;">
    <main class="flex-grow-1 pt-5 bg-green-50" style="padding-top:6.5rem!important;">
        <?php echo $content; ?>
    </main>
    <!-- Footer -->
    <footer class="nysc-gradient text-white py-4 mt-5 shadow-inner" style="background: #14532d;">
        <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <img src="/assets/nysc-logo.png" alt="NYSC Logo" class="rounded-circle bg-white p-1" style="height:32px;width:32px;"/>
                <span class="fw-semibold">ICT Corps Members Hub</span>
            </div>
            <div class="d-flex gap-3 fs-5">
                <a href="https://github.com/" target="_blank" class="text-white-50"><i class="fab fa-github"></i></a>
                <a href="https://linkedin.com/" target="_blank" class="text-white-50"><i class="fab fa-linkedin"></i></a>
                <a href="mailto:info@nysc-ict.com" class="text-white-50"><i class="fa-solid fa-envelope"></i></a>
            </div>
            <div class="small mt-2 mt-md-0">&copy; <?php echo date("Y"); ?> ICT Corps Members Hub. All rights reserved.</div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/main.js"></script>
</body>
</html>

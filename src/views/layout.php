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
        margin-bottom: 2rem;
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
      .navbar-blur {
        background: rgba(255,255,255,0.85)!important;
        backdrop-filter: blur(10px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      }
    </style>
</head>
<body class="bg-green-50 min-h-screen d-flex flex-column">
    <!-- Responsive Navbar Start -->
    <nav class="sticky top-0 left-0 w-full z-30 bg-transparent shadow-none transition-all duration-300" style="background: none;">
        <div class="absolute inset-0 w-full h-full z-[-1]">
            <!-- <img src="https://images.unsplash.com/photo-1461749280684-dccba630e2f6?auto=format&fit=crop&w=1500&q=80" alt="Navbar Background" class="w-full h-full object-cover object-center" /> -->
            <div class="absolute inset-0 bg-green-900/70 backdrop-blur-sm"></div>
        </div>
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16 items-center">
                <a href="/ICT-Corps-Members-Hub/" class="flex items-center gap-2">
                    <?php if (!empty($siteSettings['site_logo'])): ?>
                        <img src="<?= htmlspecialchars($siteSettings['site_logo']) ?>" alt="Logo" class="rounded-full w-10 h-10 object-cover shadow">
                    <?php else: ?>
                        <div class="bg-green-700 rounded-full w-10 h-10 flex items-center justify-center shadow">
                            <span class="text-white text-xl font-extrabold font-sans">ICT</span>
                        </div>
                    <?php endif; ?>
                    <span class="font-bold text-white text-lg md:text-xl tracking-wide">ICT Corps Members Hub</span>
                </a>
                <div class="hidden md:flex gap-6 items-center">
                    <a href="/ICT-Corps-Members-Hub/public" class="text-white/90 hover:text-white font-semibold transition">Home</a>
                    <a href="/ICT-Corps-Members-Hub/src/views/members.php" class="text-white/90 hover:text-white font-semibold transition">Members</a>
                    <a href="/ICT-Corps-Members-Hub/src/views/events.php" class="text-white/90 hover:text-white font-semibold transition">Events</a>
                    <a href="/ICT-Corps-Members-Hub/src/views/resources.php" class="text-white/90 hover:text-white font-semibold transition">Resources</a>
                </div>
                <div class="flex items-center gap-2 relative">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php
                        require_once __DIR__ . '/../config/database.php';
                        $uid = $_SESSION['user_id'];
                        $stmt = $conn->prepare("SELECT profile_picture, name FROM users WHERE id = ?");
                        $stmt->bind_param('i', $uid);
                        $stmt->execute();
                        $res = $stmt->get_result();
                        $user = $res->fetch_assoc();
                        $profilePic = $user && !empty($user['profile_picture']) ? $user['profile_picture'] : null;
                        if ($profilePic && strpos($profilePic, '/ICT-Corps-Members-Hub') !== 0) {
                            $profilePic = '/ICT-Corps-Members-Hub' . $profilePic;
                        }
                        $avatar = $profilePic ? htmlspecialchars($profilePic) : 'https://ui-avatars.com/api/?name=' . urlencode($user['name'] ?? 'User') . '&background=008751&color=fff';
                        ?>
                        <div class="relative group">
                            <button id="profileMenuBtn" class="focus:outline-none flex items-center">
                                <img src="<?= $avatar ?>" alt="Profile" class="w-10 h-10 rounded-full border-2 border-white shadow object-cover">
                            </button>
                            <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50 group-focus:block">
                                <a href="/ICT-Corps-Members-Hub/src/views/dashboard.php" class="block px-4 py-2 text-gray-700 hover:bg-green-50">Profile</a>
                                <form action="/ICT-Corps-Members-Hub/public/auth/logout.php" method="post" class="m-0">
                                    <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50">Logout</button>
                                </form>
                            </div>
                        </div>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const btn = document.getElementById('profileMenuBtn');
                            const dropdown = document.getElementById('profileDropdown');
                            if (btn && dropdown) {
                                btn.addEventListener('click', function(e) {
                                    e.stopPropagation();
                                    dropdown.classList.toggle('hidden');
                                });
                                document.addEventListener('click', function(e) {
                                    if (!dropdown.classList.contains('hidden')) {
                                        dropdown.classList.add('hidden');
                                    }
                                });
                            }
                        });
                        </script>
                    <?php else: ?>
                        <a href="/ICT-Corps-Members-Hub/public/auth/login.php" class="px-4 py-2 rounded-lg text-white font-semibold hover:bg-green-700/80 transition">Sign In</a>
                        <a href="#" class="px-4 py-2 rounded-lg bg-white text-green-800 font-bold shadow hover:bg-green-100 transition">Join Community</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <!-- Responsive Navbar End -->
    <script>
    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');
        if (btn && menu) {
            btn.addEventListener('click', function() {
                menu.classList.toggle('-translate-y-96');
                menu.classList.toggle('opacity-0');
                menu.classList.toggle('pointer-events-none');
                btn.classList.toggle('open');
            });
        }

        // SPA-like navigation for all internal links
        function handleLinkClick(e) {
            const href = this.getAttribute('href');
            if (
                href &&
                !href.startsWith('http') &&
                !href.startsWith('mailto:') &&
                !href.startsWith('tel:') &&
                !href.startsWith('#') &&
                !this.hasAttribute('download') &&
                !this.target
            ) {
                e.preventDefault();
                fetch(href)
                    .then(res => res.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newContent = doc.querySelector('main');
                        if (newContent) {
                            document.querySelector('main').innerHTML = newContent.innerHTML;
                            window.history.pushState({}, '', href);
                            // Re-attach SPA nav to new links
                            attachSpaNav();
                        } else {
                            window.location.href = href;
                        }
                    })
                    .catch(() => window.location.href = href);
            }
        }
        function attachSpaNav() {
            document.querySelectorAll('a').forEach(link => {
                link.removeEventListener('click', handleLinkClick);
                link.addEventListener('click', handleLinkClick);
            });
        }
        attachSpaNav();

        // Handle browser back/forward
        window.addEventListener('popstate', function() {
            fetch(window.location.pathname)
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.querySelector('main');
                    if (newContent) {
                        document.querySelector('main').innerHTML = newContent.innerHTML;
                        attachSpaNav();
                    } else {
                        window.location.reload();
                    }
                });
        });
    });
    </script>
    <!-- End Navbar -->
    <main class="flex-grow-1 pt-5" style="padding-top:6.5rem!important;">
        <?php echo $content; ?>
    </main>
    <!-- Footer -->
    <footer class="nysc-gradient text-white py-4 mt-5 shadow-inner">
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

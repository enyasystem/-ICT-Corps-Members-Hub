<?php
require_once __DIR__ . '/../config/database.php';
ob_start();
// Fetch all users with assigned roles (role_id is not null)
$excos_sql = "SELECT u.id, u.name, u.profile_picture, u.occupation, u.business_id, r.display_name as role_name, r.name as role_key, r.badge_color FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.role_id IS NOT NULL ORDER BY u.id ASC";
$excos_result = $conn->query($excos_sql);
$excos = $excos_result ? $excos_result->fetch_all(MYSQLI_ASSOC) : [];

// Define the order for key roles
$priority_roles = ['president', 'vice-president', 'pro', 'treasurer'];
$priority_members = [];
$other_members = [];
foreach ($priority_roles as $role) {
    foreach ($excos as $k => $member) {
        if (isset($member['role_key']) && strtolower($member['role_key']) === $role) {
            $priority_members[] = $member;
            unset($excos[$k]);
        }
    }
}
// Remaining members
$other_members = array_values($excos);
$ordered_members = array_merge($priority_members, $other_members);
function getBusiness($conn, $business_id) {
    if (!$business_id) return null;
    $stmt = $conn->prepare("SELECT name FROM businesses WHERE id = ?");
    $stmt->bind_param('i', $business_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? $row['name'] : null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Corper Connect</title>
  <!-- Tailwind CSS CDN for utility classes -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://hammerjs.github.io/dist/hammer.min.js"></script>
  <style>
    body, html {
      height: 100%;
      margin: 0;
    }

    .hero-section {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      background: linear-gradient(to bottom, #14532d, #15803d);
      transition: background 0.7s ease-in-out;
      overflow: hidden;
    }

    .navbar-custom {
      transition: all 0.3s ease;
    }

    .navbar-scrolled {
      background-color: #166534 !important;
      box-shadow: 0 0 10px rgba(0,0,0,0.2);
    }

    .particle {
      width: 8px;
      height: 8px;
      background-color: white;
      border-radius: 50%;
      opacity: 0.15;
      position: absolute;
      animation: float 6s ease-in-out infinite alternate;
    }

    @keyframes float {
      0% { transform: translateY(0) scale(1); }
      100% { transform: translateY(-30px) scale(1.2); }
    }

    .indicator {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background-color: rgba(255, 255, 255, 0.5);
      transition: all 0.3s ease;
    }

    .indicator.active {
      background-color: white;
      width: 32px;
    }

    .hero-overlay {
      position: absolute;
      inset: 0;
      background-color: rgba(0, 0, 0, 0.5);
    }

    .hero-buttons .btn {
      min-width: 180px;
    }

    .fade-slide-in {
      opacity: 0;
      transform: translateY(20px);
      animation: fadeSlideIn 0.6s ease forwards;
    }

    @keyframes fadeSlideIn {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @media (min-width: 768px) {
      .navbar-collapse {
        display: flex !important;
        opacity: 1 !important;
        visibility: visible !important;
      }
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-md fixed-top navbar-dark navbar-custom">
  <div class="container">
    <a class="navbar-brand fw-bold text-white" href="#">CorperConnect</a>
    <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="d-md-flex w-100 justify-content-between align-items-center collapse navbar-collapse" id="navbarMenu">
      <div class="d-flex flex-row mb-2 mb-lg-0 gap-3">
        <a class="nav-link fw-bold text-white" href="#">Home</a>
        <a class="nav-link fw-bold text-white" href="#">Members</a>
        <a class="nav-link fw-bold text-white" href="#">Events</a>
        <a class="nav-link fw-bold text-white" href="#">Resources</a>
      </div>
      <div class="d-flex gap-2">
        <a class="btn btn-link text-white fw-bold" href="#">Login</a>
        <a class="btn btn-light text-success fw-bold" href="#">Join Community</a>
      </div>
    </div>
  </div>
</nav>

<!-- Hero Section -->
<div class="hero-section text-center text-white" id="heroSection">
  <div class="hero-overlay"></div>
  <div class="container position-relative z-2" id="heroContent">
    <h1 class="display-4 fw-bold mb-4 fade-slide-in" id="slideTitle">Welcome to Corper Connect</h1>
    <p class="lead mb-4 fade-slide-in" id="slideSubtitle">Connect with fellow corps members, share experiences, and make your service year memorable</p>
    <div class="hero-buttons d-flex flex-column flex-sm-row justify-content-center gap-3 fade-slide-in">
      <button class="btn btn-light text-success">Explore Members</button>
      <button class="btn btn-success">Join Community</button>
    </div>
  </div>

  <!-- Carousel Controls -->
  <button class="position-absolute top-50 start-0 translate-middle-y btn btn-dark btn-sm opacity-75" onclick="prevSlide()">‹</button>
  <button class="position-absolute top-50 end-0 translate-middle-y btn btn-dark btn-sm opacity-75" onclick="nextSlide()">›</button>

  <!-- Indicators -->
  <div class="position-absolute bottom-0 start-50 translate-middle-x d-flex gap-2 pb-4" id="indicators"></div>
</div>

<!-- Featured Members Section -->
<section class="py-5 bg-green-50">
  <div class="container">
    <h2 class="display-5 fw-bold text-center text-success mb-4">Featured Members</h2>
    <div class="row g-4 justify-content-center">
      <?php foreach ($ordered_members as $member): 
        $profile_picture = trim($member['profile_picture']);
        $is_valid_picture = $profile_picture && strtolower($profile_picture) !== 'profile picture';
        if ($is_valid_picture && strpos($profile_picture, '/public/uploads/') === 0) {
            $profile_picture = '/ICT-Corps-Members-Hub' . $profile_picture;
        }
        // Fetch skills for this member
        $skills = [];
        $stmt = $conn->prepare("SELECT s.name FROM skills s JOIN user_skills us ON s.id = us.skill_id WHERE us.user_id = ?");
        $stmt->bind_param('i', $member['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $skills[] = $row['name'];
        }
        $business = getBusiness($conn, $member['business_id']);
        $badgeClass = !empty($member['badge_color']) ? $member['badge_color'] : 'bg-green-200 text-green-900';
      ?>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="bg-white rounded-4 shadow-lg p-4 d-flex flex-column align-items-center position-relative member-card text-center w-100 max-w-xs min-w-[240px] border border-success-subtle transition-transform" style="transition: box-shadow 0.3s, transform 0.3s; box-shadow: 0 6px 32px 0 rgba(22,101,52,0.10); cursor:pointer;">
          <?php if (!empty($member['role_name']) && strtolower($member['role_name']) !== 'member'): ?>
            <span class="position-absolute top-0 start-0 translate-middle-y px-3 py-1 fw-semibold text-xs rounded-pill shadow-sm <?= htmlspecialchars($badgeClass) ?>" style="font-size:0.85rem;left:1rem;top:1rem;letter-spacing:0.5px;">
              <?= htmlspecialchars($member['role_name']) ?>
            </span>
          <?php endif; ?>
          <div class="mb-2 position-relative" style="width: 72px; height: 72px;">
            <img src="<?= $is_valid_picture ? htmlspecialchars($profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode($member['name']) . '&background=008751&color=fff' ?>" class="rounded-circle border border-3 border-success shadow" style="width: 72px; height: 72px; object-fit: cover; background: #f0fdf4;" alt="<?= htmlspecialchars($member['name']) ?>">
          </div>
          <div class="fw-bold text-success-emphasis mb-1 w-100 text-truncate" style="font-size: 1.15rem; letter-spacing: -0.5px;"> <?= htmlspecialchars($member['name']) ?> </div>
          <div class="text-success mb-2 w-100 text-xs text-truncate" style="font-size: 0.98rem;"> <?= htmlspecialchars($member['occupation']) ?> </div>
          <div class="d-flex flex-wrap justify-content-center gap-1 mb-2">
            <?php foreach ($skills as $skill): ?>
              <span class="badge rounded-pill bg-success-subtle text-success-emphasis px-2 py-1 shadow-sm" style="font-size: 0.75rem; font-weight: 500;"> <?= htmlspecialchars($skill) ?> </span>
            <?php endforeach; ?>
          </div>
          <?php if ($business): ?>
            <div class="text-secondary-emphasis w-100 mb-1 text-truncate" style="font-size: 0.85rem;">Business: <span class="fw-semibold"><?= htmlspecialchars($business) ?></span></div>
          <?php endif; ?>
          <button type="button" class="mt-2 px-3 py-1.5 rounded-pill bg-gradient-to-r from-green-500 to-green-700 text-white fw-semibold shadow-sm border-0 hover:scale-105 hover:shadow-lg transition-all d-inline-flex align-items-center gap-1 text-sm" data-bs-toggle="modal" data-bs-target="#memberModal<?= $member['id'] ?>">
            <i class="fa fa-info-circle"></i> Details
          </button>
        </div>
      </div>
      <!-- Member Modal -->
      <div class="modal fade" id="memberModal<?= $member['id'] ?>" tabindex="-1" aria-labelledby="memberModalLabel<?= $member['id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="memberModalLabel<?= $member['id'] ?>">Member Details</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
              <div class="d-flex justify-content-center w-100 mb-3">
                <img src="<?= $is_valid_picture ? htmlspecialchars($profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode($member['name']) . '&background=008751&color=fff' ?>" class="rounded-circle border border-3 border-success shadow mx-auto d-block" style="width: 90px; height: 90px; object-fit: cover; background: #f0fdf4;" alt="<?= htmlspecialchars($member['name']) ?>">
              </div>
              <h5 class="fw-bold mb-1"> <?= htmlspecialchars($member['name']) ?> </h5>
              <div class="mb-2"><span class="badge <?= htmlspecialchars($badgeClass) ?> px-3 py-1"> <?= htmlspecialchars($member['role_name']) ?> </span></div>
              <div class="mb-2 text-success-emphasis"> <?= htmlspecialchars($member['occupation']) ?> </div>
              <div class="mb-2">
                <?php foreach ($skills as $skill): ?>
                  <span class="badge rounded-pill bg-success-subtle text-success-emphasis px-2 py-1 shadow-sm mb-1" style="font-size: 0.85rem;"> <?= htmlspecialchars($skill) ?> </span>
                <?php endforeach; ?>
              </div>
              <?php if ($business): ?>
                <div class="mb-2 text-secondary-emphasis">Business: <span class="fw-semibold"> <?= htmlspecialchars($business) ?> </span></div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
      <a href="/ICT-Corps-Members-Hub/src/views/members.php" class="btn btn-success btn-lg px-5 py-2 fw-semibold">View All Members</a>
    </div>
  </div>
</section>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script>
  const slides = [
    {
      title: "Welcome to Corper Connect",
      subtitle: "Connect with fellow corps members, share experiences, and make your service year memorable",
      bg: "linear-gradient(to bottom, #14532d, #15803d)"
    },
    {
      title: "Build Lasting Connections",
      subtitle: "Network with corps members from different backgrounds and states across Nigeria",
      bg: "linear-gradient(to bottom, #166534, #16a34a)"
    },
    {
      title: "Serve with Pride",
      subtitle: "Contributing to nation-building through dedication and excellence",
      bg: "linear-gradient(to bottom, #15803d, #22c55e)"
    }
  ];

  let currentSlide = 0;

  function updateSlide() {
    const { title, subtitle, bg } = slides[currentSlide];
    const hero = document.getElementById('heroSection');
    const content = document.getElementById('heroContent');

    // Fade in animation
    content.querySelectorAll('.fade-slide-in').forEach(el => {
      el.style.animation = 'none';
      el.offsetHeight; // trigger reflow
      el.style.animation = '';
      el.classList.remove('fade-slide-in');
      void el.offsetWidth;
      el.classList.add('fade-slide-in');
    });

    document.getElementById('slideTitle').textContent = title;
    document.getElementById('slideSubtitle').textContent = subtitle;
    hero.style.background = bg;

    document.querySelectorAll('.indicator').forEach((el, i) => {
      el.classList.toggle('active', i === currentSlide);
    });
  }

  function nextSlide() {
    currentSlide = (currentSlide + 1) % slides.length;
    updateSlide();
  }

  function prevSlide() {
    currentSlide = (currentSlide - 1 + slides.length) % slides.length;
    updateSlide();
  }

  setInterval(nextSlide, 5000);

  window.addEventListener('scroll', () => {
    const nav = document.querySelector('.navbar');
    nav.classList.toggle('navbar-scrolled', window.scrollY > 50);
  });

  // Indicators
  const indicators = document.getElementById('indicators');
  slides.forEach((_, i) => {
    const dot = document.createElement('div');
    dot.className = 'indicator' + (i === 0 ? ' active' : '');
    dot.onclick = () => {
      currentSlide = i;
      updateSlide();
    };
    indicators.appendChild(dot);
  });

  // Particle background
  for (let i = 0; i < 20; i++) {
    const p = document.createElement('div');
    p.className = 'particle';
    p.style.left = Math.random() * 100 + '%';
    p.style.top = Math.random() * 100 + '%';
    p.style.animationDuration = (Math.random() * 5 + 3) + 's';
    document.getElementById('heroSection').appendChild(p);
  }

  // Swipe Support with Hammer.js
  const heroEl = document.getElementById('heroSection');
  const hammer = new Hammer(heroEl);
  hammer.on("swipeleft", nextSlide);
  hammer.on("swiperight", prevSlide);
</script>
</body>
</html>

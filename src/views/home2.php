<?php
require_once __DIR__ . '/../config/database.php';
ob_start();
// Fetch all users with assigned roles (role_id is not null)
$excos_sql = "SELECT u.id, u.name, u.profile_picture, u.occupation, u.business_id, r.display_name as role_name, r.name as role_key, r.badge_color FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.role_id IS NOT NULL ORDER BY u.id ASC";
$excos_result = $conn->query($excos_sql);
$excos = $excos_result ? $excos_result->fetch_all(MYSQLI_ASSOC) : [];

// Define the order for key roles
$priority_roles = ['supervisor', 'president', 'vice-president', 'pro', 'treasurer', 'exco'];
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
$ordered_members = array_slice($ordered_members, 0, 9); // Limit to 9 featured members

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
  <!-- <script src="https://cdn.tailwindcss.com"></script> -->
  <!-- Bootstrap CSS loaded AFTER Tailwind to ensure Bootstrap's .collapse and other classes take precedence -->
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
    .collapse:not(.show) {
      display: none !important;
    }
    .collapse.show {
      display: block !important;
    }
    @media (min-width: 768px) {
      .navbar-expand-md .navbar-collapse {
        display: flex !important;
      }
    }
    .bs-navbar-collapse:not(.show) {
      display: none !important;
    }
    .bs-navbar-collapse.show {
      display: block !important;
    }
    @media (min-width: 768px) {
      .navbar-expand-md .bs-navbar-collapse {
        display: flex !important;
      }
    }
    .member-card {
      background: #fff;
      border-radius: 1.2rem;
      box-shadow: 0 4px 16px 0 rgba(22,101,52,0.10);
      border: 1px solid #e0e0e0;
      padding: 1.2rem 1rem 1.5rem 1rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      min-width: 0;
      transition: box-shadow 0.3s, transform 0.3s;
      position: relative;
    }
    .member-card:hover {
      box-shadow: 0 8px 32px 0 rgba(22,101,52,0.18);
      transform: translateY(-4px) scale(1.03);
    }
    .member-avatar {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      border: 2px solid #16a34a;
      object-fit: cover;
      background: #f0fdf4;
      margin-bottom: 0.5rem;
    }
    .member-name {
      font-weight: bold;
      color: #14532d;
      font-size: 1rem;
      margin-bottom: 0.2rem;
      width: 100%;
      letter-spacing: -0.5px;
      text-overflow: ellipsis;
      overflow: hidden;
      white-space: nowrap;
    }
    .member-occupation {
      color: #198754;
      font-size: 0.92rem;
      margin-bottom: 0.5rem;
      width: 100%;
      text-overflow: ellipsis;
      overflow: hidden;
      white-space: nowrap;
    }
    .member-skills {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 0.25rem;
      margin-bottom: 0.5rem;
    }
    .member-skill-badge {
      background: #e6f4ea;
      color: #198754;
      border-radius: 999px;
      padding: 0.2rem 0.7rem;
      font-size: 0.75rem;
      font-weight: 500;
      box-shadow: 0 1px 2px #e0e0e0;
    }
    .member-business {
      color: #555;
      font-size: 0.82rem;
      margin-bottom: 0.3rem;
      width: 100%;
      text-overflow: ellipsis;
      overflow: hidden;
      white-space: nowrap;
    }
    /* Role badge colors */
    .badge-supervisor { background: #1e293b; color: #fff; }
    .badge-president { background: #14532d; color: #fff; }
    .badge-vice-president { background: #15803d; color: #fff; }
    .badge-pro { background: #0d9488; color: #fff; }
    .badge-treasurer { background: #f59e42; color: #fff; }
    .badge-exco { background: #6366f1; color: #fff; }
    .badge-member { background: #e2e8f0; color: #222; }
    .member-badge {
      position: absolute;
      top: 0.7rem;
      left: 0.7rem;
      z-index: 2;
      font-size: 0.85rem;
      letter-spacing: 0.5px;
      margin-bottom: 0;
      display: inline-block;
      padding: 0.3rem 1rem;
      border-radius: 999px;
      box-shadow: 0 1px 4px #e0e0e0;
    }
    /* Details button */
    .member-details-btn {
      background: linear-gradient(90deg, #16a34a 0%, #22c55e 100%);
      color: #fff;
      border: none;
      border-radius: 999px;
      padding: 0.4rem 1.2rem;
      font-weight: 600;
      font-size: 0.98rem;
      margin-top: 0.5rem;
      box-shadow: 0 2px 8px 0 rgba(22,101,52,0.10);
      transition: background 0.2s, transform 0.2s;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    .member-details-btn:hover {
      background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%);
      color: #fff;
      transform: scale(1.04);
    }
    @media (max-width: 575.98px) {
      .member-card {
        padding: 0.8rem 0.5rem 1rem 0.5rem;
        border-radius: 0.8rem;
      }
      .member-avatar {
        width: 44px;
        height: 44px;
      }
    }
  </style>
</head>
<body>



<!-- Navbar -->
<nav class="navbar navbar-expand-md navbar-dark fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold text-white" href="#">CorperConnect</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse bs-navbar-collapse navbar-collapse" id="navbarMenu">
      <ul class="navbar-nav me-auto mb-2 mb-md-0">
        <li class="nav-item"><a class="nav-link text-white" href="#">Home</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="#">Members</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="#">Events</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="#">Resources</a></li>
      </ul>
      <ul class="navbar-nav mb-2 mb-md-0">
        <li class="nav-item"><a class="nav-link text-white" href="#">Login</a></li>
        <li class="nav-item"><a class="btn btn-light text-success ms-2" href="#">Join Community</a></li>
      </ul>
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
        $role_key = strtolower($member['role_key'] ?? 'member');
        $badgeClass = 'badge-' . str_replace('_', '-', $role_key);
      ?>
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="member-card">
          <?php if (!empty($member['role_name']) && $role_key !== 'member'): ?>
            <span class="member-badge <?= htmlspecialchars($badgeClass) ?> fw-semibold text-xs shadow-sm">
              <?= htmlspecialchars($member['role_name']) ?>
            </span>
          <?php endif; ?>
          <img src="<?= $is_valid_picture ? htmlspecialchars($profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode($member['name']) . '&background=008751&color=fff' ?>" class="member-avatar" alt="<?= htmlspecialchars($member['name']) ?>">
          <div class="member-name"> <?= htmlspecialchars($member['name']) ?> </div>
          <div class="member-occupation"> <?= htmlspecialchars($member['occupation']) ?> </div>
          <div class="member-skills">
            <?php foreach ($skills as $skill): ?>
              <span class="member-skill-badge"> <?= htmlspecialchars($skill) ?> </span>
            <?php endforeach; ?>
          </div>
          <?php if ($business): ?>
            <div class="member-business">Business: <span class="fw-semibold"><?= htmlspecialchars($business) ?></span></div>
          <?php endif; ?>
          <button type="button" class="member-details-btn" data-bs-toggle="modal" data-bs-target="#memberModal<?= $member['id'] ?>">
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

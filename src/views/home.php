<?php
require_once __DIR__ . '/../config/database.php';
ob_start();
// Fetch latest 6 registered members
$sql = "SELECT id, name, profile_picture, occupation, business_id FROM users ORDER BY id DESC LIMIT 6";
$result = $conn->query($sql);
$members = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
function getBusiness($conn, $business_id) {
    if (!$business_id) return null;
    $stmt = $conn->prepare("SELECT name FROM businesses WHERE id = ?");
    $stmt->bind_param('i', $business_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? $row['name'] : null;
}

// Fetch excos and supervisor (first 9 with role 'exco' or 'supervisor')
$excos_sql = "SELECT u.id, u.name, u.profile_picture, u.occupation, u.business_id, r.display_name as role_name, r.badge_color FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE r.name IN ('exco', 'supervisor') ORDER BY FIELD(r.name, 'supervisor', 'exco') DESC, u.id ASC LIMIT 9";
$excos_result = $conn->query($excos_sql);
$excos = $excos_result ? $excos_result->fetch_all(MYSQLI_ASSOC) : [];
// Fetch all other members (excluding excos and supervisor)
$other_sql = "SELECT u.id, u.name, u.profile_picture, u.occupation, u.business_id, r.display_name as role_name, r.badge_color FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE r.name NOT IN ('exco', 'supervisor') OR r.name IS NULL ORDER BY u.id DESC";
$other_result = $conn->query($other_sql);
$other_members = $other_result ? $other_result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!-- Bootstrap 5 CSS CDN for Carousel -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- HERO SECTION -->
<section class="relative min-h-screen flex flex-col bg-green-800 overflow-hidden">
    <!-- Particles Background Only -->
    <div id="particles-js" class="absolute inset-0 w-full h-full z-0 pointer-events-none"></div>
    <!-- Remove background image and gradient overlay -->
    <div class="flex-1 flex flex-col items-center justify-center text-center px-4 pb-24 md:pb-32 relative z-10 animate-fade-in-up">
      <span class="inline-block bg-white/20 text-white text-xs font-bold rounded-full px-4 py-1 mb-4 backdrop-blur-md border border-white/30 shadow">NYSC Jos North</span>
      <h1 class="text-4xl md:text-6xl font-extrabold text-white mb-4 drop-shadow-lg tracking-tight animate-gradient-move">Empower Your ICT Journey</h1>
      <h2 class="text-xl md:text-2xl text-white/90 font-semibold mb-4">Connect, Learn, and Grow with Nigeria's ICT Corps Community</h2>
      <p class="text-white/80 max-w-2xl mx-auto mb-8 text-base md:text-lg">Access exclusive resources, events, and a vibrant network of professionals to boost your skills and career. Join a community that supports your growth every step of the way.</p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
        <a href="/ICT-Corps-Members-Hub/src/views/resources.php" class="px-8 py-3 rounded-full bg-white text-green-800 font-bold shadow-lg hover:bg-green-100 transition flex items-center gap-2 animate-fade-in-up delay-100"><i class="fa-solid fa-book-open-reader"></i> Explore Resources</a>
        <a href="#" class="px-8 py-3 rounded-full bg-green-700 text-white font-bold shadow-lg hover:bg-green-900 transition flex items-center gap-2 animate-fade-in-up delay-200"><i class="fa-solid fa-users"></i> Join Community</a>
      </div>
      <div class="mt-6 flex justify-center gap-6 animate-fade-in-up delay-300">
        <a href="/ICT-Corps-Members-Hub/src/views/members.php" class="text-white/80 hover:text-white text-lg flex items-center gap-2"><i class="fa-solid fa-address-book"></i> Members</a>
        <a href="/ICT-Corps-Members-Hub/src/views/events.php" class="text-white/80 hover:text-white text-lg flex items-center gap-2"><i class="fa-solid fa-calendar-days"></i> Events</a>
      </div>
    </div>
</section>
<!-- Section Divider -->
<div class="w-full flex justify-center -mt-10 z-30 relative">
  <svg viewBox="0 0 1440 100" class="w-full h-24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path fill="#f3f4f6" d="M0,0 C480,100 960,0 1440,100 L1440,100 L0,100 Z"></path>
  </svg>
</div>
<!-- WHY JOIN US SECTION -->
<section class="py-20 bg-green-50 relative z-10 glassmorphism">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl md:text-5xl font-extrabold text-center text-green-900 mb-12">Why Join Us?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl shadow-lg p-10 flex flex-col items-center hover:scale-105 transition-transform">
                <div class="bg-green-100 text-green-700 rounded-full p-5 mb-4"><i class="fa-solid fa-network-wired fa-2x"></i></div>
                <h3 class="font-bold text-2xl mb-2">Networking</h3>
                <p class="text-gray-600 text-center">Meet and collaborate with ICT professionals and corps members nationwide.</p>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-10 flex flex-col items-center hover:scale-105 transition-transform">
                <div class="bg-green-100 text-green-700 rounded-full p-5 mb-4"><i class="fa-solid fa-lightbulb fa-2x"></i></div>
                <h3 class="font-bold text-2xl mb-2">Learning</h3>
                <p class="text-gray-600 text-center">Access exclusive resources, events, and mentorship opportunities.</p>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-10 flex flex-col items-center hover:scale-105 transition-transform">
                <div class="bg-green-100 text-green-700 rounded-full p-5 mb-4"><i class="fa-solid fa-rocket fa-2x"></i></div>
                <h3 class="font-bold text-2xl mb-2">Growth</h3>
                <p class="text-gray-600 text-center">Showcase your skills, grow your business, and build your portfolio.</p>
            </div>
        </div>
    </div>
</section>
<!-- Section Divider -->
<div class="w-full flex justify-center -mb-10 z-30 relative">
  <svg viewBox="0 0 1440 100" class="w-full h-24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path fill="#fff" d="M0,100 C480,0 960,100 1440,0 L1440,100 L0,100 Z"></path>
  </svg>
</div>
<!-- FEATURED MEMBERS / MEMBERSHIP SECTION -->
<section class="py-12 bg-green-50">
  <div class="container mx-auto px-4 flex flex-col items-center">
    <h2 class="text-4xl font-extrabold text-center text-green-900 mb-10 section-title">Membership</h2>
    <div id="excos-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 w-full justify-items-center">
      <?php 
      $displayed = 0;
      foreach ($excos as $member): ?>
      <?php
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
      ?>
      <div class="bg-green-50 rounded-xl shadow p-4 flex flex-col items-center relative hover:scale-105 transition-transform member-card text-center w-full max-w-xs min-w-[220px]">
          <!-- Role/position badge (only if role is not 'Member' and role_name is set) -->
          <?php if (!empty($member['role_name']) && strtolower($member['role_name']) !== 'member'): ?>
            <span class="absolute top-3 left-3 px-3 py-1 <?= htmlspecialchars($member['badge_color'] ?? 'bg-gray-200 text-gray-800') ?> text-xs rounded-full font-bold shadow capitalize"><?= htmlspecialchars($member['role_name']) ?></span>
          <?php endif; ?>
          <img src="<?= $is_valid_picture ? htmlspecialchars($profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode($member['name']) . '&background=008751&color=fff' ?>" class="w-14 h-14 rounded-full object-cover mb-2 border-2 border-white shadow mx-auto" alt="<?= htmlspecialchars($member['name']) ?>">
          <!-- Name typography -->
          <div class="font-extrabold text-base md:text-lg text-green-900 mb-1 w-full tracking-tight truncate"> <?= htmlspecialchars($member['name']) ?> </div>
          <!-- Professional title -->
          <div class="text-green-700 font-semibold mb-1 w-full text-xs md:text-sm truncate"> <?= htmlspecialchars($member['occupation']) ?> </div>
          <!-- Skills/technology tags -->
          <div class="flex flex-wrap justify-center gap-1 mb-1">
            <?php foreach ($skills as $skill): ?>
              <span class="inline-block bg-green-100 text-green-800 text-[10px] px-2 py-0.5 rounded-full font-medium shadow-sm"> <?= htmlspecialchars($skill) ?> </span>
            <?php endforeach; ?>
          </div>
          <?php $business = getBusiness($conn, $member['business_id']); if ($business): ?>
          <div class="text-[11px] text-gray-500 w-full mb-1 truncate">Business: <?= htmlspecialchars($business) ?></div>
          <?php endif; ?>
          <!-- Show more expandable section -->
          <div class="w-full">
            <!-- <button class="mt-1 mb-1 px-2 py-0.5 bg-green-200 text-green-900 rounded text-[11px] font-semibold shadow hover:bg-green-300 transition show-more-btn" data-member-id="<?= $member['id'] ?>">Show More</button> -->
            <div class="show-more-content hidden mt-1 text-left text-xs bg-white rounded p-2 border border-green-100 shadow-inner">
              <div class="mb-1"><span class="font-bold">Name:</span> <?= htmlspecialchars($member['name']) ?></div>
              <div class="mb-1"><span class="font-bold">Role:</span> <?= htmlspecialchars($member['role_name'] ?? 'Member') ?></div>
              <div class="mb-1"><span class="font-bold">Occupation:</span> <?= htmlspecialchars($member['occupation']) ?></div>
              <?php $business = getBusiness($conn, $member['business_id']); if ($business): ?>
              <div class="mb-1"><span class="font-bold">Business:</span> <?= htmlspecialchars($business) ?></div>
              <?php endif; ?>
              <div class="mb-1"><span class="font-bold">Skills:</span> <?= htmlspecialchars(implode(', ', $skills)) ?></div>
            </div>
          </div>
          <button class="mt-2 px-3 py-1 bg-green-700 text-white rounded text-xs shadow hover:bg-green-900 transition member-details-btn" data-member-id="<?= $member['id'] ?>">View Details</button>
      </div>
      <?php $displayed++; endforeach; ?>
      <?php 
      if ($displayed < 9) {
        $to_show = 9 - $displayed;
        for ($i = 0; $i < $to_show && $i < count($other_members); $i++) {
          $member = $other_members[$i];
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
      ?>
      <div class="bg-green-50 rounded-xl shadow p-4 flex flex-col items-center relative hover:scale-105 transition-transform member-card text-center w-full max-w-xs min-w-[220px]">
          <!-- Role/position badge (only if role is not 'Member' and role_name is set) -->
          <?php if (!empty($member['role_name']) && strtolower($member['role_name']) !== 'member'): ?>
            <span class="absolute top-3 left-3 px-3 py-1 <?= htmlspecialchars($member['badge_color'] ?? 'bg-gray-200 text-gray-800') ?> text-xs rounded-full font-bold shadow capitalize"><?= htmlspecialchars($member['role_name']) ?></span>
          <?php endif; ?>
          <img src="<?= $is_valid_picture ? htmlspecialchars($profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode($member['name']) . '&background=008751&color=fff' ?>" class="w-14 h-14 rounded-full object-cover mb-2 border-2 border-white shadow mx-auto" alt="<?= htmlspecialchars($member['name']) ?>">
          <div class="font-extrabold text-base md:text-lg text-green-900 mb-1 w-full tracking-tight truncate"> <?= htmlspecialchars($member['name']) ?> </div>
          <div class="text-green-700 font-semibold mb-1 w-full text-xs md:text-sm truncate"> <?= htmlspecialchars($member['occupation']) ?> </div>
          <div class="flex flex-wrap justify-center gap-1 mb-1">
            <?php foreach ($skills as $skill): ?>
              <span class="inline-block bg-green-100 text-green-800 text-[10px] px-2 py-0.5 rounded-full font-medium shadow-sm"> <?= htmlspecialchars($skill) ?> </span>
            <?php endforeach; ?>
          </div>
          <?php $business = getBusiness($conn, $member['business_id']); if ($business): ?>
          <div class="text-[11px] text-gray-500 w-full mb-1 truncate">Business: <?= htmlspecialchars($business) ?></div>
          <?php endif; ?>
          <div class="w-full">
            <button class="mt-1 mb-1 px-2 py-0.5 bg-green-200 text-green-900 rounded text-[11px] font-semibold shadow hover:bg-green-300 transition show-more-btn" data-member-id="<?= $member['id'] ?>">Show More</button>
            <div class="show-more-content hidden mt-1 text-left text-xs bg-white rounded p-2 border border-green-100 shadow-inner">
              <div class="mb-1"><span class="font-bold">Name:</span> <?= htmlspecialchars($member['name']) ?></div>
              <div class="mb-1"><span class="font-bold">Role:</span> <?= htmlspecialchars($member['role_name'] ?? 'Member') ?></div>
              <div class="mb-1"><span class="font-bold">Occupation:</span> <?= htmlspecialchars($member['occupation']) ?></div>
              <?php $business = getBusiness($conn, $member['business_id']); if ($business): ?>
              <div class="mb-1"><span class="font-bold">Business:</span> <?= htmlspecialchars($business) ?></div>
              <?php endif; ?>
              <div class="mb-1"><span class="font-bold">Skills:</span> <?= htmlspecialchars(implode(', ', $skills)) ?></div>
            </div>
          </div>
          <button class="mt-2 px-3 py-1 bg-green-700 text-white rounded text-xs shadow hover:bg-green-900 transition member-details-btn" data-member-id="<?= $member['id'] ?>">View Details</button>
      </div>
      <?php }} ?>
    </div>
    <div class="text-center mt-8">
      <a id="readMoreBtn" href="/ICT-Corps-Members-Hub/src/views/members.php" class="bg-green-700 text-white px-8 py-3 rounded-lg font-bold text-lg shadow hover:bg-green-900 transition">View All Members</a>
    </div>
  </div>
</section>
<!-- Member Details Modal -->
<div id="memberModal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center">
  <div class="bg-white rounded-xl shadow-lg p-8 max-w-md w-full relative animate-fade-in">
    <button id="closeModal" class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-2xl">&times;</button>
    <div id="modalContent">
      <!-- Member details will be loaded here -->
    </div>
  </div>
</div>
<!-- LATEST EVENTS -->
<section class="py-5 bg-green-50">
    <div class="container">
        <h2 class="display-5 fw-bold text-center text-success mb-4">Latest Events</h2>
        <div class="row g-4">
            <div class="col-12 col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <img src="https://images.unsplash.com/photo-1461749280684-dccba630e2f6?auto=format&fit=crop&w=400&q=80" class="card-img-top rounded-top" alt="Event 1">
                    <div class="card-body d-flex flex-column">
                        <h3 class="fw-bold text-success mb-1">Tech Bootcamp 2025</h3>
                        <p class="text-secondary mb-1">April 30, 2025</p>
                        <p class="text-muted mb-3">Join our intensive bootcamp to learn the latest in web and mobile development.</p>
                        <a href="/ICT-Corps-Members-Hub/src/views/events.php" class="btn btn-outline-success mt-auto">Learn More</a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <img src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=400&q=80" class="card-img-top rounded-top" alt="Event 2">
                    <div class="card-body d-flex flex-column">
                        <h3 class="fw-bold text-success mb-1">Design Thinking Workshop</h3>
                        <p class="text-secondary mb-1">May 10, 2025</p>
                        <p class="text-muted mb-3">A hands-on workshop to boost your creative and problem-solving skills.</p>
                        <a href="/ICT-Corps-Members-Hub/src/views/events.php" class="btn btn-outline-success mt-auto">Learn More</a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <img src="https://images.unsplash.com/photo-1503676382389-4809596d5290?auto=format&fit=crop&w=400&q=80" class="card-img-top rounded-top" alt="Event 3">
                    <div class="card-body d-flex flex-column">
                        <h3 class="fw-bold text-success mb-1">Business Pitch Night</h3>
                        <p class="text-secondary mb-1">May 25, 2025</p>
                        <p class="text-muted mb-3">Pitch your business ideas to a panel of experts and win amazing prizes.</p>
                        <a href="/ICT-Corps-Members-Hub/src/views/events.php" class="btn btn-outline-success mt-auto">Learn More</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="/ICT-Corps-Members-Hub/src/views/events.php" class="btn btn-success btn-lg px-5 py-2 fw-semibold">View All Events</a>
        </div>
    </div>
</section>
<style>
  @keyframes fade-in {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: none; }
  }
  .animate-fade-in {
    animation: fade-in 1s cubic-bezier(.4,0,.2,1) both;
  }
  .delay-100 { animation-delay: .1s; }
  .delay-200 { animation-delay: .2s; }
  .delay-300 { animation-delay: .3s; }
  .delay-400 { animation-delay: .4s; }
  /* Glassmorphism effect */
  .glassmorphism {
    background: rgba(255,255,255,0.18);
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.10);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-radius: 1.5rem;
    border: 1px solid rgba(255,255,255,0.25);
  }
  /* Animated gradient background for hero overlay */
  .animate-gradient-move {
    background: linear-gradient(120deg, #008751 0%, #00b16a 50%, #e0ffe0 100%);
    background-size: 200% 200%;
    animation: gradientMove 8s ease-in-out infinite;
    opacity: 0.7;
  }
  @keyframes gradientMove {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
  }
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(40px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .animate-fade-in-up {
    animation: fadeInUp 1.2s cubic-bezier(0.23, 1, 0.32, 1) both;
  }
  a, .btn, .nav-link {
    text-decoration: none !important;
  }
  a:hover, .btn:hover, .nav-link:hover {
    text-decoration: none !important;
  }
  .fade-in-slow {
    animation: fadeInModal 0.6s cubic-bezier(.4,0,.2,1) both;
  }
  .fade-out-fast {
    animation: fadeOutModal 0.4s cubic-bezier(.4,0,.2,1) both;
  }
  @keyframes fadeInModal {
    from { opacity: 0; transform: scale(0.95) translateY(40px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
  }
  @keyframes fadeOutModal {
    from { opacity: 1; transform: scale(1) translateY(0); }
    to { opacity: 0; transform: scale(0.95) translateY(40px); }
  }
</style>
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script src="/js/main.js"></script>
<!-- Bootstrap 5 JS CDN for Carousel -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    particlesJS('particles-js', {
      particles: {
        number: { value: 80, density: { enable: true, value_area: 800 } },
        color: { value: ["#22c55e", "#bbf7d0", "#4ade80", "#16a34a"] },
        shape: { type: "circle" },
        opacity: { value: 0.25, random: true },
        size: { value: 4, random: true },
        line_linked: { enable: true, distance: 120, color: "#22c55e", opacity: 0.2, width: 1 },
        move: { enable: true, speed: 1.2, direction: "none", random: true, straight: false, out_mode: "out" }
      },
      interactivity: {
        detect_on: "canvas",
        events: {
          onhover: { enable: true, mode: "grab" },
          onclick: { enable: true, mode: "push" },
          resize: true
        },
        modes: {
          grab: { distance: 180, line_linked: { opacity: 0.5 } },
          push: { particles_nb: 4 }
        }
      },
      retina_detect: true
    });
  });

  // Modal logic for member details
  const modal = document.getElementById('memberModal');
  const modalContent = document.getElementById('modalContent');
  document.querySelectorAll('.member-details-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const memberId = this.getAttribute('data-member-id');
      fetch(`/ICT-Corps-Members-Hub/public/index.php?ajax_member_id=${memberId}`)
        .then(res => res.text())
        .then(html => {
          modalContent.innerHTML = html;
          modal.classList.remove('hidden');
          modal.classList.remove('fade-out-fast');
          modal.classList.add('fade-in-slow');
        });
    });
  });
  document.getElementById('closeModal').onclick = () => {
    modal.classList.remove('fade-in-slow');
    modal.classList.add('fade-out-fast');
    setTimeout(() => modal.classList.add('hidden'), 400);
  };
  window.onclick = e => { if (e.target === modal) {
    modal.classList.remove('fade-in-slow');
    modal.classList.add('fade-out-fast');
    setTimeout(() => modal.classList.add('hidden'), 400);
  }};

  // Show more logic
  document.querySelectorAll('.show-more-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const content = this.parentElement.querySelector('.show-more-content');
      if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        content.classList.add('fade-in-slow');
        // this.textContent = 'Show Less';
      } else {
        content.classList.remove('fade-in-slow');
        content.classList.add('fade-out-fast');
        setTimeout(() => {
          content.classList.add('hidden');
          content.classList.remove('fade-out-fast');
        }, 400);
        this.textContent = 'Show More';
      }
    });
  });
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>

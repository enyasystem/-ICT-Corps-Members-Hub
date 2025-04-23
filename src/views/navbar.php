<!-- Bootstrap CSS loaded AFTER Tailwind to ensure Bootstrap's .collapse and other classes take precedence -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://hammerjs.github.io/dist/hammer.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/tailwind.css" rel="stylesheet">

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
    /* Navbar background transition on scroll */
    .navbar {
      background: rgba(20, 83, 45, 0.5) !important; /* 50% green by default */
      box-shadow: none;
      transition: background 0.3s, box-shadow 0.3s;
    }
    .navbar.navbar-scrolled {
      background: rgba(20, 83, 45, 0.97) !important; /* Deep green, almost opaque */
      box-shadow: 0 2px 16px 0 rgba(20, 83, 45, 0.10);
    }
    .member-card {
      background: rgba(22, 101, 52, 0.06); /* Faint green background */
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
    <a class="navbar-brand fw-bold text-white" href="/ICT-Corps-Members-Hub/public/index.php">ICT Corps Members Hub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse bs-navbar-collapse navbar-collapse" id="navbarMenu">
      <ul class="navbar-nav me-auto mb-2 mb-md-0">
      <li class="nav-item"><a class="nav-link text-white" href="/ICT-Corps-Members-Hub/src/views/home.php">Home</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="/ICT-Corps-Members-Hub/src/views/members.php">Members</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="/ICT-Corps-Members-Hub/src/views/events.php">Events</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="/ICT-Corps-Members-Hub/src/views/resources.php">Resources</a></li>
      </ul>
      <ul class="navbar-nav mb-2 mb-md-0">
      <li class="nav-item"><a class="nav-link text-white" href="/ICT-Corps-Members-Hub/public/auth/login.php">Login</a></li>
      <li class="nav-item"><a class="btn btn-light text-success ms-2" href="/ICT-Corps-Members-Hub/public/auth/register.php">Join Community</a></li>
      </ul>
    </div>
  </div>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Navbar background color on scroll
window.addEventListener('DOMContentLoaded', function() {
  var navbar = document.querySelector('.navbar');
  function onScroll() {
    if (window.scrollY > 10) {
      navbar.classList.add('navbar-scrolled');
    } else {
      navbar.classList.remove('navbar-scrolled');
    }
  }
  window.addEventListener('scroll', onScroll);
  // Run once on load in case page is not at top
  onScroll();
});
</script>


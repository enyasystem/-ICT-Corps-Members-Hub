<?php
// src/views/navbar.php
?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
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
      background: #14532d !important; /* Deep green background */
      transition: background 0.3s;
    }
    .navbar.navbar-scrolled {
      background: rgba(20, 83, 45, 0.97) !important;
      box-shadow: 0 2px 16px 0 rgba(20, 83, 45, 0.10);
    }
 
</style>
<!-- Navbar -->
<nav class="navbar navbar-expand-md navbar-dark fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold text-white" href="/ICT-Corps-Members-Hub/public">CorperConnect</a>
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

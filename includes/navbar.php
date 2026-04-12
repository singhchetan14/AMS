<?php
// $basePath is set by each page before including this file
// pages in root set it to '' (or dont set it), nested pages like auth/student/login.php set it to '../../'
// this makes sure navbar links always point to the correct path regardless of folder depth
if (!isset($currentPage)) {
    $currentPage = '';
}
if (!isset($basePath)) {
    $basePath = '';
}
?>

<nav class="navbar" id="navbar">
  <div class="container navbar__inner">

    <!-- Brand -->
    <a href="index.php" class="navbar__brand">AMS</a>

    <!-- Mobile Toggle -->
    <button class="navbar__toggle" id="navbar-toggle" aria-label="Toggle navigation">
      <span class="navbar__toggle-bar"></span>
      <span class="navbar__toggle-bar"></span>
      <span class="navbar__toggle-bar"></span>
    </button>

    <!-- Links -->
    <div class="navbar__links" id="navbar-links">
      <a href="<?= $basePath ?>index.php"
         class="navbar__link <?= $currentPage === 'home' ? 'navbar__link--active' : '' ?>">
        Home
      </a>
      <a href="<?= $basePath ?>about.php"
         class="navbar__link <?= $currentPage === 'about' ? 'navbar__link--active' : '' ?>">
        About
      </a>

      <!-- Login Dropdown -->
      <div class="navbar__dropdown">
        <button class="navbar__link navbar__dropdown-btn" id="login-dropdown-btn">
          Login <span class="navbar__arrow">&#9662;</span>
        </button>
        <div class="navbar__dropdown-menu" id="login-dropdown-menu">
          <a href="<?= $basePath ?>auth/student/login.php" class="navbar__dropdown-item">Student Login</a>
          <a href="<?= $basePath ?>auth/teacher/login.php" class="navbar__dropdown-item">Teacher Login</a>
        </div>
      </div>

      <!-- Signup (Student Only) -->
      <a href="<?= $basePath ?>auth/student/signup.php"
         class="navbar__link navbar__link--cta <?= $currentPage === 'signup' ? 'navbar__link--active' : '' ?>">
        Sign Up
      </a>
    </div>

  </div>
</nav>

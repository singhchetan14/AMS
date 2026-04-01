<?php
/**
 * ============================================
 * NAVBAR — Included on every page
 * ============================================
 *
 * Usage: Set $currentPage before including this file.
 *   $currentPage = 'home';
 *   include 'includes/navbar.php';
 */

if (!isset($currentPage)) {
    $currentPage = '';
}
?>

<nav class="navbar" id="navbar">
  <div class="container navbar__inner">

    <!-- Brand -->
    <a href="index.php" class="navbar__brand">
      AMS
    </a>

    <!-- Mobile Toggle -->
    <button class="navbar__toggle" id="navbar-toggle" aria-label="Toggle navigation">
      <span class="navbar__toggle-bar"></span>
      <span class="navbar__toggle-bar"></span>
      <span class="navbar__toggle-bar"></span>
    </button>

    <!-- Links -->
    <div class="navbar__links" id="navbar-links">
      <a href="index.php"
         class="navbar__link <?= $currentPage === 'home' ? 'navbar__link--active' : '' ?>">
        Home
      </a>
      <a href="about.php"
         class="navbar__link <?= $currentPage === 'about' ? 'navbar__link--active' : '' ?>">
        About
      </a>
      <a href="login.php"
         class="navbar__link <?= $currentPage === 'login' ? 'navbar__link--active' : '' ?>">
        Login
      </a>
      <a href="signup.php"
         class="navbar__link navbar__link--cta <?= $currentPage === 'signup' ? 'navbar__link--active' : '' ?>">
        Sign Up
      </a>
    </div>

  </div>
</nav>

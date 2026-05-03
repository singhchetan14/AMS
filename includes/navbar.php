<nav class="navbar">
  <div class="navbar__container">
    <div class="navbar__brand">
      <a href="<?= $basePath ?>index.php" class="navbar__logo">AMS</a>
    </div>
    <div class="navbar__menu">
      <a href="<?= $basePath ?>index.php" class="navbar__link">Home</a>
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="<?= $basePath ?>profile.php" class="navbar__link">Profile</a>
        <a href="<?= $basePath ?>logout.php" class="navbar__link">Logout</a>
      <?php else: ?>
        <a href="<?= $basePath ?>auth/student/login.php" class="navbar__link">Student Login</a>
        <a href="<?= $basePath ?>auth/student/signup.php" class="navbar__link">Student Signup</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

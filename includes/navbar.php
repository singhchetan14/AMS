<nav class="navbar">
  <style>
  .navbar {
    background-color: #1f2937;
    padding: 12px 0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .navbar__container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .navbar__brand {
    display: flex;
    align-items: center;
  }

  .navbar__logo {
    color: #ffffff;
    font-size: 24px;
    font-weight: 700;
    text-decoration: none;
    letter-spacing: 1px;
  }

  .navbar__logo:hover {
    color: #60a5fa;
  }

  .navbar__menu {
    display: flex;
    align-items: center;
    gap: 20px;
  }

  .navbar__link {
    color: #e5e7eb;
    font-size: 15px;
    font-weight: 500;
    text-decoration: none;
    padding: 6px 12px;
    border-radius: 4px;
    transition: background-color 0.2s ease, color 0.2s ease;
  }

  .navbar__link:hover {
    background-color: #374151;
    color: #ffffff;
  }
</style>


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

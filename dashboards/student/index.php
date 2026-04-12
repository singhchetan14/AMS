<?php
session_start();

// session guard - only logged in students can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: ../../auth/student/login.php');
    exit;
}

// logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../../auth/student/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard - AMS</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

  <nav class="navbar">
    <div class="container navbar__inner">
      <a href="../../index.php" class="navbar__brand">AMS</a>
      <div class="navbar__links">
        <span class="navbar__link">Welcome, <?= htmlspecialchars($_SESSION['user_email']) ?></span>
        <a href="?logout=true" class="navbar__link navbar__link--cta">Logout</a>
      </div>
    </div>
  </nav>

  <section class="auth">
    <div class="card card--form">
      <h1 class="auth__title">Student Dashboard</h1>
      <p class="auth__subtitle">Welcome to your dashboard</p>
    </div>
  </section>

  <?php include '../../includes/footer.php'; ?>

</body>
</html>

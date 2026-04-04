<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header('Location: ../../auth/teacher/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Dashboard - AMS</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

  <nav class="navbar">
    <div class="container navbar__inner">
      <a href="../../index.php" class="navbar__brand">AMS</a>
      <div class="navbar__links">
        <span class="navbar__link">Welcome, <?= htmlspecialchars($_SESSION['user_email']) ?></span>
        <a href="../../auth/teacher/login.php" class="navbar__link navbar__link--cta">Logout</a>
      </div>
    </div>
  </nav>

  <section class="auth">
    <div class="card card--form">
      <h1 class="auth__title">Teacher Dashboard</h1>
      <p class="auth__subtitle">Welcome to your dashboard</p>
    </div>
  </section>

  <?php include '../../includes/footer.php'; ?>

</body>
</html>

<?php
session_start();
require '../../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'student'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // password_verify checks against the hashed password stored in db
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = 'student';
            header('Location: ../../dashboards/student/index.php');
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Login - AMS</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

  <?php
  $currentPage = 'login';
  $basePath = '../../';
  include '../../includes/navbar.php';
  ?>

  <section class="auth">
    <div class="card card--form">
      <div class="auth__header">
        <h1 class="auth__title">Student Login</h1>
        <p class="auth__subtitle">Enter your email and password to access your account</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form__group">
          <label class="form__label"><span class="form__label-icon">&#9993;</span> Email Address</label>
          <input type="email" name="email" class="form__input" placeholder="Email" required>
        </div>

        <div class="form__group">
          <label class="form__label"><span class="form__label-icon">&#128274;</span> Password</label>
          <input type="password" name="password" class="form__input" placeholder="Password" required>
        </div>

        <button type="submit" class="btn btn--primary btn--block">Log in</button>
      </form>

      <div class="form__footer">
        Don't have an account? <a href="signup.php" class="form__link">Sign up</a>
      </div>
      <div class="form__footer">
        <a href="../forgot-password/request.php" class="form__link">Forgot password?</a>
      </div>
    </div>
  </section>

  <?php include '../../includes/footer.php'; ?>
  <script src="../../assets/js/script.js"></script>
</body>
</html>

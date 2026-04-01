<?php
session_start();
require 'config/db.php';

$error = '';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = "Invalid request.";
        } else {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? '';

            if (empty($email) || empty($password) || empty($role)) {
                $error = "All fields are required";
            } else {
                $sql = "SELECT * FROM users WHERE email = ? AND role = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$email, $role]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_email'] = $user['email'];

                    if ($user['role'] === 'teacher') {
                        header('Location: dashboards/teacher/index.php');
                    } else {
                        header('Location: dashboards/student/index.php');
                    }
                    exit;
                } else {
                    $error = "Invalid credentials";
                }
            }
        }
    }
} catch (Exception $e) {
    $error = "Something went wrong.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - AMS</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <?php include 'includes/navbar.php'; ?>

  <section class="auth" id="auth-login">
    <div class="card card--form">
      <div class="auth__header">
        <h1 class="auth__title">Welcome Back</h1>
        <p class="auth__subtitle">Sign in to your account</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert--error alert--visible"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" id="login-form">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="form__group">
          <label class="form__label">Email Address</label>
          <input type="email" name="email" class="form__input" placeholder="you@example.com" required>
        </div>

        <div class="form__group">
          <label class="form__label">Password</label>
          <input type="password" name="password" class="form__input" placeholder="••••••••" required>
        </div>

        <div class="form__group">
          <label class="form__label">Role</label>
          <select name="role" class="form__select" required>
            <option value="" disabled selected>Select your role</option>
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
          </select>
        </div>

        <button type="submit" class="btn btn--primary btn--block">Login</button>
      </form>

      <div class="form__footer">
        Don't have an account? <a href="signup.php" class="form__link">Sign Up</a>
      </div>
    </div>
  </section>

  <?php include 'includes/footer.php'; ?>

</body>
</html>

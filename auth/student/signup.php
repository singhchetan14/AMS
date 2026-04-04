<?php
require '../../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = "Email is already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'student')");

            if ($stmt->execute([$email, $hashed])) {
                $success = "Account created successfully!";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Sign Up - AMS</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

  <?php
  $currentPage = 'signup';
  include '../../includes/navbar.php';
  ?>

  <section class="auth" id="auth-signup">
    <div class="card card--form">
      <div class="auth__header">
        <h1 class="auth__title">Student Sign Up</h1>
        <p class="auth__subtitle">Create your student account</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert--success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form__group">
          <label class="form__label">Email Address</label>
          <input type="email" name="email" class="form__input" placeholder="you@example.com" required>
        </div>

        <div class="form__group">
          <label class="form__label">Password</label>
          <input type="password" name="password" class="form__input" placeholder="Min 6 characters" required>
        </div>

        <div class="form__group">
          <label class="form__label">Confirm Password</label>
          <input type="password" name="confirm_password" class="form__input" placeholder="Re-enter password" required>
        </div>

        <button type="submit" class="btn btn--primary btn--block">Create Account</button>
      </form>

      <div class="form__footer">
        Already have an account? <a href="login.php" class="form__link">Login</a>
      </div>
    </div>
  </section>

  <?php include '../../includes/footer.php'; ?>

  <script src="../../assets/js/script.js"></script>
</body>
</html>

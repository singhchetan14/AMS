<?php
session_start();
require 'config/db.php';

$message = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? 'student';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format.";
        } elseif (empty($password) || strlen($password) < 6) {
            $message = "Password must be at least 6 characters.";
        } elseif ($password !== $confirm_password) {
            $message = "Passwords do not match.";
        } else {
            $checkSql = "SELECT id FROM users WHERE email = ?";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$email]);
            
            if ($checkStmt->rowCount() > 0) {
                $message = "Email is already registered.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (email, password, role) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([$email, $hashed_password, $role])) {
                    $message = "Account created successfully! Redirecting...";
                    header('refresh: 2; url=login.php');
                } else {
                    $message = "Failed to create account.";
                }
            }
        }
    }
} catch (Exception $e) {
    $message = "Something went wrong.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - AMS</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <?php include 'includes/navbar.php'; ?>

  <section class="auth" id="auth-signup">
    <div class="card card--form">
      <div class="auth__header">
        <h1 class="auth__title">Create Account</h1>
        <p class="auth__subtitle">Join the Academic Management System</p>
      </div>

      <?php if ($message): ?>
        <div class="alert alert--error alert--visible"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <form method="POST" id="signup-form">
        <div class="form__group">
          <label class="form__label">Email Address</label>
          <input type="email" name="email" class="form__input" placeholder="you@example.com" required>
        </div>

        <div class="form__group">
          <label class="form__label">Password</label>
          <input type="password" name="password" class="form__input" required minlength="6">
        </div>

        <div class="form__group">
          <label class="form__label">Confirm Password</label>
          <input type="password" name="confirm_password" class="form__input" required>
        </div>

        <div class="form__group">
          <label class="form__label">Role</label>
          <select name="role" class="form__select" required>
            <option value="student" selected>Student</option>
            <option value="teacher">Teacher</option>
          </select>
        </div>

        <button type="submit" class="btn btn--primary btn--block">Create Account</button>
      </form>

      <div class="form__footer">
        Already have an account? <a href="login.php" class="form__link">Login</a>
      </div>
    </div>
  </section>

  <?php include 'includes/footer.php'; ?>

</body>
</html>

<?php
// Forgot Password - Step 3 of 3
// User sets a new password after verifying OTP code
session_start();
require '../../config/db.php';

$error = '';
$success = '';

// both session vars must exist - means user went through request.php and verify.php
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_verified'])) {
    header('Location: request.php');
    exit;
}

$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($password) || strlen($password) < 6) {
        $error = "Password should be atleast 6 characters.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords donot match.";
    } else {
        // hashing and updating the new password
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashed, $email]);

        // cleanup - delete used OTP from db and clear session so this page cant be reused
        $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_verified']);

        $success = "Password changed! Redirecting to login...";
        header("Refresh: 2; url=../student/login.php");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password - AMS</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

  <?php
  $currentPage = '';
  $basePath = '../../';
  include '../../includes/navbar.php';
  ?>

  <section class="auth">
    <div class="card card--form card--forgot">
      <div class="auth__header">
        <h1 class="auth__title">Reset Password</h1>
        <p class="auth__subtitle">Set new password for your account</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert--success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form__group">
          <label class="form__label"><span class="form__label-icon">&#128274;</span> New Password</label>
          <input type="password" name="password" class="form__input" placeholder="Min 6 characters" required>
        </div>

        <div class="form__group">
          <label class="form__label"><span class="form__label-icon">&#128274;</span> Confirm Password</label>
          <input type="password" name="confirm_password" class="form__input" placeholder="Re-enter password" required>
        </div>

        <button type="submit" class="btn btn--teal btn--block">Reset Password</button>
      </form>
    </div>
  </section>

  <?php include '../../includes/footer.php'; ?>
  <script src="../../assets/js/script.js"></script>
</body>
</html>

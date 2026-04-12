<?php
// Forgot Password - Step 2 of 3
// User enters the 6-digit OTP code sent to their email
// Code expires after 10 minutes (checked in SQL query below)
session_start();
require '../../config/db.php';

$error = '';

// session guard - user must come from request.php which sets this
if (!isset($_SESSION['reset_email'])) {
    header('Location: request.php');
    exit;
}

$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');

    if (empty($code) || strlen($code) !== 6) {
        $error = "Enter the 6 digit code.";
    } else {
        // matching code in db + checking 10 min expiry using created_at timestamp
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = ? AND code = ? AND created_at >= NOW() - INTERVAL 10 MINUTE");
        $stmt->execute([$email, $code]);

        if ($stmt->rowCount() > 0) {
            // setting this flag so reset.php knows the user actually verified the code
            $_SESSION['reset_verified'] = true;
            header('Location: reset.php');
            exit;
        } else {
            $error = "Wrong code or code expired. Try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify Code - AMS</title>
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
        <h1 class="auth__title">Verify Code</h1>
        <p class="auth__subtitle">Enter the code sent to <?= htmlspecialchars($email) ?></p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form__group">
          <label class="form__label">Verification Code</label>
          <input type="text" name="code" class="form__input" placeholder="Enter 6 digit code" maxlength="6" required>
        </div>

        <button type="submit" class="btn btn--teal btn--block">Verify</button>
      </form>

      <div class="form__footer">
        <a href="request.php" class="form__link">Resend Code</a>
      </div>
    </div>
  </section>

  <?php include '../../includes/footer.php'; ?>

  <script src="../../assets/js/script.js"></script>
</body>
</html>

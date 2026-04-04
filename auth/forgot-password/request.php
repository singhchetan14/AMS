<?php
session_start();
require '../../config/db.php';
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email exists in DB
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() === 0) {
            $error = "No account found with that email.";
        } else {
            // Generate 6-digit code
            $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            // Delete any old codes for this email
            $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

            // Save code to DB
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, code) VALUES (?, ?)");
            $stmt->execute([$email, $code]);

            // Store email in session for verify page
            $_SESSION['reset_email'] = $email;

            // Send code via PHPMailer
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'chetansingh206111@gmail.com';       // Replace with your Gmail
                $mail->Password = 'ppnb zonk zwlx aoiv';           // Replace with your App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('chetansingh206111@gmail.com', 'AMS');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'AMS Password Reset Code';
                $mail->Body = "<h3>Your password reset code is:</h3><h1>$code</h1><p>This code will expire in 10 minutes.</p>";

                $mail->send();
                $success = "Verification code sent to your email.";
                header("Refresh: 2; url=verify.php");
            } catch (Exception $e) {
                $error = "Failed to send email. Please try again.";
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
  <title>Forgot Password - AMS</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

  <?php
  $currentPage = '';
  include '../../includes/navbar.php';
  ?>

  <section class="auth">
    <div class="card card--form">
      <div class="auth__header">
        <h1 class="auth__title">Forgot Password</h1>
        <p class="auth__subtitle">Enter your email to receive a reset code</p>
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

        <button type="submit" class="btn btn--primary btn--block">Send Reset Code</button>
      </form>

      <div class="form__footer">
        <a href="../student/login.php" class="form__link">Back to Login</a>
      </div>
    </div>
  </section>

  <?php include '../../includes/footer.php'; ?>

  <script src="../../assets/js/script.js"></script>
</body>
</html>

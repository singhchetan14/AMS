<?php
// Forgot Password - Step 1 of 3
// Flow: request.php (enter email) -> verify.php (enter code) -> reset.php (new password)
// We use PHPMailer to send a 6-digit OTP code to the user's email via Gmail SMTP
// Mail credentials are stored in config/mail.php (gitignored for security)
session_start();
require '../../config/db.php';
require '../../config/mail.php';
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = "Please enter your email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // checking if email exsits in db
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() === 0) {
            $error = "No account found with that email.";
        } else {
            // genrating 6 digit random code
            $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            // deleteing old codes if any
            $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

            // saving new code in db
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, code) VALUES (?, ?)");
            $stmt->execute([$email, $code]);

            // storeing email in session so we can use it in verify page
            $_SESSION['reset_email'] = $email;

            // sending OTP using PHPMailer via Gmail SMTP
            // make sure you have run: composer require phpmailer/phpmailer
            // and configured config/mail.php with your Gmail app password
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = $mail_host;
                $mail->SMTPAuth = true;
                $mail->Username = $mail_username;
                $mail->Password = $mail_password;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $mail_port;

                $mail->setFrom($mail_username, $mail_from_name);
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'AMS Password Reset Code';
                $mail->Body = "<h3>Your password reset code is:</h3><h1>$code</h1><p>This code will expire in 10 minutes.</p>";

                $mail->send();
                $success = "Code sent to your email.";
                // auto redirect to verify page after 2 seconds
                header("Refresh: 2; url=verify.php");
            } catch (Exception $e) {
                $error = "Could not send email. Try again later.";
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
  $basePath = '../../';
  include '../../includes/navbar.php';
  ?>

  <section class="auth">
    <div class="card card--form">
      <div class="auth__header">
        <h1 class="auth__title">Forgot Password</h1>
        <p class="auth__subtitle">Enter your email to receive a recovery code.</p>
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

        <button type="submit" class="btn btn--teal btn--block">Send OTP Code</button>
      </form>

      <div class="form__footer">
        Remember your password? <a href="../student/login.php" class="form__link">Login</a>
      </div>
    </div>
  </section>

  <?php include '../../includes/footer.php'; ?>

  <script src="../../assets/js/script.js"></script>
</body>
</html>

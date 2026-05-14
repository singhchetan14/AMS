<?php
// Student Sign Up - Step 2 of 2
// User enters the 6-digit code that was emailed in step 1 (signup.php).
// On match we move the pending row from signup_verifications into the
// real users table and delete the pending row + clear the session.
// Resend link: signup-verify.php?resend=1 reissues a fresh code.
session_start();
require '../../config/db.php';
require '../../config/mail.php';
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';

// session guard - user must have come from signup.php
if (!isset($_SESSION['signup_email'])) {
    header('Location: signup.php');
    exit;
}

$email = $_SESSION['signup_email'];

// Flash message after a redirect (used by the resend handler below so a
// refresh of the verify page doesn't re-trigger another email)
if (isset($_SESSION['signup_flash'])) {
    $success = $_SESSION['signup_flash'];
    unset($_SESSION['signup_flash']);
}

// Resend code branch - regenerate a new 6 digit code and email it again
if (isset($_GET['resend'])) {
    $stmt = $pdo->prepare("SELECT id FROM signup_verifications WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() === 0) {
        // pending row was cleaned up (expired/cleared) - restart signup
        unset($_SESSION['signup_email']);
        header('Location: signup.php');
        exit;
    }

    $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $pdo->prepare("UPDATE signup_verifications SET code = ?, created_at = NOW() WHERE email = ?")
        ->execute([$code, $email]);

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
        $mail->Subject = 'Your new AMS verification code';
        // same branded template as the initial email, with a "new code" intro
        $mail->Body = "
<div style='font-family: Arial, Helvetica, sans-serif; max-width: 560px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;'>
  <div style='background: #0d9488; padding: 24px; text-align: center;'>
    <h1 style='margin: 0; color: #ffffff; font-size: 22px; letter-spacing: 0.5px;'>Academic Management System</h1>
  </div>
  <div style='padding: 32px 28px; color: #1f2937;'>
    <h2 style='margin: 0 0 16px; font-size: 20px; color: #111827;'>Here is your new verification code</h2>
    <p style='font-size: 15px; line-height: 1.6; margin: 0 0 20px;'>
      You requested a new code. Please use the one below to finish verifying your AMS student account. Any previous codes are no longer valid.
    </p>
    <div style='margin: 28px 0; padding: 22px; background: #f0fdfa; border: 1px dashed #0d9488; border-radius: 8px; text-align: center;'>
      <div style='font-size: 12px; color: #0f766e; letter-spacing: 2px; margin-bottom: 10px; text-transform: uppercase;'>Your Verification Code</div>
      <div style='font-size: 38px; font-weight: 700; color: #0d9488; letter-spacing: 8px;'>{$code}</div>
    </div>
    <p style='font-size: 14px; color: #6b7280; line-height: 1.6; margin: 0 0 8px;'>
      This code will expire in <strong>10 minutes</strong>.
    </p>
    <p style='font-size: 14px; color: #6b7280; line-height: 1.6; margin: 0;'>
      If you didn&rsquo;t request this, you can safely ignore this email.
    </p>
  </div>
  <div style='background: #f9fafb; padding: 16px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb;'>
    &copy; Academic Management System &middot; This is an automated message, please do not reply.
  </div>
</div>";
        $mail->AltBody = "Your new AMS verification code is: {$code}\n\nThis code expires in 10 minutes. Any previous codes are no longer valid.";

        $mail->send();
        $_SESSION['signup_flash'] = "A new code has been sent to your email.";
    } catch (Exception $e) {
        $_SESSION['signup_flash'] = "Could not resend code. Please try again.";
    }
    // redirect to a clean URL so refresh doesn't re-resend
    header('Location: signup-verify.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');

    if (empty($code) || strlen($code) !== 6) {
        $error = "Enter the 6 digit code.";
    } else {
        // matching code + 10 min expiry using created_at timestamp
        $stmt = $pdo->prepare("SELECT password_hash FROM signup_verifications WHERE email = ? AND code = ? AND created_at >= NOW() - INTERVAL 10 MINUTE");
        $stmt->execute([$email, $code]);
        $row = $stmt->fetch();

        if ($row) {
            // verified - create the real user account now using the stored hash
            $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'student')");
            $stmt->execute([$email, $row['password_hash']]);

            // cleanup - drop pending row and clear session so this page cant be reused
            $pdo->prepare("DELETE FROM signup_verifications WHERE email = ?")->execute([$email]);
            unset($_SESSION['signup_email']);

            $success = "Email verified! Account created. Redirecting to login...";
            header("Refresh: 2; url=login.php");
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
  <title>Verify Email - AMS</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

  <?php
  $currentPage = 'signup';
  $basePath = '../../';
  include '../../includes/navbar.php';
  ?>

  <section class="auth">
    <div class="card card--form card--forgot">
      <div class="auth__header">
        <h1 class="auth__title">Verify Your Email</h1>
        <p class="auth__subtitle">Enter the code sent to <?= htmlspecialchars($email) ?></p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert--success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form__group">
          <label class="form__label">Verification Code</label>
          <input type="text" name="code" class="form__input" placeholder="Enter 6 digit code" maxlength="6" required>
        </div>

        <button type="submit" class="btn btn--teal btn--block">Verify &amp; Create Account</button>
      </form>

      <div class="form__footer">
        Didn&rsquo;t get the code? <a href="signup-verify.php?resend=1" class="form__link">Resend Code</a>
      </div>
    </div>
  </section>

  <?php include '../../includes/footer.php'; ?>

  <script src="../../assets/js/script.js"></script>
  <script>
    // Auto-redirect to login after successful verification
    <?php if ($success && strpos($success, 'Account created') !== false): ?>
      setTimeout(() => {
        window.location.href = 'login.php';
      }, 2000);
    <?php endif; ?>
  </script>
</body>
</html>

<?php
// Student Sign Up - Step 1 of 2
// Flow: signup.php (enter email/password) -> signup-verify.php (enter code)
// We email a 6-digit OTP to confirm the student actually owns the email
// before the account is created in the users table. Pending signups live
// in signup_verifications; the row is moved to users only after verify.
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
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = "Email is already registered.";
        } else {
            // generating 6 digit OTP (same pattern as forgot-password)
            $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            // hashing password before storing - never store plain text passwords
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // wipe any previous unverified attempt for this email, then store fresh row
            $pdo->prepare("DELETE FROM signup_verifications WHERE email = ?")->execute([$email]);
            $stmt = $pdo->prepare("INSERT INTO signup_verifications (email, password_hash, code) VALUES (?, ?, ?)");
            $stmt->execute([$email, $hashed, $code]);

            // remember email across the redirect so signup-verify.php knows who is verifying
            $_SESSION['signup_email'] = $email;

            // sending OTP using PHPMailer via Gmail SMTP (same config as forgot-password)
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
                $mail->Subject = 'Verify your AMS account';
                // branded HTML body - inline styles so Gmail/Outlook render it correctly
                $mail->Body = "
<div style='font-family: Arial, Helvetica, sans-serif; max-width: 560px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;'>
  <div style='background: #0d9488; padding: 24px; text-align: center;'>
    <h1 style='margin: 0; color: #ffffff; font-size: 22px; letter-spacing: 0.5px;'>Academic Management System</h1>
  </div>
  <div style='padding: 32px 28px; color: #1f2937;'>
    <h2 style='margin: 0 0 16px; font-size: 20px; color: #111827;'>Verify your email address</h2>
    <p style='font-size: 15px; line-height: 1.6; margin: 0 0 12px;'>Hi there,</p>
    <p style='font-size: 15px; line-height: 1.6; margin: 0 0 20px;'>
      Thanks for signing up for AMS. To finish creating your student account, please confirm that this email address belongs to you by entering the verification code below.
    </p>
    <div style='margin: 28px 0; padding: 22px; background: #f0fdfa; border: 1px dashed #0d9488; border-radius: 8px; text-align: center;'>
      <div style='font-size: 12px; color: #0f766e; letter-spacing: 2px; margin-bottom: 10px; text-transform: uppercase;'>Your Verification Code</div>
      <div style='font-size: 38px; font-weight: 700; color: #0d9488; letter-spacing: 8px;'>{$code}</div>
    </div>
    <p style='font-size: 14px; color: #6b7280; line-height: 1.6; margin: 0 0 8px;'>
      This code will expire in <strong>10 minutes</strong>.
    </p>
    <p style='font-size: 14px; color: #6b7280; line-height: 1.6; margin: 0;'>
      If you didn&rsquo;t request this, you can safely ignore this email &mdash; no account is created until the code is verified.
    </p>
  </div>
  <div style='background: #f9fafb; padding: 16px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb;'>
    &copy; Academic Management System &middot; This is an automated message, please do not reply.
  </div>
</div>";
                $mail->AltBody = "Welcome to AMS!\n\nYour email verification code is: {$code}\n\nThis code expires in 10 minutes. If you didn't request this, ignore this email.";

                $mail->send();
                $success = "Verification code sent to your email. Redirecting...";
                // auto redirect to verify page after 2 seconds
                header("Refresh: 2; url=signup-verify.php");
            } catch (Exception $e) {
                // rollback pending row so the user can retry cleanly
                $pdo->prepare("DELETE FROM signup_verifications WHERE email = ?")->execute([$email]);
                unset($_SESSION['signup_email']);
                $error = "Could not send verification email. Please try again later.";
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
  $basePath = '../../';
  include '../../includes/navbar.php';
  ?>

  <section class="auth">
    <div class="card card--form">
      <div class="auth__header">
        <h1 class="auth__title">Create an account</h1>
        <p class="auth__subtitle">Sign up as a student</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert--success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form__group">
          <label class="form__label"><span class="form__label-icon">&#9993;</span> Email Address</label>
          <input type="email" name="email" class="form__input" placeholder="Email" required>
        </div>

        <div class="form__group">
          <label class="form__label"><span class="form__label-icon">&#128274;</span> Password</label>
          <div style="position: relative;">
            <input type="password" id="signup-password" name="password" class="form__input" placeholder="Min 6 characters" required>
            <button type="button" onclick="togglePassword('signup-password')"
              style="position:absolute; right:15px; top:50%; transform:translateY(-50%); border:none; background:none; cursor:pointer; font-size:1rem;">
              &#128065;
            </button>
          </div>
        </div>

        <div class="form__group">
          <label class="form__label"><span class="form__label-icon">&#128274;</span> Confirm Password</label>
          <div style="position: relative;">
            <input type="password" id="signup-confirm-password" name="confirm_password" class="form__input" placeholder="Re-enter password" required>
            <button type="button" onclick="togglePassword('signup-confirm-password')"
              style="position:absolute; right:15px; top:50%; transform:translateY(-50%); border:none; background:none; cursor:pointer; font-size:1rem;">
              &#128065;
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn--primary btn--block">Sign up</button>
      </form>

      <script>
        function togglePassword(id) {
          var f = document.getElementById(id);
          f.type = f.type === "password" ? "text" : "password";
        }
      </script>

      <div class="form__footer">
        Already have an account? <a href="login.php" class="form__link">Log in</a>
      </div>
    </div>
  </section>

  <?php include '../../includes/footer.php'; ?>
  <script src="../../assets/js/script.js"></script>
  <script>
    // Redirect to verify page after the OTP has been sent
    <?php if ($success): ?>
      setTimeout(() => {
        window.location.href = 'signup-verify.php';
      }, 2000);
    <?php endif; ?>
  </script>
</body>
</html>

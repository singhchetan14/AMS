<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

$stmt = $pdo->prepare("SELECT full_name, email, phone, gender, photo FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch() ?: ['full_name' => '', 'email' => $_SESSION['user_email'] ?? '', 'phone' => '', 'gender' => '', 'photo' => null];

$success = isset($_GET['success']);
$error   = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile | Academic Management System</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="layout">
    <?php require __DIR__ . '/includes/sidebar.php'; ?>
    <main class="main">
      <?php require __DIR__ . '/includes/header.php'; ?>

      <div class="card-wrap">
        <a class="back-link" href="dashboard.php">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><polyline points="12 8 8 12 12 16"/><line x1="16" y1="12" x2="8" y2="12"/>
          </svg>
          Back
        </a>
        <h2>Profile Settings</h2>
        <div class="divider"></div>

        <?php if ($success): ?>
          <div class="alert alert--success">Profile updated.</div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form class="profile-wrap" method="POST" action="actions/do_update_profile.php" enctype="multipart/form-data">
          <div class="profile-photo-row">
            <?php if (!empty($user['photo'])): ?>
              <img class="profile-avatar" src="uploads/photos/<?= htmlspecialchars($user['photo']) ?>" alt="Profile photo">
            <?php else: ?>
              <div class="profile-avatar" aria-hidden="true"></div>
            <?php endif; ?>
            <label class="btn-primary" for="photo-input">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                <circle cx="12" cy="13" r="4"/>
              </svg>
              Change Photo
            </label>
            <input id="photo-input" type="file" name="photo" accept="image/png,image/jpeg" style="display:none">
          </div>

          <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
          </div>

          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
          </div>

          <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="e.g. +977 98XXXXXXXX">
          </div>

          <div class="form-group">
            <label>Gender</label>
            <select name="gender">
              <?php $g = strtolower((string)($user['gender'] ?? '')); ?>
              <option value=""        <?= $g === ''       ? 'selected' : '' ?>>Prefer not to say</option>
              <option value="male"    <?= $g === 'male'   ? 'selected' : '' ?>>Male</option>
              <option value="female"  <?= $g === 'female' ? 'selected' : '' ?>>Female</option>
              <option value="other"   <?= $g === 'other'  ? 'selected' : '' ?>>Other</option>
            </select>
          </div>

          <div class="form-group">
            <label>New Password</label>
            <div style="position: relative;">
              <input type="password" id="profile-new-password" name="new_password" placeholder="Leave blank to keep current">
              <button type="button" onclick="togglePassword('profile-new-password')"
                style="position:absolute; right:12px; top:50%; transform:translateY(-50%); border:none; background:none; cursor:pointer; font-size:1rem; color:inherit;">
                &#128065;
              </button>
            </div>
          </div>

          <div class="btn-row">
            <button type="submit" class="btn-primary">Update</button>
            <a class="btn-primary" href="logout.php">Logout</a>
          </div>
        </form>
      </div>
    </main>
  </div>
  <?php include __DIR__ . '/../../messaging/widget/widget.php'; ?>
  <script>
    function togglePassword(id) {
      var f = document.getElementById(id);
      f.type = f.type === "password" ? "text" : "password";
    }
  </script>
</body>
</html>

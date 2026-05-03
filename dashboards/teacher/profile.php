<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

$stmt = $pdo->prepare("SELECT full_name, email, photo FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$_SESSION['teacher_id']]);
$user = $stmt->fetch() ?: ['full_name' => '', 'email' => $_SESSION['teacher_email'] ?? '', 'photo' => null];

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
  <style>
    .card-wrap {
      background: var(--bg-card);
      border-radius: var(--radius);
      padding: 28px 32px;
      border: 1px solid rgba(255,255,255,0.06);
      max-width: 1100px;
    }
    .back-link {
      display: inline-flex; align-items: center; gap: 6px;
      color: #c8d5e5; text-decoration: none;
      margin-bottom: 18px; font-size: 0.9rem;
    }
    .back-link:hover { color: #fff; }
    .divider { height: 1px; background: rgba(255,255,255,0.1); margin: 18px 0 22px; }

    .profile-wrap { max-width: 520px; margin: 0 auto; }
    .profile-photo-row { text-align: center; margin-bottom: 18px; }
    .profile-avatar {
      width: 110px; height: 110px;
      border-radius: 50%;
      background: #6c7d92;
      margin: 8px auto 14px;
      display: block; object-fit: cover;
      border: 2px solid rgba(255,255,255,0.12);
    }
    .btn-primary {
      display: inline-flex; align-items: center; justify-content: center;
      gap: 8px;
      background: #2361cf; color: #fff;
      border: none; padding: 10px 22px;
      border-radius: 22px; font-size: 0.9rem; cursor: pointer;
      text-decoration: none;
    }
    .btn-primary:hover { opacity: 0.9; }
    .btn-row { display: flex; flex-direction: column; gap: 10px; align-items: flex-start; margin-top: 8px; }
    .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
    .form-group label { font-size: 0.88rem; color: #c8d5e5; }
    .alert { padding: 10px 14px; border-radius: 8px; margin-bottom: 14px; font-size: 0.9rem; }
    .alert--success { background: rgba(0,200,80,0.12); border: 1px solid rgba(0,200,80,0.35); color: #9ff0bf; }
    .alert--error   { background: rgba(255,80,80,0.12); border: 1px solid rgba(255,80,80,0.35); color: #ff9c9c; }
  </style>
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
        <h2 style="margin:0 0 0;font-weight:500;font-size:1.25rem;">Profile Settings</h2>
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
            <label>New Password</label>
            <input type="password" name="new_password" placeholder="Leave blank to keep current">
          </div>

          <div class="btn-row">
            <button type="submit" class="btn-primary">Update</button>
            <a class="btn-primary" href="logout.php">Logout</a>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>
</html>

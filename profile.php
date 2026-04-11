<?php
/**
 * profile.php
 * 
 * Teacher profile page — update name, email, password, change photo.
 */

require_once 'includes/auth.php';
require_once 'config/db.php';

// ── Fetch teacher data ───────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT full_name, email, department, phone, photo 
    FROM teachers 
    WHERE id = ?
");
$stmt->execute([$_SESSION['teacher_id']]);
$teacher = $stmt->fetch();

$success = isset($_GET['success']) ? 1 : 0;
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profile | Academic Management System</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body class="no-sidebar">
    <div class="layout" style="min-height: 100vh; background-color: #0d1b2a; display: flex; align-items: center; justify-content: center;">
        <main class="main" style="width: 100%; max-width: 700px; padding: 20px;">
            <div class="profile-settings-card" style="background-color: #1b304c; border-radius: 12px; padding: 40px; color: #fff; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);">
                
                <div style="display:flex; align-items:center; justify-content:space-between; gap: 14px; margin-bottom: 40px;">
                    <h2 style="font-size: 24px; font-weight: 400; margin: 0; font-family: sans-serif;">Profile Settings</h2>
                    <a href="dashboard.php" style="background-color: #1967d2; color: #fff; border: none; border-radius: 20px; padding: 10px 18px; font-size: 14px; cursor: pointer; font-family: sans-serif; text-align: center; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap;">
                        Back to Dashboard
                    </a>
                </div>

                <?php if ($success): ?>
                    <div style="background: rgba(0, 255, 0, 0.1); border: 1px solid rgba(0, 255, 0, 0.3); padding: 12px; border-radius: 8px; margin-bottom: 20px; color: #90ee90;">
                        ✓ Profile updated successfully!
                    </div>
                <?php elseif ($error): ?>
                    <div style="background: rgba(255, 0, 0, 0.1); border: 1px solid rgba(255, 0, 0, 0.3); padding: 12px; border-radius: 8px; margin-bottom: 20px; color: #ff6b6b;">
                        ✗ Error: <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="actions/do_update_profile.php" enctype="multipart/form-data">
                    
                    <!-- ── Profile Photo ──────────────────────────────── -->
                    <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 30px;">
                        <?php if ($teacher['photo']): ?>
                            <img src="uploads/photos/<?= htmlspecialchars($teacher['photo']) ?>" alt="Profile" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; background-color: #7b889b; border: 3px solid #0d1b2a; margin-bottom: 16px;" />
                        <?php else: ?>
                            <div style="width: 120px; height: 120px; border-radius: 50%; background-color: #7b889b; border: 3px solid #0d1b2a; margin-bottom: 16px;"></div>
                        <?php endif; ?>

                        <label style="background-color: #1967d2; color: #fff; border: none; border-radius: 4px; padding: 8px 16px; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-family: sans-serif;">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                <circle cx="12" cy="13" r="4"></circle>
                            </svg>
                            Change Photo
                            <input type="file" name="photo" accept="image/png,image/jpeg" style="display:none" />
                        </label>
                    </div>

                    <!-- ── Form Fields ────────────────────────────────– -->
                    <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 30px;">
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-size: 14px; font-family: sans-serif; color: #e2e8f0;">Full Name</label>
                            <input name="full_name" type="text" value="<?= htmlspecialchars($teacher['full_name']) ?>" style="width: 100%; box-sizing: border-box; background-color: #7b889b; border: 1px solid #0d1b2a; padding: 12px 16px; border-radius: 20px; color: #fff; outline: none; font-family: sans-serif;" required />
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-size: 14px; font-family: sans-serif; color: #e2e8f0;">Email</label>
                            <input name="email" type="email" value="<?= htmlspecialchars($teacher['email']) ?>" style="width: 100%; box-sizing: border-box; background-color: #7b889b; border: 1px solid #0d1b2a; padding: 12px 16px; border-radius: 20px; color: #fff; outline: none; font-family: sans-serif;" required />
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-size: 14px; font-family: sans-serif; color: #e2e8f0;">New Password (leave blank to keep current)</label>
                            <input name="new_password" type="password" placeholder="••••••••" style="width: 100%; box-sizing: border-box; background-color: #7b889b; border: 1px solid #0d1b2a; padding: 12px 16px; border-radius: 20px; color: #fff; outline: none; font-family: sans-serif;" />
                        </div>
                    </div>

                    <!-- ── Buttons ────────────────────────────────────── -->
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <button type="submit" style="background-color: #1967d2; color: #fff; border: none; border-radius: 20px; padding: 10px 24px; font-size: 14px; cursor: pointer; font-family: sans-serif; text-align: center;">
                            Update Profile
                        </button>
                        <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');" style="background-color: #8b4545; color: #fff; border: none; border-radius: 20px; padding: 10px 24px; font-size: 14px; cursor: pointer; font-family: sans-serif; text-align: center; text-decoration: none; display: block;">
                            Logout
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
</body>
</html>

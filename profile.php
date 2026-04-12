<?php
/**
 * profile.php
 *
 * Teacher profile page — update name, email, password, change photo.
 */

// Force no-cache so updated values always show fresh from DB
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

require_once 'includes/auth.php';
require_once 'config/db.php';

// ── Fetch latest teacher data from DB ───────────────────────────────
$stmt = $pdo->prepare("
    SELECT full_name, email, department, phone, photo
    FROM teachers
    WHERE id = ?
");
$stmt->execute([$_SESSION['teacher_id']]);
$teacher = $stmt->fetch();

if (!$teacher) {
    header('Location: logout.php');
    exit;
}

// Update session name in case it changed
$_SESSION['teacher_name']  = $teacher['full_name'];
$_SESSION['teacher_email'] = $teacher['email'];

$success = isset($_GET['success']) ? 1 : 0;
$error   = isset($_GET['error'])   ? htmlspecialchars($_GET['error']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profile | Academic Management System</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        body {
            background: #0d1b2a;
            color: #fff;
            font-family: "Inter", "Segoe UI", Roboto, Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* ── Toast ──────────────────────────────────────────────────── */
        .toast {
            position: fixed;
            top: 28px;
            right: 28px;
            background: #1a3a5c;
            border: 1px solid rgba(25, 103, 210, 0.5);
            color: #fff;
            padding: 14px 22px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.35s ease;
            pointer-events: none;
            z-index: 9999;
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
        }
        .toast.show   { opacity: 1; transform: translateY(0); }
        .toast.error  { border-color: rgba(255, 80, 80, 0.5); background: #3a1a1a; }
        .toast.success { border-color: rgba(0, 200, 80, 0.5); background: #0f2e1a; }
    </style>
</head>
<body class="no-sidebar">
    <div class="layout" style="min-height: 100vh; background-color: #0d1b2a; display: flex; align-items: center; justify-content: center;">
        <main class="main" style="width: 100%; max-width: 700px; padding: 20px;">
            <div class="profile-settings-card" style="background-color: #1b304c; border-radius: 12px; padding: 40px; color: #fff; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);">

                <div style="display:flex; align-items:center; justify-content:space-between; gap: 14px; margin-bottom: 40px;">
                    <h2 style="font-size: 24px; font-weight: 400; margin: 0;">Profile Settings</h2>
                    <a href="dashboard.php" style="background-color: #1967d2; color: #fff; border: none; border-radius: 20px; padding: 10px 18px; font-size: 14px; cursor: pointer; text-align: center; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap;">
                        Back to Dashboard
                    </a>
                </div>

                <form method="POST" action="actions/do_update_profile.php" enctype="multipart/form-data">

                    <!-- ── Profile Photo ───────────────────────────── -->
                    <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 30px;">
                        <?php if (!empty($teacher['photo'])): ?>
                            <img src="uploads/photos/<?= htmlspecialchars($teacher['photo']) ?>"
                                 alt="Profile"
                                 style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; background-color: #7b889b; border: 3px solid #0d1b2a; margin-bottom: 16px;" />
                        <?php else: ?>
                            <div style="width: 120px; height: 120px; border-radius: 50%; background-color: #7b889b; border: 3px solid #0d1b2a; margin-bottom: 16px; display:flex; align-items:center; justify-content:center;">
                                <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#ffffff80" stroke-width="1.5">
                                    <circle cx="12" cy="8" r="4"/>
                                    <path d="M4 20c0-4 3.6-6 8-6s8 2 8 6"/>
                                </svg>
                            </div>
                        <?php endif; ?>

                        <label style="background-color: #1967d2; color: #fff; border: none; border-radius: 4px; padding: 8px 16px; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                <circle cx="12" cy="13" r="4"/>
                            </svg>
                            Change Photo
                            <input type="file" name="photo" accept="image/png,image/jpeg" style="display:none"
                                   onchange="this.closest('label').querySelector('span') && (this.closest('label').querySelector('span').textContent = this.files[0]?.name || 'Change Photo')" />
                        </label>
                    </div>

                    <!-- ── Form Fields ──────────────────────────────── -->
                    <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 30px;">

                        <div>
                            <label style="display: block; margin-bottom: 8px; font-size: 14px; color: #e2e8f0;">Full Name</label>
                            <input
                                name="full_name"
                                type="text"
                                value="<?= htmlspecialchars($teacher['full_name']) ?>"
                                required
                                style="width: 100%; box-sizing: border-box; background-color: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.15); padding: 12px 16px; border-radius: 8px; color: #fff; outline: none; font-size: 14px; transition: border-color 0.2s;"
                                onfocus="this.style.borderColor='#1967d2'"
                                onblur="this.style.borderColor='rgba(255,255,255,0.15)'"
                            />
                        </div>

                        <div>
                            <label style="display: block; margin-bottom: 8px; font-size: 14px; color: #e2e8f0;">Email</label>
                            <input
                                name="email"
                                type="email"
                                value="<?= htmlspecialchars($teacher['email']) ?>"
                                required
                                style="width: 100%; box-sizing: border-box; background-color: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.15); padding: 12px 16px; border-radius: 8px; color: #fff; outline: none; font-size: 14px; transition: border-color 0.2s;"
                                onfocus="this.style.borderColor='#1967d2'"
                                onblur="this.style.borderColor='rgba(255,255,255,0.15)'"
                            />
                        </div>

                        <div>
                            <label style="display: block; margin-bottom: 8px; font-size: 14px; color: #e2e8f0;">New Password <span style="color:#8b99a8;">(leave blank to keep current)</span></label>
                            <input
                                name="new_password"
                                type="password"
                                placeholder="••••••••"
                                style="width: 100%; box-sizing: border-box; background-color: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.15); padding: 12px 16px; border-radius: 8px; color: #fff; outline: none; font-size: 14px; transition: border-color 0.2s;"
                                onfocus="this.style.borderColor='#1967d2'"
                                onblur="this.style.borderColor='rgba(255,255,255,0.15)'"
                            />
                        </div>
                    </div>

                    <!-- ── Buttons ──────────────────────────────────── -->
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <button type="submit"
                                style="background-color: #1967d2; color: #fff; border: none; border-radius: 8px; padding: 12px 24px; font-size: 15px; cursor: pointer; transition: opacity 0.2s;"
                                onmouseover="this.style.opacity='0.85'"
                                onmouseout="this.style.opacity='1'">
                            Update Profile
                        </button>
                        <button type="button"
                           onclick="document.getElementById('logout-modal').style.display='flex'"
                           style="background-color: rgba(180,60,60,0.3); border: 1px solid rgba(180,60,60,0.5); color: #ff8080; border-radius: 8px; padding: 12px 24px; font-size: 15px; cursor: pointer; text-align: center; text-decoration: none; display: block; transition: all 0.2s;"
                           onmouseover="this.style.background='rgba(180,60,60,0.45)'"
                           onmouseout="this.style.background='rgba(180,60,60,0.3)'">
                            Logout
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- ── Logout Modal ────────────────────────────────────── -->
    <div id="logout-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:10000; align-items:center; justify-content:center; backdrop-filter: blur(2px);">
        <div style="background:#1b304c; padding:30px; border-radius:12px; width:300px; text-align:center; box-shadow:0 4px 24px rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.1);">
            <h3 style="margin-top:0; color:#fff; font-size:18px; font-weight:500;">Confirm Logout</h3>
            <p style="color:#c8d5e5; font-size:14px; margin-bottom:24px;">Are you sure you want to log out?</p>
            <div style="display:flex; justify-content:space-between; gap:12px;">
                <button type="button" onclick="document.getElementById('logout-modal').style.display='none'" style="flex:1; padding:10px; border-radius:8px; background:rgba(255,255,255,0.1); border:none; color:#c8d5e5; cursor:pointer; font-size:14px; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.15)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">Cancel</button>
                <a href="logout.php" style="flex:1; padding:10px; border-radius:8px; background:#e53e3e; border:none; color:#fff; cursor:pointer; text-decoration:none; display:inline-block; font-size:14px; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">Logout</a>
            </div>
        </div>
    </div>

    <!-- ── Toast Notification ─────────────────────────────── -->
    <div id="toast" class="toast"></div>

    <script>
        function showToast(message, type = 'success') {
            const t = document.getElementById('toast');
            t.textContent = message;
            t.className = 'toast ' + type;
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 3500);
        }

        // Auto-show toast based on URL param
        <?php if ($success): ?>
            showToast('✓ Profile updated successfully!', 'success');
            // Clean the URL without reloading
            history.replaceState(null, '', 'profile.php');
        <?php elseif ($error): ?>
            showToast('✗ <?= addslashes($error) ?>', 'error');
            history.replaceState(null, '', 'profile.php');
        <?php endif; ?>
    </script>

    <script src="script.js"></script>
</body>
</html>

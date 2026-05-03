<?php
/**
 * Handles student profile update: full_name, email, optional new password,
 * optional avatar upload (PNG/JPEG ≤ 5MB).
 */

require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'student') {
    header('Location: ../../../auth/student/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../profile.php');
    exit;
}

$fullName    = trim($_POST['full_name']    ?? '');
$email       = trim($_POST['email']        ?? '');
$newPassword = trim($_POST['new_password'] ?? '');
$photo       = $_FILES['photo'] ?? null;

if ($fullName === '' || $email === '') {
    header('Location: ../profile.php?error=' . urlencode('Name and email are required'));
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../profile.php?error=' . urlencode('Invalid email format'));
    exit;
}

$pdo->beginTransaction();
try {
    $fields = ['full_name' => $fullName, 'email' => $email];

    if ($newPassword !== '') {
        if (strlen($newPassword) < 6) {
            throw new Exception('Password must be at least 6 characters.');
        }
        $fields['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    if ($photo && ($photo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $allowed = ['image/png', 'image/jpeg'];
        $finfo   = finfo_open(FILEINFO_MIME_TYPE);
        $mime    = finfo_file($finfo, $photo['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed, true)) {
            throw new Exception('Only PNG and JPEG images are allowed.');
        }
        if ($photo['size'] > 5 * 1024 * 1024) {
            throw new Exception('Photo too large (max 5MB).');
        }

        $uploadsDir = __DIR__ . '/../uploads/photos';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        $ext      = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION) ?: ($mime === 'image/png' ? 'png' : 'jpg'));
        $filename = 'student_' . (int)$_SESSION['user_id'] . '_' . time() . '.' . $ext;
        $dest     = $uploadsDir . '/' . $filename;

        if (!move_uploaded_file($photo['tmp_name'], $dest)) {
            throw new Exception('Failed to save photo.');
        }
        $fields['photo'] = $filename;
    }

    $cols  = [];
    $vals  = [];
    foreach ($fields as $col => $val) {
        $cols[] = "$col = ?";
        $vals[] = $val;
    }
    $vals[] = $_SESSION['user_id'];

    $sql = "UPDATE users SET " . implode(', ', $cols) . " WHERE id = ? AND role = 'student'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($vals);

    // Keep the linked `students` row in sync where possible (best-effort)
    $pdo->prepare("UPDATE students SET full_name = ?, email = ? WHERE email = ?")
        ->execute([$fullName, $email, $_SESSION['user_email']]);

    // Refresh session-cached values used by the header / auth.php
    $_SESSION['user_email']   = $email;
    $_SESSION['student_name'] = $fullName;

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: ../profile.php?error=' . urlencode($e->getMessage()));
    exit;
}

header('Location: ../profile.php?success=1');
exit;

<?php
/**
 * actions/do_update_profile.php
 * 
 * Handles teacher profile updates: name, email, password, photo.
 */

require_once __DIR__ . '/../config/db.php';

session_start();
if (!isset($_SESSION['teacher_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../profile.php');
    exit;
}

// ── Get form input ───────────────────────────────────────────────────
$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$newPassword = trim($_POST['new_password'] ?? '');
$photo = $_FILES['photo'] ?? null;

// ── Validate input ───────────────────────────────────────────────────
if (empty($fullName) || empty($email)) {
    header('Location: ../profile.php?error=Name%20and%20email%20are%20required');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../profile.php?error=Invalid%20email%20format');
    exit;
}

// ── Start transaction ────────────────────────────────────────────────
$pdo->beginTransaction();

try {
    // ── Update name and email ────────────────────────────────────────
    $updateFields = ['full_name' => $fullName, 'email' => $email];
    
    // ── Update password if provided ──────────────────────────────────
    if (!empty($newPassword)) {
        if (strlen($newPassword) < 6) {
            throw new Exception('Password must be at least 6 characters.');
        }
        $updateFields['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
    }
    
    // ── Handle photo upload ──────────────────────────────────────────
    if ($photo && $photo['error'] === UPLOAD_ERR_OK) {
        $allowedMimes = ['image/png', 'image/jpeg'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileMime = finfo_file($finfo, $photo['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($fileMime, $allowedMimes)) {
            throw new Exception('Only PNG and JPEG images are allowed.');
        }
        
        if ($photo['size'] > 5 * 1024 * 1024) {  // 5MB max
            throw new Exception('Photo is too large (max 5MB).');
        }
        
        // ── Create uploads directory if needed ────────────────────
        $uploadsDir = __DIR__ . '/../uploads/photos';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        
        // ── Generate unique filename ────────────────────────────────
        $extension = pathinfo($photo['name'], PATHINFO_EXTENSION);
        $photoFilename = 'teacher_' . $_SESSION['teacher_id'] . '_' . time() . '.' . $extension;
        $storagePath = $uploadsDir . '/' . $photoFilename;
        
        if (!move_uploaded_file($photo['tmp_name'], $storagePath)) {
            throw new Exception('Failed to upload photo.');
        }
        
        $updateFields['photo'] = $photoFilename;
    }
    
    // ── Build UPDATE query ───────────────────────────────────────────
    $updateCols = [];
    $updateVals = [];
    foreach ($updateFields as $col => $val) {
        $updateCols[] = "$col = ?";
        $updateVals[] = $val;
    }
    $updateVals[] = $_SESSION['teacher_id'];
    
    $updateQuery = "UPDATE users SET " . implode(', ', $updateCols) . " WHERE id = ? AND role = 'teacher'";
    $stmt = $pdo->prepare($updateQuery);
    $stmt->execute($updateVals);
    
    // ── Update session data ──────────────────────────────────────────
    $_SESSION['teacher_name'] = $fullName;
    $_SESSION['teacher_email'] = $email;
    
    $pdo->commit();
    
} catch (Exception $e) {
    $pdo->rollBack();
    $error = urlencode($e->getMessage());
    header("Location: ../profile.php?error=$error");
    exit;
}

// ── Success — redirect back ──────────────────────────────────────────
header('Location: ../profile.php?success=1');
exit;

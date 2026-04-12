<?php
/**
 * actions/do_upload_material.php
 *
 * Handles material file upload and database insertion.
 * Validates file type (MIME + extension), size, generates unique filename,
 * stores in uploads/materials/
 */

require_once __DIR__ . '/../config/db.php';

// ── Only allow POST ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../upload-materials.php');
    exit;
}

// ── Start session ────────────────────────────────────────────────────
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header('Location: ../login.php');
    exit;
}

// ── Get form input ───────────────────────────────────────────────────
$courseId = (int)($_POST['course_id'] ?? 0);
$title    = trim($_POST['title'] ?? '');
$file     = $_FILES['file'] ?? null;

// ── Validate input ───────────────────────────────────────────────────
if (empty($courseId) || empty($title) || !$file || $file['error'] !== UPLOAD_ERR_OK) {
    header('Location: ../upload-materials.php?error=Invalid%20input');
    exit;
}

// ── Verify course belongs to teacher ─────────────────────────────────
$stmt = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->execute([$courseId, $_SESSION['teacher_id']]);
if (!$stmt->fetch()) {
    header('Location: ../upload-materials.php?error=Course%20not%20found');
    exit;
}

// ── Allowed extensions and MIME types ────────────────────────────────
$allowedExtensions = ['pdf', 'docx', 'pptx', 'txt'];

$allowedMimes = [
    'application/pdf',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    // .txt can have many MIME variants depending on OS/content
    'text/plain',
    'text/x-c',
    'text/x-pascal',
    'text/x-script.phyton',
    'text/html',
    'inode/x-empty',  // empty text file on some systems
];

$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

// Primary: extension whitelist check
if (!in_array($extension, $allowedExtensions)) {
    header('Location: ../upload-materials.php?error=File%20type%20not%20allowed%20(PDF%2C%20DOCX%2C%20PPTX%2C%20TXT%20only)');
    exit;
}

// Secondary: MIME check — for text files, allow any text/* MIME type
if (function_exists('finfo_open')) {
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $fileMime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $mimeOk = in_array($fileMime, $allowedMimes)
           || ($extension === 'txt' && strpos($fileMime, 'text/') === 0)
           || ($extension === 'txt' && $fileMime === 'application/octet-stream');

    if (!$mimeOk) {
        header('Location: ../upload-materials.php?error=Invalid%20file%20content%20type');
        exit;
    }
}

// ── Validate file size (max 10MB) ────────────────────────────────────
if ($file['size'] > 10 * 1024 * 1024) {
    header('Location: ../upload-materials.php?error=File%20too%20large%20(max%2010MB)');
    exit;
}

// ── Ensure uploads directory exists and is writable ──────────────────
$uploadsDir = __DIR__ . '/../uploads/materials';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
    chmod($uploadsDir, 0777);
}

if (!is_writable($uploadsDir)) {
    header('Location: ../upload-materials.php?error=Upload%20directory%20not%20writable');
    exit;
}

// ── Generate unique filename ─────────────────────────────────────────
$storedFilename = uniqid('mat_') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
$storagePath    = $uploadsDir . '/' . $storedFilename;

// ── Move uploaded file ───────────────────────────────────────────────
if (!move_uploaded_file($file['tmp_name'], $storagePath)) {
    header('Location: ../upload-materials.php?error=Upload%20failed%20(server%20error)');
    exit;
}

// ── Insert into database ─────────────────────────────────────────────
try {
    $stmt = $pdo->prepare("
        INSERT INTO materials (title, filename, course_id, teacher_id)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$title, $storedFilename, $courseId, $_SESSION['teacher_id']]);
} catch (Exception $e) {
    // Clean up uploaded file on DB error
    @unlink($storagePath);
    header('Location: ../upload-materials.php?error=Database%20error');
    exit;
}

// ── Success ──────────────────────────────────────────────────────────
header('Location: ../upload-materials.php?success=1');
exit;

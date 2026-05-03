<?php
/**
 * download.php
 *
 * Streams a teacher-uploaded material file to the logged-in student
 * with Content-Disposition: attachment so the browser saves it instead
 * of opening it inline.
 *
 * URL: download.php?id=<material_id>
 *
 * The on-disk filename is randomised (e.g. mat_xxxxx.pdf); the file
 * is presented to the user as "<material title>.<ext>".
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    exit('Invalid request');
}

$stmt = $pdo->prepare("SELECT title, filename FROM materials WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$material = $stmt->fetch();

if (!$material) {
    http_response_code(404);
    exit('Material not found');
}

// Files are stored by the teacher dashboard at:
//   dashboards/teacher/uploads/materials/<filename>
$path = __DIR__ . '/../teacher/uploads/materials/' . $material['filename'];

// Defence-in-depth: make sure the resolved path is still inside the
// teacher uploads directory and exists as a regular file.
$realBase = realpath(__DIR__ . '/../teacher/uploads/materials');
$realPath = realpath($path);
if ($realBase === false || $realPath === false || strpos($realPath, $realBase) !== 0 || !is_file($realPath)) {
    http_response_code(404);
    exit('File not found on server');
}

// Build the user-visible filename: "<title>.<ext>"
$ext = strtolower(pathinfo($material['filename'], PATHINFO_EXTENSION));
$safeTitle = preg_replace('/[^A-Za-z0-9 _.\-]/', '_', $material['title']);
$safeTitle = trim($safeTitle) !== '' ? $safeTitle : ('material_' . $id);
$downloadName = $ext !== '' ? ($safeTitle . '.' . $ext) : $safeTitle;

// Pick a sensible Content-Type
$mime = 'application/octet-stream';
if (function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detected = finfo_file($finfo, $realPath);
    finfo_close($finfo);
    if ($detected) { $mime = $detected; }
}

// Headers
while (ob_get_level() > 0) { ob_end_clean(); }
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Transfer-Encoding: binary');
header('Cache-Control: private, no-store, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Length: ' . filesize($realPath));

readfile($realPath);
exit;

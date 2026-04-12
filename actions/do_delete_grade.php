<?php
/**
 * actions/do_delete_grade.php
 * 
 * Deletes a grade record after verifying ownership.
 */

require_once __DIR__ . '/../config/db.php';

session_start();
if (!isset($_SESSION['teacher_id'])) {
    header('Location: ../login.php');
    exit;
}

$gradeId = (int)($_GET['id'] ?? 0);

// ── Verify grade belongs to teacher ──────────────────────────────────
$stmt = $pdo->prepare("
    SELECT course_id FROM grades WHERE id = ? AND teacher_id = ?
");
$stmt->execute([$gradeId, $_SESSION['teacher_id']]);
$grade = $stmt->fetch();

if (!$grade) {
    header('Location: ../upload-grades.php?error=Grade%20not%20found');
    exit;
}

// ── Get course info for redirect ─────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT group_name FROM courses WHERE id = ?
");
$stmt->execute([$grade['course_id']]);
$course = $stmt->fetch();

// ── Delete grade ─────────────────────────────────────────────────────
$stmt = $pdo->prepare("DELETE FROM grades WHERE id = ? AND teacher_id = ?");
$stmt->execute([$gradeId, $_SESSION['teacher_id']]);

// ── Redirect back ───────────────────────────────────────────────────
$redirect = '../upload-grades.php?group=' . urlencode($course['group_name']) . '&course=' . $grade['course_id'] . '&success=1';
header('Location: ' . $redirect);
exit;

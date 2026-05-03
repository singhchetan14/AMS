<?php
/**
 * Session guard for student pages.
 * Redirects to canonical student login if not signed in,
 * and resolves the matching `students` row (looked up by email)
 * so child pages can use $_SESSION['student_id'] / ['student_group'].
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'student') {
    header('Location: ../../auth/student/login.php');
    exit;
}

// Resolve the linked `students` row + display name once per session
if (!array_key_exists('student_id', $_SESSION)) {
    require_once __DIR__ . '/../config/db.php';

    $stmt = $pdo->prepare("SELECT id, group_name FROM students WHERE email = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_email']]);
    $row = $stmt->fetch();
    $_SESSION['student_id']    = $row['id']         ?? null;
    $_SESSION['student_group'] = $row['group_name'] ?? null;

    $stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch();
    $_SESSION['student_name'] = $u['full_name'] ?? $_SESSION['user_email'];
}

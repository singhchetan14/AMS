<?php
/**
 * actions/do_login.php
 * 
 * Handles login form submission.
 * Validates credentials, starts session, redirects to dashboard or back to login.
 */

require_once __DIR__ . '/../config/db.php';

// ── Only allow POST ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

// ── Get form input ───────────────────────────────────────────────────
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// ── Validate input ───────────────────────────────────────────────────
if (empty($email) || empty($password)) {
    header('Location: ../login.php?error=1');
    exit;
}

// ── Query teacher by email (prepared statement) ──────────────────────
$stmt = $pdo->prepare("
    SELECT id, full_name, email, password 
    FROM teachers 
    WHERE email = ?
    LIMIT 1
");
$stmt->execute([$email]);
$teacher = $stmt->fetch();

// ── Verify password ─────────────────────────────────────────────────
if (!$teacher || !password_verify($password, $teacher['password'])) {
    // Invalid credentials
    header('Location: ../login.php?error=1');
    exit;
}

// ── Login success — start session ────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID for security
session_regenerate_id(true);

// Store in session
$_SESSION['teacher_id'] = $teacher['id'];
$_SESSION['teacher_name'] = $teacher['full_name'];
$_SESSION['teacher_email'] = $teacher['email'];

// Redirect to dashboard
header('Location: ../dashboard.php');
exit;

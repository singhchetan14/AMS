<?php
/**
 * includes/auth.php
 * 
 * Session validation — checks if teacher is logged in.
 * If not logged in, redirects to login page.
 * Include this at the top of every protected page.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Check Session ────────────────────────────────────────────────────
if (!isset($_SESSION['teacher_id']) || !isset($_SESSION['teacher_name'])) {
    // Not logged in — redirect to login page
    header('Location: login.php');
    exit;
}

// ── Optional: Add session timeout (30 minutes) ──────────────────────
// Uncomment if you want automatic logout after inactivity
/*
if (isset($_SESSION['last_activity'])) {
    if ((time() - $_SESSION['last_activity']) > (30 * 60)) {
        // Session expired
        session_destroy();
        header('Location: login.php?timeout=1');
        exit;
    }
}
$_SESSION['last_activity'] = time();
*/
?>

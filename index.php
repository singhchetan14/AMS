<?php
/**
 * index.php
 * 
 * Entry point — redirects to login or dashboard based on session status.
 */

session_start();

if (isset($_SESSION['teacher_id'])) {
    // Already logged in
    header('Location: dashboard.php');
} else {
    // Not logged in
    header('Location: login.php');
}
exit;

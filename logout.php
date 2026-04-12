<?php
/**
 * logout.php
 * 
 * Logs out the teacher by destroying session and redirecting to login.
 */

session_start();
session_destroy();

header('Location: login.php');
exit;

<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: ../../auth/student/login.php');
exit;

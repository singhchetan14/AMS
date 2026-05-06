<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: ../../auth/teacher/login.php');
exit;

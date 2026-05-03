<?php
session_start();
include("../../config/db.php");

if(!isset($_SESSION['admin'])){
    header("Location: ../login.php");
    exit();
}

// SAFE INPUT HANDLING
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = $_GET['type'] ?? "";

// VALIDATION
if($id <= 0 || empty($type)){
    die("Invalid request");
}

// DELETE TEACHER
if($type === "teacher"){
    $stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role='teacher'");
    $stmt->execute([$id]);

    header("Location: view_teachers.php");
    exit();
}

// DELETE COURSE
elseif($type === "course"){
    $stmt = $conn->prepare("DELETE FROM courses WHERE id=?");
    $stmt->execute([$id]);

    header("Location: view_courses.php");
    exit();
}

// UN-ASSIGN COURSE (clear teacher_id, keep course)
elseif($type === "assignment"){
    $stmt = $conn->prepare("UPDATE courses SET teacher_id=NULL WHERE id=?");
    $stmt->execute([$id]);

    header("Location: view_assigned_courses.php");
    exit();
}

else{
    die("Invalid type");
}
?>
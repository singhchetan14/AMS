<?php
session_start();
include("../../config/db.php");

// Check admin session
if(!isset($_SESSION['admin'])){
    header("Location: ../login.php");
    exit();
}

// Get course ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($id <= 0){
    die("Invalid ID");
}

// Fetch existing course
$stmt = $conn->prepare("SELECT * FROM courses WHERE id=?");
$stmt->execute([$id]);
$course = $stmt->fetch();

if(!$course){
    die("Course not found");
}

// Update course
if(isset($_POST['update'])){
    $name = $_POST['course'];

    $stmt = $conn->prepare("UPDATE courses SET name=? WHERE id=?");
    $stmt->execute([$name, $id]);

    header("Location: view_courses.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Course</title>

<!-- ✅ ADDED CSS LINK -->
<link rel="stylesheet" href="../../assets/css/admin_style.css">

<style>
body {
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(to bottom right, #071a2f, #02101f);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Card */
.card {
    width: 700px;
    padding: 50px;
    background: #1e3a5f;
    border-radius: 20px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.6);
    color: #fff;
    border: 2px solid #000;
}

/* Back */
.back {
    color: #cfd8dc;
    text-decoration: none;
    margin-bottom: 30px;
    display: inline-block;
}

/* Title */
h2 {
    margin-bottom: 40px;
    font-weight: 500;
}

/* Label */
label {
    display: block;
    margin-bottom: 10px;
}

/* Input */
input {
    width: 100%;
    padding: 15px;
    background: transparent;
    border: 2px solid #8fa3b8;
    border-radius: 5px;
    color: #fff;
    margin-bottom: 40px;
}

/* Button */
button {
    display: block;
    margin: auto;
    padding: 12px 40px;
    border-radius: 25px;
    border: none;
    background: #2e6fd8;
    color: #fff;
    cursor: pointer;
    font-size: 15px;
    transition: 0.3s;
}

button:hover {
    background: #1c4fa3;
}
</style>

</head>
<body>

<div class="card">

    <!--  Back Button -->
    <a href="dashboard.php" class="back">← Back</a>

    <h2>Edit Course</h2>

    <form method="POST">
        <label>Course Name</label>
        <input type="text" name="course" value="<?= htmlspecialchars($course['name'] ?? '') ?>" required>

        <button name="update">Save Changes</button>
    </form>

</div>

</body>
</html>
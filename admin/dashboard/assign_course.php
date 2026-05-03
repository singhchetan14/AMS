<?php
session_start();
include("../../config/db.php");

if(!isset($_SESSION['admin'])){ header("Location: ../login.php"); exit; }

$success = "";

if(isset($_POST['assign'])){
    $conn->prepare("UPDATE courses SET teacher_id=? WHERE id=?")
         ->execute([$_POST['teacher'], $_POST['course']]);

    $success = "Course assigned successfully";
}

$teachers = $conn->query("SELECT id, full_name FROM users WHERE role='teacher'");
$courses  = $conn->query("SELECT id, name FROM courses WHERE teacher_id IS NULL");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Assign Course</title>

<!-- CSS link -->
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
    width: 650px;
    padding: 50px;
    background: #1e3a5f;
    border-radius: 20px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.6);
    color: #fff;
    border: 2px solid #000;
}

/* Back button */
.back {
    color: #cfd8dc;
    text-decoration: none;
    display: inline-block;
    margin-bottom: 30px;
    font-size: 14px;
}

/* Title */
h2 {
    margin-bottom: 30px;
    font-weight: 500;
}

/* Labels */
label {
    display: block;
    margin-bottom: 10px;
    color: #dfe6e9;
}

/* Select */
select {
    width: 100%;
    padding: 12px;
    border-radius: 25px;
    border: none;
    outline: none;
    background: #aab7c4;
    margin-bottom: 25px;
}

/* Button */
button {
    display: block;
    margin: 20px auto 0;
    padding: 12px 35px;
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

/* Success Message */
.success {
    background: #8ddab8;
    padding: 10px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
}
</style>

</head>
<body>

<div class="card">

    <!-- Back -->
    <a href="dashboard.php" class="back">← Back</a>

    <h2>Assign Course</h2>

    <!--  Success Message -->
    <?php if($success): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">

        <label>Teacher's Name</label>
        <select name="teacher" required>
            <?php while($t=$teachers->fetch()){ ?>
                <option value="<?=$t['id']?>"><?= htmlspecialchars($t['full_name'] ?? '') ?></option>
            <?php } ?>
        </select>

        <label>Course Name</label>
        <select name="course" required>
            <?php while($c=$courses->fetch()){ ?>
                <option value="<?=$c['id']?>"><?= htmlspecialchars($c['name'] ?? '') ?></option>
            <?php } ?>
        </select>

        <button name="assign">Save Changes</button>

    </form>

</div>

</body>
</html>
<?php
session_start();
include("../../config/db.php");
if(!isset($_SESSION['admin'])){ header("Location: ../login.php"); exit; }

// GET COURSE ID (assignment lives on the course row itself)
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// FETCH EXISTING ASSIGNMENT (= the course row)
$stmt = $conn->prepare("SELECT id, name, teacher_id FROM courses WHERE id=?");
$stmt->execute([$id]);
$assignment = $stmt->fetch();

if(!$assignment){
    die("Assignment not found");
}

// UPDATE LOGIC
if(isset($_POST['update'])){
    $conn->prepare("UPDATE courses SET teacher_id=? WHERE id=?")
         ->execute([$_POST['teacher'], $id]);

    header("Location: view_assigned_courses.php");
    exit();
}

// FETCH TEACHERS & COURSES for dropdowns
$teachers = $conn->query("SELECT id, full_name FROM users WHERE role='teacher'");
$courses  = $conn->query("SELECT id, name FROM courses");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Assigned Courses</title>

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
    display: inline-block;
    margin-bottom: 30px;
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
</style>

</head>
<body>

<div class="card">

    <!-- Back Button -->
    <a href="dashboard.php" class="back">← Back</a>

    <h2>Edit Assigned Courses</h2>

    <form method="POST">

        <!-- Teacher -->
        <label>Teacher’s Name</label>
        <select name="teacher" required>
            <?php while($t = $teachers->fetch()){ ?>
                <option value="<?= $t['id'] ?>"
                    <?= ($t['id'] == $assignment['teacher_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['full_name'] ?? '') ?>
                </option>
            <?php } ?>
        </select>

        <!-- Course (read-only since the assignment IS the course) -->
        <label>Assigned Course</label>
        <select name="course" required disabled>
            <?php while($c = $courses->fetch()){ ?>
                <option value="<?= $c['id'] ?>"
                    <?= ($c['id'] == $assignment['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['name'] ?? '') ?>
                </option>
            <?php } ?>
        </select>

        <button name="update">Save Changes</button>

    </form>

</div>

</body>
</html>
<?php
session_start();
include("../../config/db.php");
if(!isset($_SESSION['admin'])){ header("Location: ../login.php"); exit; }
$data=$conn->query("SELECT * FROM courses");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Courses</title>
<!--  CSS LINK -->
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
    padding: 40px;
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
    margin-bottom: 20px;
    font-size: 14px;
}

/* Title */
h2 {
    margin-bottom: 30px;
    font-weight: 500;
}

/* Table */
.table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 10px;
    overflow: hidden;
}

.table thead {
    background: #4a617d;
}

.table th {
    padding: 15px;
    text-align: left;
    color: #dcdde1;
}

.table td {
    padding: 15px;
    background: #3e5672;
    border-bottom: 5px solid #1e3a5f;
}

/* Actions */
.actions a {
    color: #fff;
    text-decoration: none;
    margin: 0 5px;
}

.actions span {
    margin: 0 5px;
    color: #ccc;
}

.actions a:hover {
    text-decoration: underline;
}
</style>

<script>
function confirmDelete() {
    return confirm("Are you sure you want to delete this course?");
}
</script>

</head>
<body>

<div class="card">

    <!--  Back Button -->
    <a href="dashboard.php" class="back">← Back</a>

    <h2>View Courses</h2>

    <table class="table">
        <thead>
            <tr>
                <th>Course Name</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php while($c=$data->fetch()){ ?>
            <tr>
                <td><?= htmlspecialchars($c['name'] ?? '') ?></td>
                <td class="actions">
                    
                    <!--  EDIT BUTTON CONNECTED -->
                    <a href="edit_course.php?id=<?= $c['id'] ?>">Edit</a>
                    
                    <span>|</span>

                    <!--  DELETE BUTTON -->
                    <a href="delete.php?id=<?= $c['id'] ?>&type=course"
                       onclick="return confirmDelete()">
                       Delete
                    </a>

                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

</div>

</body>
</html>
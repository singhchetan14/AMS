<?php
include("../../config/db.php");

// FETCH ASSIGNED COURSES WITH JOIN
$data = $conn->query("
    SELECT ca.id, u.name AS teacher_name, c.course_name
    FROM course_assignments ca
    JOIN users u ON ca.teacher_id = u.id
    JOIN courses c ON ca.course_id = c.id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Assigned Courses</title>
<!-- ✅ ADDED CSS LINK -->
<link rel="stylesheet" href="../../assets/css/style.css">

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
    width: 900px;
    padding: 40px;
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
    margin-bottom: 20px;
}

/* Title */
h2 {
    margin-bottom: 25px;
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
    return confirm("Are you sure you want to delete this assignment?");
}
</script>

</head>
<body>

<div class="card">

    <!-- Back Button -->
    <a href="dashboard.php" class="back">← Back</a>

    <h2>View Assigned Courses</h2>

    <table class="table">
        <thead>
            <tr>
                <th>Teacher's Name</th>
                <th>Assigned Course</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php while($row = $data->fetch()){ ?>
            <tr>
                <td><?= $row['teacher_name'] ?></td>
                <td><?= $row['course_name'] ?></td>
                <td class="actions">
                    
                    <!--  EDIT BUTTON FIXED -->
                    <a href="edit_assigned_courses.php?id=<?= $row['id'] ?>">Edit</a>
                    
                    <span>|</span>
                    
                    <!-- DELETE -->
                    <a href="delete.php?id=<?= $row['id'] ?>&type=assignment"
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
<?php
include("../../config/db.php");

$data = $conn->query("SELECT * FROM users WHERE role='teacher'");
?>

<!DOCTYPE html>
<html>
<head>
<title>View Teachers</title>
<!--  CSS LINK -->
<link rel="stylesheet" href="../../assets/css/style.css">

<style>
body {
    margin: 0;
    font-family: Arial;
    background: #0b1f3a;
    color: white;
}

.container {
    width: 70%;
    margin: 80px auto;
    background: #1e3a5f;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.4);
}

.back {
    color: #ccc;
    text-decoration: none;
    display: inline-block;
    margin-bottom: 20px;
}

.back:hover {
    color: white;
}

h2 {
    margin-bottom: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #3c5470;
    padding: 15px;
    text-align: left;
    color: #ddd;
}

td {
    background: #4b627e;
    padding: 15px;
}

.action a {
    color: #fff;
    text-decoration: none;
    margin-right: 10px;
}

.action a:hover {
    text-decoration: underline;
}
</style>

</head>
<body>

<div class="container">

    <a href="../dashboard/dashboard.php" class="back">⬅ Back</a>

    <h2>View Teachers</h2>

    <table>
        <tr>
            <th>Teacher Name</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>

        <?php while($t = $data->fetch()){ ?>
        <tr>
            <td><?= $t['name'] ?></td>
            <td><?= $t['email'] ?></td>
            <td class="action">

                <a href="edit_teacher.php?id=<?= $t['id'] ?>">Edit</a> |

                <!-- FIXED DELETE LINK -->
                <a href="delete.php?id=<?= $t['id'] ?>&type=teacher"
                   onclick="return confirm('Are you sure you want to delete this teacher?')">
                    Delete
                </a>

            </td>
        </tr>
        <?php } ?>

    </table>

</div>

</body>
</html>
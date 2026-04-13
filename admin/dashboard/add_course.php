<?php
include("../../config/db.php");

$success = "";

if(isset($_POST['add'])){
    $conn->prepare("INSERT INTO courses(course_name) VALUES(?)")
    ->execute([$_POST['course']]);

    $success = "Course successfully added";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Course</title>

<!--   CSS LINK -->
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

/* Card container */
.card {
    width: 600px;
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

/* Label */
label {
    display: block;
    margin-bottom: 10px;
    color: #dfe6e9;
}

/* Input */
input {
    width: 100%;
    padding: 12px;
    border-radius: 25px;
    border: none;
    outline: none;
    background: #aab7c4;
    margin-bottom: 20px;
}

/* Button */
button {
    display: block;
    margin: 0 auto;
    padding: 10px 25px;
    border-radius: 25px;
    border: 2px solid #fff;
    background: transparent;
    color: #fff;
    cursor: pointer;
    transition: 0.3s;
}

button:hover {
    background: #fff;
    color: #1e3a5f;
}

/* Success Message */
.success {
    background: #8ab69b;
    color: #fff;
    padding: 10px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
}
</style>

</head>
<body>

<div class="card">
    <a href="dashboard.php" class="back">← Back</a>

    <h2>Add Course</h2>

    <!--  Success Message -->
    <?php if($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Course Name</label>
        <input type="text" name="course" required>

        <button name="add">Add Course</button>
    </form>
</div>

</body>
</html>
<?php
session_start();
include("../../config/db.php");

if(!isset($_SESSION['admin'])){
    header("Location: ../auth/login.php");
    exit();
}

// Get ID from URL
$id = $_GET['id'] ?? 0;

// Fetch existing teacher data
$stmt = $conn->prepare("SELECT * FROM users WHERE id=? AND role='teacher'");
$stmt->execute([$id]);
$teacher = $stmt->fetch();

if(!$teacher){
    die("Teacher not found");
}

$message = "";

// Update logic
if(isset($_POST['update'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $message = "Invalid email format!";
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
        $stmt->execute([$name, $email, $id]);

        $message = "Teacher updated successfully!";
        
        // Refresh data
        $stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$id]);
        $teacher = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Teacher</title>

<!-- ✅ ADDED CSS LINK -->
<link rel="stylesheet" href="../../assets/css/admin_style.css">


<style>
body {
    margin: 0;
    font-family: Arial;
    background: #0b1f3a;
    color: white;
}

/* CONTAINER */
.container {
    width: 50%;
    margin: 80px auto;
    background: #1e3a5f;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}

/* BACK BUTTON */
.back {
    color: #ccc;
    text-decoration: none;
    display: inline-block;
    margin-bottom: 20px;
}

.back:hover {
    color: white;
}

/* TITLE */
h2 {
    margin-bottom: 25px;
}

/* INPUTS */
input {
    width: 100%;
    padding: 12px;
    border-radius: 5px;
    border: 1px solid #ccc;
    margin-bottom: 20px;
    background: transparent;
    color: white;
}

/* BUTTON */
button {
    padding: 10px 25px;
    border-radius: 20px;
    border: none;
    background: #2d6cdf;
    color: white;
    cursor: pointer;
    float: right;
}

button:hover {
    background: #1e4fbf;
}

/* MESSAGE */
.message {
    margin-bottom: 15px;
}
</style>

</head>
<body>

<div class="container">

    <!-- Back Button -->
    <a href="../dashboard/dashboard.php" class="back">⬅ Back</a>

    <h2>Edit Teacher</h2>

    <?php if($message){ echo "<p class='message'>$message</p>"; } ?>

    <form method="POST">

        <label>Teacher’s Name</label>
        <input name="name" value="<?= $teacher['name'] ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= $teacher['email'] ?>" required>

        <button name="update">Save Changes</button>

    </form>

</div>

</body>
</html>
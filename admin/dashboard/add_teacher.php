<?php
session_start();
include("../../config/db.php");

if(!isset($_SESSION['admin'])){
    header("Location: ../auth/login.php");
}

$message = "";

if(isset($_POST['add'])){

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Email validation
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $message = "Invalid email format!";
    }
    // Password validation
    elseif(strlen($password) < 6){
        $message = "Password must be at least 6 characters!";
    }
    else{
        $pass = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            INSERT INTO users(name,email,password,role)
            VALUES(?,?,?,'teacher')
        ");

        $stmt->execute([$name,$email,$pass]);

        $message = "Teacher Added Successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Teacher</title>

<!-- CSS LINK -->
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
    width: 40%;
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
    border-radius: 20px;
    border: none;
    margin-bottom: 20px;
    background: #7f8fa6;
    color: black;
}

/* BUTTON */
button {
    padding: 10px 25px;
    border-radius: 20px;
    border: 1px solid white;
    background: transparent;
    color: white;
    cursor: pointer;
}

button:hover {
    background: white;
    color: #0b1f3a;
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

    <h2>Add Teacher</h2>

    <?php if($message){ echo "<p class='message'>$message</p>"; } ?>

    <form method="POST">

        <label>Full Name</label>
        <input name="name" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button name="add">Add Teacher</button>

    </form>

</div>

</body>
</html>
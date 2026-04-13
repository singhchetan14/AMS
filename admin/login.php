<?php
session_start();
include("../config/db.php");
$error = "";
if(isset($_POST['login'])){
$email = trim($_POST['email']);
$password = trim($_POST['password']);
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
$error = "Invalid email format!";
    }
elseif(strlen($password) < 6){
$error = "Password must be at least 6 characters!";
    }
else{
$stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND role='admin'");
$stmt->execute([$email]);
$admin = $stmt->fetch();
if($admin && password_verify($password, $admin['password'])){
$_SESSION['admin']=$admin;
header("Location: dashboard/dashboard.php");
exit();
        } else {
$error = "Invalid email or password!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <!--  CSS -->
    <link rel="stylesheet" href="../assets/css/admin_style.css">
    
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #0d1b2e;
            font-family: sans-serif;
        }
        .card {
            background-color: #2d3e52;
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 460px;
        }
        h2 { color: #fff; text-align: center; margin-bottom: 8px; }
        .subtitle { color: #a8b8c8; font-size: 0.85rem; text-align: center; margin-bottom: 24px; }
        label { color: #fff; display: block; margin-bottom: 6px; }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px 18px;
            border: none;
            border-radius: 50px;
            background-color: #e8eaed;
            font-size: 0.95rem;
            outline: none;
        }
        .error { color: #ff8a8a; text-align: center; margin-bottom: 12px; }
        button[name="login"] {
            display: block;
            margin: 20px auto 0;
            padding: 12px 52px;
            background-color: #dde2e8;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
        }
        button[name="login"]:hover { background-color: #fff; }
    </style>
</head>
<body>
<div class="card">
    <form method="POST">
        <h2>Admin Login</h2>
        <p class="subtitle">Enter your email and password to access your account</p>
        <?php if($error){ echo "<p class='error'>$error</p>"; } ?>
        <label>Email Address</label>
        <input type="email" name="email" placeholder="Enter Email" required><br><br>
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter Password" required><br><br>
        <button name="login">Login</button>
    </form>
</div>
</body>
</html>
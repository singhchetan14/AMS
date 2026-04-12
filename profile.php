<?php session_start(); $admin=$_SESSION['admin']; ?>

<!DOCTYPE html>
<html>
<head>
<title>Profile</title>

<!--  CSS LINK -->
<link rel="stylesheet" href="../../assets/css/style.css">

<style>
/* ===== RESET ===== */
body {
    margin: 0;
    background: linear-gradient(to bottom right, #071a2f, #02101f);
    font-family: Arial;
}

/* ===== CENTER MAIN ===== */
.main {
    margin-left: 0 !important;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* ===== PROFILE CARD ===== */
.profile-card {
    background: #223c5a;
    padding: 40px;
    border-radius: 25px;
    width: 500px;
    color: #fff;
    border: 2px solid #000;
    box-shadow: 0 20px 40px rgba(0,0,0,0.6);
}

/* TITLE */
.profile-card h2 {
    text-align: center;
    margin-bottom: 20px;
}

/* ===== PROFILE IMAGE ===== */
.profile-img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    margin: 20px auto;
    display: block;
    border: 3px solid #000;
    object-fit: cover;
}

/* INPUTS */
.profile-card input {
    width: 100%;
    padding: 12px;
    margin: 10px 0 20px;
    border-radius: 20px;
    border: none;
    background: #9aa3ad;
}

/* LOGOUT */
.logout-btn {
    background: #2b6cb0;
    border: none;
    padding: 12px 25px;
    border-radius: 25px;
    color: #fff;
    cursor: pointer;
    display: block;
    margin: 10px auto;
}

.logout-btn:hover {
    background: #1e4e8c;
}

/* MOBILE */
@media (max-width: 480px) {
    .profile-card {
        width: 90%;
    }
}
</style>

</head>

<body>

<div class="main">

    <div class="profile-card">
        <h2>Profile Settings</h2>

        <!-- ✅ DEFAULT PROFILE IMAGE -->
        <img src="https://three.psbdigital.ca/wp-content/uploads/2024/03/6515859.webp?6bfec1&6bfec1" class="profile-img">

        <!-- Name -->
        <label>Full Name</label>
        <input type="text" value="<?= $admin['name'] ?>" readonly>

        <!-- Email -->
        <label>Email</label>
        <input type="text" value="<?= $admin['email'] ?>" readonly>

        <!-- Logout -->
        <a href="../../admin/logout.php">
            <button class="logout-btn">Logout</button>
        </a>
    </div>

</div>

</body>
</html>
<?php
session_start();
if(!isset($_SESSION['admin'])){ header("Location: ../login.php"); exit; }

include("../../config/db.php");

$teacherCount = $conn->query("SELECT COUNT(*) FROM users WHERE role='teacher'")->fetchColumn();
$courseCount = $conn->query("SELECT COUNT(*) FROM courses")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>

<!--  CSS PATH -->
<link rel="stylesheet" href="../../assets/css/admin_style.css">

<!-- JS -->
<script src="../../assets/js/script.js"></script>

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: Arial, sans-serif; background: #060d1f; color: white; }

.sidebar { width: 250px; height: 100vh; background: #0d1f3c; position: fixed; top: 0; left: 0; padding: 20px 0; transition: transform 0.3s; }
.sidebar.hidden { transform: translateX(-250px); }

.sidebar h2 {
    font-size: 20px;
    font-weight: bold;
    color: white;
    line-height: 1.4;
    padding: 10px 20px 25px;
    margin-bottom: 10px;
}

.sidebar a {
    display: block;
    color: #8ba3c7;
    text-decoration: none;
    padding: 10px 20px;
    font-size: 14px;
}

.sidebar a:hover,
.sidebar a.active {
    background: rgba(59,130,246,0.15);
    color: #3b82f6;
}

.dropdown-btn {
    display: block;
    width: 100%;
    color: #8ba3c7;
    background: none;
    border: none;
    padding: 10px 20px;
    font-size: 14px;
    cursor: pointer;
    text-align: left;
}

.dropdown-btn:hover {
    background: rgba(59,130,246,0.15);
    color: #3b82f6;
}

.submenu {
    display: none;
    padding-left: 20px;
}

.submenu.open {
    display: block;
}

.submenu a {
    font-size: 13px;
    padding: 6px 20px;
}

.topbar {
    position: fixed;
    top: 0;
    left: 10px;
    right: 0;
    height: 110px;
    background: #060D1F;
    border-bottom: 1px solid #1a2e55;
    display: flex;
    align-items: center;
    padding: 0 25px;
    gap: 15px;
    z-index: 99;
    transition: left 0.3s;
}

.topbar.full { left: 0; }

.topbar-text h3 { font-size: 18px; color: white; }
.topbar-text p { font-size: 13px; color: #8ba3c7; margin-top: 4px; }

.profile-icon {
    margin-left: auto;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 2px solid white;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 22px;
    text-decoration: none;
}

.hamburger {
    background: none;
    border: none;
    color: white;
    font-size: 22px;
    cursor: pointer;
}

.main {
    margin-left: 250px;
    margin-top: 110px;
    padding: 30px;
    transition: margin-left 0.3s;
}

.main.full { margin-left: 0; }

.card-row {
    display: flex;
    gap: 20px;
    margin-top: 10px;
}

.card {
    background: #122040;
    border: 1px solid rgba(59,130,246,0.3);
    border-radius: 12px;
    padding: 20px;
    width: 220px;
}

.card h3 {
    font-size: 12px;
    color: #8ba3c7;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
}

.card hr {
    border-color: #1a2e55;
    margin-bottom: 15px;
}

.card p {
    font-size: 36px;
    font-weight: bold;
    color: white;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">

    <h2>Academic<br>Management<br>System</h2>

    <a href="dashboard.php" class="active">Dashboard</a>

    <!-- TEACHERS -->
    <button class="dropdown-btn" onclick="toggleDropdown('teachers-sub')">
        Manage Teachers ▼
    </button>
    <div class="submenu" id="teachers-sub">
        <a href="add_teacher.php">Add Teacher</a>
        <a href="view_teachers.php">View Teachers</a>
    </div>

    <!-- COURSES -->
    <button class="dropdown-btn" onclick="toggleDropdown('courses-sub')">
        Manage Courses ▼
    </button>
    <div class="submenu" id="courses-sub">
        <a href="add_course.php">Add Course</a>
        <a href="view_courses.php">View Courses</a>
    </div>

    <!-- ASSIGN -->
    <button class="dropdown-btn" onclick="toggleDropdown('assign-sub')">
        Assign Courses ▼
    </button>
    <div class="submenu" id="assign-sub">
        <a href="assign_course.php">Assign Course</a>
        <a href="view_assigned_courses.php">View Assigned Courses</a>
    </div>

    <a href="profile.php">Profile</a>

</div>

<!-- TOPBAR -->
<div class="topbar" id="topbar">
    <button class="hamburger" onclick="toggleMenu()">☰</button>

    <div class="topbar-text">
        <h3>Hello, Admin</h3>
        <p>What would you like to do today?</p>
    </div>

    <a href="profile.php" class="profile-icon">👤</a>
</div>

<!-- MAIN -->
<div class="main" id="main">

    <h2>Welcome Admin</h2>

    <div class="card-row">
        <div class="card">
            <h3>Total Teachers</h3>
            <hr>
            <p><?php echo $teacherCount; ?></p>
        </div>

        <div class="card">
            <h3>Total Courses</h3>
            <hr>
            <p><?php echo $courseCount; ?></p>
        </div>
    </div>

</div>

<script>
let sidebarOpen = true;

function toggleMenu() {
    sidebarOpen = !sidebarOpen;
    document.getElementById('sidebar').classList.toggle('hidden', !sidebarOpen);
    document.getElementById('topbar').classList.toggle('full', !sidebarOpen);
    document.getElementById('main').classList.toggle('full', !sidebarOpen);
}

function toggleDropdown(id) {
    document.getElementById(id).classList.toggle('open');
}
</script>

</body>
</html>
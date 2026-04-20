<?php
require "db.php";

$student_id = 1;

$stmt = mysqli_prepare($conn, "SELECT name FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result       = mysqli_stmt_get_result($stmt);
$student      = mysqli_fetch_assoc($result);
$student_name = explode(" ", $student["name"])[0];

$stmt2  = mysqli_prepare($conn, "SELECT COUNT(*) as total, AVG(score) as avg FROM grades WHERE student_id = ?");
mysqli_stmt_bind_param($stmt2, "i", $student_id);
mysqli_stmt_execute($stmt2);
$result2      = mysqli_stmt_get_result($stmt2);
$grade_data   = mysqli_fetch_assoc($result2);
$grades_count = $grade_data["total"];
$average      = number_format($grade_data["avg"], 1);

$stmt3 = mysqli_prepare($conn, "SELECT c.name FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.student_id = ?");
mysqli_stmt_bind_param($stmt3, "i", $student_id);
mysqli_stmt_execute($stmt3);
$courses_result = mysqli_stmt_get_result($stmt3);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="student.css">
</head>
<body>

<div id="sidebar">
    <h2>Academic Management System</h2>

    <a href="dashboard.php" class="active">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Dashboard
    </a>
    <a href="grades.php">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        My Grades
    </a>
    <a href="courses.php">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
        My Courses
    </a>
    <a href="materials.php">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
        Course Materials
    </a>
    <a href="profile.php">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Profile
    </a>
</div>

<div id="main">

    <div id="topbar">
        <div>
            <h2>Hello, <?php echo htmlspecialchars($student_name); ?></h2>
            <p>What would you like to do today?</p>
        </div>
        <div id="avatar">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
    </div>

    <div id="content">

        <div id="boxes">
            <div class="box">
                <p>Grades Recorded</p>
                <h2><?php echo $grades_count; ?></h2>
            </div>
            <div class="box">
                <p>Average Grade</p>
                <h2><?php echo $average; ?>%</h2>
            </div>
        </div>

        <div id="course-list">
            <p>My Courses</p>
            <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
            <div class="row">
                <span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    <?php echo htmlspecialchars($course["name"]); ?>
                </span>
                <button onclick="location.href='materials.php?course=<?php echo urlencode($course["name"]); ?>'">View Materials ></button>
            </div>
            <?php endwhile; ?>
        </div>

    </div>

</div>

<script>
// AJAX navigation for course materials
document.addEventListener("DOMContentLoaded", function() {
    const courseButtons = document.querySelectorAll("button");
    
    courseButtons.forEach(button => {
        if (button.textContent.includes("View Materials")) {
            button.addEventListener("click", function(e) {
                const url = this.getAttribute("onclick").match(/'([^']+)'/)[1];
                window.location.href = url;
            });
        }
    });
});
</script>

</body>
</html>

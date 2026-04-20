<?php
require "db.php";

$student_id = 1;

// Get student name
$stmt_name = mysqli_prepare($conn, "SELECT name FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt_name, "i", $student_id);
mysqli_stmt_execute($stmt_name);
$result_name = mysqli_stmt_get_result($stmt_name);
$user_data = mysqli_fetch_assoc($result_name);
$student_name = ($user_data) ? explode(" ", $user_data["name"])[0] : "User";

$stmt = mysqli_prepare($conn, "SELECT c.name, c.code, c.description FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.student_id = ?");
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result  = mysqli_stmt_get_result($stmt);
$courses = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Courses</title>
    <link rel="stylesheet" href="student.css">
</head>
<body>

<div id="sidebar">
    <h2>Academic Management System</h2>

    <a href="dashboard.php">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Dashboard
    </a>
    <a href="grades.php">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        My Grades
    </a>
    <a href="courses.php" class="active">
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
        <div class="card">
            <h3>My Courses</h3>

            <?php if (count($courses) == 0): ?>
                <p id="no-courses">No courses found.</p>
            <?php else: ?>
                <?php foreach ($courses as $course): ?>
                <div class="course-card">
                    <div class="course-card-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    </div>
                    <div class="course-card-info">
                        <p class="course-card-name"><?php echo htmlspecialchars($course["name"]); ?></p>
                        <p class="course-card-code"><?php echo htmlspecialchars($course["code"]); ?></p>
                        <p class="course-card-desc"><?php echo htmlspecialchars($course["description"]); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>

</div>

<script>
// AJAX course filtering and search
document.addEventListener("DOMContentLoaded", function() {
    const content = document.getElementById("content");
    
    if (content && content.querySelector(".card")) {
        // Create search bar for courses
        const card = content.querySelector(".card");
        const searchDiv = document.createElement("div");
        searchDiv.style.marginBottom = "15px";
        
        const searchInput = document.createElement("input");
        searchInput.type = "text";
        searchInput.placeholder = "Search courses...";
        searchInput.style.width = "100%";
        searchInput.style.padding = "10px";
        searchInput.style.borderRadius = "5px";
        searchInput.style.border = "1px solid #ddd";
        
        searchDiv.appendChild(searchInput);
        card.insertBefore(searchDiv, card.querySelector(".course-card") || card.querySelector("p"));
        
        // Search functionality
        searchInput.addEventListener("keyup", function(e) {
            const query = this.value.toLowerCase();
            const courses = content.querySelectorAll(".course-card");
            
            courses.forEach(course => {
                const text = course.textContent.toLowerCase();
                course.style.display = text.includes(query) ? "block" : "none";
            });
        });
    }
});
</script>

</body>
</html>

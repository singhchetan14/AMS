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

$course_name = isset($_GET["course"]) ? trim($_GET["course"]) : "";

if ($course_name != "") {
    $stmt = mysqli_prepare($conn, "SELECT m.file_name, c.name as course_name, m.teacher, m.upload_date FROM materials m JOIN courses c ON m.course_id = c.id WHERE c.name = ?");
    mysqli_stmt_bind_param($stmt, "s", $course_name);
} else {
    $stmt = mysqli_prepare($conn, "SELECT m.file_name, c.name as course_name, m.teacher, m.upload_date FROM materials m JOIN courses c ON m.course_id = c.id");
}

mysqli_stmt_execute($stmt);
$result    = mysqli_stmt_get_result($stmt);
$materials = mysqli_fetch_all($result, MYSQLI_ASSOC);

$page_title = ($course_name != "") ? $course_name . " - Materials" : "All Course Materials";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Course Materials</title>
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
    <a href="courses.php">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
        My Courses
    </a>
    <a href="materials.php" class="active">
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
            <div id="materials-header">
                <button id="back-btn" onclick="location.href='dashboard.php'">&#8592; Back</button>
                <h3><?php echo htmlspecialchars($page_title); ?></h3>
            </div>

            <?php if (count($materials) == 0): ?>
                <p id="no-materials">No materials available yet.</p>
            <?php else: ?>
                <?php foreach ($materials as $item): ?>
                <div class="material-card">
                    <div class="material-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                    </div>
                    <div class="material-info">
                        <p class="material-filename"><?php echo htmlspecialchars($item["file_name"]); ?></p>
                        <p class="material-meta">Course: <?php echo htmlspecialchars($item["course_name"]); ?></p>
                        <p class="material-meta">Teacher: <?php echo htmlspecialchars($item["teacher"]); ?></p>
                        <p class="material-meta">Date: <?php echo $item["upload_date"]; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>

</div>

<script>
// AJAX-enhanced back button and course navigation
document.addEventListener("DOMContentLoaded", function() {
    const backBtn = document.getElementById("back-btn");
    if (backBtn) {
        backBtn.addEventListener("click", function(e) {
            e.preventDefault();
            window.location.href = "dashboard.php";
        });
    }
});

// Load materials dynamically if needed
function loadMaterials(courseName) {
    const url = courseName ? `materials.php?course=${encodeURIComponent(courseName)}` : "materials.php";
    
    fetch(url, {
        method: "GET",
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }
    })
    .then(response => response.text())
    .then(data => {
        const materialsContent = document.getElementById("content");
        const parser = new DOMParser();
        const newDoc = parser.parseFromString(data, "text/html");
        const newContent = newDoc.getElementById("content");
        if (newContent) {
            materialsContent.innerHTML = newContent.innerHTML;
            document.addEventListener("DOMContentLoaded", arguments.callee);
        }
    })
    .catch(error => console.error("Error loading materials:", error));
}
</script>

</body>
</html>

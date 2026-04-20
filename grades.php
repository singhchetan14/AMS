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

$stmt   = mysqli_prepare($conn, "SELECT course, score FROM grades WHERE student_id = ?");
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

function getLetterGrade($score) {
    if ($score >= 90) return "A+";
    elseif ($score >= 85) return "A";
    elseif ($score >= 80) return "A-";
    elseif ($score >= 75) return "B+";
    elseif ($score >= 70) return "B";
    elseif ($score >= 65) return "B-";
    elseif ($score >= 60) return "C+";
    elseif ($score >= 55) return "C";
    elseif ($score >= 50) return "C-";
    else return "F";
}

function getStatus($score) {
    if ($score >= 50) return "Pass";
    else return "Fail";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Grades</title>
    <link rel="stylesheet" href="student.css">
</head>
<body>

<div id="sidebar">
    <h2>Academic Management System</h2>

    <a href="dashboard.php">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Dashboard
    </a>
    <a href="grades.php" class="active">
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
        <div class="card">
            <h3>My Grades</h3>
            <table>
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Score</th>
                        <th>Grade</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)):
                        $grade  = getLetterGrade($row["score"]);
                        $status = getStatus($row["score"]);
                        $color  = ($status == "Fail") ? "color:#e05c5c;" : "";
                    ?>
                    <tr style="<?php echo $color; ?>">
                        <td><?php echo htmlspecialchars($row["course"]); ?></td>
                        <td><?php echo $row["score"]; ?></td>
                        <td><?php echo $grade; ?></td>
                        <td><?php echo $status; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
// AJAX enhanced grade sorting
document.addEventListener("DOMContentLoaded", function() {
    const table = document.querySelector("table");
    
    if (table) {
        const headers = table.querySelectorAll("thead th");
        
        headers.forEach((header, index) => {
            header.style.cursor = "pointer";
            header.addEventListener("click", function() {
                const tbody = table.querySelector("tbody");
                const rows = Array.from(tbody.querySelectorAll("tr"));
                
                rows.sort((a, b) => {
                    const cellA = a.querySelectorAll("td")[index]?.textContent || "";
                    const cellB = b.querySelectorAll("td")[index]?.textContent || "";
                    
                    // Try numeric sort if both are numbers
                    const numA = parseFloat(cellA);
                    const numB = parseFloat(cellB);
                    
                    if (!isNaN(numA) && !isNaN(numB)) {
                        return numA - numB;
                    }
                    
                    return cellA.localeCompare(cellB);
                });
                
                tbody.innerHTML = "";
                rows.forEach(row => tbody.appendChild(row));
            });
        });
    }
});
</script>

</body>
</html>

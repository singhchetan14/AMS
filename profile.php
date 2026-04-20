<?php
require "db.php";

$student_id = 1;
$message    = "";

$stmt   = mysqli_prepare($conn, "SELECT name, email, photo FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result  = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
    $new_name  = trim($_POST["name"]);
    $new_email = trim($_POST["email"]);
    $new_pass  = trim($_POST["password"]);

    if ($new_pass != "") {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt2  = mysqli_prepare($conn, "UPDATE users SET name=?, email=?, password=? WHERE id=?");
        mysqli_stmt_bind_param($stmt2, "sssi", $new_name, $new_email, $hashed, $student_id);
    } else {
        $stmt2 = mysqli_prepare($conn, "UPDATE users SET name=?, email=? WHERE id=?");
        mysqli_stmt_bind_param($stmt2, "ssi", $new_name, $new_email, $student_id);
    }

    mysqli_stmt_execute($stmt2);

    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
        $photo_name = time() . "_" . basename($_FILES["photo"]["name"]);
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $upload_dir . $photo_name);
        $stmt3 = mysqli_prepare($conn, "UPDATE users SET photo=? WHERE id=?");
        mysqli_stmt_bind_param($stmt3, "si", $photo_name, $student_id);
        mysqli_stmt_execute($stmt3);
        $student["photo"] = $photo_name;
    }

    $message = "Profile updated successfully!";
    
    // Refetch student data from database
    $stmt_refresh = mysqli_prepare($conn, "SELECT name, email, photo FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt_refresh, "i", $student_id);
    mysqli_stmt_execute($stmt_refresh);
    $result_refresh = mysqli_stmt_get_result($stmt_refresh);
    $student = mysqli_fetch_assoc($result_refresh);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
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
    <a href="materials.php">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
        Course Materials
    </a>
    <a href="profile.php" class="active">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Profile
    </a>
</div>

<div id="main">

    <div id="topbar">
        <div>
            <h2>Hello, <?php echo htmlspecialchars(explode(" ", $student["name"])[0]); ?></h2>
            <p>What would you like to do today?</p>
        </div>
        <div id="avatar">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
    </div>

    <div id="content">
        <div id="profile-center">
            <div id="profile-card">

                <h3>Profile Settings</h3>

                <?php if ($message): ?>
                    <p id="success-message" style="color:#4a9eda; margin-bottom:10px;"><?php echo $message; ?></p>
                <?php endif; ?>

                <form method="POST" action="profile.php" enctype="multipart/form-data">

                    <div id="pic-area">
                        <?php if (!empty($student["photo"])): ?>
                            <div id="pic" style="background-image:url('uploads/<?php echo $student["photo"]; ?>'); background-size:cover; background-position:center;"></div>
                        <?php else: ?>
                            <div id="pic"></div>
                        <?php endif; ?>
                        <label id="change-photo-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                            Change Photo
                            <input type="file" name="photo" style="display:none;" onchange="this.form.submit()">
                        </label>
                    </div>

                    <div class="field">
                        <label>Full Name</label>
                        <input type="text" id="input-name" name="name" value="<?php echo htmlspecialchars($student["name"]); ?>">
                    </div>

                    <div class="field">
                        <label>Email</label>
                        <input type="text" id="input-email" name="email" value="<?php echo htmlspecialchars($student["email"]); ?>">
                    </div>

                    <div class="field">
                        <label>New Password</label>
                        <input type="password" name="password" placeholder="Leave empty to keep current">
                    </div>

                    <button id="update-btn" type="submit" name="update">Update</button>

                </form>

            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const updateForm = document.querySelector("form[method='POST'][enctype='multipart/form-data']");
    
    if (updateForm) {
        updateForm.addEventListener("submit", function(e) {
            e.preventDefault();
            
            const name = document.getElementById("input-name").value.trim();
            const email = document.getElementById("input-email").value.trim();
            
            if (!name || !email) {
                alert("Please fill in all required fields");
                return;
            }
            
            const formData = new FormData(this);
            
            fetch("profile.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // Reload page to reflect changes
                location.reload();
            })
            .catch(error => {
                console.error("Error:", error);
                this.submit();
            });
        });
    }
});

// Auto-hide success message and clear form fields after 3.5 seconds
window.addEventListener('load', function() {
    const messageElement = document.getElementById('success-message');
    if (messageElement) {
        setTimeout(function() {
            messageElement.style.transition = 'opacity 0.5s ease';
            messageElement.style.opacity = '0';
            setTimeout(function() {
                messageElement.style.display = 'none';
            }, 500);
        }, 3500);
    }
});
</script>

</body>
</html>

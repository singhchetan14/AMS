<?php
/**
 * upload-materials.php
 * 
 * Course materials upload page.
 * Shows course dropdown, file upload zone, and recent materials list.
 */

require_once 'includes/auth.php';
require_once 'config/db.php';

// ── Fetch teacher's courses ──────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT id, name 
    FROM courses 
    WHERE teacher_id = ? 
    ORDER BY name ASC
");
$stmt->execute([$_SESSION['teacher_id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Fetch recent materials ───────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT m.id, m.title, c.name as course_name, m.uploaded_at
    FROM materials m
    INNER JOIN courses c ON m.course_id = c.id
    WHERE m.teacher_id = ?
    ORDER BY m.uploaded_at DESC
    LIMIT 10
");
$stmt->execute([$_SESSION['teacher_id']]);
$materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success = isset($_GET['success']) ? 1 : 0;
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Upload Materials | Academic Management System</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body class="no-sidebar">
    <div class="layout" style="min-height: 100vh; background-color: #0d1b2a; display: flex; align-items: center; justify-content: center; padding: 20px;">
        <main class="main" style="width: 100%; max-width: 900px; padding: 0;">
            <div class="upload-card" style="background-color: #1b304c; border-radius: 12px; color: #fff; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3); overflow: hidden; padding: 40px;">
                
                <a href="dashboard.php" style="display: inline-flex; align-items: center; gap: 8px; color: #fff; text-decoration: none; margin-bottom: 26px; font-family: sans-serif; font-size: 14px;">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 8 8 12 12 16"></polyline>
                        <line x1="16" y1="12" x2="8" y2="12"></line>
                    </svg>
                    Back
                </a>

                <h2 class="section-title" style="margin-top: 0;">Upload Course Materials</h2>

                <?php if ($success): ?>
                    <div style="background: rgba(0, 255, 0, 0.1); border: 1px solid rgba(0, 255, 0, 0.3); padding: 12px; border-radius: 8px; margin-bottom: 20px; color: #90ee90;">
                        ✓ Material uploaded successfully!
                    </div>
                <?php elseif ($error): ?>
                    <div style="background: rgba(255, 0, 0, 0.1); border: 1px solid rgba(255, 0, 0, 0.3); padding: 12px; border-radius: 8px; margin-bottom: 20px; color: #ff6b6b;">
                        ✗ Error: <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="actions/do_upload_material.php" enctype="multipart/form-data">
                    
                    <div class="form-group custom-form-group">
                        <label for="course-select">Course</label>
                        <div class="select-wrapper">
                            <select id="course-select" name="course_id" class="custom-input" required>
                                <option value="" disabled selected>Select a course</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['id'] ?>">
                                        <?= htmlspecialchars($course['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group custom-form-group">
                        <label for="material-title">Title</label>
                        <input id="material-title" type="text" name="title" class="custom-input" placeholder="Material Title" required />
                    </div>

                    <div class="form-group custom-form-group">
                        <label for="file-input">File (PDF, DOCX, PPTX)</label>
                        <input id="file-input" type="file" name="file" class="custom-input" accept=".pdf,.docx,.pptx" required />
                    </div>

                    <button type="submit" style="background-color: #1967d2; color: #fff; border: none; border-radius: 20px; padding: 10px 24px; font-size: 14px; cursor: pointer; font-family: sans-serif;">
                        Upload Material
                    </button>
                </form>

                <div class="divider"></div>

                <h3 class="recent-title">Recent Materials</h3>
                <ul class="recent-list">
                    <?php if (empty($materials)): ?>
                        <li class="recent-item">No materials uploaded yet.</li>
                    <?php else: ?>
                        <?php foreach ($materials as $material): ?>
                            <li class="recent-item">
                                <?= htmlspecialchars($material['title']) ?> 
                                <span style="opacity: 0.7;">— <?= htmlspecialchars($material['course_name']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
</body>
</html>

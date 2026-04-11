<?php
/**
 * upload-grades.php
 * 
 * Grade upload page — shows group/course filters and student grade table.
 * Supports Edit/Delete buttons for individual grades.
 */

require_once 'includes/auth.php';
require_once 'config/db.php';

// ── Get unique groups ────────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT DISTINCT c.group_name 
    FROM courses c
    WHERE c.teacher_id = ?
    ORDER BY c.group_name ASC
");
$stmt->execute([$_SESSION['teacher_id']]);
$groups = $stmt->fetchAll(PDO::FETCH_COLUMN);

$selectedGroup = $_GET['group'] ?? '';
$selectedCourse = $_GET['course'] ?? '';

// ── Fetch courses for selected group ─────────────────────────────────
$courses = [];
if ($selectedGroup) {
    $stmt = $pdo->prepare("
        SELECT id, name 
        FROM courses 
        WHERE teacher_id = ? AND group_name = ?
        ORDER BY name ASC
    ");
    $stmt->execute([$_SESSION['teacher_id'], $selectedGroup]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ── Fetch grades for selected course ─────────────────────────────────
$grades = [];
$courseName = '';
if ($selectedCourse && $selectedGroup) {
    // Verify course belongs to teacher
    $stmt = $pdo->prepare("SELECT name FROM courses WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$selectedCourse, $_SESSION['teacher_id']]);
    $courseRow = $stmt->fetch();
    if ($courseRow) {
        $courseName = $courseRow['name'];
        
        // Fetch grades
        $stmt = $pdo->prepare("
            SELECT 
                g.id,
                s.student_no,
                s.full_name,
                g.score,
                g.status
            FROM grades g
            INNER JOIN students s ON g.student_id = s.id
            INNER JOIN courses c ON g.course_id = c.id
            WHERE g.course_id = ? AND c.group_name = ? AND g.teacher_id = ?
            ORDER BY s.student_no ASC
        ");
        $stmt->execute([$selectedCourse, $selectedGroup, $_SESSION['teacher_id']]);
        $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Upload Grades | Academic Management System</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body class="no-sidebar">
    <div class="layout" style="min-height: 100vh; background-color: #0d1b2a; display: flex; align-items: center; justify-content: center; padding: 20px;">
        <main class="main" style="width: 100%; max-width: 900px; padding: 0;">
            <div class="upload-card" style="background-color: #1b304c; border-radius: 12px; color: #fff; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3); overflow: hidden;">
                
                <div style="padding: 40px;">
                    <a href="dashboard.php" style="display: inline-flex; align-items: center; gap: 8px; color: #fff; text-decoration: none; margin-bottom: 30px; font-family: sans-serif; font-size: 14px;">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 8 8 12 12 16"></polyline>
                            <line x1="16" y1="12" x2="8" y2="12"></line>
                        </svg>
                        Back
                    </a>
                    <h2 style="font-size: 18px; font-weight: normal; margin-bottom: 30px; font-family: sans-serif; color: #fff;">Upload Grades</h2>

                    <!-- ── Filters ────────────────────────────────────── -->
                    <form method="GET" style="margin-bottom: 30px;">
                        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                            <div style="flex: 1;">
                                <label style="display: block; margin-bottom: 8px; font-size: 14px; font-family: sans-serif; color: #e2e8f0;">Group</label>
                                <select name="group" onchange="this.form.submit()" style="width: 100%; box-sizing: border-box; background-color: transparent; border: 1px solid #7b889b; padding: 12px 16px; color: #7b889b; outline: none; font-family: sans-serif; border-radius: 4px;">
                                    <option value="">Select Group</option>
                                    <?php foreach ($groups as $g): ?>
                                        <option value="<?= htmlspecialchars($g) ?>" <?= ($selectedGroup === $g) ? 'selected' : '' ?>>
                                            Group <?= htmlspecialchars($g) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="flex: 1;">
                                <label style="display: block; margin-bottom: 8px; font-size: 14px; font-family: sans-serif; color: #e2e8f0;">Course</label>
                                <select name="course" onchange="this.form.submit()" style="width: 100%; box-sizing: border-box; background-color: transparent; border: 1px solid #7b889b; padding: 12px 16px; color: #7b889b; outline: none; font-family: sans-serif; border-radius: 4px;">
                                    <option value="">Select Course</option>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= ($selectedCourse == $c['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>

                <?php if ($selectedCourse && $courseName): ?>
                    <div style="border-top: 1px solid #7b889b; padding: 20px 40px 40px 40px; background-color: #1b304c;">
                        <div style="font-family: sans-serif; font-size: 16px; color: #fff; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                            Course <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg> <?= htmlspecialchars($courseName) ?>
                        </div>
                        
                        <div style="background-color: #3b526b; border-radius: 8px; padding: 10px 0; overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; font-family: sans-serif; color: #fff; text-align: left; font-size: 15px;">
                                <thead>
                                    <tr style="color: #92a2b8;">
                                        <th style="padding: 10px 30px; font-weight: normal; width: 10%;">S No.</th>
                                        <th style="padding: 10px 20px; font-weight: normal; width: 35%;">Student Name</th>
                                        <th style="padding: 10px 20px; font-weight: normal; text-align: center; width: 20%;">Score/100</th>
                                        <th style="padding: 10px 20px; font-weight: normal; width: 15%;">Status</th>
                                        <th style="padding: 10px 30px; font-weight: normal; text-align: right; width: 20%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($grades)): ?>
                                        <tr>
                                            <td colspan="5" style="padding: 20px; text-align: center; color: #92a2b8;">No grades found for this course.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($grades as $grade): ?>
                                            <tr>
                                                <td style="padding: 16px 30px;"><?= htmlspecialchars($grade['student_no']) ?></td>
                                                <td style="padding: 16px 20px;"><?= htmlspecialchars($grade['full_name']) ?></td>
                                                <td style="padding: 16px 20px; text-align: center;"><?= (int)$grade['score'] ?></td>
                                                <td style="padding: 16px 20px;">
                                                    <span style="padding: 4px 8px; border-radius: 4px; background: <?= ($grade['status'] === 'Pass') ? 'rgba(0, 255, 0, 0.1)' : 'rgba(255, 0, 0, 0.1)' ?>; color: <?= ($grade['status'] === 'Pass') ? '#90ee90' : '#ff6b6b' ?>;">
                                                        <?= htmlspecialchars($grade['status']) ?>
                                                    </span>
                                                </td>
                                                <td style="padding: 16px 30px; text-align: right;">
                                                    <a href="edit-grade.php?id=<?= $grade['id'] ?>" style="color: #fff; text-decoration: none; margin-right: 16px;">Edit</a>
                                                    <a href="actions/do_delete_grade.php?id=<?= $grade['id'] ?>" onclick="return confirm('Delete this grade?');" style="color: #ff6b6b; text-decoration: none;">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (!empty($grades)): ?>
                            <a href="edit-grade.php?course=<?= $selectedCourse ?>&group=<?= htmlspecialchars($selectedGroup) ?>" style="display: inline-block; margin-top: 20px; background-color: #1967d2; color: #fff; border: none; border-radius: 20px; padding: 10px 24px; font-size: 14px; cursor: pointer; font-family: sans-serif; text-decoration: none;">
                                Add New Grade
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
</body>
</html>

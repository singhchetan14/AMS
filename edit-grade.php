<?php
/**
 * edit-grade.php
 * 
 * Edit or add a grade for a student.
 * Supports URL parameters: ?id=GRADE_ID (for edit) or ?course=X&group=Y (for add)
 */

require_once 'includes/auth.php';
require_once 'config/db.php';

$gradeId = $_GET['id'] ?? null;
$courseId = $_GET['course'] ?? null;
$group = $_GET['group'] ?? null;

$grade = null;
$students = [];
$courseName = '';
$isEdit = false;

// ── Handle Edit Mode (existing grade) ─────────────────────────────────
if ($gradeId) {
    $gradeId = (int)$gradeId;
    
    $stmt = $pdo->prepare("
        SELECT 
            g.id, g.student_id, g.course_id, g.score, g.status,
            s.student_no, s.full_name,
            c.name as course_name
        FROM grades g
        INNER JOIN students s ON g.student_id = s.id
        INNER JOIN courses c ON g.course_id = c.id
        WHERE g.id = ? AND g.teacher_id = ?
    ");
    $stmt->execute([$gradeId, $_SESSION['teacher_id']]);
    $grade = $stmt->fetch();
    
    if (!$grade) {
        header('Location: upload-grades.php?error=Grade%20not%20found');
        exit;
    }
    
    $courseId = $grade['course_id'];
    $courseName = $grade['course_name'];
    $isEdit = true;
}

// ── Handle Add Mode (new grade) ───────────────────────────────────────
if ($courseId && !$isEdit) {
    $courseId = (int)$courseId;
    
    $stmt = $pdo->prepare("
        SELECT name FROM courses WHERE id = ? AND teacher_id = ?
    ");
    $stmt->execute([$courseId, $_SESSION['teacher_id']]);
    $courseRow = $stmt->fetch();
    
    if (!$courseRow) {
        header('Location: upload-grades.php?error=Course%20not%20found');
        exit;
    }
    
    $courseName = $courseRow['name'];
    
    // ── Fetch students for this group ────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT id, student_no, full_name 
        FROM students 
        WHERE group_name = ?
        ORDER BY student_no ASC
    ");
    $stmt->execute([$group]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ── Handle POST (save grade) ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = (int)($_POST['student_id'] ?? 0);
    $score = (int)($_POST['score'] ?? 0);
    
    // Validate score
    if ($score < 0 || $score > 100) {
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'upload-grades.php') . '?error=Invalid%20score');
        exit;
    }
    
    // Auto-calculate status
    $status = ($score >= 50) ? 'Pass' : 'Fail';
    
    if ($isEdit) {
        // Update existing grade
        $stmt = $pdo->prepare("
            UPDATE grades 
            SET score = ?, status = ?
            WHERE id = ? AND teacher_id = ?
        ");
        $stmt->execute([$score, $status, $gradeId, $_SESSION['teacher_id']]);
    } else {
        // Insert new grade (or update if already exists)
        $stmt = $pdo->prepare("
            INSERT INTO grades (student_id, course_id, score, status, teacher_id)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE score = ?, status = ?
        ");
        $stmt->execute([$studentId, $courseId, $score, $status, $_SESSION['teacher_id'], $score, $status]);
    }
    
    header('Location: upload-grades.php?group=' . ($isEdit ? $group : urlencode($group)) . '&course=' . $courseId . '&success=1');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $isEdit ? 'Edit' : 'Add' ?> Grade | Academic Management System</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body class="no-sidebar">
    <div class="layout" style="min-height: 100vh; background-color: #0d1b2a; display: flex; align-items: center; justify-content: center; padding: 20px;">
        <main class="main" style="width: 100%; max-width: 700px; padding: 0;">
            <div style="background-color: #1b304c; border-radius: 12px; padding: 40px; color: #fff;">
                
                <a href="upload-grades.php" style="display: inline-flex; align-items: center; gap: 8px; color: #fff; text-decoration: none; margin-bottom: 30px; font-family: sans-serif; font-size: 14px;">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 8 8 12 12 16"></polyline>
                        <line x1="16" y1="12" x2="8" y2="12"></line>
                    </svg>
                    Back
                </a>

                <h2 style="font-size: 24px; font-weight: 400; margin-bottom: 30px; font-family: sans-serif;">
                    <?= $isEdit ? 'Edit' : 'Add' ?> Grade — <?= htmlspecialchars($courseName) ?>
                </h2>

                <form method="POST">
                    <?php if (!$isEdit): ?>
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-size: 14px; color: #e2e8f0;">Student</label>
                            <select name="student_id" required style="width: 100%; box-sizing: border-box; background-color: #7b889b; border: 1px solid #0d1b2a; padding: 12px 16px; color: #fff; border-radius: 8px; outline: none;">
                                <option value="">Select Student</option>
                                <?php foreach ($students as $s): ?>
                                    <option value="<?= $s['id'] ?>">
                                        <?= htmlspecialchars($s['student_no']) ?> — <?= htmlspecialchars($s['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <div style="margin-bottom: 20px; padding: 12px; background: rgba(255, 255, 255, 0.05); border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.1);">
                            <strong><?= htmlspecialchars($grade['student_no']) ?> — <?= htmlspecialchars($grade['full_name']) ?></strong>
                            <input type="hidden" name="student_id" value="<?= $grade['student_id'] ?>" />
                        </div>
                    <?php endif; ?>

                    <div style="margin-bottom: 30px;">
                        <label style="display: block; margin-bottom: 8px; font-size: 14px; color: #e2e8f0;">Score (0-100)</label>
                        <input 
                            type="number" 
                            name="score" 
                            min="0" 
                            max="100" 
                            value="<?= $isEdit ? (int)$grade['score'] : '' ?>"
                            required
                            style="width: 100%; box-sizing: border-box; background-color: #7b889b; border: 1px solid #0d1b2a; padding: 12px 16px; color: #fff; border-radius: 8px; outline: none; font-size: 16px;"
                        />
                    </div>

                    <?php if ($isEdit): ?>
                        <div style="margin-bottom: 30px; padding: 12px; background: rgba(255, 255, 255, 0.05); border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.1);">
                            <label style="display: block; margin-bottom: 8px; font-size: 14px; color: #e2e8f0;">Status (Auto-calculated)</label>
                            <strong>
                                <span id="status-display" style="padding: 4px 8px; border-radius: 4px; background: <?= ($grade['status'] === 'Pass') ? 'rgba(0, 255, 0, 0.1)' : 'rgba(255, 0, 0, 0.1)' ?>; color: <?= ($grade['status'] === 'Pass') ? '#90ee90' : '#ff6b6b' ?>;">
                                    <?= htmlspecialchars($grade['status']) ?>
                                </span>
                            </strong>
                        </div>
                    <?php endif; ?>

                    <button type="submit" style="background-color: #1967d2; color: #fff; border: none; border-radius: 20px; padding: 12px 40px; font-size: 16px; cursor: pointer; font-family: sans-serif; width: 100%;">
                        Save Grade
                    </button>
                </form>

                <script>
                    // Auto-update status display as score changes
                    if (<?= $isEdit ? 'true' : 'false' ?>) {
                        const scoreInput = document.querySelector('input[name="score"]');
                        const statusDisplay = document.getElementById('status-display');
                        
                        scoreInput.addEventListener('change', function() {
                            const score = parseInt(this.value);
                            const status = score >= 50 ? 'Pass' : 'Fail';
                            statusDisplay.textContent = status;
                            statusDisplay.style.background = (status === 'Pass') ? 'rgba(0, 255, 0, 0.1)' : 'rgba(255, 0, 0, 0.1)';
                            statusDisplay.style.color = (status === 'Pass') ? '#90ee90' : '#ff6b6b';
                        });
                    }
                </script>
            </div>
        </main>
    </div>
</body>
</html>

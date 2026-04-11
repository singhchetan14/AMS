<?php
/**
 * dashboard.php
 * 
 * Teacher Dashboard — main overview page.
 * Shows: assigned course count, total students, today's schedule, recent materials.
 * 
 * Database tables used: courses, students, grades, materials
 */

require_once 'includes/auth.php';
require_once 'config/db.php';

// ── Fetch assigned course count ──────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT COUNT(*) as count 
    FROM courses 
    WHERE teacher_id = ?
");
$stmt->execute([$_SESSION['teacher_id']]);
$assignedCourses = $stmt->fetchColumn();

// ── Fetch total students (distinct) in teacher's courses ──────────────
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT s.id) as count
    FROM students s
    INNER JOIN grades g ON s.id = g.student_id
    INNER JOIN courses c ON g.course_id = c.id
    WHERE c.teacher_id = ?
");
$stmt->execute([$_SESSION['teacher_id']]);
$totalStudents = $stmt->fetchColumn();

// ── Fetch today's schedule ───────────────────────────────────────────
$today = date('l');  // e.g., "Monday"
$stmt = $pdo->prepare("
    SELECT schedule_time, name, group_name 
    FROM courses 
    WHERE teacher_id = ? AND schedule_day = ?
    ORDER BY schedule_time ASC
");
$stmt->execute([$_SESSION['teacher_id'], $today]);
$schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Fetch recent materials ───────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT m.title, c.name as course_name, m.uploaded_at
    FROM materials m
    INNER JOIN courses c ON m.course_id = c.id
    WHERE m.teacher_id = ?
    ORDER BY m.uploaded_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['teacher_id']]);
$recentMaterials = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard | Academic Management System</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <div class="layout">
        <?php require_once 'includes/sidebar.php'; ?>

        <main class="main">
            <?php require_once 'includes/header.php'; ?>

            <!-- ── Stats Cards ────────────────────────────────────── -->
            <section class="card-grid" aria-label="Dashboard Stats">
                <article class="card stat-card">
                    <h3>Assigned Courses</h3>
                    <div class="stat-value" id="assigned-courses-count">
                        <?= str_pad($assignedCourses, 2, '0', STR_PAD_LEFT) ?>
                    </div>
                </article>
                <article class="card stat-card">
                    <h3>Total Students</h3>
                    <div class="stat-value" id="total-students-count">
                        <?= $totalStudents ?>
                    </div>
                </article>
            </section>

            <!-- ── Today's Schedule ──────────────────────────────── -->
            <section class="schedule-card">
                <h2>Today's Schedule</h2>
                <ul id="schedule-list" class="schedule-list">
                    <?php if (empty($schedule)): ?>
                        <li class="schedule-item">No classes scheduled for today.</li>
                    <?php else: ?>
                        <?php foreach ($schedule as $item): ?>
                            <li class="schedule-item clickable">
                                <span class="time-badge"><?= htmlspecialchars($item['schedule_time']) ?></span>
                                <span class="schedule-course">
                                    <?= htmlspecialchars($item['name']) ?> (Group <?= htmlspecialchars($item['group_name']) ?>)
                                </span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </section>

            <!-- ── Recent Materials ───────────────────────────────── -->
            <section class="materials-section">
                <h2>Recent Materials</h2>
                <ul class="materials-list">
                    <?php if (empty($recentMaterials)): ?>
                        <li class="material-item">No materials uploaded yet.</li>
                    <?php else: ?>
                        <?php foreach ($recentMaterials as $material): ?>
                            <li class="material-item">
                                <strong><?= htmlspecialchars($material['title']) ?></strong>
                                <span class="material-course">
                                    <?= htmlspecialchars($material['course_name']) ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </section>
        </main>
    </div>

    <script src="script.js"></script>
</body>
</html>

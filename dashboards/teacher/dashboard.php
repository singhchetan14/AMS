<?php
/**
 * dashboard.php
 * 
 * Teacher Dashboard — main overview page.
 * Shows: assigned course count, total students, today's schedule, recent materials.
 * 
 * Database tables used: courses, students, grades, materials
 */

// Force no-cache headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sun, 01 Jan 2000 00:00:00 GMT");

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

// ── Fetch total students (real count from students table) ───────────────
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM students
");
$stmt->execute();
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
    SELECT m.id, m.title, c.name as course_name, m.uploaded_at
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
    <style>
        a[href="view-students.php"] .stat-card:hover {
            border-color: #1967d2 !important;
            box-shadow: 0 0 15px rgba(25, 103, 210, 0.4);
            transform: translateY(-2px);
        }

        .material-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .material-actions {
            display: flex;
            gap: 8px;
        }

        .btn-edit, .btn-delete {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .btn-edit {
            background: rgba(25, 103, 210, 0.15);
            color: #90b8ff;
            border-color: rgba(25, 103, 210, 0.3);
        }
        .btn-edit:hover { background: rgba(25, 103, 210, 0.3); }

        .btn-delete {
            background: rgba(255, 70, 70, 0.12);
            color: #ff8080;
            border-color: rgba(255, 70, 70, 0.28);
        }
        .btn-delete:hover { background: rgba(255, 70, 70, 0.25); }

        #toast {
            position: fixed;
            bottom: 28px;
            right: 28px;
            padding: 14px 22px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #fff;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.35s ease;
            pointer-events: none;
            z-index: 9999;
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
        }
        #toast.show { opacity: 1; transform: translateY(0); }
        #toast.t-success { background: #0f2e1a; border: 1px solid rgba(0,200,80,0.45); }
        #toast.t-error   { background: #2e0f0f; border: 1px solid rgba(255,80,80,0.45); }
    </style>
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
                <a href="view-students.php" style="text-decoration: none; cursor: pointer;">
                    <article class="card stat-card" style="transition: all 0.3s ease; border: 2px solid transparent;">
                        <h3 style="color: #e2e8f0;">Total Students</h3>
                        <div class="stat-value" id="total-students-count" style="color: #fff;">
                            <?= str_pad($totalStudents, 2, '0', STR_PAD_LEFT) ?>
                        </div>
                    </article>
                </a>
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
                                <span class="time-badge"><?= date('g:i A', strtotime($item['schedule_time'])) ?></span>
                                <span class="schedule-course">
                                    <?= htmlspecialchars($item['name']) ?> (Group <?= htmlspecialchars($item['group_name']) ?>)
                                </span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </section>

            <!-- ── Recent Materials ───────────────────────────────── -->
            <section class="schedule-card">
                <h2>Recent Materials</h2>
                <ul class="schedule-list">
                    <?php if (empty($recentMaterials)): ?>
                        <li class="schedule-item">No materials uploaded yet.</li>
                    <?php else: ?>
                        <?php foreach ($recentMaterials as $material): ?>
                            <li class="schedule-item material-item" id="material-<?= $material['id'] ?>">
                                <div class="schedule-info">
                                    <div class="schedule-course" id="material-title-<?= $material['id'] ?>">
                                        <?= htmlspecialchars($material['title']) ?>
                                    </div>
                                    <div class="schedule-class">
                                        <?= htmlspecialchars($material['course_name']) ?>
                                    </div>
                                </div>
                                <div class="material-actions">
                                    <button class="btn-edit" onclick="editMaterial(<?= $material['id'] ?>, '<?= htmlspecialchars(addslashes($material['title'])) ?>')">Edit</button>
                                    <button class="btn-delete" onclick="deleteMaterial(<?= $material['id'] ?>)">Delete</button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </section>
        </main>
    </div>

    <div id="toast"></div>

    <script>
        function showToast(message, type = 'success') {
            const t = document.getElementById('toast');
            t.textContent = message;
            t.className = 'show ' + (type === 'success' ? 't-success' : 't-error');
            setTimeout(() => t.classList.remove('show'), 3500);
        }

        function deleteMaterial(id) {
            if (!confirm("Are you sure you want to delete this material?")) return;
            
            fetch('actions/api_materials.php?action=delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Material deleted successfully');
                    const el = document.getElementById('material-' + id);
                    if (el) el.remove();
                } else {
                    showToast(data.error || 'Failed to delete material', 'error');
                }
            })
            .catch(() => showToast('Network error', 'error'));
        }

        function editMaterial(id, currentTitle) {
            const newTitle = prompt("Enter new title:", currentTitle);
            if (newTitle === null || newTitle.trim() === '' || newTitle.trim() === currentTitle) return;

            fetch('actions/api_materials.php?action=edit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, title: newTitle.trim() })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Material updated successfully');
                    const el = document.getElementById('material-title-' + id);
                    if (el) el.textContent = newTitle.trim();
                } else {
                    showToast(data.error || 'Failed to update material', 'error');
                }
            })
            .catch(() => showToast('Network error', 'error'));
        }
    </script>
    <script src="script.js"></script>
</body>
</html>

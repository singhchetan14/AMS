<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

// All courses in the system, with assigned teacher info
$myCourses = $pdo->query("
    SELECT c.id, c.name, c.group_name,
           u.full_name  AS teacher_name,
           u.department AS teacher_department
    FROM courses c
    LEFT JOIN users u ON u.id = c.teacher_id
    ORDER BY c.name ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Courses | Academic Management System</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="layout">
    <?php require __DIR__ . '/includes/sidebar.php'; ?>
    <main class="main">
      <?php require __DIR__ . '/includes/header.php'; ?>

      <div class="card-wrap">
        <a class="back-link" href="dashboard.php">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><polyline points="12 8 8 12 12 16"/><line x1="16" y1="12" x2="8" y2="12"/>
          </svg>
          Back
        </a>
        <h2>My Courses</h2>
        <div class="divider"></div>

        <?php if (empty($myCourses)): ?>
          <div class="empty-state">No courses available yet.</div>
        <?php else: ?>
          <div class="tile-grid">
            <?php foreach ($myCourses as $c): ?>
              <a class="tile material-card" href="course-materials.php?course=<?= (int)$c['id'] ?>" style="text-decoration:none;">
                <div class="tile__icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14H6a2 2 0 0 1-2-2z"/>
                    <path d="M8 7h8M8 11h8"/>
                  </svg>
                </div>
                <h3 class="tile__title"><?= htmlspecialchars($c['name']) ?></h3>
                <p class="tile__code">
                  <?= !empty($c['teacher_name'])
                        ? 'Teacher: ' . htmlspecialchars($c['teacher_name'])
                        : 'Teacher: not assigned' ?>
                </p>
                <p class="tile__desc">
                  <?= !empty($c['teacher_department'])
                        ? htmlspecialchars($c['teacher_department'])
                        : 'View course materials' ?>
                </p>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>

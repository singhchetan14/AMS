<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

$studentId    = $_SESSION['student_id']    ?? null;
$studentGroup = $_SESSION['student_group'] ?? null;

// Stat: grades recorded + average score
$gradesRecorded = 0;
$averageGrade   = null;
if ($studentId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS c, AVG(score) AS a FROM grades WHERE student_id = ?");
    $stmt->execute([$studentId]);
    $r = $stmt->fetch();
    $gradesRecorded = (int)($r['c'] ?? 0);
    $averageGrade   = $r['a'] !== null ? round((float)$r['a'], 1) : null;
}

// All courses in the system, with the assigned teacher's name (if any)
$myCourses = $pdo->query("
    SELECT c.id, c.name, u.full_name AS teacher_name
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
  <title>Dashboard | Academic Management System</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="layout">
    <?php require __DIR__ . '/includes/sidebar.php'; ?>
    <main class="main">
      <?php require __DIR__ . '/includes/header.php'; ?>

      <section class="card-grid" aria-label="Stats">
        <article class="stat-card">
          <h3>Grades Recorded</h3>
          <div class="stat-value"><?= str_pad($gradesRecorded, 2, '0', STR_PAD_LEFT) ?></div>
        </article>
        <article class="stat-card">
          <h3>Average Grade</h3>
          <div class="stat-value">
            <?= $averageGrade !== null ? htmlspecialchars((string)$averageGrade) : '—' ?>
          </div>
        </article>
      </section>

      <section class="panel">
        <h2>My Courses</h2>
        <?php if (empty($myCourses)): ?>
          <div class="empty-state">No courses available yet.</div>
        <?php else: ?>
          <ul class="course-list">
            <?php foreach ($myCourses as $c): ?>
              <li class="course-row">
                <span class="course-row__icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14H6a2 2 0 0 1-2-2z"/>
                    <path d="M8 7h8M8 11h8"/>
                  </svg>
                </span>
                <span class="course-row__name">
                  <?= htmlspecialchars($c['name']) ?>
                  <?php if (!empty($c['teacher_name'])): ?>
                    <small style="display:block;color:#8b99a8;font-size:0.8rem;margin-top:2px;">
                      Teacher: <?= htmlspecialchars($c['teacher_name']) ?>
                    </small>
                  <?php endif; ?>
                </span>
                <a class="btn-link" href="course-materials.php?course=<?= (int)$c['id'] ?>">View Materials &rsaquo;</a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>
    </main>
  </div>
</body>
</html>

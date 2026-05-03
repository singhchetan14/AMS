<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

/**
 * Map a numeric score (0–100) to a letter grade for display.
 */
function score_to_letter(int $s): string {
    if ($s >= 90) return 'A+';
    if ($s >= 85) return 'A';
    if ($s >= 80) return 'A-';
    if ($s >= 75) return 'B+';
    if ($s >= 70) return 'B';
    if ($s >= 65) return 'B-';
    if ($s >= 60) return 'C+';
    if ($s >= 55) return 'C';
    if ($s >= 50) return 'C-';
    return 'F';
}

$grades = [];
if (!empty($_SESSION['student_id'])) {
    $stmt = $pdo->prepare("
        SELECT c.name AS course_name, g.score, g.status
        FROM grades g
        INNER JOIN courses c ON g.course_id = c.id
        WHERE g.student_id = ?
        ORDER BY c.name ASC
    ");
    $stmt->execute([$_SESSION['student_id']]);
    $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Grades | Academic Management System</title>
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
        <h2>My Grades</h2>
        <div class="divider"></div>

        <?php if (empty($grades)): ?>
          <div class="empty-state">No grades recorded yet.</div>
        <?php else: ?>
          <table class="grade-table">
            <thead>
              <tr>
                <th>Course</th>
                <th>Score</th>
                <th>Grade</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($grades as $g): ?>
                <?php $score = (int)$g['score']; ?>
                <tr>
                  <td><?= htmlspecialchars($g['course_name']) ?></td>
                  <td><?= $score ?></td>
                  <td><?= score_to_letter($score) ?></td>
                  <td class="<?= $g['status'] === 'Pass' ? 's-pass' : 's-fail' ?>">
                    <?= htmlspecialchars($g['status']) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>

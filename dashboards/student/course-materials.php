<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

$courseFilter = isset($_GET['course']) ? (int)$_GET['course'] : 0;

// All materials uploaded by teachers, optionally filtered to one course
$sql = "
    SELECT m.id, m.title, m.filename, m.uploaded_at,
           c.name AS course_name,
           u.full_name AS teacher_name
    FROM materials m
    INNER JOIN courses c ON m.course_id = c.id
    LEFT  JOIN users   u ON u.id = m.teacher_id
";
$params = [];
if ($courseFilter > 0) {
    $sql .= " WHERE c.id = ?";
    $params[] = $courseFilter;
}
$sql .= " ORDER BY m.uploaded_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Friendly heading when filtered to a single course
$courseHeading = null;
if ($courseFilter > 0) {
    $s = $pdo->prepare("SELECT name FROM courses WHERE id = ?");
    $s->execute([$courseFilter]);
    $courseHeading = $s->fetchColumn() ?: null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Course Materials | Academic Management System</title>
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
        <h2>
          Courses Materials
          <?php if ($courseHeading): ?>
            <small style="font-size:0.85rem;color:#8b99a8;font-weight:400;">— <?= htmlspecialchars($courseHeading) ?></small>
          <?php endif; ?>
        </h2>
        <div class="divider"></div>

        <?php if (empty($materials)): ?>
          <div class="empty-state">No materials uploaded yet.</div>
        <?php else: ?>
          <div class="material-grid">
            <?php foreach ($materials as $m): ?>
              <div class="material-card" style="cursor:default;">
                <div class="material-card__title"><?= htmlspecialchars($m['title']) ?></div>
                <div class="material-card__meta">
                  <span>Course: <?= htmlspecialchars($m['course_name']) ?></span>
                  <span>Uploaded: <?= htmlspecialchars($m['teacher_name'] ?? '—') ?></span>
                  <span>Date: <?= htmlspecialchars(date('Y-m-d', strtotime($m['uploaded_at']))) ?></span>
                </div>
                <a class="btn-link" style="margin-top:12px;display:inline-flex;align-items:center;gap:6px;"
                   href="download.php?id=<?= (int)$m['id'] ?>">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                  </svg>
                  Download
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>

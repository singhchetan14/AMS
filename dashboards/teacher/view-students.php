<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

// All students currently in the system
$students = $pdo->query("
    SELECT id, student_no, full_name, email, group_name, created_at
    FROM students
    ORDER BY student_no ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Students | Academic Management System</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    .stu-table { width: 100%; border-collapse: collapse; }
    .stu-table th {
      text-align: left; padding: 12px 14px;
      font-size: 0.78rem; font-weight: 600;
      letter-spacing: 0.06em; text-transform: uppercase;
      color: #8b99a8;
      border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    .stu-table td {
      padding: 14px;
      border-bottom: 1px solid rgba(255,255,255,0.05);
      font-size: 0.95rem;
    }
    .stu-table tbody tr:hover { background: rgba(255,255,255,0.03); }
    .group-badge {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 999px;
      background: rgba(25,103,210,0.18);
      border: 1px solid rgba(25,103,210,0.35);
      color: #90b8ff;
      font-size: 0.78rem;
      font-weight: 600;
    }
    .panel-wrap {
      background: var(--bg-card);
      border-radius: var(--radius);
      padding: 28px 32px;
      border: 1px solid rgba(255,255,255,0.06);
      max-width: 1100px;
    }
    .empty { text-align: center; padding: 40px; color: #8b99a8; }
    .back-link {
      display: inline-flex; align-items: center; gap: 6px;
      color: #c8d5e5; text-decoration: none;
      margin-bottom: 18px; font-size: 0.9rem;
    }
    .back-link:hover { color: #fff; }
  </style>
</head>
<body>
  <div class="layout">
    <?php require __DIR__ . '/includes/sidebar.php'; ?>
    <main class="main">
      <?php require __DIR__ . '/includes/header.php'; ?>

      <div class="panel-wrap">
        <a class="back-link" href="dashboard.php">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><polyline points="12 8 8 12 12 16"/><line x1="16" y1="12" x2="8" y2="12"/>
          </svg>
          Back
        </a>

        <h2 style="margin:0 0 8px;font-weight:500;font-size:1.25rem;">Students</h2>
        <p style="margin:0 0 20px;color:#8b99a8;font-size:0.9rem;">
          Total: <?= count($students) ?>
        </p>

        <?php if (empty($students)): ?>
          <div class="empty">No students registered yet.</div>
        <?php else: ?>
          <table class="stu-table">
            <thead>
              <tr>
                <th>S.No.</th>
                <th>Student No.</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Group</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($students as $i => $s): ?>
                <tr>
                  <td style="color:#676e7a;"><?= $i + 1 ?></td>
                  <td style="color:#8b99a8;"><?= htmlspecialchars($s['student_no']) ?></td>
                  <td><?= htmlspecialchars($s['full_name']) ?></td>
                  <td><?= htmlspecialchars($s['email'] ?? '—') ?></td>
                  <td>
                    <?php if (!empty($s['group_name'])): ?>
                      <span class="group-badge"><?= htmlspecialchars($s['group_name']) ?></span>
                    <?php else: ?>
                      <span style="color:#8b99a8;">—</span>
                    <?php endif; ?>
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

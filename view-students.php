<?php
/**
 * view-students.php
 * 
 * View all students — read-only table.
 * Shows: Student No., Full Name, Group, Email
 */

require_once 'includes/auth.php';
require_once 'config/db.php';

$stmt = $pdo->prepare("
    SELECT student_no, full_name, group_name, email 
    FROM students 
    ORDER BY student_no ASC
");
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>View Students | Academic Management System</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body class="no-sidebar">
    <div class="layout" style="min-height: 100vh; background-color: #0d1b2a; display: flex; align-items: center; justify-content: center; padding: 20px;">
        <main class="main" style="width: 100%; max-width: 900px; padding: 0;">
            <div class="students-card" style="background-color: #1a2a3f; border-radius: 12px; color: #e2e8f0; overflow: hidden; padding-bottom: 40px;">
                <div style="padding: 40px 40px 20px 40px;">
                    <a href="dashboard.php" style="display: flex; align-items: center; gap: 8px; color: #e2e8f0; text-decoration: none; margin-bottom: 40px; font-family: sans-serif; font-size: 14px;">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 8 8 12 12 16"></polyline>
                            <line x1="16" y1="12" x2="8" y2="12"></line>
                        </svg>
                        Back
                    </a>
                    <h2 style="font-size: 20px; font-weight: 400; margin-bottom: 20px; font-family: sans-serif;">All Students</h2>
                </div>

                <div style="padding: 0;">
                    <table style="width: 100%; border-collapse: collapse; font-family: sans-serif; color: #e2e8f0; text-align: left;">
                        <thead>
                            <tr style="color: #9baac2; font-size: 16px; background-color: rgba(255, 255, 255, 0.05); border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                <th style="padding: 16px 40px; font-weight: normal; width: 15%;">S No.</th>
                                <th style="padding: 16px 20px; font-weight: normal; width: 35%;">Student Name</th>
                                <th style="padding: 16px 20px; font-weight: normal; text-align: center; width: 20%;">Group</th>
                                <th style="padding: 16px 40px; font-weight: normal; text-align: right; width: 30%;">Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="4" style="padding: 20px; text-align: center; color: #9baac2;">No students found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($students as $i => $student): ?>
                                    <tr style="background-color: <?= ($i % 2 === 0) ? 'rgba(255, 255, 255, 0.02)' : 'transparent' ?>; border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                        <td style="padding: 16px 40px;"><?= htmlspecialchars($student['student_no']) ?></td>
                                        <td style="padding: 16px 20px;"><?= htmlspecialchars($student['full_name']) ?></td>
                                        <td style="padding: 16px 20px; text-align: center;">
                                            <span style="background: rgba(25, 103, 210, 0.2); padding: 4px 12px; border-radius: 12px;">
                                                <?= htmlspecialchars($student['group_name']) ?>
                                            </span>
                                        </td>
                                        <td style="padding: 16px 40px; text-align: right;"><?= htmlspecialchars($student['email']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
</body>
</html>

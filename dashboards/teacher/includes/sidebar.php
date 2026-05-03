<?php
/**
 * includes/sidebar.php
 * 
 * Sidebar navigation — included in every main page (not on login/profile).
 * Highlight active page based on current filename.
 */

$currentPage = basename($_SERVER['PHP_SELF']);
$navItems = [
    'dashboard.php' => ['icon' => 'dashboard', 'label' => 'Dashboard'],
    'upload-materials.php' => ['icon' => 'upload-materials', 'label' => 'Upload Materials'],
    'upload-grades.php' => ['icon' => 'upload-grades', 'label' => 'Upload Grades'],
    'view-students.php' => ['icon' => 'view-students', 'label' => 'View Students'],
    'profile.php' => ['icon' => 'profile', 'label' => 'Profile'],
];
?>

<aside class="sidebar">
  <h2 class="sidebar-title">Academic Management System</h2>
  <nav class="nav-menu" aria-label="Sidebar Navigation">
    <?php foreach ($navItems as $page => $item): ?>
      <a 
        class="nav-link clickable <?= ($currentPage === $page) ? 'active' : '' ?>"
        data-page="<?= str_replace('.php', '', $page) ?>"
        href="<?= $page ?>"
      >
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <?php if ($item['icon'] === 'dashboard'): ?>
            <path d="M21 12a9 9 0 1 1-9-9" />
            <path d="M12 3a9 9 0 0 1 9 9h-9z" />
          <?php elseif ($item['icon'] === 'upload-materials'): ?>
            <path d="M12 3v12" />
            <path d="m7 8 5-5 5 5" />
            <rect x="4" y="15" width="16" height="6" rx="2" />
          <?php elseif ($item['icon'] === 'upload-grades'): ?>
            <path d="m12 2 3.1 6.3L22 9.3l-5 4.9 1.2 6.9L12 17.8 5.8 21l1.2-6.9L2 9.3l6.9-1z" />
          <?php elseif ($item['icon'] === 'view-students'): ?>
            <circle cx="9" cy="8" r="3" />
            <path d="M3 19c0-3 2.4-5 6-5s6 2 6 5" />
            <circle cx="17" cy="9" r="2" />
            <path d="M14 19c.4-1.9 1.9-3 4-3 2.5 0 4 1.4 4 3" />
          <?php elseif ($item['icon'] === 'profile'): ?>
            <circle cx="12" cy="8" r="4" />
            <path d="M4 20c0-4 3.6-6 8-6s8 2 8 6" />
          <?php endif; ?>
        </svg>
        <span><?= htmlspecialchars($item['label']) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
</aside>

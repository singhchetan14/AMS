<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$navItems = [
    'dashboard.php'        => 'Dashboard',
    'my-grades.php'        => 'My Grades',
    'my-courses.php'       => 'My Courses',
    'course-materials.php' => 'Course Materials',
    'profile.php'          => 'Profile',
];
?>
<aside class="sidebar">
  <h2 class="sidebar-title">Academic<br>Management<br>System</h2>
  <nav class="nav-menu" aria-label="Sidebar Navigation">
    <?php foreach ($navItems as $page => $label): ?>
      <a class="nav-link <?= ($currentPage === $page) ? 'active' : '' ?>" href="<?= $page ?>">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <?php if ($page === 'dashboard.php'): ?>
            <circle cx="12" cy="12" r="9"/><path d="M12 3v9l5 3"/>
          <?php elseif ($page === 'my-grades.php'): ?>
            <path d="M3 3v18h18"/><path d="M7 14l4-4 4 4 5-5"/>
          <?php elseif ($page === 'my-courses.php'): ?>
            <path d="M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14H6a2 2 0 0 1-2-2z"/><path d="M8 7h8M8 11h8"/>
          <?php elseif ($page === 'course-materials.php'): ?>
            <path d="M4 4h12l4 4v12H4z"/><path d="M16 4v4h4"/>
          <?php elseif ($page === 'profile.php'): ?>
            <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-6 8-6s8 2 8 6"/>
          <?php endif; ?>
        </svg>
        <span><?= htmlspecialchars($label) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
</aside>

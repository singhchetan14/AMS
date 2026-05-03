<?php
// Requires session + $_SESSION['user_email']
$displayName = $_SESSION['student_name']
    ?? ($_SESSION['user_email'] ?? 'Student');
?>
<header class="top-header">
  <div class="greeting">
    <h1>Hello, <span><?= htmlspecialchars($displayName) ?></span></h1>
    <p>What would you like to do today?</p>
  </div>
  <a href="profile.php" class="avatar-btn" aria-label="Open Profile">
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="8" r="4"/>
      <path d="M4 20c0-4 3.6-6 8-6s8 2 8 6"/>
    </svg>
  </a>
</header>

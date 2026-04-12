<?php
/**
 * includes/header.php
 * 
 * Top header HTML — included in every page.
 * Shows teacher greeting, profile button.
 */

// Requires: $_SESSION['teacher_name'] to be set (check in auth.php)
?>

<header class="top-header">
  <div class="greeting">
    <h1>
      Hello, <span id="teacher-name"><?= htmlspecialchars($_SESSION['teacher_name'] ?? 'Teacher') ?></span>
    </h1>
    <p>What would you like to do today?</p>
  </div>
  <button class="avatar-btn clickable" id="profile-button" type="button" aria-label="Open Profile"
          onclick="window.location.href='profile.php'" style="transition: all 0.3s ease;">
    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="12" cy="8" r="4" />
      <path d="M4 20c0-4 3.6-6 8-6s8 2 8 6" />
    </svg>
  </button>
</header>

<style>
  #profile-button:hover {
    box-shadow: 0 0 20px rgba(25, 103, 210, 0.6);
    border-color: #1967d2;
  }
</style>

// Mobile navbar toggle
const navbarToggle = document.getElementById('navbar-toggle');
const navbarLinks = document.getElementById('navbar-links');

if (navbarToggle) {
  navbarToggle.addEventListener('click', function () {
    navbarLinks.classList.toggle('active');
  });
}

// Login dropdown toggle
const dropdownBtn = document.getElementById('login-dropdown-btn');
const dropdownMenu = document.getElementById('login-dropdown-menu');

if (dropdownBtn) {
  dropdownBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    dropdownMenu.classList.toggle('show');
  });
}

// Close dropdown when clicking outside
document.addEventListener('click', function () {
  if (dropdownMenu) {
    dropdownMenu.classList.remove('show');
  }
});

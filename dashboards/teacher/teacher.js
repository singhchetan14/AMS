function setActiveNavigation() {
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';

  document.querySelectorAll('.nav-link').forEach((link) => {
    const href = link.getAttribute('href');
    if (href === currentPage) {
      link.classList.add('active');
    } else {
      link.classList.remove('active');
    }
  });
}

function addClickFeedback() {
  const targets = document.querySelectorAll('.clickable');

  targets.forEach((target) => {
    if (target.dataset.clickFeedbackBound === '1') return;
    target.dataset.clickFeedbackBound = '1';

    target.addEventListener('mousedown', () => target.classList.add('pressed'));
    target.addEventListener('mouseup', () => target.classList.remove('pressed'));
    target.addEventListener('mouseleave', () => target.classList.remove('pressed'));
    target.addEventListener('click', () => {
      target.classList.add('pressed');
      setTimeout(() => target.classList.remove('pressed'), 120);
    });
  });
}

// Stats and schedule are rendered server-side by PHP from the database.
// loadStats() and loadSchedule() were removed — they were overwriting live DB values
// with hardcoded placeholder data.

document.addEventListener('DOMContentLoaded', () => {
  setActiveNavigation();
  addClickFeedback();
});


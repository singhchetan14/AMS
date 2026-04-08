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

function loadStats() {
  const assignedCourses = document.getElementById('assigned-courses-count');
  const totalStudents = document.getElementById('total-students-count');

  if (!assignedCourses || !totalStudents) return;

  // TODO: Replace with fetch() API call to PHP backend for teacher stats.
  assignedCourses.textContent = '02';
  totalStudents.textContent = '111';
}

function loadSchedule() {
  const scheduleList = document.getElementById('schedule-list');
  if (!scheduleList) return;

  // TODO: Replace with fetch() API call to PHP backend for today's schedule.
  const placeholderSchedule = [
    { time: '9:00 AM', title: 'Chemistry | Class A' },
    { time: '1:30 PM', title: 'Mathematics | Class C' },
    { time: '4:00 PM', title: 'Physics | Class B' },
  ];

  scheduleList.innerHTML = '';

  placeholderSchedule.forEach((item, index) => {
    const li = document.createElement('li');
    li.className = 'schedule-item clickable';
    li.setAttribute('data-schedule-index', String(index));
    li.setAttribute('tabindex', '0');

    li.innerHTML = `
      <span class="time-badge" data-time="${item.time}">${item.time}</span>
      <span class="schedule-course" data-course="${item.title}">${item.title}</span>
    `;

    scheduleList.appendChild(li);
  });

  addClickFeedback();
}

document.addEventListener('DOMContentLoaded', () => {
  setActiveNavigation();
  addClickFeedback();
  loadStats();
  loadSchedule();

  const profileButton = document.getElementById('profile-button');
  if (profileButton) {
    profileButton.addEventListener('click', () => {
      window.location.href = 'profile.html';
    });
  }

  // Upload Materials Page Functionality
  const dragDropZone = document.getElementById('drag-drop-zone');
  const fileInput = document.getElementById('file-input');
  const uploadBtn = document.getElementById('upload-action-btn');
  const recentList = document.getElementById('recent-materials-list');
  const courseSelect = document.getElementById('course-select');
  const materialTitle = document.getElementById('material-title');
  
  if (dragDropZone) {
    dragDropZone.addEventListener('click', () => {
      fileInput.click();
    });

    dragDropZone.addEventListener('dragover', (e) => {
      e.preventDefault();
      dragDropZone.style.background = 'rgba(255, 255, 255, 0.05)';
      dragDropZone.style.borderColor = 'rgba(255, 255, 255, 0.6)';
    });

    dragDropZone.addEventListener('dragleave', () => {
      dragDropZone.style.background = 'transparent';
      dragDropZone.style.borderColor = 'rgba(255, 255, 255, 0.3)';
    });

    dragDropZone.addEventListener('drop', (e) => {
      e.preventDefault();
      dragDropZone.style.background = 'transparent';
      dragDropZone.style.borderColor = 'rgba(255, 255, 255, 0.3)';
      if (e.dataTransfer.files.length > 0) {
        fileInput.files = e.dataTransfer.files;
        updateDragDropText();
      }
    });

    fileInput.addEventListener('change', () => {
      if (fileInput.files.length > 0) {
        updateDragDropText();
      }
    });

    function updateDragDropText() {
      const fileName = fileInput.files[0].name;
      dragDropZone.querySelector('p').innerHTML = `Selected: <span style="color: #fff; font-weight: 600;">${fileName}</span>`;
    }

    uploadBtn.addEventListener('click', () => {
      const file = fileInput.files[0];
      const course = courseSelect.value;
      const title = materialTitle.value.trim();
      
      if (!file) {
        alert("Please select a file to upload.");
        return;
      }
      if (!course) {
        alert("Please select a course.");
        return;
      }
      
      const displayName = title || file.name;

      // Add to recent list
      const li = document.createElement('li');
      li.className = 'recent-item';
      li.textContent = `${displayName} - ${course}`;
      
      // prepend to top
      recentList.insertBefore(li, recentList.firstChild);

      // reset form
      fileInput.value = '';
      courseSelect.selectedIndex = 0;
      materialTitle.value = '';
      dragDropZone.querySelector('p').innerHTML = 'Click to upload <span class="drag-text-light">or drag and drop</span>';
    });
  }
});

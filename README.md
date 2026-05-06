# Academic Management System (AMS)

A web-based Academic Management System built for managing students, teachers, and academic operations. The system provides role-based authentication, dashboards, and account recovery features. Built using PHP and MySQL, running on XAMPP.

This project is being developed as part of our academic coursework by a team of 6 members. Each member is responsible for specific modules and features across multiple sprints.

---

## Features (Sprint 1)

- **Student Login & Signup** — Students can register and log in to their dashboard
- **Teacher Login** — Teachers can log in (no signup, accounts are created by admin)
- **Admin Login** — Separate admin login and dashboard panel
- **Forgot Password** — 6-digit OTP code sent to email via PHPMailer, with 10-minute expiry
- **Role-Based Dashboards** — Separate protected dashboards for Student, Teacher, and Admin
- **Responsive Navbar** — Navigation bar with login dropdown (Student/Teacher), signup button, and mobile toggle

---

## Features (Sprint 2)

Sprint 2 builds the day-to-day functionality on top of Sprint 1's authentication foundation. This sprint focused on what users actually *do* once they log in — manage their profile, view/upload course materials, see and assign grades, and message each other in real time.

### 1. Profile Management (Student & Teacher)

Both students and teachers now have a dedicated profile page (`dashboards/student/profile.php` and `dashboards/teacher/profile.php`) where they can:

- **View and edit personal details** — full name and email address, both stored in the `users` table.
- **Upload a profile photo** — PNG or JPEG, validated server-side with `finfo_file` (MIME-sniffing, not just extension), capped at 5MB. Files are saved to `dashboards/student/uploads/photos/` or `dashboards/teacher/uploads/photos/` with a unique filename pattern (`student_{id}_{timestamp}.{ext}` / `teacher_{id}_{timestamp}.{ext}`) so old photos aren't overwritten and there are no collisions.
- **Change password** — leaves the old password in place if the new field is left blank; otherwise hashes the new one with `password_hash(PASSWORD_DEFAULT)` before storing.
- **All updates run inside a PDO transaction** (`do_update_profile.php`) — if any single step fails (validation, file move, or DB update), the whole change is rolled back so the database never ends up in a half-updated state. The linked `students` row is kept in sync as a best-effort secondary update.
- **Logout** — clears the session and redirects back to the login page.

### 2. Student Dashboard Pages

The student dashboard is now a full sidebar layout (`dashboards/student/`) with these pages:

- **`dashboard.php`** — Overview page showing total grades recorded, average score (rounded to 1 decimal), and the list of all courses in the system with their assigned teacher's name (joined from `users` via `c.teacher_id`).
- **`my-courses.php`** — Read-only list of every course the student is enrolled in.
- **`my-grades.php`** — All grades the student has received, by course.
- **`course-materials.php`** — Lists every material a teacher has uploaded for any course the student belongs to. Files are downloaded through `download.php` (a controlled passthrough, so direct file URLs are not exposed).
- **`profile.php`** — Profile management (described above).

All pages share a common `includes/sidebar.php`, `includes/header.php`, and `includes/auth.php` (which redirects to login if the session is missing or the role is wrong).

### 3. Teacher Dashboard Pages

The teacher dashboard (`dashboards/teacher/`) mirrors the student layout but with write/management permissions:

- **`dashboard.php`** — Shows assigned course count (from `courses WHERE teacher_id = ?`), total student count, today's class schedule (filtered by the current weekday using `date('l')`), and recent uploaded materials. Uses no-cache HTTP headers so a logged-out user can't navigate back to a cached version.
- **`view-students.php`** — Read-only roster of all students in the system.
- **`upload-grades.php`** — Records grades for students in a course. Calls `do_upload_grade.php` (form handler) and inserts into the `grades` table.
- **`edit-grade.php`** — Edit or delete a previously recorded grade. Delete goes through `do_delete_grade.php`.
- **`upload-materials.php`** — Uploads a course material file (PDF, DOCX, etc.) via `do_upload_material.php`. Files are saved to `uploads/materials/`, and the metadata row is inserted into the `materials` table. There is a small JSON endpoint (`actions/api_materials.php`) used by the page to list materials without a full reload.
- **`profile.php`** — Profile management (described above).

The teacher session uses dedicated session keys (`teacher_id`, `teacher_email`, `teacher_name`) which are set during login alongside the generic `user_id` / `user_role` keys, so both the new teacher pages and any shared code (like the messaging widget) can read whichever they need.

### 4. Messaging Feature (`messaging/`)

A self-contained chat module that lets students and teachers message each other directly. The module is dropped into a single folder so it can be enabled/disabled cleanly:

```
messaging/
├── messages.php                 # Full-page Messenger-style UI (sidebar + thread)
├── sql/messages.sql             # Schema for the messages table
├── includes/auth.php            # Resolves the current user from either session
├── widget/                      # Floating chat bubble that embeds messages.php
│   ├── widget.php               # Bubble + iframe wrapper
│   ├── widget.css               # Bubble styling
│   └── widget.js                # Open/close + unread polling
└── api/                         # JSON endpoints (used by the widget for AJAX)
    ├── contacts.php
    ├── conversations.php
    ├── fetch.php
    ├── send.php
    ├── mark_read.php
    └── unread_count.php
```

How it works:

- **Database** — A single `messages` table with `sender_id`, `receiver_id`, `body`, `is_read`, and `created_at`. Foreign keys cascade on user delete. Two indexes: `(receiver_id, is_read)` for the unread badge query, and `(sender_id, receiver_id, created_at)` for thread fetches. The table is auto-created on first hit of `messages.php`, so there is no manual migration step required.
- **Full-page UI (`messages.php`)** — A two-pane layout: left sidebar shows either conversations (sorted by latest message) or live search results when the user types in the search box; right pane shows the active thread. Sending is a plain POST form (no AJAX needed) which redirects back to the same page. Opening a thread auto-marks its incoming messages as read in the same request.
- **Floating widget (`widget/widget.php`)** — A blue bubble pinned bottom-right of every dashboard page. Clicking it opens a panel that loads `messages.php?embed=1` inside an iframe — `embed=1` switches the page to a compact stacked layout that fits the panel. The bubble shows a red unread-count badge driven by a single `COUNT(*)` query against unread messages. The base URL is auto-detected from `DOCUMENT_ROOT` so it works whether AMS is served from `/AMS` or another path.
- **Cross-role** — Because the widget reads either `user_id` (student) or `teacher_id` (teacher) from the session, the same code works for both roles without duplication.

### 5. Show / Hide Password Toggle

Login and signup pages now have an eye-icon button next to each password field. Clicking it toggles the input between `type="password"` and `type="text"` so users can verify what they typed. This was added to:

- `auth/student/login.php`
- `auth/student/signup.php` (both the password and confirm-password fields, with independent toggles)
- `auth/teacher/login.php`
- `admin/login.php` (already had this from Sprint 1's admin work)

The implementation is intentionally tiny — a positioned `<button type="button">` overlaying the input, plus a one-liner `togglePassword(id)` JS function. It is purely a UI affordance: form submission, validation, and the `password_verify` flow on the server are completely untouched.

### 6. Database Schema Additions for Sprint 2

In addition to Sprint 1's `users` and `password_resets` tables, Sprint 2 introduces:

```sql
-- Profile photo + full name on the users table (added as ALTER)
ALTER TABLE users ADD COLUMN full_name VARCHAR(255) NULL AFTER email;
ALTER TABLE users ADD COLUMN photo VARCHAR(255) NULL;

-- Students roster (separate from auth)
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    group_name VARCHAR(50) NULL
);

-- Courses, owned by a teacher
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    teacher_id INT NOT NULL,
    schedule_time TIME NULL,
    schedule_day VARCHAR(20) NULL,
    group_name VARCHAR(50) NULL,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Grades recorded by a teacher for a student
CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Course materials uploaded by a teacher
CREATE TABLE materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    teacher_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Messaging table (also auto-created on first hit of messages.php)
CREATE TABLE messages (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    sender_id   INT NOT NULL,
    receiver_id INT NOT NULL,
    body        TEXT NOT NULL,
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_receiver (receiver_id, is_read),
    KEY idx_thread   (sender_id, receiver_id, created_at),
    FOREIGN KEY (sender_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Tech Stack

- **Backend:** PHP 8+
- **Database:** MySQL (PDO)
- **Server:** XAMPP (Apache + MySQL)
- **Mail Service:** PHPMailer (Gmail SMTP)
- **Frontend:** HTML, CSS, JavaScript
- **Design:** Figma
- **Version Control:** Git & GitHub
- **Project Tracking:** Jira

---

## Folder Structure

```
Academic-Management-System/
├── index.php                          # Landing page
├── about.php                          # About page
├── README.md
│
├── config/
│   ├── db.php                         # PDO database connection
│   └── mail.php                       # SMTP credentials (gitignored)
│
├── includes/
│   ├── navbar.php                     # Shared navbar with $basePath routing
│   └── footer.php                     # Shared footer
│
├── assets/
│   ├── css/
│   │   └── style.css                  # Main stylesheet (Poppins, dark theme)
│   └── js/
│       └── script.js                  # Mobile toggle & dropdown logic
│
├── auth/
│   ├── student/
│   │   ├── login.php                  # Student login
│   │   └── signup.php                 # Student registration
│   ├── teacher/
│   │   └── login.php                  # Teacher login (no signup)
│   └── forgot-password/
│       ├── request.php                # Step 1 - enter email, sends OTP
│       ├── verify.php                 # Step 2 - enter 6-digit code
│       └── reset.php                  # Step 3 - set new password
│
├── admin/
│   ├── login.php                      # Admin login
│   └── dashboard/
│       └── index.php                  # Admin panel
│
├── dashboards/
│   ├── student/
│   │   └── index.php                  # Student dashboard (session protected)
│   └── teacher/
│       └── index.php                  # Teacher dashboard (session protected)
│
└── vendor/                            # Composer dependencies (gitignored)
```

---

## Getting Started

1. Clone the repository
2. Place the project folder inside `xampp/htdocs/`
3. Start **Apache** and **MySQL** from the XAMPP control panel
4. Create the database `ams_db` in phpMyAdmin
5. Run the following SQL to create the required tables:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'teacher', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    code VARCHAR(6) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

6. Install PHPMailer (run from project root):

   ```
   composer require phpmailer/phpmailer
   ```

7. Create `config/mail.php` with your Gmail SMTP credentials:

   ```php
   <?php
   $mail_host = 'smtp.gmail.com';
   $mail_port = 587;
   $mail_username = 'your-email@gmail.com';
   $mail_password = 'your-app-password';
   $mail_from_name = 'AMS';
   ```

   > Use a Gmail App Password, not your regular password. You can generate one from Google Account > Security > App Passwords.

8. Open `http://localhost/Academic%20Management%20System/` in your browser

---

## My Contributions — Chetan Singh

I worked on the core authentication system and frontend for Sprint 1. Here's what I built:

### Student Login & Signup

- Built the student registration page with email validation, password hashing (`password_hash`), and duplicate email check
- Built the student login page with `password_verify` against hashed passwords in the database
- Only students can sign up through the form — teacher accounts are created by admin
- After login, students are redirected to a session-protected dashboard

### Teacher Login

- Built the teacher login page — same flow as student but checks for `role = 'teacher'` in the database
- No signup option for teachers, no forgot password link
- Redirects to a separate teacher dashboard after login

### Forgot Password (3-step flow using PHPMailer)

- **Step 1 (request.php):** User enters their email. If the email exists in the database, a random 6-digit OTP is generated, stored in `password_resets` table, and sent to the user's email using PHPMailer via Gmail SMTP. The email is saved in the session and the user is auto-redirected to the verify page after 2 seconds.
- **Step 2 (verify.php):** User enters the OTP code. The code is matched against the database with a 10-minute expiry check (`created_at >= NOW() - INTERVAL 10 MINUTE`). If valid, a `reset_verified` flag is set in the session and user moves to step 3.
- **Step 3 (reset.php):** User sets a new password. Both `reset_email` and `reset_verified` must exist in session (so no one can skip steps). After password update, the OTP is deleted from the database and session is cleared.

### Navbar & Layout

- Built a shared navbar (`includes/navbar.php`) with Home, About, Login dropdown (Student/Teacher), and Sign Up button
- Used a `$basePath` variable pattern so the navbar links work correctly from any folder depth (root pages set `$basePath = ''`, nested pages like `auth/student/login.php` set `$basePath = '../../'`)
- Mobile responsive with hamburger toggle
- Built shared footer component

### Styling (CSS)

- Designed the dark theme UI matching the team's Figma design
- Used Google Fonts (Poppins) throughout
- Login/Signup pages have light pill-shaped inputs and white buttons
- Forgot password pages have a different card style with transparent inputs and blue buttons
- Fully responsive with mobile breakpoint at 768px

### Dashboards

- Built session-protected student and teacher dashboards
- Each dashboard checks the user's role in session before allowing access
- Logout functionality clears the session and redirects back to login

---

## Git Workflow

> **Developers: Do NOT push directly to the `main` branch.**

1. Create your own branch from `main`
   ```
   git checkout -b feature/your-feature-name
   ```
2. Work on your assigned Jira ticket in your branch
3. Commit regularly with clear messages
4. Push your branch and create a Pull Request
   ```
   git push origin feature/your-feature-name
   ```
5. Your code will be reviewed before merging into `main`

---

## Team

- Built and maintained by **Team Limitless**
- Project tracked on Jira

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

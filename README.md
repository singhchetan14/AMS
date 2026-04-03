# Academic Management System (AMS)

A web-based Academic Management System for managing students, teachers, and admin operations. Built with PHP, MySQL, and hosted on XAMPP.

---

## Features (Sprint 1)

- **Student Login & Signup** — Students can register and log in to their dashboard
- **Teacher Login** — Teachers can log in (no signup, accounts are created by admin)
- **Admin Login** — Separate admin login and dashboard panel
- **Forgot Password** — 6-digit verification code sent to registered email via PHPMailer
- **Role-Based Dashboards** — Separate dashboards for Student, Teacher, and Admin

---

## Tech Stack

- **Backend:** PHP
- **Database:** MySQL
- **Server:** XAMPP (Apache)
- **Mail:** PHPMailer

---

## Folder Structure

```
Academic-Management-System/
├── index.php                          # Landing page
├── about.php                          # About page
├── README.md
│
├── config/
│   └── db.php                         # Database connection
│
├── includes/
│   ├── navbar.php                     # Navigation bar
│   └── footer.php                     # Footer
│
├── assets/
│   ├── css/
│   │   └── style.css                  # Stylesheet
│   └── js/
│       └── script.js                  # JavaScript
│
├── auth/
│   ├── student/
│   │   ├── login.php                  # Student login
│   │   └── signup.php                 # Student signup (students only)
│   ├── teacher/
│   │   └── login.php                  # Teacher login (no signup)
│   └── forgot-password/
│       ├── request.php                # Enter email to receive code
│       ├── verify.php                 # Enter 6-digit verification code
│       └── reset.php                  # Set new password
│
├── admin/
│   ├── login.php                      # Admin login (separate)
│   └── dashboard/
│       └── index.php                  # Admin panel
│
└── dashboards/
    ├── student/
    │   └── index.php                  # Student dashboard
    └── teacher/
        └── index.php                  # Teacher dashboard
```

---

## Getting Started

1. Clone the repository
2. Place the project folder inside `xampp/htdocs/`
3. Start Apache and MySQL from the XAMPP control panel
4. Create the database `ams_db` in phpMyAdmin
5. Open `http://localhost/Academic%20Management%20System/` in your browser

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

- Built and maintained by the AMS development team
- Project tracked on Jira

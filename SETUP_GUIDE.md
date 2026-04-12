/**
 * SETUP GUIDE - AMS (Academic Management System)
 * 
 * Complete PHP + MySQL Teacher Dashboard Backend
 */

// ════════════════════════════════════════════════════════════════════════
// 1️⃣  INSTALLATION
// ════════════════════════════════════════════════════════════════════════

STEP 1: Database Import
-----------------------
Option A - Command line:
  $ mysql -u root -p < database/schema.sql
  (Enter your MySQL password when prompted)

Option B - phpMyAdmin:
  1. Go to http://localhost/phpmyadmin
  2. Click "Import" tab
  3. Select database/schema.sql
  4. Click "Go"

This creates:
  ✓ Database: ams_db
  ✓ Tables: teachers, courses, students, materials, grades
  ✓ Demo data: 1 teacher, 3 courses, 10 students, 10 grades


STEP 2: Configure Database (if needed)
--------------------------------------
Edit config/db.php if your MySQL credentials differ:

  define('DB_HOST', 'localhost');    // Usually 'localhost'
  define('DB_PORT', '3306');         // MySQL port
  define('DB_NAME', 'ams_db');       // Database name
  define('DB_USER', 'root');         // MySQL user
  define('DB_PASS', '');             // MySQL password (empty if none)


STEP 3: Create Upload Directories
---------------------------------
These are created automatically, but ensure they're writable:

  $ chmod 755 uploads/materials
  $ chmod 755 uploads/photos


STEP 4: Start the Server
------------------------
Option A - PHP Built-in Server (development only):
  $ cd /path/to/AMS
  $ php -S localhost:8000
  → Visit http://localhost:8000

Option B - Apache/XAMPP:
  1. Copy AMS folder to htdocs/
  2. Visit http://localhost/AMS


// ════════════════════════════════════════════════════════════════════════
// 2️⃣  DEMO LOGIN
// ════════════════════════════════════════════════════════════════════════

Email:    teacher@ams.com
Password: password123

Teacher:  Sarah Johnson
Department: Computer Science
Courses:  Computer Science (09:00), Mathematics (11:00), Physics (14:00)


// ════════════════════════════════════════════════════════════════════════
// 3️⃣  FILE STRUCTURE & ROUTING
// ════════════════════════════════════════════════════════════════════════

Public Pages (no login required):
  ├─ index.php              → Redirects to login or dashboard
  └─ login.php              → Login form

Protected Pages (requires session):
  ├─ dashboard.php          → Overview (courses, students, schedule)
  ├─ upload-materials.php   → Upload course materials
  ├─ upload-grades.php      → View/manage grades
  ├─ edit-grade.php         → Edit/add individual grade
  ├─ view-students.php      → View all students
  ├─ profile.php            → Teacher profile + settings
  └─ logout.php             → Logout & redirect to login

Form Handlers (POST actions):
  ├─ actions/do_login.php           → Process login form
  ├─ actions/do_upload_material.php → Handle file upload
  ├─ actions/do_delete_grade.php    → Delete a grade
  └─ actions/do_update_profile.php  → Update profile/photo/password

Shared Templates:
  ├─ includes/auth.php      → Session check (require on protected pages)
  ├─ includes/sidebar.php   → Navigation menu
  └─ includes/header.php    → Top greeting bar

Configuration & Database:
  ├─ config/db.php          → PDO connection setup
  └─ database/schema.sql    → SQL schema + demo data


// ════════════════════════════════════════════════════════════════════════
// 4️⃣  FEATURES & HOW TO USE
// ════════════════════════════════════════════════════════════════════════

LOGIN
  Page: login.php
  Action: actions/do_login.php
  - Enter email and password
  - Password verified using bcrypt
  - Session created on success
  - Redirects to dashboard.php

DASHBOARD
  Page: dashboard.php
  - Shows: Assigned courses count, total students, today's schedule
  - Real-time data from database
  - Links to all main features

UPLOAD MATERIALS
  Page: upload-materials.php
  Action: actions/do_upload_material.php
  - Select a course
  - Enter material title
  - Upload file (PDF, DOCX, PPTX) — max 10MB
  - File stored with unique name in uploads/materials/
  - Recent materials list shown below

MANAGE GRADES
  Page: upload-grades.php
  - Filter by group (A, B, C) → course updates via form
  - Table shows students in selected course
  - Click "Edit" to modify score (status auto-calculated)
  - Click "Delete" to remove grade
  - "Add New Grade" button for new entries

EDIT/ADD GRADE
  Page: edit-grade.php
  - Edit mode: pre-fills student name, score, status
  - Add mode: dropdown to select student
  - Score validates 0-100
  - Status auto-set: Pass (≥50) or Fail (<50)
  - Visual feedback on status change

VIEW STUDENTS
  Page: view-students.php
  - Read-only table of all students
  - Columns: S No., Full Name, Group, Email
  - Sorted by student number

PROFILE
  Page: profile.php
  Action: actions/do_update_profile.php
  - Update full name, email
  - Change password (optional, min 8 chars)
  - Upload profile photo (JPG/PNG, max 5MB)
  - Logout button


// ════════════════════════════════════════════════════════════════════════
// 5️⃣  SECURITY MEASURES
// ════════════════════════════════════════════════════════════════════════

✅ SQL Injection Prevention
   - All queries use PDO prepared statements
   - No string concatenation with user input

✅ XSS Prevention (Cross-Site Scripting)
   - All output escaped with htmlspecialchars()
   - User data never echoed raw to HTML

✅ Password Security
   - Stored as bcrypt hashes (PASSWORD_BCRYPT)
   - Verified with password_verify()
   - Never stored or transmitted in plain text

✅ Session Security
   - Session ID regenerated after login (session_regenerate_id)
   - Session destroyed on logout
   - Teacher ID used for data filtering/authorization

✅ File Upload Security
   - MIME type validated with finfo_file() (not just extension)
   - File size limits enforced (10MB materials, 5MB photos)
   - Unique filenames generated with uniqid()
   - Files stored with restricted access

✅ Authorization
   - All database queries include teacher_id check
   - Teachers can only access their own courses/grades/materials
   - Grade deletion confirms teacher ownership


// ════════════════════════════════════════════════════════════════════════
// 6️⃣  DATABASE SCHEMA
// ════════════════════════════════════════════════════════════════════════

teachers
  id (INT, PK)
  full_name (VARCHAR 100)
  email (VARCHAR 150, UNIQUE)
  password (VARCHAR 255, bcrypt hash)
  department (VARCHAR 100)
  phone (VARCHAR 20)
  photo (VARCHAR 255, filename)
  created_at (TIMESTAMP)

courses
  id (INT, PK)
  name (VARCHAR 100, e.g. "Computer Science")
  teacher_id (INT, FK → teachers.id)
  group_name (VARCHAR 10, e.g. "A", "B", "C")
  schedule_time (TIME, e.g. "09:00:00")
  schedule_day (VARCHAR 20, e.g. "Monday")
  created_at (TIMESTAMP)

students
  id (INT, PK)
  student_no (VARCHAR 20, UNIQUE, e.g. "101", "102")
  full_name (VARCHAR 100)
  email (VARCHAR 150)
  group_name (VARCHAR 10)
  created_at (TIMESTAMP)

materials
  id (INT, PK)
  title (VARCHAR 255)
  filename (VARCHAR 255, unique name in uploads/materials/)
  course_id (INT, FK → courses.id)
  teacher_id (INT, FK → teachers.id)
  uploaded_at (TIMESTAMP)

grades
  id (INT, PK)
  student_id (INT, FK → students.id)
  course_id (INT, FK → courses.id)
  score (INT, 0-100)
  status (ENUM 'Pass', 'Fail', auto-calculated)
  teacher_id (INT, FK → teachers.id)
  created_at (TIMESTAMP)
  UNIQUE KEY (student_id, course_id)


// ════════════════════════════════════════════════════════════════════════
// 7️⃣  ADDING MORE TEACHERS
// ════════════════════════════════════════════════════════════════════════

Generate a bcrypt password hash:

  $ php -r "echo password_hash('password123', PASSWORD_BCRYPT);"

Insert into database:

  INSERT INTO teachers (full_name, email, password, department, phone)
  VALUES (
    'John Doe',
    'john@ams.com',
    '$2y$10$...bcrypt_hash_here...',
    'Mathematics',
    '+1 (555) 987-6543'
  );


// ════════════════════════════════════════════════════════════════════════
// 8️⃣  BACKUP & RESTORE
// ════════════════════════════════════════════════════════════════════════

Backup:
  $ mysqldump -u root -p ams_db > ams_backup.sql

Restore:
  $ mysql -u root -p ams_db < ams_backup.sql


// ════════════════════════════════════════════════════════════════════════
// 9️⃣  TROUBLESHOOTING
// ════════════════════════════════════════════════════════════════════════

"Database connection error"
  → Check config/db.php credentials
  → Verify MySQL is running: service mysql status
  → Test connection: mysql -u root -p

"Page shows blank / errors not displaying"
  → Edit config/db.php, change PDO::ERRMODE_SILENT to ERRMODE_EXCEPTION
  → Check PHP logs: tail -f /var/log/php-errors.log

"File upload fails"
  → Check folder permissions: chmod 755 uploads/materials
  → Verify file size limits in edit-grade.php
  → Check MIME type (PDF, DOCX, PPTX for materials)

"Login fails"
  → Verify demo data imported: SELECT * FROM teachers;
  → Reset password hash if needed (see section 7)

"Session expires immediately"
  → Check browser cookie settings
  → Verify session.save_path is writable
  → Review includes/auth.php session timeout settings


// ════════════════════════════════════════════════════════════════════════
// 🔟 CODE COMMENTS & DOCUMENTATION
// ════════════════════════════════════════════════════════════════════════

Every PHP file includes:
  ✓ File purpose and description (top comment block)
  ✓ Database tables used
  ✓ Dependencies (includes)
  ✓ Section separators (// ── Section Label ──)
  ✓ Inline comments for complex logic
  ✓ Security notes where relevant

Each page follows this structure:
  1. Require auth/config
  2. Validate input
  3. Database queries (grouped with section comments)
  4. Process data
  5. HTML output with escaping


// ════════════════════════════════════════════════════════════════════════
// 📞 SUPPORT & NEXT STEPS
// ════════════════════════════════════════════════════════════════════════

For issues:
  1. Check the inline comments in the PHP file
  2. Review error message carefully
  3. Check MySQL logs: SELECT * FROM your_table;
  4. Verify file permissions and directories exist

Future enhancements:
  - Email notifications on grade updates
  - Pagination for large student lists
  - File download for uploaded materials
  - Export grades to Excel/PDF
  - Admin dashboard for managing teachers
  - Two-factor authentication (2FA)
  - API endpoints for mobile apps

Questions? Review the code — it's heavily commented!

═══════════════════════════════════════════════════════════════════════════

VERSION: 1.0
CREATED: April 2026
PHP: 7.4+
MySQL: 5.7+ (8.0+ recommended)

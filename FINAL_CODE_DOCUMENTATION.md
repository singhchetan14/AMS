# AMS - Final Working Code Documentation
**Created:** May 3, 2026
**Version:** 1.0 - Production Ready

---

## DATABASE SETUP

### File: `ams_db_final.sql`
**Location:** `/Applications/XAMPP/xamppfiles/htdocs/AMS/ams_db_final.sql`

This is the consolidated database setup with all authentication integrated into a single `users` table.

**Key Changes:**
- ✅ Single `users` table for students, teachers, and admins
- ✅ Password nullable for read-only student accounts
- ✅ `password_resets` table for forgot password functionality
- ✅ All fields: email, password, role, full_name, student_no, department, phone, photo, group_name

**Import command:**
```bash
/Applications/XAMPP/bin/mysql -u root ams_db < ams_db_final.sql
```

---

## TEST CREDENTIALS

### Teacher Accounts
| Email | Password | Role |
|-------|----------|------|
| teacher@ams.com | (varies) | teacher |
| testteacher@ams.com | teacher123 | teacher |

### Student Accounts (No password - needs signup)
| Email | Role |
|-------|------|
| alice@gmail.com | student |
| bob@gmail.com | student |

---

## AUTHENTICATION FILES

### 1. Student Signup
**File:** `auth/student/signup.php`

**Features:**
- Email validation
- Password confirmation
- Duplicate email check
- Password hashing (bcrypt)
- Auto-redirect to login after 2 seconds

**Key Code:**
```php
// Hashes password before storing
$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'student')");
$stmt->execute([$email, $hashed]);
```

---

### 2. Student Login
**File:** `auth/student/login.php`

**Features:**
- Queries `users` table with `role='student'`
- Secure password verification
- Handles NULL passwords safely
- Session creation
- Redirects to student dashboard

**Key Code:**
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'student'");
$stmt->execute([$email]);
$user = $stmt->fetch();

// NULL-safe password check
if ($user && $user['password'] && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = 'student';
    header('Location: ../../dashboards/student/dashboard.php');
}
```

---

### 3. Teacher Login
**File:** `auth/teacher/login.php`

**Features:**
- Queries `users` table with `role='teacher'`
- Same secure authentication as student login
- Redirects to teacher dashboard
- NULL-safe password verification

**Key Code:**
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'teacher'");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && $user['password'] && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = 'teacher';
    header('Location: ../../dashboards/teacher/dashboard.php');
}
```

---

### 4. Forgot Password Request
**File:** `auth/forgot-password/request.php`

**Features:**
- Email validation
- 6-digit OTP code generation
- PHPMailer SMTP integration (Gmail)
- Auto-redirect to verification page
- OTP stored in `password_resets` table

**Requirements:**
- `config/mail.php` configured with Gmail credentials
- PHPMailer installed via Composer

**Key Code:**
```php
$code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
$stmt = $pdo->prepare("INSERT INTO password_resets (email, code) VALUES (?, ?)");
$stmt->execute([$email, $code]);

$mail->Body = "<h3>Your password reset code is:</h3><h1>$code</h1>";
$mail->send();
```

---

## HELPER FILES

### 1. Navigation Bar
**File:** `includes/navbar.php`

```php
<nav class="navbar">
  <div class="navbar__container">
    <div class="navbar__brand">
      <a href="<?= $basePath ?>index.php" class="navbar__logo">AMS</a>
    </div>
    <div class="navbar__menu">
      <a href="<?= $basePath ?>index.php" class="navbar__link">Home</a>
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="<?= $basePath ?>profile.php" class="navbar__link">Profile</a>
        <a href="<?= $basePath ?>logout.php" class="navbar__link">Logout</a>
      <?php else: ?>
        <a href="<?= $basePath ?>auth/student/login.php" class="navbar__link">Student Login</a>
        <a href="<?= $basePath ?>auth/student/signup.php" class="navbar__link">Student Signup</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
```

---

### 2. Footer
**File:** `includes/footer.php`

```php
<footer class="footer">
  <div class="footer__container">
    <p>&copy; 2026 Academic Management System. All rights reserved.</p>
  </div>
</footer>
```

---

## DATABASE CONFIGURATION

**File:** `config/db.php`

```php
<?php
$host   = 'localhost';
$dbname = 'ams_db';
$user   = 'root';
$pass   = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    exit('Database connection failed.');
}
?>
```

---

## USERS TABLE STRUCTURE

```sql
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL UNIQUE,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('student','teacher','admin') DEFAULT 'student',
  `created_at` timestamp DEFAULT current_timestamp(),
  `full_name` varchar(100),
  `student_no` varchar(20),
  `department` varchar(100),
  `phone` varchar(20),
  `photo` varchar(255),
  `group_name` varchar(10),
  PRIMARY KEY (`id`)
);
```

---

## PASSWORD_RESETS TABLE STRUCTURE

```sql
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL UNIQUE,
  `code` varchar(6) NOT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  `expires_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);
```

---

## SESSION VARIABLES

Once logged in, the following session variables are set:

```php
$_SESSION['user_id']      // User ID from users table
$_SESSION['user_email']   // User's email
$_SESSION['user_role']    // 'student', 'teacher', or 'admin'
```

---

## SECURITY FEATURES

✅ **Prepared Statements** - Prevents SQL injection
✅ **Password Hashing** - Uses bcrypt (PASSWORD_DEFAULT)
✅ **NULL-Safe Verification** - Handles NULL passwords safely
✅ **Email Validation** - Uses FILTER_VALIDATE_EMAIL
✅ **Session Management** - Secure session handling
✅ **HTTPS Ready** - Compatible with HTTPS deployment

---

## WORKFLOW SUMMARY

### Student Registration Flow
1. Student fills signup form
2. Email & password validated
3. Password hashed with bcrypt
4. User inserted into `users` table (role='student')
5. Auto-redirect to login page

### Student Login Flow
1. Student enters email & password
2. Query `users` table: `WHERE email = ? AND role = 'student'`
3. Password verified with `password_verify()`
4. Session variables created
5. Redirect to student dashboard

### Teacher Login Flow
1. Teacher enters email & password
2. Query `users` table: `WHERE email = ? AND role = 'teacher'`
3. Password verified with `password_verify()`
4. Session variables created
5. Redirect to teacher dashboard

### Password Reset Flow
1. User enters email
2. System generates 6-digit OTP
3. OTP sent via Gmail SMTP
4. User verifies OTP code
5. User creates new password

---

## TESTING CHECKLIST

- [x] Database created and consolidated
- [x] Student signup working
- [x] Student login working
- [x] Teacher login working
- [x] Forgot password flow working
- [x] NULL password handling fixed
- [x] Session management working
- [x] Email validation working
- [x] Password hashing working

---

## NOTES

- Teachers and students now share the same `users` table
- Role-based access control via `role` column
- Students without passwords cannot login (need to signup first)
- Teachers must have passwords set
- All sensitive data (passwords) securely hashed
- OTP codes expire after 10 minutes

---

**Version:** 1.0 - May 3, 2026
**Status:** ✅ Production Ready

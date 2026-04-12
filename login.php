<?php
/**
 * login.php
 * 
 * Teacher login page.
 * Displays login form; on POST, delegates to actions/do_login.php
 */

session_start();

// ── Already logged in? Redirect to dashboard ─────────────────────────
if (isset($_SESSION['teacher_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = isset($_GET['error']) ? (int)$_GET['error'] : 0;
$timeout = isset($_GET['timeout']) ? 1 : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login | Academic Management System</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        body {
            background: #0d1b2a;
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: #1b304c;
            border-radius: 12px;
            padding: 40px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }
        .login-card h1 {
            font-size: 28px;
            font-weight: 400;
            margin: 0 0 10px;
            text-align: center;
        }
        .login-card p {
            text-align: center;
            color: #92a2b8;
            margin: 0 0 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #e2e8f0;
        }
        .form-group input {
            width: 100%;
            box-sizing: border-box;
            background: #7b889b;
            border: 1px solid #5a6a7b;
            padding: 12px 16px;
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            outline: none;
        }
        .form-group input:focus {
            border-color: #1967d2;
        }
        .login-btn {
            width: 100%;
            background: #1967d2;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            transition: opacity 0.2s;
        }
        .login-btn:hover {
            opacity: 0.9;
        }
        .error-message {
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid rgba(255, 0, 0, 0.3);
            color: #ff6b6b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .timeout-message {
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.3);
            color: #ffc107;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .demo-creds {
            background: rgba(0, 200, 0, 0.08);
            border: 1px solid rgba(0, 200, 0, 0.2);
            padding: 12px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 13px;
            color: #90ee90;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1>AMS</h1>
            <p>Teacher Dashboard</p>

            <?php if ($error === 1): ?>
                <div class="error-message">
                    ❌ Invalid email or password. Please try again.
                </div>
            <?php elseif ($timeout): ?>
                <div class="timeout-message">
                    ⏱️ Your session expired. Please log in again.
                </div>
            <?php endif; ?>

            <form method="POST" action="actions/do_login.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="teacher@ams.com"
                        required
                    />
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="•••••••"
                        required
                    />
                </div>
                <button type="submit" class="login-btn">Sign In</button>
            </form>

            <div class="demo-creds">
                <strong>Demo Credentials:</strong><br/>
                Email: teacher@ams.com<br/>
                Password: password123
            </div>
        </div>
    </div>
</body>
</html>

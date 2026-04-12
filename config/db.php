<?php
/**
 * config/db.php
 * 
 * PDO database connection configuration.
 * Handles MySQL connection with proper error handling and charset.
 */

// ── Database Credentials ─────────────────────────────────────────────
// Edit these values to match your MySQL setup:
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'ams_db');
define('DB_USER', 'root');
define('DB_PASS', '');  // Leave empty if no password

// ── PDO Connection ───────────────────────────────────────────────────
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on DB errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,  // Use real prepared statements
        ]
    );
} catch (PDOException $e) {
    // Log error securely; never expose DB details to user in production
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(500);
    die('Database connection error. Please try again later.');
}

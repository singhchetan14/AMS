<?php
// Shared session/db loader for messaging API endpoints.
// Resolves $me_id from either student session (user_id) or teacher session
// (teacher_id) — both are users.id in the AMS schema.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/db.php';

$me_id = $_SESSION['user_id'] ?? $_SESSION['teacher_id'] ?? null;

if (!$me_id) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$me_id = (int) $me_id;

// Auto-create the messages table on first hit so the feature works without
// having to run the SQL file manually. Cheap because it's CREATE IF NOT EXISTS.
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `messages` (
          `id`          INT(11)      NOT NULL AUTO_INCREMENT,
          `sender_id`   INT(11)      NOT NULL,
          `receiver_id` INT(11)      NOT NULL,
          `body`        TEXT         NOT NULL,
          `is_read`     TINYINT(1)   NOT NULL DEFAULT 0,
          `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_receiver` (`receiver_id`, `is_read`),
          KEY `idx_thread`   (`sender_id`, `receiver_id`, `created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (Throwable $e) {
    // If creation fails, the API endpoints below will report the real error.
}

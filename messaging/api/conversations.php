<?php
// Return the list of people I've chatted with, plus last message and unread count.
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

try {
    // Pick everyone who has either sent me a message or received one from me,
    // and attach the latest message snippet + unread counter for each.
    $sql = "
        SELECT
            u.id, u.full_name, u.email, u.role,
            (SELECT body FROM messages m
              WHERE (m.sender_id = u.id AND m.receiver_id = :me)
                 OR (m.sender_id = :me AND m.receiver_id = u.id)
              ORDER BY m.created_at DESC, m.id DESC LIMIT 1) AS last_body,
            (SELECT created_at FROM messages m
              WHERE (m.sender_id = u.id AND m.receiver_id = :me)
                 OR (m.sender_id = :me AND m.receiver_id = u.id)
              ORDER BY m.created_at DESC, m.id DESC LIMIT 1) AS last_at,
            (SELECT COUNT(*) FROM messages m
              WHERE m.sender_id = u.id
                AND m.receiver_id = :me
                AND m.is_read = 0) AS unread
          FROM users u
         WHERE u.id IN (
            SELECT receiver_id FROM messages WHERE sender_id = :me
            UNION
            SELECT sender_id   FROM messages WHERE receiver_id = :me
         )
         ORDER BY last_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':me' => $me_id]);

    echo json_encode([
        'ok'            => true,
        'conversations' => $stmt->fetchAll(),
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server_error', 'message' => $e->getMessage()]);
}

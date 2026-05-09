<?php
// Return how many unread messages I currently have (used by the badge).
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

try {
    // Count messages addressed to me that haven't been opened yet.
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0
    ");
    $stmt->execute([$me_id]);

    echo json_encode([
        'ok'    => true,
        'count' => (int) $stmt->fetchColumn(),
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server_error', 'message' => $e->getMessage()]);
}

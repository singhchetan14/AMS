<?php
// Send a chat message from the logged-in user to another user.
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

try {
    // Only POST is allowed for sending.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'method_not_allowed']);
        exit;
    }

    // Read receiver and message body from the form.
    $receiver_id = (int) ($_POST['receiver_id'] ?? 0);
    $body        = trim($_POST['body'] ?? '');

    // Reject empty receiver or empty message.
    if (!$receiver_id || $body === '') {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_input']);
        exit;
    }

    // Don't allow sending a message to yourself.
    if ($receiver_id === $me_id) {
        http_response_code(400);
        echo json_encode(['error' => 'cannot_message_self']);
        exit;
    }

    // Make sure the recipient actually exists in the users table.
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$receiver_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'recipient_not_found']);
        exit;
    }

    // Insert the new message row.
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, body) VALUES (?, ?, ?)");
    $stmt->execute([$me_id, $receiver_id, $body]);

    echo json_encode([
        'ok'         => true,
        'id'         => (int) $pdo->lastInsertId(),
        'created_at' => date('Y-m-d H:i:s'),
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server_error', 'message' => $e->getMessage()]);
}

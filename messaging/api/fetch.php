<?php
// Fetch the full chat thread between me and one other user.
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

try {
    // The other user's ID comes in via ?with=<id>.
    $with = (int) ($_GET['with'] ?? 0);
    if (!$with) {
        http_response_code(400);
        echo json_encode(['error' => 'missing_with']);
        exit;
    }

    // Mark messages from peer -> me as read whenever I open the thread.
    $pdo->prepare("
        UPDATE messages
           SET is_read = 1
         WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
    ")->execute([$with, $me_id]);

    // Pull every message exchanged between the two of us, oldest first.
    $stmt = $pdo->prepare("
        SELECT id, sender_id, receiver_id, body, is_read, created_at
          FROM messages
         WHERE (sender_id = ? AND receiver_id = ?)
            OR (sender_id = ? AND receiver_id = ?)
         ORDER BY created_at ASC, id ASC
    ");
    $stmt->execute([$me_id, $with, $with, $me_id]);

    // Also load basic info about the peer so the UI can show their name and role.
    $peerStmt = $pdo->prepare("SELECT id, full_name, email, role FROM users WHERE id = ? LIMIT 1");
    $peerStmt->execute([$with]);
    $peer = $peerStmt->fetch();

    echo json_encode([
        'ok'       => true,
        'me_id'    => $me_id,
        'peer'     => $peer ?: null,
        'messages' => $stmt->fetchAll(),
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server_error', 'message' => $e->getMessage()]);
}

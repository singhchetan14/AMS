<?php
// Mark all messages from a given peer as read.
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

// Peer user id can come from POST or GET.
$with = (int) ($_POST['with'] ?? $_GET['with'] ?? 0);
if (!$with) {
    http_response_code(400);
    echo json_encode(['error' => 'missing_with']);
    exit;
}

// Flip is_read = 1 for every message that peer sent me.
$stmt = $pdo->prepare("
    UPDATE messages
       SET is_read = 1
     WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
");
$stmt->execute([$with, $me_id]);

echo json_encode([
    'ok'      => true,
    'updated' => $stmt->rowCount(),
]);

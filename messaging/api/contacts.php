<?php
// Search users by full name or email — anyone can find anyone (except self).
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

try {
    // Search query from the sidebar input.
    $q = trim($_GET['q'] ?? '');
    if ($q === '') {
        echo json_encode(['ok' => true, 'results' => []]);
        exit;
    }

    // Match email or full name with a LIKE pattern, exclude self.
    $like = '%' . $q . '%';
    $stmt = $pdo->prepare("
        SELECT id, full_name, email, role
          FROM users
         WHERE id <> :me
           AND (email LIKE :q OR full_name LIKE :q)
         ORDER BY full_name ASC, email ASC
         LIMIT 20
    ");
    $stmt->execute([':me' => $me_id, ':q' => $like]);

    echo json_encode(['ok' => true, 'results' => $stmt->fetchAll()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server_error', 'message' => $e->getMessage()]);
}

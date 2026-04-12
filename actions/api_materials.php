<?php
/**
 * api_materials.php
 *
 * AJAX endpoints for editing and deleting materials from the dashboard.
 */
require_once __DIR__ . '/../config/db.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$teacherId = (int)$_SESSION['teacher_id'];
$action = $_GET['action'] ?? '';

if ($action === 'delete') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);

    $stmt = $pdo->prepare("SELECT filename FROM materials WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$id, $teacherId]);
    $material = $stmt->fetch();

    if ($material) {
        $path = __DIR__ . '/../uploads/materials/' . $material['filename'];
        if (file_exists($path)) {
            @unlink($path);
        }
        $pdo->prepare("DELETE FROM materials WHERE id = ? AND teacher_id = ?")->execute([$id, $teacherId]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Material not found or unauthorized']);
    }
    exit;
}

if ($action === 'edit') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);
    $title = trim($data['title'] ?? '');

    if ($id && !empty($title)) {
        $stmt = $pdo->prepare("UPDATE materials SET title = ? WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$title, $id, $teacherId]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No changes made or unauthorized']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);

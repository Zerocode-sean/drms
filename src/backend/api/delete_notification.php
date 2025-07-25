<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}
require_once '../config/db_config.php';
$data = json_decode(file_get_contents('php://input'), true);
$notification_id = intval($data['id'] ?? 0);
if (!$notification_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Notification ID required']);
    exit();
}
try {
    $stmt = $pdo->prepare('DELETE FROM notifications WHERE id = ?');
    $stmt->execute([$notification_id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 
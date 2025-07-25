<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}
header('Content-Type: application/json');
require_once '../config/db_config.php';
$data = json_decode(file_get_contents('php://input'), true);
$request_id = $data['id'] ?? null;
if (!$request_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Request ID required']);
    exit();
}
$stmt = $pdo->prepare("UPDATE requests1 SET status = 'Rejected', updated_at = NOW() WHERE id = ?");
if ($stmt->execute([$request_id])) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to reject request']);
} 
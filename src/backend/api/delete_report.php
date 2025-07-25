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
$report_id = intval($data['id'] ?? 0);
if (!$report_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Report ID required']);
    exit();
}
try {
    $stmt = $pdo->prepare('DELETE FROM reports WHERE id = ?');
    $stmt->execute([$report_id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 
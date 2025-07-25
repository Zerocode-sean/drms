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
$driver_id = intval($data['id'] ?? 0);
if (!$driver_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Driver ID required']);
    exit();
}
try {
    $pdo->beginTransaction();
    // Delete assignments for this driver
    $stmt = $pdo->prepare('DELETE FROM assignments WHERE driver_id = ?');
    $stmt->execute([$driver_id]);
    // Optionally delete notifications related to this driver (if needed)
    // $stmt = $pdo->prepare('DELETE FROM notifications WHERE sender_id = ?');
    // $stmt->execute([$driver_id]);
    // Delete driver
    $stmt = $pdo->prepare('DELETE FROM drivers WHERE id = ?');
    $stmt->execute([$driver_id]);
    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 
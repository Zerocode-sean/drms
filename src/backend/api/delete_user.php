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
$user_id = intval($data['id'] ?? 0);
if (!$user_id) {
    http_response_code(400);
    echo json_encode(['error' => 'User ID required']);
    exit();
}
try {
    $pdo->beginTransaction();
    // Delete notifications where user is sender or receiver
    $stmt = $pdo->prepare('DELETE FROM notifications WHERE sender_id = ? OR receiver_id = ?');
    $stmt->execute([$user_id, $user_id]);
    // Delete assignments if user is a driver (optional, if assignments table has user_id)
    // $stmt = $pdo->prepare('DELETE FROM assignments WHERE driver_id = ?');
    // $stmt->execute([$user_id]);
    // Delete user
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 
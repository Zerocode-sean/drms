<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}
header('Content-Type: application/json');
require_once '../config/db_config.php';
$stmt = $pdo->prepare('SELECT a.id as task_id, u.username as driver, a.request_id, a.status FROM assignments a JOIN drivers d ON a.driver_id = d.id JOIN users u ON d.user_id = u.id ORDER BY a.assigned_at DESC LIMIT 10');
$stmt->execute();
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)); 
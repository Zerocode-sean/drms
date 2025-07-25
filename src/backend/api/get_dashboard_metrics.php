<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}
header('Content-Type: application/json');
require_once '../config/db_config.php';
try {
    // Total requests
    $stmt = $pdo->query('SELECT COUNT(*) FROM requests1');
    $total_requests = $stmt->fetchColumn();
    // Pending approvals
    $stmt = $pdo->query("SELECT COUNT(*) FROM requests1 WHERE status = 'Pending'");
    $pending_approvals = $stmt->fetchColumn();
    // Active drivers
    $stmt = $pdo->query('SELECT COUNT(*) FROM drivers WHERE is_active = 1');
    $active_drivers = $stmt->fetchColumn();
    // Active users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'resident'");
    $active_users = $stmt->fetchColumn();
    echo json_encode([
        'total_requests' => (int)$total_requests,
        'pending_approvals' => (int)$pending_approvals,
        'active_drivers' => (int)$active_drivers,
        'active_users' => (int)$active_users
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
} 
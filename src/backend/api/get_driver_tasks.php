<?php
require_once '../config/db_config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$driver_user_id = $_SESSION['user_id'];

// Get the driver's id from the drivers table
$driver_id = null;
$stmt = $mysqli->prepare('SELECT id FROM drivers WHERE user_id = ?');
$stmt->bind_param('i', $driver_user_id);
$stmt->execute();
$stmt->bind_result($driver_id);
$stmt->fetch();
$stmt->close();

if (!$driver_id) {
    echo json_encode([]);
    exit;
}

// Fetch assigned requests for this driver
$sql = "SELECT a.id as assignment_id, r.id as request_id, r.status, r.created_at, r.waste_type as document, r.location as address, r.preferred_date as scheduled_time, u.username as user_name, u.phone as user_phone, u.email as user_email FROM assignments a JOIN requests1 r ON a.request_id = r.id JOIN users u ON r.user_id = u.id WHERE a.driver_id = ? ORDER BY r.created_at DESC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $driver_id);
$stmt->execute();
$result = $stmt->get_result();
$tasks = [];
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}
header('Content-Type: application/json');
echo json_encode($tasks); 
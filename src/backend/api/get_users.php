<?php
require_once '../config/db_config.php';

$role = isset($_GET['role']) ? $_GET['role'] : 'resident';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$sql = "SELECT id, username, email, phone, role FROM users WHERE role = ? ORDER BY username ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $role);
$stmt->execute();
$result = $stmt->get_result();
$users = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
header('Content-Type: application/json');
echo json_encode($users);
$conn->close(); 
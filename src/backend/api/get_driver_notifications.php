<?php
require_once '../config/db_config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get filter parameters
$sender_id = isset($_GET['sender_id']) ? intval($_GET['sender_id']) : 0;
$receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : 0;

// Build SQL with optional filters
$sql = "SELECT n.id, n.message, n.sent_at, n.is_read,
        sender.id AS sender_id, sender.username AS sender_username, sender.email AS sender_email,
        receiver.id AS receiver_id, receiver.username AS receiver_username, receiver.email AS receiver_email
    FROM notifications n
    JOIN users sender ON n.sender_id = sender.id AND sender.role = 'driver'
    JOIN users receiver ON n.receiver_id = receiver.id AND receiver.role = 'resident'
    WHERE 1=1";
$params = [];
$types = '';
if ($sender_id) {
    $sql .= " AND sender.id = ?";
    $params[] = $sender_id;
    $types .= 'i';
}
if ($receiver_id) {
    $sql .= " AND receiver.id = ?";
    $params[] = $receiver_id;
    $types .= 'i';
}
$sql .= " ORDER BY n.sent_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}
header('Content-Type: application/json');
echo json_encode($notifications);
$conn->close(); 
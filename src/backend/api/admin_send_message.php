<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/db_config.php';
require_once '../models/notification.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$receiver_id = intval($data['receiver_id'] ?? 0);
$message = trim($data['message'] ?? '');

if (!$receiver_id || !$message) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$sender_id = $_SESSION['user_id'];
add_notification($conn, $sender_id, $receiver_id, $message);

$conn->close();
echo json_encode(['success' => true, 'message' => 'Message sent successfully']); 
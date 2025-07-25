<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$file = __DIR__ . '/../config/db_config.php';
if (!file_exists($file)) {
    die(json_encode(['success' => false, 'error' => 'db_config.php not found at: ' . $file]));
}
require_once $file;
if (!defined('DB_HOST')) {
    die(json_encode(['success' => false, 'error' => 'DB_HOST is not defined after including: ' . $file]));
}

require_once __DIR__ . '/../models/notification.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) throw new Exception($conn->connect_error);
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) throw new Exception('Invalid JSON');
    $request_id = intval($data['request_id'] ?? 0);
    $new_status = trim($data['status'] ?? '');
    if (!$request_id || !$new_status) throw new Exception('Missing required fields');
    // Update request status
    $stmt = $conn->prepare('UPDATE requests1 SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $new_status, $request_id);
    if ($stmt->execute()) {
        // Get resident (request owner)
        $res = $conn->query("SELECT user_id FROM requests1 WHERE id = $request_id");
        if ($row = $res->fetch_assoc()) {
            $resident_id = $row['user_id'];
            add_notification($conn, 1, $resident_id, "Your request #$request_id status changed to $new_status.");
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 
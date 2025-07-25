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
    $driver_id = intval($data['driver_id'] ?? 0);
    if (!$request_id || !$driver_id) throw new Exception('Missing required fields');
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Assign driver to request
        $stmt = $conn->prepare('UPDATE requests1 SET status = "Assigned", assigned_driver = ? WHERE id = ?');
        $stmt->bind_param('ii', $driver_id, $request_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to update request: ' . $stmt->error);
        }
        
        // Create assignment record
        $assign_stmt = $conn->prepare('INSERT INTO assignments (request_id, driver_id, status, assigned_at) VALUES (?, ?, "Assigned", NOW())');
        $assign_stmt->bind_param('ii', $request_id, $driver_id);
        if (!$assign_stmt->execute()) {
            throw new Exception('Failed to create assignment: ' . $assign_stmt->error);
        }
        
        // Get driver's user_id for notification
        $driver_user_result = $conn->query("SELECT user_id FROM drivers WHERE id = $driver_id");
        if ($driver_user_row = $driver_user_result->fetch_assoc()) {
            $driver_user_id = $driver_user_row['user_id'];
            add_notification($conn, 1, $driver_user_id, "You have been assigned to request #$request_id.");
        }
        
        // Notify resident
        $res = $conn->query("SELECT user_id FROM requests1 WHERE id = $request_id");
        if ($row = $res->fetch_assoc()) {
            $resident_id = $row['user_id'];
            add_notification($conn, 1, $resident_id, "A driver has been assigned to your request #$request_id.");
        }
        
        // Commit transaction
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Task assigned successfully!']);
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 
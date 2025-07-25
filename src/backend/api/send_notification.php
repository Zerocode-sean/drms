<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once '../config/db_config.php';
require_once '../models/notification.php';

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
    
    // Handle both old format (receiver_id, message) and new format (request_id, type, message)
    if (isset($data['request_id'])) {
        // New format - get user from request
        $request_id = intval($data['request_id']);
        $type = trim($data['type'] ?? 'general');
        $custom_message = trim($data['message'] ?? '');
        
        if (!$request_id) throw new Exception('Missing request ID');
        
        // Get user_id from request
        $stmt = $conn->prepare('SELECT user_id FROM requests1 WHERE id = ?');
        $stmt->bind_param('i', $request_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Request not found');
        }
        
        $row = $result->fetch_assoc();
        $receiver_id = $row['user_id'];
        
        // Use custom message if provided, otherwise use default for type
        if ($custom_message) {
            $message = $custom_message;
        } else {
            $default_messages = [
                'missed' => 'Your waste collection was missed. We will reschedule it for the next available slot.',
                'delayed' => 'Your waste collection has been delayed. We will arrive shortly.',
                'completed' => 'Your waste collection has been completed successfully.',
                'rescheduled' => 'Your waste collection has been rescheduled.',
                'issue' => 'There was an issue with your waste collection. We will contact you shortly.',
                'general' => 'You have a new notification regarding your waste collection request.'
            ];
            $message = $default_messages[$type] ?? $default_messages['general'];
        }
        
    } else {
        // Old format - direct receiver_id and message
        $receiver_id = intval($data['receiver_id'] ?? 0);
        $message = trim($data['message'] ?? '');
        
        if (!$receiver_id || !$message) throw new Exception('Missing required fields');
    }
    
    $sender_id = $_SESSION['user_id'];
    add_notification($conn, $sender_id, $receiver_id, $message);
    
    echo json_encode(['success' => true, 'message' => 'Notification sent successfully']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 
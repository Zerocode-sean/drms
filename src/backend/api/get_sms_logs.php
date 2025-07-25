<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'driver'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../config/db_config.php';

try {
    // Get SMS logs from database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed');
    }
    
    $sql = "SELECT 
                s.id,
                s.recipient_phone as phone,
                s.message,
                s.status,
                s.twilio_sid as message_id,
                s.error_message,
                s.created_at,
                u.username as recipient_name,
                sender.username as sent_by_name
            FROM sms_logs s
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN users sender ON s.sent_by = sender.id
            ORDER BY s.created_at DESC 
            LIMIT 50";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = [
            'id' => $row['message_id'] ?? $row['id'],
            'to' => $row['phone'],
            'message' => $row['message'],
            'status' => $row['status'],
            'timestamp' => $row['created_at'],
            'recipient_name' => $row['recipient_name'] ?? 'Unknown User',
            'sent_by' => $row['sent_by_name'] ?? 'System',
            'error' => $row['error_message']
        ];
    }
    
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'logs' => $logs,
        'count' => count($logs)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>

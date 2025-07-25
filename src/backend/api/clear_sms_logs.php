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
    // Clear SMS logs from database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed');
    }
    
    $result = $conn->query("DELETE FROM sms_logs");
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'SMS logs cleared successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to clear logs: ' . $conn->error
        ]);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>

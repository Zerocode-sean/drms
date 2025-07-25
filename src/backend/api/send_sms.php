<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'driver'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../config/db_config.php';
require_once '../config/mock_sms_config.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = intval($data['user_id'] ?? 0);
    $message = trim($data['message'] ?? '');
    
    if (!$user_id || !$message) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields: user_id and message']);
        exit();
    }
    
    // Get user details from database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed');
    }
    
    $stmt = $conn->prepare("SELECT username, email, phone FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        $stmt->close();
        $conn->close();
        exit();
    }
    
    $user = $result->fetch_assoc();
    $phone = $user['phone'];
    
    // If no phone number, use demo number for testing
    if (empty($phone)) {
        $phone = '+254700000000'; // Demo number for testing
    }
    
    // Send SMS using Mock SMS Service
    $smsService = new MockSMSService();
    $smsResult = $smsService->sendSMS($phone, $message, $_SESSION['user_id']);
    
    if ($smsResult['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'SMS sent successfully! (Development Mode - Check SMS logs)',
            'message_id' => $smsResult['message_id'],
            'recipient' => $user['username'],
            'phone' => $smsResult['to'],
            'service' => 'MockSMS',
            'note' => 'This is a simulated SMS for development. Check the SMS logs page to view sent messages.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => $smsResult['error']]);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?> 
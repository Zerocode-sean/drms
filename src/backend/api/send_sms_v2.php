<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'driver'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../config/db_config.php';
require_once '../config/sms_gateway_manager.php';

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
    
    // Check if user has a phone number
    if (empty($phone)) {
        http_response_code(400);
        echo json_encode([
            'error' => 'User does not have a phone number registered',
            'suggestion' => 'Please ask the user to update their profile with a valid phone number'
        ]);
        $stmt->close();
        $conn->close();
        exit();
    }
    
    // Add sender signature
    $senderName = $_SESSION['username'] ?? 'DRMS';
    $fullMessage = $message . "\n\n- " . $senderName . " (DRMS)";
    
    // Send SMS using the gateway manager with fallback
    $smsResult = sendSMSWithFallback($phone, $fullMessage);
    
    if ($smsResult['success']) {
        // Log successful SMS to database with gateway information
        $logStmt = $conn->prepare("INSERT INTO sms_logs (user_id, recipient_phone, message, status, sent_by, gateway_used, message_id, used_fallback, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        if ($logStmt) {
            $status = 'sent';
            $gateway = $smsResult['gateway'] ?? 'unknown';
            $message_id = $smsResult['message_id'] ?? $smsResult['sid'] ?? null;
            $used_fallback = $smsResult['used_fallback'] ?? false;
            $recipient_phone = $smsResult['to'] ?? $phone;
            
            $logStmt->bind_param('issssssi', 
                $user_id, 
                $recipient_phone, 
                $fullMessage, 
                $status, 
                $_SESSION['user_id'], 
                $gateway,
                $message_id,
                $used_fallback
            );
            $logStmt->execute();
            $logStmt->close();
        }
        
        $gateway_name = ucfirst($smsResult['gateway'] ?? 'SMS Gateway');
        $fallback_message = ($smsResult['used_fallback'] ?? false) ? ' (using fallback)' : '';
        
        echo json_encode([
            'success' => true,
            'message' => "SMS sent successfully via {$gateway_name}!{$fallback_message}",
            'message_id' => $smsResult['message_id'] ?? $smsResult['sid'] ?? null,
            'recipient' => $user['username'],
            'phone' => $recipient_phone,
            'service' => $gateway_name,
            'gateway' => $smsResult['gateway'] ?? 'unknown',
            'used_fallback' => $smsResult['used_fallback'] ?? false,
            'cost' => $smsResult['cost'] ?? 'Unknown',
            'status' => $smsResult['status'] ?? 'Sent'
        ]);
    } else {
        // Log failed SMS to database with gateway information
        $logStmt = $conn->prepare("INSERT INTO sms_logs (user_id, recipient_phone, message, status, sent_by, gateway_used, error_message, used_fallback, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        if ($logStmt) {
            $status = 'failed';
            $gateway = $smsResult['gateway'] ?? 'unknown';
            $used_fallback = $smsResult['used_fallback'] ?? false;
            $error_message = $smsResult['error'] ?? 'Unknown error';
            
            $logStmt->bind_param('issssssi', 
                $user_id, 
                $phone, 
                $fullMessage, 
                $status, 
                $_SESSION['user_id'], 
                $gateway,
                $error_message,
                $used_fallback
            );
            $logStmt->execute();
            $logStmt->close();
        }
        
        $gateway_name = ucfirst($smsResult['gateway'] ?? 'SMS Gateway');
        $fallback_info = ($smsResult['used_fallback'] ?? false) ? ' (fallback also failed)' : '';
        
        http_response_code(500);
        echo json_encode([
            'error' => 'Failed to send SMS: ' . ($smsResult['error'] ?? 'Unknown error'),
            'service' => $gateway_name . $fallback_info,
        ]);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>

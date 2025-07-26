<?php
// Optimized request submission with timeout handling and caching
require_once __DIR__ . '/../config/performance.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/cached_api.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Set execution timeout and memory limits
set_time_limit(15); // Reduced to 15 seconds max
ini_set('memory_limit', '64M');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    // Use the existing optimized connection from db_config.php
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('Database connection failed');
    }
    
    // Set connection timeout for faster failure
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) throw new Exception('Invalid JSON');
    
    $user_id = $_SESSION['user_id'];
    $waste_type = trim($data['waste_type'] ?? '');
    $preferred_date = trim($data['preferred_date'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $location = trim($data['location'] ?? '');
    $notes = trim($data['notes'] ?? '');
    $urgency = trim($data['urgency'] ?? 'Normal');
    $resolved_address = trim($data['resolved_address'] ?? '');
    $address_details = trim($data['address_details'] ?? '');
    
    // Validate required fields
    if (!$waste_type || !$preferred_date) {
        throw new Exception('Missing required fields: waste_type and preferred_date are required');
    }
    
    // Update user's phone number if provided (use consistent connection variable)
    if (!empty($phone)) {
        $updateUserSql = "UPDATE users SET phone = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateUserSql);
        if ($updateStmt) {
            $updateStmt->bind_param('si', $phone, $user_id);
            $updateStmt->execute();
            $updateStmt->close();
        }
    }
    
    $document = 'Waste collection request';
    
    // Use correct table name: requests (not requests1)  
    $sql = "INSERT INTO requests (user_id, document, location, waste_type, preferred_date, notes, status, urgency, resolved_address, address_details) VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
    
    $stmt->bind_param('issssssss', $user_id, $document, $location, $waste_type, $preferred_date, $notes, $urgency, $resolved_address, $address_details);
    
    if ($stmt->execute()) {
        $request_id = $conn->insert_id;
        
        // Optimized notification handling - use a simple insert without complex loops
        $notificationSql = "INSERT INTO notifications (user_id, recipient_id, message, type, is_read, created_at) SELECT ?, id, ?, 'request', 0, NOW() FROM users WHERE role='admin'";
        $notifyStmt = $conn->prepare($notificationSql);
        if ($notifyStmt) {
            $notifyMessage = "A new waste collection request (ID: $request_id) has been submitted.";
            $notifyStmt->bind_param('is', $user_id, $notifyMessage);
            $notifyStmt->execute();
            $notifyStmt->close();
        }
        
        // Invalidate relevant caches
        invalidate_request_cache($user_id);
        
        echo json_encode([
            'success' => true, 
            'request_id' => $request_id,
            'message' => 'Request submitted successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 
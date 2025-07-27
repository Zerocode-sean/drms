<?php
// Enhanced place_request.php with better error handling and field validation
require_once __DIR__ . '/../config/performance.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db_config.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Set execution timeout and memory limits
set_time_limit(15);
ini_set('memory_limit', '64M');

function sendResponse($success, $data = null, $error = null, $httpCode = 200) {
    http_response_code($httpCode);
    $response = ['success' => $success];
    if ($data) $response = array_merge($response, $data);
    if ($error) $response['error'] = $error;
    echo json_encode($response);
    exit;
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    sendResponse(false, null, 'Unauthorized', 401);
}

try {
    // Verify database connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('Database connection failed');
    }
    
    // Get and validate input data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON input');
    }
    
    // Extract and validate required fields
    $user_id = $_SESSION['user_id'];
    $waste_type = trim($data['waste_type'] ?? '');
    $preferred_date = trim($data['preferred_date'] ?? '');
    
    // Required field validation
    if (empty($waste_type)) {
        throw new Exception('Waste type is required');
    }
    
    if (empty($preferred_date)) {
        throw new Exception('Preferred date is required');
    }
    
    // Validate and clean date format
    $dateTime = DateTime::createFromFormat('Y-m-d\TH:i', $preferred_date);
    if (!$dateTime) {
        $dateTime = DateTime::createFromFormat('Y-m-d', $preferred_date);
        if (!$dateTime) {
            throw new Exception('Invalid date format. Use YYYY-MM-DD or YYYY-MM-DDTHH:MM');
        }
    }
    $cleanDate = $dateTime->format('Y-m-d');
    
    // Extract optional fields with defaults
    $phone = trim($data['phone'] ?? '');
    $location = trim($data['location'] ?? 'Not specified');
    $notes = trim($data['notes'] ?? '');
    $urgency = trim($data['urgency'] ?? 'Normal');
    $resolved_address = trim($data['resolved_address'] ?? '');
    $address_details = trim($data['address_details'] ?? '');
    $document = 'Waste collection request';
    
    // Validate urgency level
    $validUrgency = ['Normal', 'High', 'Low'];
    if (!in_array($urgency, $validUrgency)) {
        $urgency = 'Normal';
    }
    
    // Update user's phone number if provided
    if (!empty($phone) && preg_match('/^0\d{9}$/', $phone)) {
        $updateUserSql = "UPDATE users SET phone = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateUserSql);
        if ($updateStmt) {
            $updateStmt->bind_param('si', $phone, $user_id);
            $updateStmt->execute();
            $updateStmt->close();
        }
    }
    
    // Check if all required columns exist in the table
    $tableCheck = $conn->query("DESCRIBE requests");
    $existingColumns = [];
    while ($row = $tableCheck->fetch_assoc()) {
        $existingColumns[] = $row['Field'];
    }
    
    // Build dynamic SQL based on available columns
    $columns = ['user_id', 'document', 'waste_type', 'preferred_date', 'status'];
    $values = ['?', '?', '?', '?', '?'];
    $params = [$user_id, $document, $waste_type, $cleanDate, 'Pending'];
    $types = 'issss';
    
    // Add optional columns if they exist
    $optionalFields = [
        'location' => $location,
        'notes' => $notes,
        'urgency' => $urgency,
        'resolved_address' => $resolved_address,
        'address_details' => $address_details
    ];
    
    foreach ($optionalFields as $column => $value) {
        if (in_array($column, $existingColumns)) {
            $columns[] = $column;
            $values[] = '?';
            $params[] = $value;
            $types .= 's';
        }
    }
    
    // Build and execute the SQL
    $sql = "INSERT INTO requests (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ")";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        $request_id = $conn->insert_id;
        
        // Add notification for admins (if notifications table exists)
        $notificationCheck = $conn->query("SHOW TABLES LIKE 'notifications'");
        if ($notificationCheck->num_rows > 0) {
            $notificationSql = "INSERT INTO notifications (user_id, recipient_id, message, type, is_read, created_at) 
                               SELECT ?, id, ?, 'request', 0, NOW() FROM users WHERE role='admin'";
            $notifyStmt = $conn->prepare($notificationSql);
            if ($notifyStmt) {
                $notifyMessage = "New waste collection request (ID: $request_id) submitted by user ID: $user_id";
                $notifyStmt->bind_param('is', $user_id, $notifyMessage);
                $notifyStmt->execute();
                $notifyStmt->close();
            }
        }
        
        sendResponse(true, [
            'request_id' => $request_id,
            'message' => 'Request submitted successfully',
            'waste_type' => $waste_type,
            'preferred_date' => $cleanDate,
            'status' => 'Pending'
        ]);
        
    } else {
        throw new Exception('Failed to insert request: ' . $stmt->error);
    }
    
} catch (Exception $e) {
    error_log("Place Request Error: " . $e->getMessage());
    sendResponse(false, null, $e->getMessage(), 500);
}
?>

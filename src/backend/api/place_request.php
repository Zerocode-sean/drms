<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Debug: Check if db_config.php exists at the expected path
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
    
    // Update user's phone number if provided
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
    
    // Updated SQL with address_details column
    $sql = "INSERT INTO requests1 (user_id, document, location, waste_type, preferred_date, notes, status, urgency, resolved_address, address_details) VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
    
    $stmt->bind_param('issssssss', $user_id, $document, $location, $waste_type, $preferred_date, $notes, $urgency, $resolved_address, $address_details);
    
    if ($stmt->execute()) {
        $request_id = $conn->insert_id;
        
        // Notify all admins
        $adminResult = $conn->query("SELECT id FROM users WHERE role='admin'");
        while ($admin = $adminResult->fetch_assoc()) {
            add_notification($conn, $user_id, $admin['id'], "A new waste collection request (ID: $request_id) has been submitted.");
        }
        
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
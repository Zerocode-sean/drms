<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}
header('Content-Type: application/json');

try {
    // Use consistent PDO connection like dashboard metrics
    $pdo = new PDO("mysql:host=localhost;dbname=drms2", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $request_id = $data['id'] ?? null;
    
    if (!$request_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Request ID required']);
        exit();
    }
    
    // Update the request status in the correct table
    $stmt = $pdo->prepare("UPDATE requests SET status = 'Approved' WHERE id = ?");
    
    if ($stmt->execute([$request_id])) {
        // Log the approval for debugging
        error_log("Request $request_id approved successfully");
        echo json_encode(['success' => true, 'message' => 'Request approved successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to approve request']);
    }
    
} catch (Exception $e) {
    error_log("Approve request error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} 
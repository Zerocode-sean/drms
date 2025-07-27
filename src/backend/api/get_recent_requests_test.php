<?php
// Test version of get_recent_requests.php without authentication
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/db_config.php';

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Get all requests with user information (for testing - no auth required)
    $sql = "SELECT r.id, r.location, r.waste_type, r.preferred_date, r.status, r.notes, r.address_details, r.created_at,
                   u.username, u.email, u.phone, r.assigned_driver,
                   COALESCE(d.name, du.username) as driver_name
            FROM requests1 r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN drivers d ON r.assigned_driver = d.id
            LEFT JOIN users du ON d.user_id = du.id
            ORDER BY r.created_at DESC
            LIMIT 50";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'requests' => $requests,
        'count' => count($requests)
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>

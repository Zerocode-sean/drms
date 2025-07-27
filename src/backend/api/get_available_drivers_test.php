<?php
// Test version of get_available_drivers.php without authentication
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
    
    // Get all available drivers (for testing - no auth required)
    // Join with users table to get name and contact info
    $sql = "SELECT d.id, 
                   COALESCE(d.name, u.username) as name,
                   COALESCE(d.email, u.email) as email, 
                   COALESCE(d.phone, u.phone) as phone,
                   d.license_number, 
                   d.vehicle_number, 
                   d.status
            FROM drivers d
            LEFT JOIN users u ON d.user_id = u.id 
            WHERE d.status = 'available'
            ORDER BY COALESCE(d.name, u.username)";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $drivers = [];
    while ($row = $result->fetch_assoc()) {
        $drivers[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'drivers' => $drivers,
        'count' => count($drivers)
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

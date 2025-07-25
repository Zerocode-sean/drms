<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/db_config.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Get all active drivers with their details
    $sql = "SELECT d.id, d.name, d.phone, d.vehicle_type, d.capacity, d.current_load, d.status,
                   u.username, u.email
            FROM drivers d
            JOIN users u ON d.user_id = u.id
            WHERE d.status = 'active'
            ORDER BY d.current_load ASC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $drivers = [];
    while ($row = $result->fetch_assoc()) {
        $drivers[] = [
            'id' => $row['id'],
            'name' => $row['name'] ?: $row['username'],
            'phone' => $row['phone'],
            'vehicle_type' => $row['vehicle_type'],
            'capacity' => $row['capacity'],
            'current_load' => $row['current_load'],
            'status' => $row['status'],
            'username' => $row['username'],
            'email' => $row['email'],
            'available_capacity' => $row['capacity'] - $row['current_load']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'drivers' => $drivers,
        'total_drivers' => count($drivers)
    ]);
    
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
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
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed");
    }
    
    // Get all active drivers with their details (compatible with actual schema)
    $sql = "SELECT 
                d.id,
                u.username as name,
                COALESCE(u.phone, 'N/A') as phone,
                COALESCE(d.vehicle_type, 'Waste Collection Vehicle') as vehicle_type,
                10 as capacity,
                0 as current_load,
                'active' as status,
                u.username,
                u.email,
                d.license_number,
                d.is_active
            FROM drivers d
            JOIN users u ON d.user_id = u.id
            WHERE d.is_active = 1
            ORDER BY u.username ASC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $drivers = [];
    while ($row = $result->fetch_assoc()) {
        $capacity = (int)$row['capacity'];
        $currentLoad = (int)$row['current_load'];
        
        $drivers[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'phone' => $row['phone'],
            'vehicle_type' => $row['vehicle_type'],
            'capacity' => $capacity,
            'current_load' => $currentLoad,
            'status' => $row['status'],
            'username' => $row['username'],
            'email' => $row['email'],
            'license_number' => $row['license_number'],
            'available_capacity' => $capacity - $currentLoad,
            'is_available' => ($capacity - $currentLoad) > 0
        ];
    }
    
    // Success response
    echo json_encode([
        'success' => true,
        'drivers' => $drivers,
        'total_drivers' => count($drivers),
        'message' => count($drivers) > 0 ? 'Drivers retrieved successfully' : 'No active drivers available'
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
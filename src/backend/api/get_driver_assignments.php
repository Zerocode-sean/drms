<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=drms2", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get driver assignments with request and driver details
    $stmt = $pdo->query("
        SELECT 
            a.id as task_id,
            a.status,
            a.assigned_at,
            r.id as request_id,
            r.document,
            r.waste_type,
            r.location,
            d.name as driver_name,
            u.username as driver
        FROM assignments a
        JOIN requests r ON a.request_id = r.id
        JOIN drivers d ON a.driver_id = d.id
        LEFT JOIN users u ON d.user_id = u.id
        ORDER BY a.assigned_at DESC
        LIMIT 20
    ");
    
    $assignments = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $assignments[] = [
            'task_id' => $row['task_id'],
            'request_id' => $row['request_id'],
            'driver' => $row['driver_name'] ?: $row['driver'],
            'document' => $row['document'] ?: $row['waste_type'],
            'location' => $row['location'],
            'status' => $row['status'],
            'assigned_at' => $row['assigned_at']
        ];
    }
    
    echo json_encode($assignments);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 
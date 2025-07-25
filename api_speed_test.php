<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Track execution time
$start_time = microtime(true);

try {
    // Test database connection
    require_once __DIR__ . '/src/backend/config/db_config.php';
    
    $db_time = microtime(true);
    
    // Simple test query
    $result = $conn->query("SELECT COUNT(*) as count FROM users LIMIT 1");
    $row = $result->fetch_assoc();
    
    $query_time = microtime(true);
    
    echo json_encode([
        'success' => true,
        'message' => 'API is working',
        'database_connected' => true,
        'user_count' => $row['count'],
        'timings' => [
            'total_time' => round((microtime(true) - $start_time) * 1000, 2) . 'ms',
            'db_connection' => round(($db_time - $start_time) * 1000, 2) . 'ms',
            'query_time' => round(($query_time - $db_time) * 1000, 2) . 'ms'
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'execution_time' => round((microtime(true) - $start_time) * 1000, 2) . 'ms'
    ]);
}
?>

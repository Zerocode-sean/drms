<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/db_config.php';
require_once '../models/scheduler.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Check if scheduler class exists
    if (!class_exists('Scheduler')) {
        throw new Exception("Scheduler class not found");
    }
    
    $scheduler = new Scheduler($conn);
    
    // Get date from request, default to today
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    
    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        throw new Exception("Invalid date format. Use YYYY-MM-DD");
    }
    
    // Generate schedule
    $schedule = $scheduler->scheduleCollectionRequests($date);
    
    // Get recommendations
    $recommendations = $scheduler->getSchedulingRecommendations($date);
    
    $response = [
        'success' => true,
        'schedule' => $schedule,
        'recommendations' => $recommendations,
        'generated_at' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
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
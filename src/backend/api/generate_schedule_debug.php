<?php
// Debug version of generate_schedule.php with extensive error reporting
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$debug_info = [];
$debug_info['step'] = 'Starting debug';

try {
    $debug_info['step'] = 'Loading config files';
    
    // Check if config file exists
    $config_path = __DIR__ . '/../config/db_config.php';
    if (!file_exists($config_path)) {
        throw new Exception("db_config.php not found at: $config_path");
    }
    require_once $config_path;
    $debug_info['config_loaded'] = true;
    
    // Check if scheduler model exists
    $scheduler_path = __DIR__ . '/../models/scheduler.php';
    if (!file_exists($scheduler_path)) {
        throw new Exception("scheduler.php not found at: $scheduler_path");
    }
    require_once $scheduler_path;
    $debug_info['scheduler_model_loaded'] = true;
    
    $debug_info['step'] = 'Connecting to database';
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $debug_info['database_connected'] = true;
    
    // Check if scheduler class exists
    if (!class_exists('Scheduler')) {
        throw new Exception("Scheduler class not found after loading scheduler.php");
    }
    $debug_info['scheduler_class_exists'] = true;
    
    $debug_info['step'] = 'Creating scheduler instance';
    $scheduler = new Scheduler($conn);
    $debug_info['scheduler_instance_created'] = true;
    
    // Get date from request, default to today
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    $debug_info['date'] = $date;
    
    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        throw new Exception("Invalid date format. Use YYYY-MM-DD");
    }
    $debug_info['date_valid'] = true;
    
    $debug_info['step'] = 'Checking available requests';
    // Check if we have requests to schedule
    $requests_check = $conn->query("SELECT COUNT(*) as count FROM requests1 WHERE status IN ('Pending', 'Approved')");
    if ($requests_check) {
        $row = $requests_check->fetch_assoc();
        $debug_info['available_requests'] = $row['count'];
    }
    
    $debug_info['step'] = 'Checking available drivers';
    // Check if we have drivers available
    $drivers_check = $conn->query("SELECT COUNT(*) as count FROM drivers WHERE status = 'available'");
    if ($drivers_check) {
        $row = $drivers_check->fetch_assoc();
        $debug_info['available_drivers'] = $row['count'];
    }
    
    $debug_info['step'] = 'Generating schedule';
    // Generate schedule
    $schedule = $scheduler->scheduleCollectionRequests($date);
    $debug_info['schedule_generated'] = true;
    $debug_info['schedule_count'] = count($schedule);
    
    $debug_info['step'] = 'Getting recommendations';
    // Get recommendations
    $recommendations = $scheduler->getSchedulingRecommendations($date);
    $debug_info['recommendations_generated'] = true;
    $debug_info['recommendations_count'] = count($recommendations);
    
    $response = [
        'success' => true,
        'schedule' => $schedule,
        'recommendations' => $recommendations,
        'generated_at' => date('Y-m-d H:i:s'),
        'debug_info' => $debug_info
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    $debug_info['error'] = $e->getMessage();
    $debug_info['error_file'] = $e->getFile();
    $debug_info['error_line'] = $e->getLine();
    
    $response = [
        'success' => false,
        'error' => $e->getMessage(),
        'debug_info' => $debug_info
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
} catch (Error $e) {
    $debug_info['fatal_error'] = $e->getMessage();
    $debug_info['error_file'] = $e->getFile();
    $debug_info['error_line'] = $e->getLine();
    
    $response = [
        'success' => false,
        'error' => 'Fatal error: ' . $e->getMessage(),
        'debug_info' => $debug_info
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
}

if (isset($conn)) {
    $conn->close();
}
?>

<?php
// Cached dashboard metrics API
require_once '../config/performance.php';
require_once '../config/cached_api.php';
require_once '../config/session.php';

header('Content-Type: application/json');
header('Cache-Control: private, max-age=300'); // 5 minute cache

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    // Get cached dashboard metrics
    $metrics = CachedAPI::getDashboardMetrics();
    
    echo json_encode([
        'success' => true,
        'metrics' => $metrics,
        'cached' => true
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to fetch dashboard metrics']);
}
?>

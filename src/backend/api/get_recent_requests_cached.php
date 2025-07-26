<?php
// Cached recent requests API
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
    $user_id = $_SESSION['user_id'];
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    
    // Get cached user requests
    $requests = CachedAPI::getUserRequests($user_id, $limit);
    
    echo json_encode([
        'success' => true,
        'requests' => $requests,
        'count' => count($requests),
        'cached' => true
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to fetch recent requests']);
}
?>

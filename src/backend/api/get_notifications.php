<?php
// Optimized notifications API with caching
require_once '../config/performance.php';
require_once '../config/cached_api.php';
require_once '../config/session.php';

// Set API cache headers
header('Content-Type: application/json');
header('Cache-Control: private, max-age=60'); // 1 minute cache

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Use cached API for notifications
    $notifications = CachedAPI::getUserNotifications($user_id);
    
    echo json_encode(['success' => true, 'notifications' => $notifications]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to fetch notifications']);
}
?> 
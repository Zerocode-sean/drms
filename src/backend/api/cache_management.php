<?php
// Cache management API
require_once '../config/performance.php';
require_once '../config/cache.php';
require_once '../config/session.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check if user is admin (only admins can manage cache)
require_once '../config/db_config.php';
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Admin only.']);
    exit;
}

$action = $_GET['action'] ?? 'stats';

try {
    switch ($action) {
        case 'stats':
            $stats = $cache->getStats();
            $cleaned = cleanup_expired_cache();
            echo json_encode([
                'success' => true,
                'cache_stats' => $stats,
                'expired_cleaned' => $cleaned
            ]);
            break;
            
        case 'clear':
            $type = $_GET['type'] ?? null;
            cache_clear($type);
            echo json_encode([
                'success' => true,
                'message' => $type ? "Cleared {$type} cache" : 'Cleared all cache'
            ]);
            break;
            
        case 'warmup':
            // Warm up frequently accessed data
            $warmup_results = [];
            
            // Warm up dashboard metrics
            require_once '../config/cached_api.php';
            $metrics = CachedAPI::getDashboardMetrics();
            $warmup_results['dashboard_metrics'] = 'warmed';
            
            // Warm up available drivers
            $drivers = CachedAPI::getAvailableDrivers();
            $warmup_results['available_drivers'] = count($drivers) . ' drivers cached';
            
            echo json_encode([
                'success' => true,
                'message' => 'Cache warmed up',
                'warmup_results' => $warmup_results
            ]);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

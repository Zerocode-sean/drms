<?php
// Cached API endpoints for better performance
require_once __DIR__ . '/cache.php';
require_once __DIR__ . '/db_config.php';

class CachedAPI {
    
    // Cache user notifications for 2 minutes
    public static function getUserNotifications($user_id) {
        $cache_key = "user_notifications_{$user_id}";
        $cached = cache_get($cache_key, 'user_data');
        
        if ($cached !== null) {
            return $cached;
        }
        
        global $conn;
        $sql = "SELECT * FROM notifications WHERE recipient_id = ? ORDER BY created_at DESC LIMIT 20";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        
        // Cache for 2 minutes
        cache_set($cache_key, $notifications, 120, 'user_data');
        return $notifications;
    }
    
    // Cache user requests for 5 minutes
    public static function getUserRequests($user_id, $limit = 10) {
        $cache_key = "user_requests_{$user_id}_{$limit}";
        $cached = cache_get($cache_key, 'user_data');
        
        if ($cached !== null) {
            return $cached;
        }
        
        global $conn;
        $sql = "SELECT * FROM requests WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $user_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        
        // Cache for 5 minutes
        cache_set($cache_key, $requests, 300, 'user_data');
        return $requests;
    }
    
    // Cache dashboard metrics for 10 minutes
    public static function getDashboardMetrics() {
        $cache_key = "dashboard_metrics";
        $cached = cache_get($cache_key, 'api');
        
        if ($cached !== null) {
            return $cached;
        }
        
        global $conn;
        
        // Get various metrics
        $metrics = [];
        
        // Total requests
        $result = $conn->query("SELECT COUNT(*) as count FROM requests");
        $metrics['total_requests'] = $result->fetch_assoc()['count'];
        
        // Pending requests
        $result = $conn->query("SELECT COUNT(*) as count FROM requests WHERE status = 'Pending'");
        $metrics['pending_requests'] = $result->fetch_assoc()['count'];
        
        // Completed requests
        $result = $conn->query("SELECT COUNT(*) as count FROM requests WHERE status = 'Completed'");
        $metrics['completed_requests'] = $result->fetch_assoc()['count'];
        
        // Total users
        $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'resident'");
        $metrics['total_users'] = $result->fetch_assoc()['count'];
        
        // Active drivers
        $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'driver'");
        $metrics['active_drivers'] = $result->fetch_assoc()['count'];
        
        // Recent activity (last 24 hours)
        $result = $conn->query("SELECT COUNT(*) as count FROM requests WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $metrics['recent_requests'] = $result->fetch_assoc()['count'];
        
        // Cache for 10 minutes
        cache_set($cache_key, $metrics, 600, 'api');
        return $metrics;
    }
    
    // Clear user-specific cache when data changes
    public static function invalidateUserCache($user_id) {
        cache_delete("user_notifications_{$user_id}", 'user_data');
        cache_delete("user_requests_{$user_id}_10", 'user_data');
        cache_delete("user_requests_{$user_id}_5", 'user_data');
        cache_delete("dashboard_metrics", 'api');
    }
    
    // Cache driver assignments for 5 minutes
    public static function getDriverAssignments($driver_id) {
        $cache_key = "driver_assignments_{$driver_id}";
        $cached = cache_get($cache_key, 'user_data');
        
        if ($cached !== null) {
            return $cached;
        }
        
        global $conn;
        $sql = "SELECT r.*, u.name as user_name, u.phone as user_phone 
                FROM requests r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.assigned_driver_id = ? 
                ORDER BY r.preferred_date ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $driver_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $assignments = [];
        while ($row = $result->fetch_assoc()) {
            $assignments[] = $row;
        }
        
        // Cache for 5 minutes
        cache_set($cache_key, $assignments, 300, 'user_data');
        return $assignments;
    }
    
    // Cache available drivers for 10 minutes
    public static function getAvailableDrivers() {
        $cache_key = "available_drivers";
        $cached = cache_get($cache_key, 'api');
        
        if ($cached !== null) {
            return $cached;
        }
        
        global $conn;
        $sql = "SELECT id, name, phone FROM users WHERE role = 'driver' ORDER BY name";
        $result = $conn->query($sql);
        
        $drivers = [];
        while ($row = $result->fetch_assoc()) {
            $drivers[] = $row;
        }
        
        // Cache for 10 minutes
        cache_set($cache_key, $drivers, 600, 'api');
        return $drivers;
    }
}

// Helper functions for cache invalidation
function invalidate_request_cache($user_id = null) {
    if ($user_id) {
        CachedAPI::invalidateUserCache($user_id);
    }
    cache_delete("dashboard_metrics", 'api');
    cache_delete("available_drivers", 'api');
}

function invalidate_driver_cache($driver_id) {
    cache_delete("driver_assignments_{$driver_id}", 'user_data');
    cache_delete("available_drivers", 'api');
}
?>

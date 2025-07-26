<?php
// Enhanced performance optimizations for DRMS
// Comprehensive caching and performance improvements

// Enable output buffering with compression
if (!ob_get_level()) {
    ob_start();
}

// Advanced gzip compression
if (!headers_sent() && extension_loaded('zlib')) {
    if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false) {
        ob_start('ob_gzhandler');
    }
}

// Enhanced cache headers based on content type
$request_uri = $_SERVER['REQUEST_URI'] ?? '';
$extension = pathinfo($request_uri, PATHINFO_EXTENSION);

// Long-term caching for static assets
if (in_array($extension, ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'woff', 'woff2', 'svg', 'webp'])) {
    header('Cache-Control: public, max-age=31536000, immutable'); // 1 year with immutable
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
    header('Pragma: public');
    
    // Add ETag for better cache validation
    $etag = md5_file($_SERVER['SCRIPT_FILENAME'] ?? '');
    header('ETag: "' . $etag . '"');
    
    // Check if client has cached version
    if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $etag . '"') {
        http_response_code(304);
        exit;
    }
}

// Medium-term caching for API responses
if (strpos($request_uri, '/api/') !== false) {
    header('Cache-Control: private, max-age=300'); // 5 minutes for API
    header('Vary: Accept-Encoding');
}

// Short-term caching for dynamic pages
if (in_array($extension, ['php', 'html']) || empty($extension)) {
    header('Cache-Control: private, max-age=60'); // 1 minute for pages
    header('Vary: Accept-Encoding, Cookie');
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Performance optimizations
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '30');

// Session optimization
ini_set('session.cookie_lifetime', 7200); // 2 hours
ini_set('session.gc_maxlifetime', 7200);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.cookie_samesite', 'Lax');

// Opcache status check (settings cannot be changed at runtime)
if (function_exists('opcache_get_status')) {
    $opcache_status = opcache_get_status();
    // OPcache is available and configured via php.ini
    // Settings like opcache.enable cannot be changed at runtime
}

// Auto cleanup cache periodically (1% chance)
if (mt_rand(1, 100) === 1) {
    if (file_exists(__DIR__ . '/cache.php')) {
        require_once __DIR__ . '/cache.php';
        cleanup_expired_cache();
    }
}
?>

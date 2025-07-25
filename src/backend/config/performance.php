<?php
// Performance optimizations for DRMS
// Add to the top of critical files

// Enable output buffering
if (!ob_get_level()) {
    ob_start();
}

// Set cache headers for static content
$request_uri = $_SERVER['REQUEST_URI'] ?? '';
$extension = pathinfo($request_uri, PATHINFO_EXTENSION);

// Cache static assets
if (in_array($extension, ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'woff', 'woff2'])) {
    header('Cache-Control: public, max-age=31536000'); // 1 year
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
}

// Enable gzip compression
if (!ob_get_level() && extension_loaded('zlib') && !headers_sent()) {
    if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false) {
        ob_start('ob_gzhandler');
    }
}

// Set session configuration but don't start session automatically
// Sessions will be started by individual files as needed
ini_set('session.cookie_lifetime', 3600); // 1 hour
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
?>

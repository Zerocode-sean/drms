<?php
// DRMS Application Entry Point - Optimized for Performance
// Production-ready entry point for cloud deployment

// Include performance optimizations
require_once __DIR__ . '/src/backend/config/performance.php';

// Set default timezone
date_default_timezone_set('Africa/Nairobi');

// Handle health check requests quickly
if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] === '/health') {
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'healthy', 'timestamp' => date('Y-m-d H:i:s')]);
    exit;
}

// Check if user is accessing the root
if (!isset($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/index.php') {
    // Redirect to the main application
    header('Location: /src/frontend/assets/home.php');
    exit;
}

// For other requests, try to serve the appropriate file
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Security: prevent directory traversal
if (strpos($path, '..') !== false) {
    http_response_code(404);
    exit;
}

// Check if file exists
$filePath = __DIR__ . $path;
if (file_exists($filePath) && is_file($filePath)) {
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    
    // Set appropriate content type and serve file
    switch ($extension) {
        case 'css':
            header('Content-Type: text/css');
            header('Cache-Control: public, max-age=31536000');
            break;
        case 'js':
            header('Content-Type: application/javascript');
            header('Cache-Control: public, max-age=31536000');
            break;
        case 'png':
            header('Content-Type: image/png');
            header('Cache-Control: public, max-age=31536000');
            break;
        case 'jpg':
        case 'jpeg':
            header('Content-Type: image/jpeg');
            header('Cache-Control: public, max-age=31536000');
            break;
        case 'gif':
            header('Content-Type: image/gif');
            header('Cache-Control: public, max-age=31536000');
            break;
        case 'php':
            // Include PHP files directly for better performance
            include $filePath;
            exit;
    }
    
    // Serve static files
    readfile($filePath);
    exit;
}

// If file not found, redirect to home
header('Location: /src/frontend/assets/home.php');
exit;
?>

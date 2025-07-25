<?php
// DRMS Application Entry Point
// Production-ready entry point for cloud deployment

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set default timezone
date_default_timezone_set('Africa/Nairobi');

// Handle health check requests
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

// Remove query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Define the document root
$docRoot = __DIR__;

// Try to find the file
$filePath = $docRoot . $path;

if (file_exists($filePath) && is_file($filePath)) {
    // Serve the file with appropriate content type
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    
    switch ($extension) {
        case 'css':
            header('Content-Type: text/css');
            break;
        case 'js':
            header('Content-Type: application/javascript');
            break;
        case 'png':
            header('Content-Type: image/png');
            break;
        case 'jpg':
        case 'jpeg':
            header('Content-Type: image/jpeg');
            break;
        case 'gif':
            header('Content-Type: image/gif');
            break;
        case 'php':
            // Include PHP files
            include $filePath;
            exit;
    }
    
    readfile($filePath);
    exit;
}

// If file not found, redirect to home
header('Location: /src/frontend/assets/home.php');
exit;
?>

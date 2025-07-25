<?php
// DRMS Application Entry Point
// Redirect to the main application

// Check if user is accessing the root
if ($_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/index.php') {
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

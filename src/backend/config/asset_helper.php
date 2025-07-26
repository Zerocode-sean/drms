<?php
// Environment-aware asset path helper
// This file helps with asset paths that work on both localhost and production

// Detect environment
function getBasePath() {
    static $basePath = null;
    
    if ($basePath === null) {
        $isLocalhost = (
            isset($_SERVER['HTTP_HOST']) && 
            (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
             strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
             strpos($_SERVER['HTTP_HOST'], '::1') !== false)
        );
        
        $basePath = $isLocalhost ? '/project' : '';
    }
    
    return $basePath;
}

// Helper functions for different asset types
function assetPath($path) {
    return getBasePath() . $path;
}

function cssPath($filename) {
    return getBasePath() . '/src/frontend/css/' . $filename;
}

function jsPath($filename) {
    return getBasePath() . '/src/frontend/js/' . $filename;
}

function imagePath($filename) {
    return getBasePath() . '/src/frontend/images/' . $filename;
}

function logoPath() {
    return imagePath('logo.png');
}

// API path helper
function apiPath($endpoint) {
    return getBasePath() . '/src/backend/api/' . $endpoint;
}
?>

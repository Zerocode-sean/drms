<?php
// src/backend/config/db_config.php

// Load environment variable loader utility
require_once __DIR__ . '/env_loader.php';

// Load environment variables
loadEnv(__DIR__ . '/../../../.env');

// Database Configuration from environment variables
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'drms2');

// Create mysqli connection
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    throw new Exception('Database connection failed: ' . $mysqli->connect_error);
}

// Also create PDO connection for compatibility
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    throw new Exception('PDO Database connection failed: ' . $e->getMessage());
}
?>


<?php
// src/backend/config/db_config.php

// Load environment variable loader utility
require_once __DIR__ . '/env_loader.php';

// Load environment variables
loadEnv(__DIR__ . '/../../../.env');

// Database Configuration from environment variables (updated for Render + Aiven)
define('DB_HOST', $_ENV['DATABASE_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DATABASE_USER'] ?? 'root');
define('DB_PASS', $_ENV['DATABASE_PASSWORD'] ?? '');
define('DB_NAME', $_ENV['DATABASE_NAME'] ?? 'drms2');
define('DB_PORT', $_ENV['DATABASE_PORT'] ?? '3306');

// Create mysqli connection with port
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if ($mysqli->connect_error) {
    throw new Exception('Database connection failed: ' . $mysqli->connect_error);
}

// Also create PDO connection for compatibility
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch(PDOException $e) {
    throw new Exception('PDO Database connection failed: ' . $e->getMessage());
}
?>


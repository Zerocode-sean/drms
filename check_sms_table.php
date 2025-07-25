<?php
require_once 'src/backend/config/env_loader.php';
require_once 'src/backend/config/db_config.php';

loadEnv(__DIR__ . '/.env');

try {
    $pdo = new PDO("mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}", $_ENV['DB_USER'], $_ENV['DB_PASS']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "SMS Logs Table Structure:\n";
    $stmt = $pdo->query('DESCRIBE sms_logs');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}\n";
    }
    
    echo "\nSample SMS Log Entries:\n";
    $stmt = $pdo->query('SELECT * FROM sms_logs ORDER BY created_at DESC LIMIT 3');
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($logs as $log) {
        echo "Entry ID {$log['id']}:\n";
        foreach ($log as $key => $value) {
            echo "  $key: " . (strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value) . "\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

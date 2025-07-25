<?php
require_once 'src/backend/config/env_loader.php';
require_once 'src/backend/config/db_config.php';

loadEnv(__DIR__ . '/.env');

try {
    $pdo = new PDO("mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}", $_ENV['DB_USER'], $_ENV['DB_PASS']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Users Table Structure:\n";
    echo "=====================\n";
    $stmt = $pdo->query('DESCRIBE users');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}\n";
    }
    
    echo "\nSample User Data:\n";
    echo "================\n";
    $stmt = $pdo->query('SELECT * FROM users WHERE role = "resident" LIMIT 3');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $user) {
        echo "User ID {$user['id']}:\n";
        foreach ($user as $key => $value) {
            if ($key === 'password') $value = '[HIDDEN]';
            echo "  $key: $value\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

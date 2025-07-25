<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../models/user.php';

$userModel = new User($mysqli);

// Seed admin
if (!$userModel->findByUsername('admin')) {
    $userModel->createUser('admin', 'admin@drms.com', 'admin123', 'admin');
    echo "Seeded admin user.\n";
} else {
    echo "Admin user already exists.\n";
}
// Seed driver
if (!$userModel->findByUsername('driver1')) {
    $userModel->createUser('driver1', 'driver1@drms.com', 'driver123', 'driver');
    echo "Seeded driver user.\n";
} else {
    echo "Driver user already exists.\n";
}




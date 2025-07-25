<?php
require_once __DIR__ . '/../config/db_config.php';

// Always create a new driver user and driver row
$driver_username = 'testdriver_' . rand(1000,9999);
$driver_email = $driver_username . '@example.com';
$driver_password_plain = 'password123';
$driver_password = password_hash($driver_password_plain, PASSWORD_BCRYPT);
$driver_role = 'driver';
$stmt = $mysqli->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $driver_username, $driver_email, $driver_password, $driver_role);
$stmt->execute();
$driver_user_id = $stmt->insert_id;
$stmt->close();
echo "Created test driver user: $driver_username ($driver_email)\n";
echo "Driver password: $driver_password_plain\n";
// Create a driver row
$license_number = 'LIC' . rand(10000,99999);
$stmt = $mysqli->prepare('INSERT INTO drivers (user_id, license_number, is_active) VALUES (?, ?, 1)');
$stmt->bind_param('is', $driver_user_id, $license_number);
$stmt->execute();
$driver_id = $stmt->insert_id;
$stmt->close();
echo "Created driver with license: $license_number\n";

// 1. Create a test user (resident)
$username = 'testuser_' . rand(1000,9999);
$email = $username . '@example.com';
$password = password_hash('password123', PASSWORD_BCRYPT);
$role = 'resident';
$stmt = $mysqli->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $username, $email, $password, $role);
$stmt->execute();
$user_id = $stmt->insert_id;
$stmt->close();
echo "Created test user: $username ($email)\n";

// 2. Create a test request for that user
date_default_timezone_set('UTC');
$document = 'Test waste collection at 123 Main St, City';
$status = 'Pending';
$stmt = $mysqli->prepare('INSERT INTO requests (user_id, document, status) VALUES (?, ?, ?)');
$stmt->bind_param('iss', $user_id, $document, $status);
$stmt->execute();
$request_id = $stmt->insert_id;
$stmt->close();
echo "Created test request: $document\n";

// 3. Assign the request to the new driver
$stmt = $mysqli->prepare('INSERT INTO assignments (request_id, driver_id, status) VALUES (?, ?, ?)');
$assignment_status = 'Assigned';
$stmt->bind_param('iis', $request_id, $driver_id, $assignment_status);
$stmt->execute();
$stmt->close();
echo "Assigned request to driver ID: $driver_id\n";

echo "Seeding complete.\n"; 
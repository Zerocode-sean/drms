<?php
require_once '../config/db_config.php';

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // First, create some driver users
    $driverUsers = [
        ['username' => 'driver1', 'email' => 'driver1@drms.com', 'password' => password_hash('driver123', PASSWORD_DEFAULT), 'role' => 'driver'],
        ['username' => 'driver2', 'email' => 'driver2@drms.com', 'password' => password_hash('driver123', PASSWORD_DEFAULT), 'role' => 'driver'],
        ['username' => 'driver3', 'email' => 'driver3@drms.com', 'password' => password_hash('driver123', PASSWORD_DEFAULT), 'role' => 'driver']
    ];
    
    foreach ($driverUsers as $driverUser) {
        // Check if user already exists
        $checkSql = "SELECT id FROM users WHERE username = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param('s', $driverUser['username']);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows == 0) {
            // Insert user
            $userSql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
            $userStmt = $conn->prepare($userSql);
            $userStmt->bind_param('ssss', 
                $driverUser['username'], 
                $driverUser['email'], 
                $driverUser['password'], 
                $driverUser['role']
            );
            $userStmt->execute();
            $userId = $conn->insert_id;
            
            // Insert driver details
            $driverDetails = [
                'name' => 'Driver ' . substr($driverUser['username'], -1),
                'phone' => '+254700' . rand(100000, 999999),
                'vehicle_type' => 'Truck',
                'capacity' => 1000.00,
                'current_load' => rand(0, 300),
                'status' => 'active'
            ];
            
            $driverSql = "INSERT INTO drivers (user_id, name, phone, vehicle_type, capacity, current_load, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $driverStmt = $conn->prepare($driverSql);
            $driverStmt->bind_param('isssdds', 
                $userId,
                $driverDetails['name'],
                $driverDetails['phone'],
                $driverDetails['vehicle_type'],
                $driverDetails['capacity'],
                $driverDetails['current_load'],
                $driverDetails['status']
            );
            $driverStmt->execute();
            
            echo "Created driver: " . $driverDetails['name'] . "\n";
        } else {
            echo "Driver user already exists: " . $driverUser['username'] . "\n";
        }
    }
    
    echo "\nDriver seeding completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 
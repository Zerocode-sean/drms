<?php
/**
 * Fix Driver Records - Create missing driver entries
 */

require_once 'src/backend/config/db_config.php';

echo "🔧 FIXING DRIVER RECORDS\n";
echo str_repeat("=", 40) . "\n\n";

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed');
    }

    // Find users with driver role but no driver record
    echo "🔍 Finding users with driver role but no driver record...\n";
    $sql = "SELECT u.id, u.username, u.phone, u.email 
            FROM users u 
            LEFT JOIN drivers d ON u.id = d.user_id 
            WHERE u.role = 'driver' AND d.id IS NULL";
    
    $result = $conn->query($sql);
    $usersToFix = [];
    
    while ($row = $result->fetch_assoc()) {
        $usersToFix[] = $row;
        echo "   • User ID: {$row['id']}, Username: {$row['username']}\n";
    }
    
    if (empty($usersToFix)) {
        echo "✅ No users need fixing - all driver users have driver records.\n";
    } else {
        echo "\n🔧 Creating driver records...\n";
        
        foreach ($usersToFix as $user) {
            // Generate a license number
            $licenseNumber = 'DRV' . str_pad($user['id'], 6, '0', STR_PAD_LEFT);
            
            // Insert driver record
            $stmt = $conn->prepare("INSERT INTO drivers (user_id, license_number, phone, status) VALUES (?, ?, ?, 'active')");
            $phone = $user['phone'] ?: '+254700000000';
            $stmt->bind_param('iss', $user['id'], $licenseNumber, $phone);
            
            if ($stmt->execute()) {
                $driverId = $conn->insert_id;
                echo "   ✅ Created driver record for {$user['username']} (Driver ID: $driverId, License: $licenseNumber)\n";
            } else {
                echo "   ❌ Failed to create driver record for {$user['username']}: " . $stmt->error . "\n";
            }
            $stmt->close();
        }
        
        echo "\n🎯 Now assigning some test requests to drivers...\n";
        
        // Get some unassigned requests
        $result = $conn->query("SELECT r.id, r.location, u.username as requester 
                               FROM requests1 r 
                               JOIN users u ON r.user_id = u.id 
                               WHERE r.id NOT IN (SELECT request_id FROM assignments) 
                               AND r.location IS NOT NULL 
                               AND r.location != '' 
                               LIMIT 5");
        
        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        
        if (!empty($requests)) {
            // Get the first newly created driver
            $firstDriverResult = $conn->query("SELECT id FROM drivers WHERE user_id = {$usersToFix[0]['id']}");
            $firstDriver = $firstDriverResult->fetch_assoc();
            $driverId = $firstDriver['id'];
            
            foreach ($requests as $request) {
                $stmt = $conn->prepare("INSERT INTO assignments (driver_id, request_id, assigned_at) VALUES (?, ?, NOW())");
                $stmt->bind_param('ii', $driverId, $request['id']);
                
                if ($stmt->execute()) {
                    echo "   ✅ Assigned Request {$request['id']} (Location: {$request['location']}) to Driver $driverId\n";
                } else {
                    echo "   ❌ Failed to assign Request {$request['id']}: " . $stmt->error . "\n";
                }
                $stmt->close();
            }
        } else {
            echo "   ⚠️ No unassigned requests with valid locations found.\n";
        }
    }
    
    echo "\n" . str_repeat("=", 40) . "\n";
    echo "✅ Driver records fix complete!\n";
    echo "🌐 Now the driver dashboard should show routes.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>

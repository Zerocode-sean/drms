<?php
// Simplified test version of get_available_drivers.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $conn = new mysqli('localhost', 'root', '', 'drms_db');
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // First check what columns actually exist
    $columns_result = $conn->query("DESCRIBE drivers");
    $available_columns = [];
    while ($col = $columns_result->fetch_assoc()) {
        $available_columns[] = $col['Field'];
    }
    
    // Build query based on available columns
    $select_fields = ['id'];
    
    if (in_array('name', $available_columns)) {
        $select_fields[] = 'name';
    } elseif (in_array('driver_name', $available_columns)) {
        $select_fields[] = 'driver_name as name';
    } else {
        $select_fields[] = "'Unknown Driver' as name";
    }
    
    if (in_array('email', $available_columns)) {
        $select_fields[] = 'email';
    } else {
        $select_fields[] = "'no-email@example.com' as email";
    }
    
    if (in_array('phone', $available_columns)) {
        $select_fields[] = 'phone';
    } else {
        $select_fields[] = "'000-000-0000' as phone";
    }
    
    if (in_array('license_number', $available_columns)) {
        $select_fields[] = 'license_number';
    } else {
        $select_fields[] = "'N/A' as license_number";
    }
    
    if (in_array('vehicle_number', $available_columns)) {
        $select_fields[] = 'vehicle_number';
    } else {
        $select_fields[] = "'N/A' as vehicle_number";
    }
    
    if (in_array('status', $available_columns)) {
        $select_fields[] = 'status';
        $where_clause = "WHERE status = 'available'";
    } else {
        $select_fields[] = "'available' as status";
        $where_clause = "";
    }
    
    $sql = "SELECT " . implode(', ', $select_fields) . " FROM drivers " . $where_clause . " ORDER BY id";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $drivers = [];
    while ($row = $result->fetch_assoc()) {
        $drivers[] = $row;
    }
    
    // If no drivers found, create some test data
    if (empty($drivers)) {
        // Try to insert test drivers
        for ($i = 1; $i <= 3; $i++) {
            $insert_fields = ['id'];
            $insert_values = [$i];
            $placeholders = ['?'];
            
            if (in_array('name', $available_columns)) {
                $insert_fields[] = 'name';
                $insert_values[] = "Test Driver $i";
                $placeholders[] = '?';
            }
            
            if (in_array('status', $available_columns)) {
                $insert_fields[] = 'status';
                $insert_values[] = 'available';
                $placeholders[] = '?';
            }
            
            $insert_sql = "INSERT IGNORE INTO drivers (" . implode(', ', $insert_fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $conn->prepare($insert_sql);
            
            if (count($insert_values) === 1) {
                $stmt->bind_param('i', $insert_values[0]);
            } elseif (count($insert_values) === 2) {
                $stmt->bind_param('is', $insert_values[0], $insert_values[1]);
            } elseif (count($insert_values) === 3) {
                $stmt->bind_param('iss', $insert_values[0], $insert_values[1], $insert_values[2]);
            }
            
            $stmt->execute();
        }
        
        // Re-run the select query
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $drivers[] = $row;
        }
    }
    
    echo json_encode([
        'success' => true,
        'drivers' => $drivers,
        'count' => count($drivers),
        'available_columns' => $available_columns,
        'query_used' => $sql
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'available_columns' => $available_columns ?? [],
        'query_used' => $sql ?? 'N/A'
    ], JSON_PRETTY_PRINT);
}
?>

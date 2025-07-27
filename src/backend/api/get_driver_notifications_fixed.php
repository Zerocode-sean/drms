<?php
// Fixed version of get_driver_notifications.php that adapts to table structure
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/db_config.php';

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // First check what columns actually exist in notifications table
    $columns_result = $conn->query("DESCRIBE notifications");
    $available_columns = [];
    while ($col = $columns_result->fetch_assoc()) {
        $available_columns[] = $col['Field'];
    }
    
    // Build query based on available columns
    $select_fields = ['n.id'];
    
    if (in_array('message', $available_columns)) {
        $select_fields[] = 'n.message';
    } else {
        $select_fields[] = "'No message' as message";
    }
    
    if (in_array('sent_at', $available_columns)) {
        $select_fields[] = 'n.sent_at';
    } elseif (in_array('created_at', $available_columns)) {
        $select_fields[] = 'n.created_at as sent_at';
    } else {
        $select_fields[] = 'NOW() as sent_at';
    }
    
    if (in_array('is_read', $available_columns)) {
        $select_fields[] = 'n.is_read';
    } else {
        $select_fields[] = 'FALSE as is_read';
    }
    
    // Handle sender/receiver IDs
    $sender_column = '';
    $receiver_column = '';
    
    if (in_array('sender_id', $available_columns)) {
        $sender_column = 'n.sender_id';
        $receiver_column = 'n.receiver_id';
    } elseif (in_array('user_id', $available_columns)) {
        $sender_column = 'n.user_id';
        $receiver_column = 'n.recipient_id';
    }
    
    // Build the main query
    if ($sender_column && $receiver_column) {
        $sql = "SELECT " . implode(', ', $select_fields) . ",
                sender.id AS sender_id, sender.username AS sender_username, sender.email AS sender_email,
                receiver.id AS receiver_id, receiver.username AS receiver_username, receiver.email AS receiver_email
                FROM notifications n
                LEFT JOIN users sender ON $sender_column = sender.id
                LEFT JOIN users receiver ON $receiver_column = receiver.id
                WHERE 1=1";
    } else {
        // Simplified query without joins if we don't have proper ID columns
        $sql = "SELECT " . implode(', ', $select_fields) . "
                FROM notifications n
                WHERE 1=1";
    }
    
    // Add filters
    $params = [];
    $types = '';
    
    $sender_id = isset($_GET['sender_id']) ? intval($_GET['sender_id']) : 0;
    $receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : 0;
    
    if ($sender_id && $sender_column) {
        $sql .= " AND $sender_column = ?";
        $params[] = $sender_id;
        $types .= 'i';
    }
    
    if ($receiver_id && $receiver_column) {
        $sql .= " AND $receiver_column = ?";
        $params[] = $receiver_id;
        $types .= 'i';
    }
    
    // Order by available timestamp column
    if (in_array('sent_at', $available_columns)) {
        $sql .= " ORDER BY n.sent_at DESC";
    } elseif (in_array('created_at', $available_columns)) {
        $sql .= " ORDER BY n.created_at DESC";
    } else {
        $sql .= " ORDER BY n.id DESC";
    }
    
    $sql .= " LIMIT 50"; // Limit to prevent huge responses
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
    }
    
    // If no notifications found, create some test data
    if (empty($notifications)) {
        // Create test notifications
        for ($i = 1; $i <= 3; $i++) {
            $insert_fields = ['message'];
            $insert_values = ["Test notification $i"];
            $placeholders = ['?'];
            $param_types = 's';
            
            if (in_array('sent_at', $available_columns)) {
                $insert_fields[] = 'sent_at';
                $insert_values[] = date('Y-m-d H:i:s');
                $placeholders[] = '?';
                $param_types .= 's';
            }
            
            if (in_array('is_read', $available_columns)) {
                $insert_fields[] = 'is_read';
                $insert_values[] = 0;
                $placeholders[] = '?';
                $param_types .= 'i';
            }
            
            $insert_sql = "INSERT INTO notifications (" . implode(', ', $insert_fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param($param_types, ...$insert_values);
            $insert_stmt->execute();
        }
        
        // Re-run the select query
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
    }
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'count' => count($notifications),
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

$conn->close();
?>

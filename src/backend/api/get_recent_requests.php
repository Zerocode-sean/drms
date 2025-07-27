<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

try {
    // Create database connection for drms2
    $conn = new mysqli("localhost", "root", "", "drms2");
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    if ($role === 'admin') {
        // Get requests with user information for admin
        $sql = "SELECT r.id, r.location, r.waste_type, r.preferred_date, r.status, r.notes, r.address_details, r.created_at, r.document,
                       u.username, u.email, u.phone
                FROM requests r
                JOIN users u ON r.user_id = u.id
                ORDER BY r.created_at DESC 
                LIMIT 20";
        
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception("Query failed: " . $conn->error);
        }
        
        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = [
                'id' => $row['id'],
                'username' => $row['username'],
                'document' => $row['document'] ?: ($row['waste_type'] ?: 'Waste Collection'),
                'status' => $row['status'],
                'location' => $row['location'],
                'preferred_date' => $row['preferred_date'],
                'notes' => $row['notes'],
                'address_details' => $row['address_details'],
                'phone' => $row['phone'],
                'created_at' => $row['created_at'],
                'email' => $row['email']
            ];
        }
        
    } else if ($role === 'resident') {
        // Get requests for specific user
        $sql = "SELECT id, location, waste_type, preferred_date, status, notes, created_at, document
                FROM requests 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 20";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = [
                'id' => $row['id'],
                'document' => $row['document'] ?: ($row['waste_type'] ?: 'Waste Collection'),
                'status' => $row['status'],
                'location' => $row['location'],
                'preferred_date' => $row['preferred_date'],
                'notes' => $row['notes'],
                'created_at' => $row['created_at']
            ];
        }
        
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }
    
    // Return the requests array directly (not wrapped in 'requests' key)
    echo json_encode($requests);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 
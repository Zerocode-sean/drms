<?php
// Check and setup test data for bulk actions
$conn = new mysqli('localhost', 'root', '', 'drms2');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// First check what columns exist in requests table
echo "=== Requests Table Structure ===\n";
$result = $conn->query('DESCRIBE requests');
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Column: " . $row['Field'] . " (Type: " . $row['Type'] . ")\n";
    }
} else {
    echo "Error getting table structure: " . $conn->error . "\n";
}

// Check request statuses
echo "\n=== Current Request Status Distribution ===\n";
$result = $conn->query('SELECT status, COUNT(*) as count FROM requests GROUP BY status');
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Status: " . ($row['status'] ?: 'NULL/Empty') . " - Count: " . $row['count'] . "\n";
    }
} else {
    echo "No requests found or error: " . $conn->error . "\n";
}

echo "\n=== Sample Request Details ===\n";
$result = $conn->query('SELECT id, status, location, created_at FROM requests LIMIT 5');
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . ", Status: " . ($row['status'] ?: 'NULL') . ", Location: " . $row['location'] . ", Created: " . $row['created_at'] . "\n";
    }
} else {
    echo "No sample requests found or error: " . $conn->error . "\n";
}

echo "\n=== Adding Some Pending Requests for Testing ===\n";
$insert_sql = "INSERT INTO requests (user_id, location, preferred_date, status, waste_type, urgency, created_at) VALUES 
    (1, 'Test Location A', CURDATE() + INTERVAL 1 DAY, 'Pending', 'General', 'Normal', NOW()),
    (1, 'Test Location B', CURDATE() + INTERVAL 1 DAY, 'Pending', 'Recyclable', 'High', NOW()),
    (1, 'Test Location C', CURDATE() + INTERVAL 1 DAY, 'Pending', 'General', 'Low', NOW())";

if ($conn->query($insert_sql)) {
    echo "Successfully added 3 test pending requests\n";
} else {
    echo "Error adding test requests: " . $conn->error . "\n";
}

echo "\n=== Updated Status Distribution ===\n";
$result = $conn->query('SELECT status, COUNT(*) as count FROM requests GROUP BY status');
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Status: " . ($row['status'] ?: 'NULL/Empty') . " - Count: " . $row['count'] . "\n";
    }
}

$conn->close();
echo "\nTest data setup complete!\n";
?>

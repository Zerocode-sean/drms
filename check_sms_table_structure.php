<?php
require_once 'src/backend/config/db_config.php';
$conn = getConnection();
$result = $conn->query('DESCRIBE sms_logs');
if ($result) {
    echo "SMS Logs Table Structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . $row['Null'] . ' - ' . $row['Default'] . "\n";
    }
} else {
    echo 'Error: ' . $conn->error . "\n";
}
$conn->close();
?>

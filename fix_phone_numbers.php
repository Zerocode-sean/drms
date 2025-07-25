<?php
require_once 'src/backend/config/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->query("UPDATE users SET phone = '+254701234567' WHERE username = 'resident_nairobi' AND (phone IS NULL OR phone = '')");
$conn->query("UPDATE users SET phone = '+254707654321' WHERE username = 'resident_mombasa' AND (phone IS NULL OR phone = '')");
echo "Phone numbers updated for demo users.\n";
?>

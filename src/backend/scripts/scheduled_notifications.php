<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../models/notification.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die('DB connection error: ' . $conn->connect_error);

// Scheduled pickup reminders (1 hour before preferred_date, status Pending or Approved)
$reminder_sql = "SELECT r.id, r.user_id, r.preferred_date FROM requests r WHERE r.status IN ('Pending','Approved') AND r.preferred_date > NOW() AND r.preferred_date <= DATE_ADD(NOW(), INTERVAL 1 HOUR)";
$res = $conn->query($reminder_sql);
while ($row = $res->fetch_assoc()) {
    add_notification($conn, 1, $row['user_id'], "Reminder: Your waste pickup (Request #{$row['id']}) is scheduled for " . $row['preferred_date'] . ".");
}

// Missed/delayed pickups (preferred_date in the past, not Completed or Rejected)
$missed_sql = "SELECT r.id, r.user_id FROM requests r WHERE r.status NOT IN ('Completed','Rejected') AND r.preferred_date < NOW()";
$res = $conn->query($missed_sql);
while ($row = $res->fetch_assoc()) {
    // Notify resident
    add_notification($conn, 1, $row['user_id'], "Alert: Your waste pickup (Request #{$row['id']}) was missed or delayed. Please contact support or reschedule.");
    // Notify all admins
    $admins = $conn->query("SELECT id FROM users WHERE role='admin'");
    while ($admin = $admins->fetch_assoc()) {
        add_notification($conn, 1, $admin['id'], "Missed/delayed pickup for request #{$row['id']} (resident notified).");
    }
}

$conn->close();
echo "Scheduled notifications processed.\n";
?> 
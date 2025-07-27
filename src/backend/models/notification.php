<?php
function add_notification($conn, $sender_id, $receiver_id, $message) {
    // Use correct column names: user_id (sender) and recipient_id (receiver)
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, recipient_id, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param('iis', $sender_id, $receiver_id, $message);
    $stmt->execute();
    $stmt->close();
}
?> 
<?php
function add_notification($conn, $sender_id, $receiver_id, $message) {
    $stmt = $conn->prepare("INSERT INTO notifications (sender_id, receiver_id, message, sent_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param('iis', $sender_id, $receiver_id, $message);
    $stmt->execute();
    $stmt->close();
}
?> 
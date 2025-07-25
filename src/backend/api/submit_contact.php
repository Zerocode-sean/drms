<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../models/contact.php';
require_once __DIR__ . '/../models/user.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get and sanitize POST data
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) $data = $_POST;

function clean($v) {
    return htmlspecialchars(trim($v ?? ''));
}

$name = clean($data['name'] ?? '');
$email = clean($data['email'] ?? '');
$phone = clean($data['phone'] ?? '');
$inquiry_type = clean($data['inquiry'] ?? '');
$message = clean($data['message'] ?? '');

// Validate required fields
if (!$name || !$email || !$message) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

$contactModel = new Contact($pdo);
$success = $contactModel->create($name, $email, $phone, $inquiry_type, $message);

if ($success) {
    // Notify all admins
    $userModel = new User($mysqli);
    $admins = $userModel->getAllAdmins();
    $notifStmt = $pdo->prepare('INSERT INTO notifications (sender_id, receiver_id, message, sent_at) VALUES (?, ?, ?, NOW())');
    $notifMsg = "New contact message from $name ($email): $inquiry_type";
    $sender_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
    foreach ($admins as $admin) {
        $notifStmt->execute([$sender_id, $admin['id'], $notifMsg]);
    }
    // Send email to all admins
    foreach ($admins as $admin) {
        if (!empty($admin['email'])) {
            @mail($admin['email'], 'New Contact Message', $notifMsg . "\n\nMessage: $message\nPhone: $phone");
        }
    }
    echo json_encode(['success' => true, 'message' => 'Message sent successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send message.']);
} 
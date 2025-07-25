<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../models/report.php';
require_once __DIR__ . '/../models/user.php';
session_start();

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get user_id from session (fallback to 1 if not logged in)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

// Get and sanitize POST data
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) $data = $_POST;

function clean($v) {
    return htmlspecialchars(trim($v ?? ''));
}

$report_type = clean($data['report_type'] ?? '');
$location = clean($data['location'] ?? '');
$description = clean($data['description'] ?? '');
$impact = clean($data['impact'] ?? '');
$suggestion = clean($data['suggestion'] ?? '');
$additional_notes = clean($data['additional_notes'] ?? '');
$contact_preference = clean($data['contact_preference'] ?? '');
$user_name = clean($data['user_name'] ?? '');
$report_date = clean($data['report_date'] ?? date('Y-m-d'));
$report_time = clean($data['report_time'] ?? date('H:i:s'));

// Validate required fields
if (!$report_type || !$location || !$description || !$impact || !$suggestion) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

$reportModel = new Report($pdo);
$success = $reportModel->createFull($user_id, $report_type, $location, $description, $impact, $suggestion, $additional_notes, $contact_preference, $user_name, $report_date, $report_time);

if ($success) {
    // Notify all admins
    $userModel = new User($mysqli);
    $admins = $userModel->getAllAdmins();
    $notifStmt = $pdo->prepare('INSERT INTO notifications (sender_id, receiver_id, message, sent_at) VALUES (?, ?, ?, NOW())');
    $notifMsg = "New report submitted by $user_name: $report_type";
    foreach ($admins as $admin) {
        $notifStmt->execute([$user_id, $admin['id'], $notifMsg]);
    }
    // Send email to all admins
    foreach ($admins as $admin) {
        if (!empty($admin['email'])) {
            @mail($admin['email'], 'New Report Submitted', $notifMsg . "\n\nLocation: $location\nDescription: $description\nImpact: $impact\nSuggestion: $suggestion\nContact: $contact_preference");
        }
    }
    echo json_encode(['success' => true, 'message' => 'Report submitted successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to submit report.']);
} 
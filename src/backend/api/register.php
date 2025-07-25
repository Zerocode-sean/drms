<?php
session_start();
header('Content-Type: application/json');

// If user is already logged in, redirect them
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    http_response_code(403);
    echo json_encode(['error' => 'You are already logged in.']);
    exit;
}

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../models/user.php';

$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

error_log("Registration attempt - Username: $username, Email: $email");

if (!$username || !$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required.']);
    exit;
}

try {
    $userModel = new User($mysqli);
    
    if ($userModel->findByUsername($username)) {
        http_response_code(409);
        echo json_encode(['error' => 'Username already exists.']);
        exit;
    }
    if ($userModel->findByEmail($email)) {
        http_response_code(409);
        echo json_encode(['error' => 'Email already exists.']);
        exit;
    }
    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['error' => 'Password must be at least 6 characters.']);
        exit;
    }
    
    if (!$userModel->createUser($username, $email, $password, 'resident')) {
        error_log("Failed to create user: $username");
        http_response_code(500);
        echo json_encode(['error' => 'Registration failed. Please try again.']);
        exit;
    }
    
    error_log("User created successfully: $username");
    echo json_encode(['success' => true, 'message' => 'Registration successful.']);
    
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Registration failed. Please try again.']);
    exit;
}


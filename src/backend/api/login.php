<?php
session_start();
header('Content-Type: application/json');

// If user is already logged in, destroy session to allow new login
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    session_unset();
    session_destroy();
    session_start();
}

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
$password = $data['password'] ?? '';

error_log("Login attempt - Username: $username");

if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Username and password are required.']);
    exit;
}

try {
    $userModel = new User($mysqli);
    $user = $userModel->verifyPassword($username, $password);
    
    if (!$user) {
        error_log("Login failed for username: $username");
        http_response_code(401);
        echo json_encode(['error' => 'Invalid username or password.']);
        exit;
    }
    
    // Create session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    
    error_log("Login successful for username: $username, role: " . $user['role']);
    // Return user info and role for RBAC
    unset($user['password']);
    echo json_encode(['success' => true, 'user' => $user]);
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Login failed. Please try again.']);
    exit;
}


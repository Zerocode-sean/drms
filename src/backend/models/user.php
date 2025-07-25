<?php
// src/backend/models/user.php
class User {
    private $mysqli;
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }
    public function findByUsername($username) {
        $stmt = $this->mysqli->prepare('SELECT * FROM users WHERE username = ?');
        if (!$stmt) {
            error_log("Prepare failed: " . $this->mysqli->error);
            return false;
        }
        $stmt->bind_param('s', $username);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    public function findByEmail($email) {
        $stmt = $this->mysqli->prepare('SELECT * FROM users WHERE email = ?');
        if (!$stmt) {
            error_log("Prepare failed: " . $this->mysqli->error);
            return false;
        }
        $stmt->bind_param('s', $email);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    public function createUser($username, $email, $password, $role = 'resident') {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->mysqli->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
        if (!$stmt) {
            error_log("Prepare failed: " . $this->mysqli->error);
            return false;
        }
        $stmt->bind_param('ssss', $username, $email, $hash, $role);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }
        return true;
    }
    public function verifyPassword($username, $password) {
        $user = $this->findByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
    public function getAllAdmins() {
        $stmt = $this->mysqli->prepare('SELECT * FROM users WHERE role = "admin"');
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}


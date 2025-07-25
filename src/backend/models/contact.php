<?php
require_once __DIR__ . '/../config/db_config.php';
class Contact {
    private $conn;
    public function __construct($db) {
        $this->conn = $db;
    }
    public function create($name, $email, $phone, $inquiry_type, $message) {
        $stmt = $this->conn->prepare('INSERT INTO contact_messages (name, email, phone, inquiry_type, message) VALUES (?, ?, ?, ?, ?)');
        return $stmt->execute([$name, $email, $phone, $inquiry_type, $message]);
    }
} 
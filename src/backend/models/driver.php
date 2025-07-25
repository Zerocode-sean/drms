<?php
require_once __DIR__ . '/../config/db_config.php';
class Driver {
    private $conn;
    public function __construct($db) {
        $this->conn = $db;
    }
    public function getAll() {
        $stmt = $this->conn->prepare('SELECT d.*, u.username FROM drivers d JOIN users u ON d.user_id = u.id ORDER BY d.created_at DESC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getActive() {
        $stmt = $this->conn->prepare('SELECT d.*, u.username FROM drivers d JOIN users u ON d.user_id = u.id WHERE d.is_active = 1 ORDER BY d.created_at DESC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function create($user_id, $license_number) {
        $stmt = $this->conn->prepare('INSERT INTO drivers (user_id, license_number) VALUES (?, ?)');
        return $stmt->execute([$user_id, $license_number]);
    }
    public function setActive($id, $is_active) {
        $stmt = $this->conn->prepare('UPDATE drivers SET is_active = ? WHERE id = ?');
        return $stmt->execute([$is_active, $id]);
    }
    public function delete($id) {
        $stmt = $this->conn->prepare('DELETE FROM drivers WHERE id = ?');
        return $stmt->execute([$id]);
    }
} 
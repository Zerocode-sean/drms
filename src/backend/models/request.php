<?php
require_once __DIR__ . '/../config/db_config.php';
class Request {
    private $conn;
    public function __construct($db) {
        $this->conn = $db;
    }
    public function getAll() {
        $stmt = $this->conn->prepare('SELECT r.*, u.username FROM requests r JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getByStatus($status) {
        $stmt = $this->conn->prepare('SELECT r.*, u.username FROM requests r JOIN users u ON r.user_id = u.id WHERE r.status = ? ORDER BY r.created_at DESC');
        $stmt->execute([$status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function create($user_id, $document) {
        $stmt = $this->conn->prepare('INSERT INTO requests (user_id, document) VALUES (?, ?)');
        return $stmt->execute([$user_id, $document]);
    }
    public function updateStatus($id, $status) {
        $stmt = $this->conn->prepare('UPDATE requests SET status = ?, updated_at = NOW() WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }
    public function delete($id) {
        $stmt = $this->conn->prepare('DELETE FROM requests WHERE id = ?');
        return $stmt->execute([$id]);
    }
} 
<?php
require_once __DIR__ . '/../config/db_config.php';
class Assignment {
    private $conn;
    public function __construct($db) {
        $this->conn = $db;
    }
    public function getAll() {
        $stmt = $this->conn->prepare('SELECT a.*, d.id as driver_id, u.username as driver_name, r.id as request_id FROM assignments a JOIN drivers d ON a.driver_id = d.id JOIN users u ON d.user_id = u.id JOIN requests r ON a.request_id = r.id ORDER BY a.assigned_at DESC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getByDriver($driver_id) {
        $stmt = $this->conn->prepare('SELECT a.*, r.document FROM assignments a JOIN requests r ON a.request_id = r.id WHERE a.driver_id = ? ORDER BY a.assigned_at DESC');
        $stmt->execute([$driver_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function create($request_id, $driver_id) {
        $stmt = $this->conn->prepare('INSERT INTO assignments (request_id, driver_id) VALUES (?, ?)');
        return $stmt->execute([$request_id, $driver_id]);
    }
    public function updateStatus($id, $status) {
        $stmt = $this->conn->prepare('UPDATE assignments SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }
    public function delete($id) {
        $stmt = $this->conn->prepare('DELETE FROM assignments WHERE id = ?');
        return $stmt->execute([$id]);
    }
} 
<?php
require_once __DIR__ . '/../config/db_config.php';
class Report {
    private $conn;
    public function __construct($db) {
        $this->conn = $db;
    }
    public function getAll() {
        $stmt = $this->conn->prepare('SELECT r.*, u.username FROM reports r JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getByUser($user_id) {
        $stmt = $this->conn->prepare('SELECT * FROM reports WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function create($user_id, $title, $content) {
        $stmt = $this->conn->prepare('INSERT INTO reports (user_id, title, content) VALUES (?, ?, ?)');
        return $stmt->execute([$user_id, $title, $content]);
    }
    public function createFull($user_id, $report_type, $location, $description, $impact, $suggestion, $additional_notes, $contact_preference, $user_name, $report_date, $report_time) {
        $stmt = $this->conn->prepare('INSERT INTO reports (user_id, report_type, location, description, impact, suggestion, additional_notes, contact_preference, user_name, report_date, report_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        return $stmt->execute([$user_id, $report_type, $location, $description, $impact, $suggestion, $additional_notes, $contact_preference, $user_name, $report_date, $report_time]);
    }
    public function delete($id) {
        $stmt = $this->conn->prepare('DELETE FROM reports WHERE id = ?');
        return $stmt->execute([$id]);
    }
} 
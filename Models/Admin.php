<?php
require_once __DIR__ . '/User.php';

class Admin extends User {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getTotalPatients() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM users WHERE role = 'Patient'");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    public function getPendingTherapists() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM pending_therapists WHERE status = 'Pending'");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    public function getAuditLogsCount() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM audit_logs");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    public function getAllPatients() {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE role = 'Patient' ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getAllTherapists() {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE role = 'Therapist' ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function approveTherapist($therapist_id) {
        $stmt = $this->conn->prepare("UPDATE pending_therapists SET status = 'Approved' WHERE therapist_id = ?");
        return $stmt->execute([$therapist_id]);
    }
    
    public function rejectTherapist($therapist_id) {
        $stmt = $this->conn->prepare("UPDATE pending_therapists SET status = 'Rejected' WHERE therapist_id = ?");
        return $stmt->execute([$therapist_id]);
    }
}

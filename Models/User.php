<?php
require_once __DIR__ . '/../Core/Database.php';

class User {
    protected $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    public function getUserById($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }
    
    public function getUserAge($user_id) {
        $stmt = $this->conn->prepare("SELECT age FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        return $user['age'] ?? '';
    }
    
    public function getUserGender($user_id) {
        $stmt = $this->conn->prepare("SELECT gender FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        return $user['gender'] ?? '';
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
}

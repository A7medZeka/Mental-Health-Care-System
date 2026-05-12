<?php
// Models/Repositories/AccessRulesRepository.php
require_once __DIR__ . '/../../Core/SingletonDatabase.php';
class AccessRulesRepository {
    private $db;
    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }
    public function getPatientRules(int $patientId): array {
        $stmt = $this->db->prepare("SELECT resource_id, is_allowed FROM resource_access_control WHERE patient_id = ?");
        $stmt->execute([$patientId]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    }
    public function updatePatientResourceAccess(int $patientId, int $resourceId, int $status): bool {
        $stmt = $this->db->prepare("
            INSERT INTO resource_access_control (patient_id, resource_id, is_allowed, granted_by) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE is_allowed = ?
        ");
        // Get therapist ID from session or pass it as parameter
        $therapistId = $_SESSION['user_id'] ?? 1;
        return $stmt->execute([$patientId, $resourceId, $status, $therapistId, $status]);
    }
}
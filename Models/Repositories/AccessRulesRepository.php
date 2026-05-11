<?php
// Models/Repositories/AccessRulesRepository.php
require_once __DIR__ . '/../../Core/SingletonDatabase.php';
class AccessRulesRepository {
    private $db;
    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }
    public function getPatientRules(int $patientId): array {
        $stmt = $this->db->prepare("SELECT resource_id, has_access FROM patient_resources WHERE patient_id = ?");
        $stmt->execute([$patientId]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    }
    public function updatePatientResourceAccess(int $patientId, int $resourceId, int $status): bool {
        $stmt = $this->db->prepare("
            INSERT INTO patient_resources (patient_id, resource_id, has_access) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE has_access = ?
        ");
        return $stmt->execute([$patientId, $resourceId, $status, $status]);
    }
}
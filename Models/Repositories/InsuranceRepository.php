<?php
require_once __DIR__ . '/../../Core/SingletonDatabase.php';
require_once __DIR__ . '/../Insurance.php';

/**
 * InsuranceRepository — UC 4 insurance queries.
 */
class InsuranceRepository {

    private $db;

    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }

    public function getInsurance(int $patientId): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM insurance WHERE patient_id = ? LIMIT 1"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function saveInsurance(int $patientId, array $data): array {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO insurance (patient_id, provider_name, policy_number, plan_type, coverage, expiry_date, eligibility_status)
                 VALUES (?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                   provider_name     = VALUES(provider_name),
                   policy_number     = VALUES(policy_number),
                   plan_type         = VALUES(plan_type),
                   coverage          = VALUES(coverage),
                   expiry_date       = VALUES(expiry_date),
                   eligibility_status = VALUES(eligibility_status)"
            );
            $ok = $stmt->execute([
                $patientId,
                $data['provider_name'] ?? '',
                $data['policy_number'] ?? '',
                $data['plan_type'] ?? '',
                $data['coverage'] ?? '',
                $data['expiry_date'] ?? null,
                $data['eligibility_status'] ?? 'Unknown'
            ]);
            return ['success' => $ok, 'message' => $ok ? 'Insurance saved.' : 'Save failed.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getAcceptedInsurance(int $therapistId): array {
        $stmt = $this->db->prepare(
            "SELECT insurance_provider FROM therapist_insurance WHERE therapist_id = ?"
        );
        $stmt->execute([$therapistId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    public function updateEligibilityStatus(int $patientId, string $status): bool {
        $stmt = $this->db->prepare(
            "UPDATE insurance SET eligibility_status = ? WHERE patient_id = ?"
        );
        return $stmt->execute([$status, $patientId]);
    }
}

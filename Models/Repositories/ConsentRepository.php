<?php
require_once __DIR__ . '/../../Core/SingletonDatabase.php';

/**
 * ConsentRepository — UC 7 consent queries.
 */
class ConsentRepository {

    private $db;

    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }

    public function getConsents(int $patientId): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM consents WHERE patient_id = ? ORDER BY signed_date DESC"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function signConsent(int $patientId, string $docName, string $version): array {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO consents (patient_id, document_name, document_version, signed_date)
                 VALUES (?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE signed_date = NOW(), document_version = VALUES(document_version)"
            );
            $ok = $stmt->execute([$patientId, $docName, $version]);
            return ['success' => $ok, 'message' => $ok ? 'Consent signed.' : 'Signing failed.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function checkConsentStatus(int $patientId): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS cnt FROM consents WHERE patient_id = ? AND signed_date IS NOT NULL"
        );
        $stmt->execute([$patientId]);
        return ((int)($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0)) > 0;
    }
}

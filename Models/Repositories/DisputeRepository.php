<?php
require_once __DIR__ . '/../../Core/SingletonDatabase.php';

/**
 * DisputeRepository — UC 34 dispute queries.
 */
class DisputeRepository {

    private $db;

    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }

    public function saveNewDispute(int $patientId, int $appointmentId, string $reason, string $description): array {
        try {
            $code = 'DSP-' . strtoupper(bin2hex(random_bytes(4)));
            $stmt = $this->db->prepare(
                "INSERT INTO disputes (appointment_id, dispute_code, raised_by_id, reason, description, status, created_at)
                 VALUES (?, ?, ?, ?, ?, 'Under Review', NOW())"
            );
            $ok = $stmt->execute([$appointmentId, $code, $patientId, $reason, $description]);
            return ['success' => $ok, 'message' => $ok ? 'Dispute submitted.' : 'Failed.', 'dispute_code' => $code];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function fetchPending(): array {
        $stmt = $this->db->prepare(
            "SELECT d.*, u.first_name, u.last_name FROM disputes d JOIN users u ON u.user_id = d.raised_by_id WHERE d.status = 'Under Review' ORDER BY d.created_at ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function updateDisputeStatus(int $disputeId, string $result): bool {
        $stmt = $this->db->prepare("UPDATE disputes SET status = ?, resolved_at = NOW() WHERE dispute_id = ?");
        return $stmt->execute([$result, $disputeId]);
    }

    public function getByCode(string $code): ?array {
        $stmt = $this->db->prepare("SELECT * FROM disputes WHERE dispute_code = ? LIMIT 1");
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

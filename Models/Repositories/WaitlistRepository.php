<?php
require_once __DIR__ . '/../../Core/SingletonDatabase.php';

/**
 * WaitlistRepository — UC 9 clinical waitlist queries.
 */
class WaitlistRepository {

    private $db;

    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }

    public function saveEntry(array $data, int $priority): array {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO waitlist (patient_id, therapist_id, priority, position, status, created_at)
                 VALUES (?, ?, ?, ?, 'Waiting', NOW())"
            );
            $position = $this->getNextPosition((int)$data['therapist_id']);
            $ok = $stmt->execute([
                $data['patient_id'],
                $data['therapist_id'],
                $priority,
                $position
            ]);
            return ['success' => $ok, 'message' => $ok ? 'Added to waitlist.' : 'Failed.', 'position' => $position];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function removeEntry(int $waitlistId): bool {
        $stmt = $this->db->prepare("DELETE FROM waitlist WHERE waitlist_id = ?");
        return $stmt->execute([$waitlistId]);
    }

    public function getWaitlistByPriority(int $therapistId): array {
        $stmt = $this->db->prepare(
            "SELECT w.*, u.first_name, u.last_name
             FROM waitlist w
             JOIN users u ON u.user_id = w.patient_id
             WHERE w.therapist_id = ? AND w.status = 'Waiting'
             ORDER BY w.priority DESC, w.position ASC"
        );
        $stmt->execute([$therapistId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getPosition(int $waitlistId): int {
        $stmt = $this->db->prepare("SELECT position FROM waitlist WHERE waitlist_id = ?");
        $stmt->execute([$waitlistId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['position'] ?? 0);
    }

    private function getNextPosition(int $therapistId): int {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(MAX(position), 0) + 1 AS next_pos
             FROM waitlist WHERE therapist_id = ? AND status = 'Waiting'"
        );
        $stmt->execute([$therapistId]);
        return (int)($stmt->fetch(PDO::FETCH_ASSOC)['next_pos'] ?? 1);
    }
}

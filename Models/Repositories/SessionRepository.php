<?php
require_once __DIR__ . '/../../Core/SingletonDatabase.php';

/**
 * SessionRepository — UC 13 session state management queries.
 */
class SessionRepository {

    private $db;

    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }

    public function findById(int $sessionId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM sessions WHERE session_id = ?");
        $stmt->execute([$sessionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateStatus(int $sessionId, string $state): bool {
        $stmt = $this->db->prepare("UPDATE sessions SET session_state = ? WHERE session_id = ?");
        return $stmt->execute([$state, $sessionId]);
    }

    public function saveAuditLog(int $sessionId, array $statesList): void {
        $stmt = $this->db->prepare(
            "INSERT INTO audit_logs (action, severity, description, created_at) VALUES (?, ?, ?, NOW())"
        );
        $stmt->execute(['SESSION_STATE_CHANGE', 'Info', json_encode(['session_id' => $sessionId, 'states' => $statesList])]);
    }

    public function logNoShowEvent(int $sessionId): void {
        $stmt = $this->db->prepare(
            "INSERT INTO audit_logs (action, severity, description, created_at) VALUES (?, ?, ?, NOW())"
        );
        $stmt->execute(['PATIENT_NO_SHOW', 'Warning', json_encode(['session_id' => $sessionId])]);
    }

    public function createSessionForAppointment(int $appointmentId): int {
        $link = 'https://meet.platform.com/' . bin2hex(random_bytes(8));
        $stmt = $this->db->prepare("INSERT INTO sessions (appointment_id, session_state, meeting_link) VALUES (?, 'Scheduled', ?)");
        $stmt->execute([$appointmentId, $link]);
        return (int)$this->db->lastInsertId();
    }
}

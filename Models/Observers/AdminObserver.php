<?php
// Models/Observers/AdminObserver.php
require_once __DIR__ . '/../../Interfaces/Observer/IObserver.php';
require_once __DIR__ . '/../../Core/SingletonDatabase.php';

use Interfaces\Observer\IObserver;

/**
 * AdminObserver — reacts to cross-module events published by NotificationService.
 *
 * Handlers:
 *   handleCrisisAlert   — UC 29 crisis detected
 *   handleDisputeRaised — UC 34 dispute submitted
 *   handleSystemAlert   — UC 35 integrity breach
 */
class AdminObserver implements IObserver {

    private int $adminId;
    private $db;

    public function __construct(int $adminId) {
        $this->adminId = $adminId;
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }

    // ── IObserver contract ────────────────────────────────────────────────
    public function update(string $event, array $data): void {
        switch ($event) {
            case 'CRISIS_ALERT':
            case 'EMERGENCY_PROTOCOL_ACTIVATED':
                $this->handleCrisisAlert($data);
                break;
            case 'DISPUTE_RAISED':
                $this->handleDisputeRaised($data);
                break;
            case 'SYSTEM_ALERT':
            case 'INTEGRITY_BREACH':
                $this->handleSystemAlert($data);
                break;
        }
    }

    // ── UC 29 — Crisis keyword detected ───────────────────────────────────
    public function handleCrisisAlert(array $data): void {
        $message = sprintf(
            'URGENT: Crisis content detected. Post ID: %s. Severity: %s.',
            $data['post_id'] ?? 'N/A',
            $data['severity'] ?? 'Unknown'
        );
        $this->persistNotification($message, 'CrisisAlert');
        $this->logAuditEntry('CRISIS_ALERT', $data);
    }

    // ── UC 34 — Dispute submitted by patient ──────────────────────────────
    public function handleDisputeRaised(array $data): void {
        $message = sprintf(
            'New dispute submitted. Dispute Code: %s. Patient ID: %s.',
            $data['dispute_code'] ?? 'N/A',
            $data['patient_id'] ?? 'N/A'
        );
        $this->persistNotification($message, 'DisputeRaised');
    }

    // ── UC 35 — Integrity breach / system alert ───────────────────────────
    public function handleSystemAlert(array $data): void {
        $message = sprintf(
            'System Alert: %s. Action required immediately.',
            $data['message'] ?? 'Unknown system event'
        );
        $this->persistNotification($message, 'SystemAlert');
        $this->logAuditEntry('SYSTEM_ALERT', $data);
    }

    // ── Helpers ───────────────────────────────────────────────────────────
    private function persistNotification(string $message, string $type): void {
        $stmt = $this->db->prepare(
            "INSERT INTO notifications (user_id, message, type, is_read, created_at)
             VALUES (?, ?, ?, 0, NOW())"
        );
        $stmt->execute([$this->adminId, $message, $type]);
    }

    private function logAuditEntry(string $action, array $data): void {
        $stmt = $this->db->prepare(
            "INSERT INTO audit_logs (action, severity, description, user_id, created_at)
             VALUES (?, ?, ?, ?, NOW())"
        );
        $stmt->execute([
            $action,
            $data['severity'] ?? 'High',
            json_encode($data),
            $this->adminId
        ]);
    }

    public function getAdminId(): int {
        return $this->adminId;
    }
}

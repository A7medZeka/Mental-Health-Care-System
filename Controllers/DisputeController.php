<?php
// Controllers/DisputeController.php
require_once __DIR__ . '/../Core/SingletonDatabase.php';
require_once __DIR__ . '/../Models/Repositories/DisputeRepository.php';
require_once __DIR__ . '/../Models/Services/NotificationService.php';

/**
 * DisputeController — UC 34: Handle Service Disputes and Refunds.
 *
 * SD flow (Patient side):
 *   Patient → DisputeController.createDispute(patientId, appointmentId, reason, description)
 *     → DisputeRepository.saveNewDispute()
 *     → NotificationService.publishEvent('DISPUTE_RAISED')
 *     → return disputeCode
 *
 * SD flow (Admin side):
 *   Admin → DisputeController.resolveDispute(disputeId, action, adminId)
 *     → [alt] action="Refund" → processRefund()
 *     → DisputeRepository.updateDisputeStatus()
 */
class DisputeController {

    private DisputeRepository   $repo;
    private NotificationService $notifier;

    public function __construct(DisputeRepository $repo, NotificationService $notifier) {
        $this->repo     = $repo;
        $this->notifier = $notifier;
    }

    /**
     * +createDispute(patientId, appointmentId, reason, description) : void
     */
    public function createDispute(int $patientId, int $appointmentId, string $reason, string $description): array {
        $result = $this->repo->saveNewDispute($patientId, $appointmentId, $reason, $description);

        if ($result['success']) {
            $this->notifier->publishEvent('DISPUTE_RAISED', [
                'patient_id'   => $patientId,
                'dispute_code' => $result['dispute_code'] ?? '',
                'reason'       => $reason,
            ]);
        }

        return $result;
    }

    /**
     * +resolveDispute(disputeId, action, adminId) : void
     */
    public function resolveDispute(int $disputeId, string $action, int $adminId): array {
        $validActions = ['Resolved', 'Refunded', 'Dismissed'];
        if (!in_array($action, $validActions, true)) {
            return ['success' => false, 'message' => 'Invalid resolution action.'];
        }

        if ($action === 'Refunded') {
            $this->processRefund($disputeId);
        }

        $ok = $this->repo->updateDisputeStatus($disputeId, $action);

        // Log the resolution
        $db = SingletonDatabase::getInstance()->getConnection();
        $db->prepare(
            "INSERT INTO audit_logs (action, severity, description, user_id, created_at) VALUES (?, ?, ?, ?, NOW())"
        )->execute([
            'DISPUTE_RESOLVED',
            'Info',
            json_encode(['dispute_id' => $disputeId, 'action' => $action]),
            $adminId
        ]);

        return ['success' => $ok, 'message' => $ok ? "Dispute {$action}." : 'Resolution failed.'];
    }

    /**
     * +processRefund(disputeId) : void
     */
    public function processRefund(int $disputeId): void {
        $db = SingletonDatabase::getInstance()->getConnection();

        // Get the dispute to find the appointment, then find the payment
        $dispute = $this->repo->fetchPending();
        // Simple refund: mark payment as Refunded
        $stmt = $db->prepare(
            "UPDATE payments SET status = 'Refunded'
             WHERE appointment_id = (SELECT appointment_id FROM disputes WHERE dispute_id = ?)"
        );
        $stmt->execute([$disputeId]);
    }

    /**
     * +getDisputeQueue() : array
     */
    public function getDisputeQueue(): array {
        return $this->repo->fetchPending();
    }
}

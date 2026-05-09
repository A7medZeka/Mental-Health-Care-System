<?php
// Controllers/WaitlistController.php
require_once __DIR__ . '/../Models/Repositories/WaitlistRepository.php';
require_once __DIR__ . '/../Models/Services/NotificationService.php';

/**
 * WaitlistController — UC 9: Manage Clinical Waitlist.
 *
 * SD flow:
 *   Patient → WaitlistController.addToWaitlist(patientId, therapistId)
 *     → checkAvailability(therapistId)
 *     → [alt] available → "No waitlist needed"
 *     → [alt] full      → WaitlistRepository.saveEntry(data, priority)
 *     → return position
 *
 *   System → notifyNextPatient(therapistId)
 *     → WaitlistRepository.getWaitlistByPriority()
 *     → NotificationService.queueNotification()
 */
class WaitlistController {

    private WaitlistRepository  $repo;
    private NotificationService $notifier;

    public function __construct(WaitlistRepository $repo, NotificationService $notifier) {
        $this->repo     = $repo;
        $this->notifier = $notifier;
    }

    /**
     * +addToWaitlist(patientId, therapistId) : void
     */
    public function addToWaitlist(int $patientId, int $therapistId): array {
        if ($this->checkAvailability($therapistId)) {
            return ['success' => false, 'message' => 'Therapist has open slots. No waitlist needed.'];
        }

        $priority = $this->calculatePriority($patientId);
        $result = $this->repo->saveEntry(
            ['patient_id' => $patientId, 'therapist_id' => $therapistId],
            $priority
        );

        return $result;
    }

    /**
     * +removeFromWaitlist(entryId) : void
     */
    public function removeFromWaitlist(int $entryId): bool {
        return $this->repo->removeEntry($entryId);
    }

    /**
     * +notifyNextPatient(therapistId) : void
     */
    public function notifyNextPatient(int $therapistId): void {
        $waitlist = $this->repo->getWaitlistByPriority($therapistId);
        if (empty($waitlist)) {
            return;
        }

        $next = $waitlist[0];
        $this->notifier->publishEvent('WAITLIST_SLOT_FREED', [
            'patient_id'   => $next['patient_id'],
            'therapist_id' => $therapistId,
        ]);
        $this->notifier->queueNotification(
            (int)$next['patient_id'],
            'A slot has opened with your requested therapist. Book now!',
            'WaitlistNotified'
        );
    }

    /**
     * +getWaitlistByPriority(therapistId) : array
     */
    public function getWaitlistByPriority(int $therapistId): array {
        return $this->repo->getWaitlistByPriority($therapistId);
    }

    /**
     * +checkAvailability(therapistId) : Boolean
     */
    public function checkAvailability(int $therapistId): bool {
        $db = SingletonDatabase::getInstance()->getConnection();
        $stmt = $db->prepare(
            "SELECT COUNT(*) AS cnt FROM appointments
             WHERE therapist_id = ? AND status IN ('Scheduled','Confirmed')
               AND appointment_date >= NOW()"
        );
        $stmt->execute([$therapistId]);
        $count = (int)($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);
        return $count < 10; // arbitrary capacity threshold
    }

    private function calculatePriority(int $patientId): int {
        $db = SingletonDatabase::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT level_of_care FROM patients WHERE patient_id = ?");
        $stmt->execute([$patientId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $level = $row['level_of_care'] ?? 'Outpatient';

        $map = ['Inpatient' => 4, 'Partial Hospitalization' => 3, 'Intensive Outpatient' => 2, 'Outpatient' => 1];
        return $map[$level] ?? 1;
    }
}

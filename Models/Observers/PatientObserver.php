<?php
require_once __DIR__ . '/../../Interfaces/Observer/IObserver.php';
require_once __DIR__ . '/../Services/NotificationService.php';

/**
 * PatientObserver - Implements Observer Pattern
 * Handles all events that patients need to respond to
 */
class PatientObserver implements IObserver {
    private int $patientId;
    private NotificationService $notificationService;

    public function __construct(int $patientId, ?NotificationService $notificationService = null) {
        $this->patientId = $patientId;
        $this->notificationService = $notificationService ?? new NotificationService();
        
        // Register with NotificationService
        $this->notificationService->registerObserver($this);
    }

    /**
     * Handle match found event (UC 2)
     */
    public function handleMatchFound(array $data): void {
        $message = sprintf(
            "You have been matched with therapist %s! View their profile to schedule your first session.",
            $data['therapist_name'] ?? 'a qualified professional'
        );
        $this->notificationService->queueNotification($this->patientId, $message, 'MatchFound');
    }

    /**
     * Handle session reminder event (UC 14)
     */
    public function handleSessionReminder(array $data): void {
        if ($data['patient_id'] === $this->patientId) {
            $message = sprintf(
                "Reminder: You have a session scheduled for %s.",
                $data['session_time'] ?? ($data['appointment_date'] ?? 'soon')
            );
            $this->notificationService->queueNotification($this->patientId, $message, 'SessionReminder');
        }
    }

    /**
     * Handle badge awarded event (UC 26)
     */
    public function handleBadgeAwarded(array $data): void {
        if ($data['patient_id'] === $this->patientId) {
            $message = sprintf(
                "Congratulations! You've earned the '%s' badge for %s.",
                $data['badge_type'] ?? 'Achievement',
                $data['achievement_reason'] ?? 'your progress'
            );
            $this->notificationService->queueNotification($this->patientId, $message, 'BadgeAwarded');
        }
    }

    /**
     * Handle waitlist notified event (UC 9)
     */
    public function handleWaitlistNotified(array $data): void {
        if ($data['patient_id'] === $this->patientId) {
            $message = sprintf(
                "Good news! A slot has opened up with %s. You can now book your session.",
                $data['therapist_name'] ?? 'your preferred therapist'
            );
            $this->notificationService->queueNotification($this->patientId, $message, 'WaitlistAvailable');
        }
    }

    /**
     * Main update method - routes events to appropriate handlers
     */
    public function update(string $event, array $data): void {
        switch ($event) {
            case 'MATCH_FOUND':
            case 'match_found':
                $this->handleMatchFound($data);
                break;
                
            case 'SESSION_REMINDER':
            case 'session_reminder':
                $this->handleSessionReminder($data);
                break;
                
            case 'BADGE_AWARDED':
            case 'badge_awarded':
                $this->handleBadgeAwarded($data);
                break;
                
            case 'WAITLIST_AVAILABLE':
            case 'WAITLIST_SLOT_FREED':
            case 'waitlist_available':
                $this->handleWaitlistNotified($data);
                break;
                
            default:
                error_log("PatientObserver: Unknown event '$event' received");
                break;
        }
    }

    /**
     * Get patient ID
     */
    public function getPatientId(): int {
        return $this->patientId;
    }

    /**
     * Cleanup - unregister from NotificationService
     */
    public function __destruct() {
        $this->notificationService->removeObserver($this);
    }
}

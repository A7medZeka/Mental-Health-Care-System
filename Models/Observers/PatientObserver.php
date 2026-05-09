<?php
require_once __DIR__ . '/../../Interfaces/Observer/IObserver.php';
require_once __DIR__ . '/../../Core/NotificationService.php';
require_once __DIR__ . '/../../Core/SingletonDatabase.php';

/**
 * PatientObserver - Implements Observer Pattern
 * Handles all events that patients need to respond to
 */
class PatientObserver implements IObserver {
    private int $patientId;
    private $database;

    public function __construct(int $patientId) {
        $this->patientId = $patientId;
        $this->database = SingletonDatabase::getInstance();
        
        // Register with NotificationService
        NotificationService::getInstance()->registerObserver($this);
    }

    /**
     * Handle match found event (UC 2)
     */
    public function handleMatchFound(array $data): void {
        $message = sprintf(
            "You have been matched with therapist %s! View their profile to schedule your first session.",
            $data['therapist_name'] ?? 'a qualified professional'
        );
        
        $this->database->execute(
            "INSERT INTO notifications (user_id, message, type, created_at) VALUES (?, ?, ?, NOW())",
            [$this->patientId, $message, 'match_found']
        );
    }

    /**
     * Handle session reminder event (UC 14)
     */
    public function handleSessionReminder(array $data): void {
        if ($data['patient_id'] === $this->patientId) {
            $message = sprintf(
                "Reminder: You have a session scheduled for %s.",
                $data['session_time'] ?? 'soon'
            );
            
            $this->database->execute(
                "INSERT INTO notifications (user_id, message, type, created_at) VALUES (?, ?, ?, NOW())",
                [$this->patientId, $message, 'session_reminder']
            );
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
            
            $this->database->execute(
                "INSERT INTO notifications (user_id, message, type, created_at) VALUES (?, ?, ?, NOW())",
                [$this->patientId, $message, 'badge_awarded']
            );
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
            
            $this->database->execute(
                "INSERT INTO notifications (user_id, message, type, created_at) VALUES (?, ?, ?, NOW())",
                [$this->patientId, $message, 'waitlist_available']
            );
        }
    }

    /**
     * Main update method - routes events to appropriate handlers
     */
    public function update(string $event, array $data): void {
        switch ($event) {
            case 'match_found':
                $this->handleMatchFound($data);
                break;
                
            case 'session_reminder':
                $this->handleSessionReminder($data);
                break;
                
            case 'badge_awarded':
                $this->handleBadgeAwarded($data);
                break;
                
            case 'waitlist_available':
                $this->handleWaitlistNotified($data);
                break;
                
            default:
                // Log unknown events for debugging
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
        NotificationService::getInstance()->removeObserver($this);
    }
}

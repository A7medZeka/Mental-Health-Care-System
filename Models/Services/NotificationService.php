<?php
require_once __DIR__ . '/../../Interfaces/Observer/IObserver.php';
require_once __DIR__ . '/../../Interfaces/Observer/ISubject.php';
require_once __DIR__ . '/../../Core/SingletonDatabase.php';

use Interfaces\Observer\IObserver;
use Interfaces\Observer\ISubject;

class NotificationService implements ISubject {

    private static ?NotificationService $instance = null;
    private static array $observers = [];
    private static array $eventQueue = [];

    public static function getInstance(): NotificationService {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function registerObserver(IObserver $o): void {
        self::$observers[] = $o;
    }

    public function removeObserver(IObserver $o): void {
        self::$observers = array_filter(self::$observers, fn($obs) => $obs !== $o);
    }

    public function notifyObservers(string $event, array $data): void {
        foreach (self::$observers as $observer) {
            $observer->update($event, $data);
        }
    }
    public function publishEvent(string $event, array $data): void {
        self::$eventQueue[] = ['event' => $event, 'data' => $data];
        $this->notifyObservers($event, $data);
    }
    public function queueNotification(int $userId, string $msg, string $type): void {
        $db = SingletonDatabase::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $msg, $type]);
    }

    /**
     * UC 14 — Send automated session reminders to patient and therapist.
     *
     * Fires SESSION_REMINDER event through the Observer pattern so that
     * PatientObserver and TherapistObserver handle their own notifications.
     */
    public function sendAutomatedReminders(int $patientId, int $therapistId, array $sessionData = []): void {
        $data = array_merge($sessionData, [
            'patient_id'   => $patientId,
            'therapist_id' => $therapistId,
        ]);

        // Fire-and-forget through Observer pattern
        $this->publishEvent('SESSION_REMINDER', $data);

        // Also queue persistent notification records
        $message = sprintf(
            'Session reminder: Your session is scheduled for %s.',
            $sessionData['appointment_date'] ?? 'soon'
        );
        $this->queueNotification($patientId,   $message, 'SessionReminder');
        $this->queueNotification($therapistId, $message, 'SessionReminder');
    }
}

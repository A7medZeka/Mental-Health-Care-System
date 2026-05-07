<?php

/**
 * Observer Pattern Implementation for Patient Status Changes
 * Allows multiple observers to be notified when patient status changes
 */

// Subject Interface
interface PatientStatusSubject {
    public function attach(PatientStatusObserver $observer): void;
    public function detach(PatientStatusObserver $observer): void;
    public function notify(PatientStatusChangeEvent $event): void;
}

// Observer Interface
interface PatientStatusObserver {
    public function update(PatientStatusChangeEvent $event): void;
}

// Event Data Object
class PatientStatusChangeEvent {
    private $patientId;
    private $patientName;
    private $oldStatus;
    private $newStatus;
    private $timestamp;
    private $changedBy;

    public function __construct(int $patientId, string $patientName, string $oldStatus, string $newStatus, int $changedBy) {
        $this->patientId = $patientId;
        $this->patientName = $patientName;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->changedBy = $changedBy;
        $this->timestamp = new DateTime();
    }

    public function getPatientId(): int {
        return $this->patientId;
    }

    public function getPatientName(): string {
        return $this->patientName;
    }

    public function getOldStatus(): string {
        return $this->oldStatus;
    }

    public function getNewStatus(): string {
        return $this->newStatus;
    }

    public function getChangedBy(): int {
        return $this->changedBy;
    }

    public function getTimestamp(): DateTime {
        return $this->timestamp;
    }

    public function toArray(): array {
        return [
            'patient_id' => $this->patientId,
            'patient_name' => $this->patientName,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'changed_by' => $this->changedBy,
            'timestamp' => $this->timestamp->format('Y-m-d H:i:s')
        ];
    }
}

// Concrete Subject
class PatientStatusManager implements PatientStatusSubject {
    private $observers = [];
    private $database;

    public function __construct() {
        $this->database = SingletonDatabase::getInstance();
    }

    public function attach(PatientStatusObserver $observer): void {
        $this->observers[] = $observer;
    }

    public function detach(PatientStatusObserver $observer): void {
        $this->observers = array_filter($this->observers, function($obs) use ($observer) {
            return $obs !== $observer;
        });
    }

    public function notify(PatientStatusChangeEvent $event): void {
        foreach ($this->observers as $observer) {
            $observer->update($event);
        }
    }

    public function changePatientStatus(int $patientId, string $newStatus, int $changedBy): bool {
        // Get current patient data
        $stmt = $this->database->execute(
            "SELECT user_id, first_name, last_name, status FROM users WHERE user_id = ? AND role = 'Patient'",
            [$patientId]
        );
        $patient = $stmt->fetch();

        if (!$patient) {
            return false;
        }

        $oldStatus = $patient['status'];
        $patientName = $patient['first_name'] . ' ' . $patient['last_name'];

        // Update status in database
        $updateStmt = $this->database->execute(
            "UPDATE users SET status = ? WHERE user_id = ? AND role = 'Patient'",
            [$newStatus, $patientId]
        );

        if ($updateStmt->rowCount() > 0) {
            // Create event and notify observers
            $event = new PatientStatusChangeEvent($patientId, $patientName, $oldStatus, $newStatus, $changedBy);
            $this->notify($event);
            return true;
        }

        return false;
    }
}

// Concrete Observers

// Database Logger Observer
class PatientStatusDatabaseLogger implements PatientStatusObserver {
    private $database;

    public function __construct() {
        $this->database = SingletonDatabase::getInstance();
    }

    public function update(PatientStatusChangeEvent $event): void {
        $this->database->execute(
            "INSERT INTO patient_status_logs (patient_id, patient_name, old_status, new_status, changed_by, timestamp) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $event->getPatientId(),
                $event->getPatientName(),
                $event->getOldStatus(),
                $event->getNewStatus(),
                $event->getChangedBy(),
                $event->getTimestamp()->format('Y-m-d H:i:s')
            ]
        );
    }
}

// Email Notification Observer
class PatientStatusEmailNotifier implements PatientStatusObserver {
    public function update(PatientStatusChangeEvent $event): void {
        // In a real implementation, this would send an email
        // For now, we'll just log the email attempt
        error_log(sprintf(
            "[EMAIL] Patient status change notification sent for patient %s: %s -> %s",
            $event->getPatientName(),
            $event->getOldStatus(),
            $event->getNewStatus()
        ));
    }
}

// Audit Log Observer
class PatientStatusAuditLogger implements PatientStatusObserver {
    private $database;

    public function __construct() {
        $this->database = SingletonDatabase::getInstance();
    }

    public function update(PatientStatusChangeEvent $event): void {
        $this->database->execute(
            "INSERT INTO audit_logs (action, table_name, record_id, old_values, new_values, user_id, timestamp) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                'STATUS_CHANGE',
                'users',
                $event->getPatientId(),
                json_encode(['status' => $event->getOldStatus()]),
                json_encode(['status' => $event->getNewStatus()]),
                $event->getChangedBy(),
                $event->getTimestamp()->format('Y-m-d H:i:s')
            ]
        );
    }
}

// File Logger Observer
class PatientStatusFileLogger implements PatientStatusObserver {
    private $logFile;

    public function __construct(string $logFile = 'patient_status_changes.log') {
        $this->logFile = $logFile;
    }

    public function update(PatientStatusChangeEvent $event): void {
        $logEntry = sprintf(
            "[%s] Patient ID: %d (%s) - Status changed from '%s' to '%s' by user %d\n",
            $event->getTimestamp()->format('Y-m-d H:i:s'),
            $event->getPatientId(),
            $event->getPatientName(),
            $event->getOldStatus(),
            $event->getNewStatus(),
            $event->getChangedBy()
        );

        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

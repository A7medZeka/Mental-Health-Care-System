<?php

require_once 'SingletonDatabase.php';
require_once 'ObserverPattern.php';
require_once 'ImmutablePattern.php';

/**
 * Demonstration of all three design patterns working together
 * Shows how Singleton, Observer, and Immutable patterns can be used in the Mental Health Care System
 */

class PatternDemonstration {
    private $database;
    private $patientStatusManager;
    private $userFactory;

    public function __construct() {
        // Singleton Pattern: Get the single database instance
        $this->database = SingletonDatabase::getInstance();
        
        // Observer Pattern: Set up patient status change notifications
        $this->patientStatusManager = new PatientStatusManager();
        
        // Attach observers
        $this->patientStatusManager->attach(new PatientStatusDatabaseLogger());
        $this->patientStatusManager->attach(new PatientStatusEmailNotifier());
        $this->patientStatusManager->attach(new PatientStatusAuditLogger());
        $this->patientStatusManager->attach(new PatientStatusFileLogger());
        
        // Immutable Pattern: Factory for creating immutable objects
        $this->userFactory = new ImmutableUserFactory();
    }

    /**
     * Demonstrate patient status change using all three patterns
     */
    public function demonstratePatientStatusChange(int $patientId, string $newStatus, int $adminId): array {
        try {
            // Immutable Pattern: Get immutable patient object
            $patient = $this->userFactory->createPatientFromId($patientId);
            
            if (!$patient) {
                return ['success' => false, 'message' => 'Patient not found'];
            }

            $oldStatus = $patient->getStatus();
            
            // Observer Pattern: Change status and notify all observers
            $success = $this->patientStatusManager->changePatientStatus($patientId, $newStatus, $adminId);
            
            if ($success) {
                // Immutable Pattern: Create new immutable object with updated status
                $updatedPatient = $patient->withStatus($newStatus);
                
                return [
                    'success' => true,
                    'message' => 'Patient status updated successfully',
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'patient_data' => $updatedPatient->toArray()
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to update patient status'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Demonstrate immutable object creation and manipulation
     */
    public function demonstrateImmutableObjects(): array {
        // Create immutable patient object
        $patient = new ImmutablePatientRecord(
            1,
            'john_doe',
            'john@example.com',
            'John',
            'Doe',
            'Registered',
            '2024-01-15 10:00:00',
            35,
            'Male',
            '555-0123',
            '123 Main St',
            'No prior mental health issues',
            null
        );

        // Original patient data
        $originalData = $patient->toArray();

        // Create new instance with modified status (immutable)
        /** @var ImmutablePatient $updatedPatient */
        $updatedPatient = $patient->withStatus('Screened');
        
        // Create another instance with assigned therapist (immutable)
        $patientWithTherapist = $updatedPatient->withAssignedTherapist(5);

        return [
            'original_patient' => $originalData,
            'updated_patient' => $updatedPatient->toArray(),
            'patient_with_therapist' => $patientWithTherapist->toArray(),
            'objects_are_different' => [
                'original_vs_updated' => $patient !== $updatedPatient,
                'updated_vs_with_therapist' => $updatedPatient !== $patientWithTherapist
            ]
        ];
    }

    /**
     * Demonstrate singleton database connection
     */
    public function demonstrateSingletonConnection(): array {
        // Get multiple instances - they should be the same
        $db1 = SingletonDatabase::getInstance();
        $db2 = SingletonDatabase::getInstance();
        $db3 = SingletonDatabase::getInstance();

        // Test query using singleton connection
        $stmt = $db1->execute("SELECT COUNT(*) as total_users FROM users");
        $result = $stmt->fetch();

        return [
            'instances_are_same' => [
                'db1_equals_db2' => $db1 === $db2,
                'db2_equals_db3' => $db2 === $db3,
                'db1_equals_db3' => $db1 === $db3
            ],
            'database_connection_works' => [
                'total_users' => $result['total_users'] ?? 0
            ],
            'connection_class' => get_class($db1)
        ];
    }

    /**
     * Run all demonstrations
     */
    public function runAllDemos(): array {
        return [
            'singleton_demo' => $this->demonstrateSingletonConnection(),
            'immutable_demo' => $this->demonstrateImmutableObjects(),
            'observer_demo' => $this->demonstratePatientStatusChange(1, 'Screened', 1)
        ];
    }
}

// Usage example (uncomment to test):
/*
$demo = new PatternDemonstration();
$results = $demo->runAllDemos();

echo "<h3>Design Patterns Demonstration Results</h3>";
echo "<pre>" . json_encode($results, JSON_PRETTY_PRINT) . "</pre>";
*/

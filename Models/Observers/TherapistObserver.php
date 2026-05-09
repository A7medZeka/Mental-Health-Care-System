<?php
// Models/Observers/TherapistObserver.php
require_once __DIR__ . '/../../Interfaces/Observer/IObserver.php';

use Interfaces\Observer\IObserver;

class TherapistObserver implements IObserver {
    private int $therapist_id;

    public function __construct(int $therapist_id) {
        $this->therapist_id = $therapist_id;
    }

    // استقبال التحديثات من السيستم
    public function update(string $event, $data): void {
        switch ($event) {
            case 'PATIENT_NO_SHOW':
                $this->handleNoShow($data);
                break;
            case 'CREDENTIAL_EXPIRY':
                $this->handleCredentialExpiry($data);
                break;
            case 'NEW_PATIENT_MATCHED':
                $this->handleNewPatientMatched($data);
                break;
        }
    }

    // الدوال المحددة في الـ Class Diagram الخاص بك
    public function handleNoShow($data): void {
        // منطق التعامل مع غياب المريض
    }

    public function handleCredentialExpiry($data): void {
        // منطق التحذير من انتهاء الرخصة
    }

    public function handleNewPatientMatched($data): void {
        // منطق الترحيب بمريض جديد
    }
}
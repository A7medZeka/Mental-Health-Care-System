<?php
// Models/Appointment.php
require_once __DIR__ . '/../Interfaces/IStateMachine.php';
require_once __DIR__ . '/Payment.php';
class Appointment implements IStateMachine {
    private int $appointment_id;
    private int $patient_id;
    private int $therapist_id;
    private string $appointment_date;
    private string $status;
    private string $session_type;
    private ?Payment $payment;
    public function __construct(array $data = [], ?Payment $payment = null) {
        $this->appointment_id   = (int) ($data['appointment_id'] ?? 0);
        $this->patient_id       = (int) ($data['patient_id'] ?? 0);
        $this->therapist_id     = (int) ($data['therapist_id'] ?? 0);
        $this->appointment_date = $data['appointment_date'] ?? date('Y-m-d H:i:s');
        $this->status           = $data['status'] ?? 'Scheduled';
        $this->session_type     = $data['session_type'] ?? 'Standard';

        // ربط الدفع بالموعد
        $this->payment = $payment;
    }
    public function setPayment(Payment $payment): void {
        $this->payment = $payment;
    }
    public function getPayment(): ?Payment {
        return $this->payment;
    }
    public function getState(): string { return $this->status; }
    public function transition(string $newState): bool {
        $allowed = ['Scheduled' => ['Confirmed', 'Cancelled'], 'Confirmed' => ['Completed', 'Cancelled']];
        if (isset($allowed[$this->status]) && in_array($newState, $allowed[$this->status])) {
            $this->status = $newState;
            return true;
        }
        return false;
    }
    public function getAppointmentId(): int { return $this->appointment_id; }
    public function getTherapistId(): int { return $this->therapist_id; }
}
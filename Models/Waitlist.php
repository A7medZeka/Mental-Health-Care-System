<?php
/**
 * Waitlist — Patient entity (UC 9).
 */
class Waitlist {

    private int    $waitlistId;
    private int    $patientId;
    private int    $therapistId;
    private int    $priority;
    private int    $position;
    private string $status;
    private string $createdAt;

    public function __construct(array $data = []) {
        $this->waitlistId  = (int)($data['waitlist_id'] ?? 0);
        $this->patientId   = (int)($data['patient_id'] ?? 0);
        $this->therapistId = (int)($data['therapist_id'] ?? 0);
        $this->priority    = (int)($data['priority'] ?? 0);
        $this->position    = (int)($data['position'] ?? 0);
        $this->status      = $data['status'] ?? 'Waiting';
        $this->createdAt   = $data['created_at'] ?? date('Y-m-d H:i:s');
    }

    public function getWaitlistId(): int   { return $this->waitlistId; }
    public function getPatientId(): int    { return $this->patientId; }
    public function getTherapistId(): int  { return $this->therapistId; }
    public function getPriority(): int     { return $this->priority; }
    public function getPosition(): int     { return $this->position; }
    public function getStatus(): string    { return $this->status; }
    public function getCreatedAt(): string { return $this->createdAt; }

    public function setStatus(string $status): void { $this->status = $status; }
    public function setPosition(int $pos): void     { $this->position = $pos; }
}

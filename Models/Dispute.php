<?php
/**
 * Dispute — Patient entity (UC 34).
 */
class Dispute {

    private int     $disputeId;
    private int     $appointmentId;
    private string  $disputeCode;
    private int     $raisedById;
    private string  $reason;
    private string  $description;
    private string  $status;
    private string  $createdAt;
    private ?string $resolvedAt;

    public function __construct(array $data = []) {
        $this->disputeId     = (int)($data['dispute_id'] ?? 0);
        $this->appointmentId = (int)($data['appointment_id'] ?? 0);
        $this->disputeCode   = $data['dispute_code'] ?? '';
        $this->raisedById    = (int)($data['raised_by_id'] ?? 0);
        $this->reason        = $data['reason'] ?? '';
        $this->description   = $data['description'] ?? '';
        $this->status        = $data['status'] ?? 'Under Review';
        $this->createdAt     = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->resolvedAt    = $data['resolved_at'] ?? null;
    }

    public function getDisputeId(): int       { return $this->disputeId; }
    public function getAppointmentId(): int   { return $this->appointmentId; }
    public function getDisputeCode(): string  { return $this->disputeCode; }
    public function getRaisedById(): int      { return $this->raisedById; }
    public function getReason(): string       { return $this->reason; }
    public function getDescription(): string  { return $this->description; }
    public function getStatus(): string       { return $this->status; }
    public function getCreatedAt(): string    { return $this->createdAt; }
    public function getResolvedAt(): ?string  { return $this->resolvedAt; }

    public function setStatus(string $status): void     { $this->status = $status; }
    public function setResolvedAt(string $ts): void     { $this->resolvedAt = $ts; }
}

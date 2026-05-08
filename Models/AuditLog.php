<?php
class AuditLog {
    private int $id;
    private ?int $parent_log_id;
    private string $eventId;
    private string $incident_code;
    private string $action;
    private string $severity;
    private string $description;
    private ?int $handledBy;
    private $timestamp;
    public function __construct(array $data = []) {
        $this->id = $data['id'] ?? 0;
        $this->parent_log_id = $data['parent_log_id'] ?? null;
        $this->eventId = $data['eventId'] ?? uniqid('EVT-'); // توليد كود فريد
        $this->incident_code = $data['incident_code'] ?? 'CRISIS-001';
        $this->action = $data['action'] ?? 'System Auto-Flag';
        $this->severity = $data['severity'] ?? 'Critical';
        $this->description = $data['description'] ?? '';
        $this->handledBy = $data['handledBy'] ?? null;
        $this->timestamp = $data['timestamp'] ?? date('Y-m-d H:i:s');
    }
    public function getId(): int { return $this->id; }
    public function getEventID(): string { return $this->eventId; }
    public function getAction(): string { return $this->action; }
    public function getSeverity(): string { return $this->severity; }
    public function getTimestamp(): string { return $this->timestamp; }
    public function getDescription(): string { return $this->description; }
    public function getHandledBy(): ?int { return $this->handledBy; }
}
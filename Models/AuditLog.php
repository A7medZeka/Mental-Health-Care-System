<?php
/**
 * AuditLog — «Immutable» entity.
 *
 * All fields set in constructor. No setters. Deletion always rejected.
 * UC 35 explicitly shows that deletion requests are denied —
 * enforce this in AuditController.verifyIntegrityPolicy().
 */
class AuditLog {
    private int $id;
    private ?int $parent_log_id;
    private string $eventId;
    private string $incident_code;
    private string $action;
    private string $severity;
    private string $description;
    private ?int $handledBy;
    private string $timestamp;

    /**
     * Constructor — sets all fields. Fails fast if required fields are null.
     */
    public function __construct(
        int     $id,
        ?int    $parentLogId,
        string  $eventId,
        string  $incidentCode,
        string  $action,
        string  $severity,
        string  $description,
        ?int    $handledBy,
        ?string $timestamp = null
    ) {
        // Fail-fast: required fields must not be empty
        if (empty($eventId)) {
            throw new \InvalidArgumentException('AuditLog: eventId is required.');
        }
        if (empty($action)) {
            throw new \InvalidArgumentException('AuditLog: action is required.');
        }
        if (empty($severity)) {
            throw new \InvalidArgumentException('AuditLog: severity is required.');
        }

        $this->id            = $id;
        $this->parent_log_id = $parentLogId;
        $this->eventId       = $eventId;
        $this->incident_code = $incidentCode;
        $this->action        = $action;
        $this->severity      = $severity;
        $this->description   = $description;
        $this->handledBy     = $handledBy;
        $this->timestamp     = $timestamp ?? date('Y-m-d H:i:s');
    }

    // ── Getters only — zero setters ──────────────────────────────────────
    public function getId(): int             { return $this->id; }
    public function getParentLogId(): ?int   { return $this->parent_log_id; }
    public function getEventID(): string     { return $this->eventId; }
    public function getIncidentCode(): string { return $this->incident_code; }
    public function getAction(): string      { return $this->action; }
    public function getSeverity(): string    { return $this->severity; }
    public function getDescription(): string { return $this->description; }
    public function getHandledBy(): ?int     { return $this->handledBy; }
    public function getTimestamp(): string    { return $this->timestamp; }

    /**
     * Factory — create from database row (backward-compatible).
     */
    public static function fromDatabase(array $data): self {
        return new self(
            (int)($data['id'] ?? $data['log_id'] ?? 0),
            isset($data['parent_log_id']) ? (int)$data['parent_log_id'] : null,
            $data['eventId'] ?? $data['event_id'] ?? uniqid('EVT-'),
            $data['incident_code'] ?? 'GENERAL',
            $data['action'] ?? 'System Auto-Flag',
            $data['severity'] ?? 'Info',
            $data['description'] ?? '',
            isset($data['handledBy']) ? (int)$data['handledBy'] : (isset($data['user_id']) ? (int)$data['user_id'] : null),
            $data['timestamp'] ?? $data['created_at'] ?? null
        );
    }

    // ── Immutability enforcement ─────────────────────────────────────────
    public function __set($name, $value) {
        throw new \Exception("AuditLog is immutable — cannot modify property '{$name}'.");
    }

    private function __clone() {}
}
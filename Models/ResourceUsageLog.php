<?php
/**
 * ResourceUsageLog — Patient entity (UC 24).
 * Logs every mindfulness session start/stop.
 */
class ResourceUsageLog {

    private int    $logId;
    private int    $patientId;
    private int    $resourceId;
    private int    $durationMinutes;
    private string $completedAt;

    public function __construct(array $data = []) {
        $this->logId           = (int)($data['log_id'] ?? 0);
        $this->patientId       = (int)($data['patient_id'] ?? 0);
        $this->resourceId      = (int)($data['resource_id'] ?? 0);
        $this->durationMinutes = (int)($data['duration_minutes'] ?? 0);
        $this->completedAt     = $data['completed_at'] ?? date('Y-m-d H:i:s');
    }

    public function getLogId(): int           { return $this->logId; }
    public function getPatientId(): int       { return $this->patientId; }
    public function getResourceId(): int      { return $this->resourceId; }
    public function getDurationMinutes(): int { return $this->durationMinutes; }
    public function getCompletedAt(): string  { return $this->completedAt; }
}

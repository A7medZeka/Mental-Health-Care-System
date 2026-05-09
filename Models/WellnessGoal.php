<?php
/**
 * WellnessGoal — Patient entity (UC 21).
 */
class WellnessGoal {

    private int    $goalId;
    private int    $patientId;
    private string $title;
    private int    $targetValue;
    private string $category;
    private float  $progress;
    private string $status;
    private string $createdAt;

    public function __construct(array $data = []) {
        $this->goalId      = (int)($data['goal_id'] ?? 0);
        $this->patientId   = (int)($data['patient_id'] ?? 0);
        $this->title       = $data['title'] ?? '';
        $this->targetValue = (int)($data['target_value'] ?? 0);
        $this->category    = $data['category'] ?? 'Other';
        $this->progress    = (float)($data['progress'] ?? 0.0);
        $this->status      = $data['status'] ?? 'In-Progress';
        $this->createdAt   = $data['created_at'] ?? date('Y-m-d H:i:s');
    }

    public function getGoalId(): int       { return $this->goalId; }
    public function getPatientId(): int    { return $this->patientId; }
    public function getTitle(): string     { return $this->title; }
    public function getTargetValue(): int  { return $this->targetValue; }
    public function getCategory(): string  { return $this->category; }
    public function getProgress(): float   { return $this->progress; }
    public function getStatus(): string    { return $this->status; }
    public function getCreatedAt(): string { return $this->createdAt; }

    public function setProgress(float $progress): void { $this->progress = $progress; }
    public function setStatus(string $status): void    { $this->status = $status; }
}

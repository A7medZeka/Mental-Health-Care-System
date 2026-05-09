<?php
/**
 * MoodEntry — Patient entity (UC 27).
 */
class MoodEntry {

    private int    $entryId;
    private int    $patientId;
    private int    $moodScore;
    private string $moodLabel;
    private string $note;
    private string $entryDate;

    public function __construct(array $data = []) {
        $this->entryId   = (int)($data['entry_id'] ?? 0);
        $this->patientId = (int)($data['patient_id'] ?? 0);
        $this->moodScore = (int)($data['mood_score'] ?? 0);
        $this->moodLabel = $data['mood_label'] ?? '';
        $this->note      = $data['note'] ?? '';
        $this->entryDate = $data['entry_date'] ?? date('Y-m-d');
    }

    public function getEntryId(): int     { return $this->entryId; }
    public function getPatientId(): int   { return $this->patientId; }
    public function getMoodScore(): int   { return $this->moodScore; }
    public function getMoodLabel(): string { return $this->moodLabel; }
    public function getNote(): string     { return $this->note; }
    public function getEntryDate(): string { return $this->entryDate; }
}

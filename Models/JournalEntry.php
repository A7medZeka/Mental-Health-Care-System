<?php
/**
 * JournalEntry — Patient entity (UC 22).
 * privacy_level: "Private" | "Shared"
 */
class JournalEntry {

    private int    $entryId;
    private int    $patientId;
    private string $title;
    private string $content;
    private string $privacyLevel;
    private string $createdAt;

    public function __construct(array $data = []) {
        $this->entryId      = (int)($data['entry_id'] ?? 0);
        $this->patientId    = (int)($data['patient_id'] ?? 0);
        $this->title        = $data['title'] ?? '';
        $this->content      = $data['content'] ?? '';
        $this->privacyLevel = $data['privacy_level'] ?? 'Private';
        $this->createdAt    = $data['created_at'] ?? date('Y-m-d H:i:s');
    }

    public function getEntryId(): int        { return $this->entryId; }
    public function getPatientId(): int      { return $this->patientId; }
    public function getTitle(): string       { return $this->title; }
    public function getContent(): string     { return $this->content; }
    public function getPrivacyLevel(): string { return $this->privacyLevel; }
    public function getCreatedAt(): string   { return $this->createdAt; }
    public function isShared(): bool          { return $this->privacyLevel === 'Shared'; }

    public function setPrivacyLevel(string $level): void {
        $valid = ['Private', 'Shared'];
        if (!in_array($level, $valid, true)) {
            throw new \InvalidArgumentException("Invalid privacy level: {$level}");
        }
        $this->privacyLevel = $level;
    }
}

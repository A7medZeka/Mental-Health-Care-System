<?php
// Models/ClinicalNote.php

require_once __DIR__ . '/../Interfaces/IEncryptable.php';
require_once __DIR__ . '/../core/ImmutablePattern.php';
class ClinicalNote implements IEncryptable {
    private int $noteId;
    private int $sessionId;
    private int $therapistId;
    private string $encryptedContent;
    private int $versionNo;
    private string $createdAt;

    public function __construct(
        int $noteId,
        int $sessionId,
        int $therapistId,
        string $content,
        int $versionNo = 1,
        ?string $createdAt = null
    ) {
        $this->noteId = $noteId;
        $this->sessionId = $sessionId;
        $this->therapistId = $therapistId;
        $this->encryptedContent = $content;
        $this->versionNo = $versionNo;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }
    public function encrypt(): string {
        return base64_encode($this->encryptedContent);
    }
    public function decrypt(string $key): string {
        return base64_decode($this->encryptedContent);
    }
    public function getNoteId(): int { return $this->noteId; }
    public function getSessionId(): int { return $this->sessionId; }
    public function getEncryptedContent(): string { return $this->encryptedContent; }
    public function getVersionNo(): int { return $this->versionNo; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function __set($name, $value) {
        throw new Exception("ClinicalNote is immutable and its properties cannot be modified.");
    }
    private function __clone() {}
    public function withNewContent(string $newContent): self {
        return new self(
            $this->noteId,
            $this->sessionId,
            $this->therapistId,
            $newContent,
            $this->versionNo + 1, // زيادة رقم النسخة أوتوماتيكياً
            date('Y-m-d H:i:s')
        );
    }
    public static function fromDatabase(array $data): self {
        return new self(
            (int)$data['note_id'],
            (int)$data['session_id'],
            (int)$data['therapist_id'],
            $data['encrypted_content'],
            (int)($data['version_no'] ?? 1),
            $data['created_at']
        );
    }
}
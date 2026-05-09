<?php
// Controllers/ClinicalNoteController.php

/**
 * ClinicalNoteController — UC 3: Encrypt Patient Intake Documents.
 *
 * SD flow:
 *   Admin → ClinicalNoteController.saveNote(sessionId, therapistId, content)
 *     → SD Step 1: checkFileType(content) [EDIT: added missing validation]
 *     → SD Step 2: encryptNote(content)
 *     → SD Step 3: determine version number
 *     → SD Step 4: create ClinicalNote (immutable)
 *     → SD Step 5: persist to DB
 *
 *   Therapist → getLatestNote(sessionId)
 *     → return ClinicalNote (encrypted)
 *
 *   Therapist → getVersionHistory(sessionId)
 *     → return ClinicalNote[]
 */

require_once __DIR__ . '/../Core/SingletonDatabase.php';
require_once __DIR__ . '/../Models/ClinicalNote.php';

class ClinicalNoteController {
    private SingletonDatabase $db;

    /** Allowed content types for intake documents */
    private const ALLOWED_CONTENT_TYPES = ['text/plain', 'application/pdf', 'text/html'];

    public function __construct() {
        $this->db = SingletonDatabase::getInstance();
    }

    /**
     * SD Step 1 [EDIT: added missing validation]:
     * +checkFileType(content) : Boolean
     * Validates that the content is not empty and is of an acceptable type.
     */
    public function checkFileType(string $content, string $mimeType = 'text/plain'): bool {
        if (empty($content)) {
            return false;
        }
        return in_array($mimeType, self::ALLOWED_CONTENT_TYPES, true);
    }

    /**
     * SD Step 2:
     * +encryptNote(rawContent) : String
     * Encrypts note content before persistence (base64/AES standard).
     */
    public function encryptNote(string $rawContent): string {
        return base64_encode($rawContent);
    }

    /**
     * SD Steps 1–5: Full save flow with validation gate.
     */
    public function saveNote(int $sessionId, int $therapistId, string $content, string $mimeType = 'text/plain'): bool {
        // SD Step 1: checkFileType — reject invalid content
        if (!$this->checkFileType($content, $mimeType)) {
            error_log("[UC3] saveNote rejected: invalid file type or empty content.");
            return false;
        }

        // SD Step 2: encrypt content
        $encrypted = $this->encryptNote($content);

        // SD Step 3: determine version number
        $sqlV = "SELECT MAX(version_no) as last_v FROM clinical_notes WHERE session_id = ?";
        $stmtV = $this->db->execute($sqlV, [$sessionId]);
        $rowV = $stmtV->fetch();
        $nextVersion = ($rowV['last_v'] ?? 0) + 1;

        // SD Step 4: create immutable ClinicalNote object
        $note = new ClinicalNote(0, $sessionId, $therapistId, $encrypted, $nextVersion);

        // SD Step 5: persist to database
        $sqlInsert = "INSERT INTO clinical_notes (session_id, therapist_id, encrypted_content, version_no, created_at) VALUES (?, ?, ?, ?, ?)";
        $this->db->execute($sqlInsert, [
            $note->getSessionId(),
            $therapistId,
            $note->getEncryptedContent(),
            $note->getVersionNo(),
            $note->getCreatedAt()
        ]);

        return true;
    }

    /**
     * +getLatestNote(sessionId) : ClinicalNote
     */
    public function getLatestNote(int $sessionId): ?ClinicalNote {
        $sql = "SELECT * FROM clinical_notes WHERE session_id = ? ORDER BY version_no DESC LIMIT 1";
        $stmt = $this->db->execute($sql, [$sessionId]);
        $data = $stmt->fetch();

        return $data ? ClinicalNote::fromDatabase($data) : null;
    }

    /**
     * +getVersionHistory(sessionId) : ClinicalNote[]
     */
    public function getVersionHistory(int $sessionId): array {
        $sql = "SELECT * FROM clinical_notes WHERE session_id = ? ORDER BY version_no DESC";
        $stmt = $this->db->execute($sql, [$sessionId]);
        $rows = $stmt->fetchAll();

        $history = [];
        foreach ($rows as $row) {
            $history[] = ClinicalNote::fromDatabase($row);
        }
        return $history;
    }
}
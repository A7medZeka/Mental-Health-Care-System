<?php
// Controllers/ClinicalNoteController.php

require_once __DIR__ . '/../Core/SingletonDatabase.php';
require_once __DIR__ . '/../Models/ClinicalNote.php';

class ClinicalNoteController {
    private SingletonDatabase $db;

    public function __construct() {
        // استخدام اتصال قاعدة البيانات الموحد في السيستم
        $this->db = SingletonDatabase::getInstance();
    }

    /**
     * +encryptNote() : String
     * تشفير محتوى الملحوظة قبل حفظها لضمان الخصوصية
     */
    public function encryptNote(string $rawContent): string {
        // استخدام دالة التشفير الأساسية (مثال: base64 أو AES)
        return base64_encode($rawContent);
    }
    public function saveNote(int $sessionId, int $therapistId, string $content): void {
        // 1. تحديد رقم النسخة (Version No) بالاستعلام عن آخر نسخة للجلسة
        $sqlV = "SELECT MAX(version_no) as last_v FROM clinical_notes WHERE session_id = ?";
        $stmtV = $this->db->execute($sqlV, [$sessionId]);
        $rowV = $stmtV->fetch();
        $nextVersion = ($rowV['last_v'] ?? 0) + 1;

        // 2. تشفير المحتوى
        $encrypted = $this->encryptNote($content);

        // 3. إنشاء الكائن (Creates ClinicalNote)
        // الكائن Immutable لذا نقوم بتمرير كافة البيانات في الـ Constructor
        $note = new ClinicalNote(0, $sessionId, $therapistId, $encrypted, $nextVersion);

        // 4. الحفظ في قاعدة البيانات (use database)
        $sqlInsert = "INSERT INTO clinical_notes (session_id, therapist_id, encrypted_content, version_no, created_at) VALUES (?, ?, ?, ?, ?)";
        $this->db->execute($sqlInsert, [
            $note->getSessionId(),
            $therapistId,
            $note->getEncryptedContent(),
            $note->getVersionNo(),
            $note->getCreatedAt()
        ]);
    }

    /**
     * +getLatestNote(sessionId: int) : ClinicalNote
     * جلب أحدث نسخة ملحوظات مسجلة لهذه الجلسة
     */
    public function getLatestNote(int $sessionId): ?ClinicalNote {
        $sql = "SELECT * FROM clinical_notes WHERE session_id = ? ORDER BY version_no DESC LIMIT 1";
        $stmt = $this->db->execute($sql, [$sessionId]);
        $data = $stmt->fetch();

        return $data ? ClinicalNote::fromDatabase($data) : null;
    }

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
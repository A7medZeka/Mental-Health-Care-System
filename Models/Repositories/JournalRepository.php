<?php
require_once __DIR__ . '/../../Core/SingletonDatabase.php';

/**
 * JournalRepository — UC 22 journal queries.
 */
class JournalRepository {

    private $db;

    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }

    public function fetchEntries(int $patientId, int $limit = 10): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM journal_entries WHERE patient_id = ?
             ORDER BY created_at DESC LIMIT ?"
        );
        $stmt->execute([$patientId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function storeEntry(array $data): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO journal_entries (patient_id, title, content, privacy_level, created_at)
             VALUES (?, ?, ?, ?, NOW())"
        );
        return $stmt->execute([
            $data['patient_id'],
            $data['title'],
            $data['content'],
            $data['privacy_level'] ?? 'Private'
        ]);
    }

    public function updatePrivacyFlag(int $entryId, string $level): bool {
        $stmt = $this->db->prepare(
            "UPDATE journal_entries SET privacy_level = ? WHERE entry_id = ?"
        );
        return $stmt->execute([$level, $entryId]);
    }

    public function getSharedEntries(int $patientId): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM journal_entries WHERE patient_id = ? AND privacy_level = 'Shared'
             ORDER BY created_at DESC"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

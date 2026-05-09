<?php
require_once __DIR__ . '/../../Core/SingletonDatabase.php';

/**
 * ForumRepository — UC 28 anonymous forum queries.
 */
class ForumRepository {

    private $db;

    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }

    public function getExistingPseudonym(int $patientId): ?string {
        $stmt = $this->db->prepare("SELECT pseudonym FROM identity_mapping WHERE patient_id = ? LIMIT 1");
        $stmt->execute([$patientId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['pseudonym'] : null;
    }

    public function storeMapping(int $patientId, string $pseudonym): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO identity_mapping (patient_id, pseudonym, created_at) VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE pseudonym = VALUES(pseudonym)"
        );
        return $stmt->execute([$patientId, $pseudonym]);
    }

    public function publishPost(int $userId, string $content, string $pseudonym, string $category): int {
        $stmt = $this->db->prepare(
            "INSERT INTO community_posts (user_id, author_pseudonym, category, content, is_flagged, created_at)
             VALUES (?, ?, ?, ?, 0, NOW())"
        );
        $stmt->execute([$userId, $pseudonym, $category, $content]);
        return (int)$this->db->lastInsertId();
    }

    public function getPosts(string $category = 'all', int $limit = 10, int $offset = 0): array {
        if ($category !== 'all') {
            $stmt = $this->db->prepare(
                "SELECT * FROM community_posts WHERE category = ? ORDER BY created_at DESC LIMIT ? OFFSET ?"
            );
            $stmt->execute([$category, $limit, $offset]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT * FROM community_posts ORDER BY created_at DESC LIMIT ? OFFSET ?"
            );
            $stmt->execute([$limit, $offset]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

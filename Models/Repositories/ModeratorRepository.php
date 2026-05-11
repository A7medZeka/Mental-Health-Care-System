<?php
// Models/Repositories/ModeratorRepository.php
require_once __DIR__ . '/../../Core/SingletonDatabase.php';
class ModeratorRepository {
    private $db;
    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }
    public function evaluatePost(int $postId, string $newStatus): bool {
        $isFlagged = ($newStatus === 'Cleared') ? 0 : 1;

        $stmt = $this->db->prepare("UPDATE community_posts SET is_flagged = ? WHERE post_id = ?");
        return $stmt->execute([$isFlagged, $postId]);
    }
}
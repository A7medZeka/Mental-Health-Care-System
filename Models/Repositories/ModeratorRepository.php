<?php
// Models/Repositories/ModeratorRepository.php
require_once __DIR__ . '/../../Core/SingletonDatabase.php';

class ModeratorRepository {
    private $db;

    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }

    public function evaluatePost(int $postId, string $newStatus): bool {
        // بناءً على الـ SQL: الجدول هو community_posts والعمود هو is_flagged
        // إذا كان الأكشن هو "Cleared" (تمت الموافقة)، نجعل is_flagged = 0
        // غير ذلك (Hidden/Deleted/Escalated) يظل 1 ليختفي من العام ويظهر للمشرفين
        $isFlagged = ($newStatus === 'Cleared') ? 0 : 1;

        $stmt = $this->db->prepare("UPDATE community_posts SET is_flagged = ? WHERE post_id = ?");
        return $stmt->execute([$isFlagged, $postId]);
    }
}
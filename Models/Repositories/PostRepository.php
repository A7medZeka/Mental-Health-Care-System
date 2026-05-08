<?php
require_once __DIR__ . '/../../Core/SingletonDatabase.php';
class PostRepository {
    private $db;
    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }
    public function getPostById(int $postId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM community_posts WHERE post_id = ?");
        $stmt->execute([$postId]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($post) {
            if ($post['is_flagged'] == 0) {
                $post['status'] = 'Published';
            } else {
                $logStmt = $this->db->prepare("SELECT action_taken FROM moderation_logs WHERE post_id = ? ORDER BY created_at DESC LIMIT 1");
                $logStmt->execute([$postId]);
                $latestLog = $logStmt->fetch(PDO::FETCH_ASSOC);
                if ($latestLog) {
                    $actionMap = [
                        'Hidden' => 'Hidden',
                        'Deleted' => 'Deleted',
                        'Marked Under Review' => 'Under Review',
                        'Escalated' => 'Under Review'
                    ];
                    $post['status'] = $actionMap[$latestLog['action_taken']] ?? 'Flagged';
                } else {
                    $post['status'] = 'Flagged';
                }
            }
        }
        return $post ?: null;
    }
    public function getFlaggedPosts(): array {
        $stmt = $this->db->prepare("
            SELECT c.*, 
                   (SELECT action_taken FROM moderation_logs m WHERE m.post_id = c.post_id ORDER BY m.created_at DESC LIMIT 1) as latest_action
            FROM community_posts c
            WHERE c.is_flagged = 1
            ORDER BY c.created_at DESC
        ");
        $stmt->execute();
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($posts as &$post) {
            $actionMap = ['Marked Under Review' => 'Under Review', 'Hidden' => 'Hidden', 'Deleted' => 'Deleted', 'Escalated' => 'Escalated'];
            $post['status'] = $actionMap[$post['latest_action']] ?? 'Flagged';
        }
        return $posts;
    }

    public function updatePostStatus(int $postId, string $status): bool {
        // لو المودريتور ساب البوست (Cleared) أو (Published)، بنشيل علامة الخطر (0)
        // غير كده بيفضل (1) وتفاصيل الأكشن بتتسجل في اللوج عن طريق ModerationService
        $isFlagged = in_array($status, ['Cleared', 'Published']) ? 0 : 1;

        $stmt = $this->db->prepare("UPDATE community_posts SET is_flagged = ? WHERE post_id = ?");
        return $stmt->execute([$isFlagged, $postId]);
    }
    public function markAsCritical(int $postId): bool {
        $stmt = $this->db->prepare("UPDATE community_posts SET is_flagged = 1 WHERE post_id = ?");
        return $stmt->execute([$postId]);
    }
    public function markAsUnderReview(int $postId): bool {
        $stmt = $this->db->prepare("UPDATE community_posts SET is_flagged = 1 WHERE post_id = ?");
        return $stmt->execute([$postId]);
    }
    public function deletePost(int $postId): bool {
        $stmt = $this->db->prepare("UPDATE community_posts SET is_flagged = 1 WHERE post_id = ?");
        return $stmt->execute([$postId]);
    }
}
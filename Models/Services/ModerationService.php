<?php
// Models/Services/ModerationService.php
require_once __DIR__ . '/../../Core/SingletonDatabase.php';
require_once __DIR__ . '/../ModerationLog.php'; // 1. استدعاء الكلاس لتحقيق علاقة (appends to)
class ModerationService {
    private PostRepository $postRepo;
    private ModeratorRepository $modRepo;
    private CrisisService $crisisService;
    private SingletonDatabase $db;
    public function __construct(PostRepository $postRepo, ModeratorRepository $modRepo, CrisisService $crisisService) {
        $this->postRepo = $postRepo;
        $this->modRepo = $modRepo;
        $this->crisisService = $crisisService;
        $this->db = SingletonDatabase::getInstance();
    }
    public function handleFlaggedPost(int $postId, string $content) {
        if ($this->crisisService->detectSevereCrisis($content)) {
            $this->postRepo->markAsCritical($postId);
            $this->crisisService->triggerEmergencyProtocol($postId);
            return 'crisis_handled';
        }
        $this->postRepo->markAsUnderReview($postId);
        return 'under_review';
    }
    public function resolveFlag(int $postId, string $action, int $modId, string $note = '') {
        $this->modRepo->evaluatePost($postId, $action);
        $log = new ModerationLog([
            'post_id' => $postId,
            'moderator_id' => $modId,
            'action_taken' => $action,
            'note' => $note
        ]);
        $sql = "INSERT INTO moderation_logs (post_id, moderator_id, action_taken, note) VALUES (?, ?, ?, ?)";
        $this->db->execute($sql, [
            $log->getPostId(),
            $log->getModeratorId(),
            $log->getActionTaken(),
            $log->getNote()
        ]);

        return true;
    }
    public function evaluateAndTransition(int $postId, string $newStatus, string $note = ''): bool {
        $postData = $this->postRepo->getPostById($postId);
        if (!$postData) {
            throw new Exception("Post not found for evaluation.");
        }
        $post = new ForumPost($postData);
        if ($post->transition($newStatus)) {
            $finalStatus = $post->getState();
            $success = $this->modRepo->evaluatePost($postId, $finalStatus);
            if ($success) {
                $modId = $_SESSION['user_id'] ?? 0;
                $this->resolveFlag($postId, $finalStatus, $modId, "Evaluated as: " . $note);
            }
            return $success;
        }
        throw new Exception("Evaluation Error: Invalid state transition to '{$newStatus}' from current state.");
    }
    public function getModerationQueue(): array {
        return $this->postRepo->getFlaggedPosts();
    }

    public function removePost(int $postId): bool {
        return $this->postRepo->deletePost($postId);
    }
    public function reviewPost(int $postId): void {
        // بتنادي على اللوجيك بتاع المراجعة
        $this->postRepo->markAsUnderReview($postId);
    }
    public function flagPost(int $postId, string $reason): void {
        $this->handleFlaggedPost($postId, $reason);
    }
}
<?php
// Models/Services/ModerationService.php

require_once __DIR__ . '/../../Core/SingletonDatabase.php';
require_once __DIR__ . '/../ModerationLog.php'; // 1. استدعاء الكلاس لتحقيق علاقة (appends to)

class ModerationService {
    private PostRepository $postRepo;
    private ModeratorRepository $modRepo;
    private CrisisService $crisisService;

    // 2. تحقيق علاقة (uses) المباشرة: تعريف المتغير كـ SingletonDatabase
    private SingletonDatabase $db;

    public function __construct(PostRepository $postRepo, ModeratorRepository $modRepo, CrisisService $crisisService) {
        $this->postRepo = $postRepo;
        $this->modRepo = $modRepo;
        $this->crisisService = $crisisService;

        // استخدام الـ Singleton نفسه زي ما الرسمة طالبة بدل استخراج الـ PDO
        $this->db = SingletonDatabase::getInstance();
    }

    // الدالة القديمة (شغالة زي ما هي عشان لو بتنادي عليها في مكان)
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

        // 3. تحقيق علاقة (appends to) بإنشاء الكائن برمجياً قبل الحفظ
        $log = new ModerationLog([
            'post_id' => $postId,
            'moderator_id' => $modId,
            'action_taken' => $action,
            'note' => $note
        ]);

        // استخدام دالة execute المدمجة في الـ Singleton
        $sql = "INSERT INTO moderation_logs (post_id, moderator_id, action_taken, note) VALUES (?, ?, ?, ?)";
        $this->db->execute($sql, [
            $log->getPostId(),
            $log->getModeratorId(),
            $log->getActionTaken(),
            $log->getNote()
        ]);

        return true;
    }

    // الدالة القديمة (شغالة ومتربطة صح بالـ ForumPost)
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
        return $this->postRepo->getFlaggedPosts(); // مطابقة للـ UML
    }

    public function removePost(int $postId): bool {
        return $this->postRepo->deletePost($postId); // مطابقة للـ UML
    }

    // ==========================================================
    // 4. إضافة الدوال المطابقة للـ UML بالضبط (بدون كسر السيستم)
    // ==========================================================

    public function reviewPost(int $postId): void {
        // بتنادي على اللوجيك بتاع المراجعة
        $this->postRepo->markAsUnderReview($postId);
    }

    public function flagPost(int $postId, string $reason): void {
        // بتشغل اللوجيك القديم عشان منكررش الأكواد (DRY Principle)
        $this->handleFlaggedPost($postId, $reason);
    }
}
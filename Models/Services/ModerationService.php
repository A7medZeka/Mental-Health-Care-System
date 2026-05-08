<?php
// Models/Services/ModerationService.php

class ModerationService {
    private $postRepo;
    private $crisisService;
    private $db;
    public function __construct(PostRepository $postRepo, CrisisService $crisisService) {
        $this->postRepo = $postRepo;
        $this->crisisService = $crisisService;
        // بنجيب الاتصال من السنجلتون عشان الـ Logging
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }

    // 1. كودك القديم (لم يتم لمسه نهائياً لضمان عدم تأثر الأجزاء المرتبطة به)
    public function handleFlaggedPost(int $postId, string $content) {
        if ($this->crisisService->detectSevereCrisis($content)) {
            $this->postRepo->markAsCritical($postId);
            $this->crisisService->triggerEmergencyProtocol($postId);
            return 'crisis_handled';
        }
        $this->postRepo->markAsUnderReview($postId);
        return 'under_review';
    }

    // =========================================================================
    // الإضافات الجديدة بناءً على الـ Class Diagram وعلاقة الـ Log
    // =========================================================================

    /**
     * تنفيذ علاقة "resolveFlag" من الدياجرام
     * وعلاقة "Appends to" مع الـ ModerationLog
     */
    public function resolveFlag(int $postId, string $action, int $modId, string $note = '') {
        // أ. تحديث حالة البوست في المستودع (Repository)
        $this->postRepo->updatePostStatus($postId, $action);

        // ب. تسجيل العملية في الـ ModerationLog (علاقة Appends to)
        // ده بيحقق الـ WORM compliance اللي في السيستم
        $stmt = $this->db->prepare("
            INSERT INTO moderation_logs (post_id, moderator_id, action_taken, note) 
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$postId, $modId, $action, $note]);
    }

    /**
     * تنفيذ دالة جلب قائمة المراجعة من الدياجرام
     */
    public function getModerationQueue(): array {
        return $this->postRepo->getFlaggedPosts();
    }

    /**
     * تنفيذ دالة حذف البوست من الدياجرام
     */
    public function removePost(int $postId): bool {
        return $this->postRepo->deletePost($postId);
    }
}
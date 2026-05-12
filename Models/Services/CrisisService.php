<?php
/**
 * CrisisService — UC 29: Crisis Detection and Emergency Protocol.
 *
 * SD flow:
 *   ForumPost → CrisisService.detectCrisis(text)
 *     → classifySeverity(text)
 *     → [alt] Critical → triggerEmergencyProtocol(postId)
 *     → [alt] High     → triggerAlert(userId, "High")
 *     → logCrisisEvent(userId, postId)
 */
require_once __DIR__ . '/../../Core/SingletonDatabase.php';
require_once __DIR__ . '/NotificationService.php';
require_once __DIR__ . '/../AuditLog.php';

class CrisisService {
    private $db;
    private NotificationService $notifier;
    public function __construct(NotificationService $notifier) {
        $this->db = SingletonDatabase::getInstance()->getConnection();
        $this->notifier = $notifier;
    }

    // ── UC 29 SD Step 1: detect crisis ───────────────────────────────────
    public function detectSevereCrisis(string $text): bool {
        return $this->classifySeverity($text) === 'Critical';
    }

    /**
     * +detectCrisis(text) : Boolean — alias for SD naming.
     */
    public function detectCrisis(string $text): bool {
        $severity = $this->classifySeverity($text);
        return $severity === 'Critical' || $severity === 'High';
    }

    // ── UC 29 SD Step 2: trigger emergency protocol ──────────────────────
    public function triggerEmergencyProtocol(int $postId): void {
        $this->logCrisisEvent(0, $postId);
        $this->notifier->publishEvent('EMERGENCY_PROTOCOL_ACTIVATED', [
            'post_id'  => $postId,
            'severity' => 'Critical',
            'message'  => "Emergency protocol triggered for potential life-threatening content."
        ]);
    }

    /**
     * +triggerAlert(userId, severity) : void
     * UC 29: non-critical alert path.
     */
    public function triggerAlert(int $userId, string $severity): void {
        $this->notifier->publishEvent('CRISIS_ALERT', [
            'user_id'  => $userId,
            'severity' => $severity,
            'message'  => "Crisis alert triggered (severity: {$severity}).",
        ]);

        $stmt = $this->db->prepare(
            "INSERT INTO audit_logs (action, severity, description, user_id, created_at) VALUES (?, ?, ?, ?, NOW())"
        );
        $stmt->execute([
            'CRISIS_ALERT',
            $severity,
            "Crisis alert for user ID: {$userId}",
            $userId
        ]);
    }
    public function scanKeywords(string $text, array $dictionary): string {
        $text = strtolower($text);
        foreach ($dictionary as $word) {
            if (strpos($text, $word) !== false) return $word;
        }
        return '';
    }

    public function classifySeverity(string $text): string {
        $criticalDict = ['suicide', 'kill myself', 'end it all', 'انتحار', 'اموت نفسي'];
        $highDict = ['hurt myself', 'cut myself', 'depressed', 'مكتئب', 'بأذي نفسي'];
        if ($this->scanKeywords($text, $criticalDict) !== '') return 'Critical';
        if ($this->scanKeywords($text, $highDict) !== '') return 'High';
        return 'Normal';
    }
    public function logCrisisEvent(int $userId, int $postId): void {
        $description = "Crisis event triggered for Post ID: $postId";
        $stmt = $this->db->prepare("
        INSERT INTO audit_logs (action, severity, description, handledBy, timestamp) 
        VALUES (?, ?, ?, ?, NOW())
    ");
        $stmt->execute(['Crisis Alert', 'Critical', $description, $userId]);
    }
}
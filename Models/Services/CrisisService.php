<?php
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
    public function detectSevereCrisis(string $text): bool {
        return $this->classifySeverity($text) === 'Critical';
    }
    public function triggerEmergencyProtocol(int $postId): void {
        $this->logCrisisEvent(0, $postId);
        $this->notifier->publishEvent('EMERGENCY_PROTOCOL_ACTIVATED', [
            'post_id' => $postId,
            'message' => "Emergency protocol triggered for potential life-threatening content."
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
            INSERT INTO audit_logs (action, severity, description, user_id) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute(['System Auto-Flag', 'Critical', $description, $userId]);
    }
}
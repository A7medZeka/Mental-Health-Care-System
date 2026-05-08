<?php
require_once __DIR__ . '/../../Core/SingletonDatabase.php';
require_once __DIR__ . '/NotificationService.php';
require_once __DIR__ . '/../AuditLog.php';
class CrisisService {
    private $db;
    private $notifier;
    public function __construct(NotificationService $notifier) {
        $this->db = SingletonDatabase::getInstance()->getConnection();
        $this->notifier = $notifier;
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
    public function detectCrisis(string $text): bool {
        return $this->classifySeverity($text) !== 'Normal';
    }
    public function triggerAlert(int $userId, string $severity): void {
        $this->notifier->publishEvent('CRISIS_KEYWORD', [
            'user_id' => $userId,
            'severity' => $severity,
            'content' => "System detected a $severity level crisis from user ID: $userId"
        ]);
    }
    public function logCrisisEvent(int $userId, int $postId): void {
        $description = "Crisis event triggered for Post ID: $postId by User ID: $userId";
        $auditLog = new AuditLog([
            'action' => 'System Auto-Flag',
            'severity' => 'Critical',
            'description' => $description,
            'handledBy' => null
        ]);
        $stmt = $this->db->prepare("
            INSERT INTO audit_logs (action, severity, description, handledBy) 
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $auditLog->getAction(),
            $auditLog->getSeverity(),
            $auditLog->getDescription(),
            $auditLog->getHandledBy()
        ]);
    }
}
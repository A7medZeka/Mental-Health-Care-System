<?php
// Models/Services/SafetyAuditService.php
require_once __DIR__ . '/../../Core/SingletonDatabase.php';

class SafetyAuditService {
    private $db;

    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }

    // جلب عدد المنشورات المبلغ عنها (بدل الـ 5 الثابتة)
    public function getFlaggedPostsCount(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM community_posts WHERE is_flagged = 1");
        return (int)$stmt->fetchColumn();
    }

    public function getAuditStats(): array {
        return [
            'critical' => (int)$this->db->query("SELECT COUNT(*) FROM audit_logs WHERE severity = 'Critical'")->fetchColumn(),
            'high'     => (int)$this->db->query("SELECT COUNT(*) FROM audit_logs WHERE severity = 'High'")->fetchColumn(),
            'total'    => (int)$this->db->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn(),
            'tampers'  => (int)$this->db->query("SELECT COUNT(*) FROM audit_logs WHERE action LIKE '%Tamper%'")->fetchColumn()
        ];
    }

    public function getAllLogs(): array {
        // جلب الأحداث الرئيسية مع اسم المودريتور اللي تعامل معاها
        $stmt = $this->db->query("
            SELECT a.*, u.first_name, u.last_name 
            FROM audit_logs a 
            LEFT JOIN users u ON a.handledBy = u.user_id 
            WHERE a.parent_log_id IS NULL 
            ORDER BY a.timestamp DESC
        ");
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($logs as &$log) {
            // جلب الـ Timeline (الأحداث الفرعية)
            $childStmt = $this->db->prepare("SELECT action, timestamp FROM audit_logs WHERE parent_log_id = ? ORDER BY timestamp ASC");
            $childStmt->execute([$log['id']]);
            $log['timeline'] = $childStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $logs;
    }
}
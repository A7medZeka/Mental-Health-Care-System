<?php
require_once __DIR__ . '/User.php';

class Moderator extends User {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getAllForumPosts() {
        $stmt = $this->conn->prepare("SELECT fp.*, u.first_name, u.last_name FROM forum_posts fp JOIN users u ON fp.user_id = u.user_id ORDER BY fp.created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getPendingPosts() {
        $stmt = $this->conn->prepare("SELECT fp.*, u.first_name, u.last_name FROM forum_posts fp JOIN users u ON fp.user_id = u.user_id WHERE fp.status = 'Pending' ORDER BY fp.created_at ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function approvePost($post_id) {
        $stmt = $this->conn->prepare("UPDATE forum_posts SET status = 'Approved', moderated_by = ?, moderated_at = NOW() WHERE post_id = ?");
        return $stmt->execute([$_SESSION['user_id'], $post_id]);
    }
    
    public function rejectPost($post_id, $reason) {
        $stmt = $this->conn->prepare("UPDATE forum_posts SET status = 'Rejected', moderation_reason = ?, moderated_by = ?, moderated_at = NOW() WHERE post_id = ?");
        return $stmt->execute([$reason, $_SESSION['user_id'], $post_id]);
    }
    
    public function getReportedContent() {
        $stmt = $this->conn->prepare("SELECT rc.*, u.first_name, u.last_name FROM reported_content rc JOIN users u ON rc.reported_by = u.user_id WHERE rc.status = 'Open' ORDER BY rc.reported_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function resolveReport($report_id, $action) {
        $stmt = $this->conn->prepare("UPDATE reported_content SET status = 'Resolved', resolution_action = ?, resolved_by = ?, resolved_at = NOW() WHERE report_id = ?");
        return $stmt->execute([$action, $_SESSION['user_id'], $report_id]);
    }
    
    public function getSafetyAuditLogs() {
        $stmt = $this->conn->prepare("SELECT * FROM safety_audit_logs ORDER BY created_at DESC LIMIT 100");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function createSafetyAudit($user_id, $action_type, $description, $risk_level) {
        $stmt = $this->conn->prepare("INSERT INTO safety_audit_logs (user_id, action_type, description, risk_level, created_at) VALUES (?, ?, ?, ?, NOW())");
        return $stmt->execute([$user_id, $action_type, $description, $risk_level]);
    }
    
    public function getHighRiskAlerts() {
        $stmt = $this->conn->prepare("SELECT * FROM safety_audit_logs WHERE risk_level = 'High' AND status = 'Open' ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getForumStatistics() {
        $stmt = $this->conn->prepare("SELECT 
            COUNT(*) as total_posts,
            COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending_posts,
            COUNT(CASE WHEN status = 'Approved' THEN 1 END) as approved_posts,
            COUNT(CASE WHEN status = 'Rejected' THEN 1 END) as rejected_posts
            FROM forum_posts");
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function getModerationQueue() {
        $stmt = $this->conn->prepare("
            SELECT 'post' as content_type, post_id as content_id, title, created_at 
            FROM forum_posts WHERE status = 'Pending'
            UNION ALL
            SELECT 'report' as content_type, report_id as content_id, content_type as title, reported_at as created_at 
            FROM reported_content WHERE status = 'Open'
            ORDER BY created_at ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getModeratorStats($moderator_id) {
        $stmt = $this->conn->prepare("SELECT 
            COUNT(*) as total_actions,
            COUNT(CASE WHEN action_type = 'approve_post' THEN 1 END) as posts_approved,
            COUNT(CASE WHEN action_type = 'reject_post' THEN 1 END) as posts_rejected,
            COUNT(CASE WHEN action_type = 'resolve_report' THEN 1 END) as reports_resolved
            FROM moderator_actions WHERE moderator_id = ?");
        $stmt->execute([$moderator_id]);
        return $stmt->fetch();
    }
}

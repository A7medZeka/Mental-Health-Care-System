<?php
require_once __DIR__ . '/../../Core/SingletonDatabase.php';

/**
 * WellnessRepository — UC 21, 23, 27 wellness queries.
 */
class WellnessRepository {

    private $db;

    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }

    // ── Goals ─────────────────────────────────────────────────────────────
    public function fetchGoals(int $patientId): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM wellness_goals WHERE patient_id = ? ORDER BY created_at DESC"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function saveGoal(array $data): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO wellness_goals (patient_id, title, target_value, category, progress, status, created_at)
             VALUES (?, ?, ?, ?, 0, 'In-Progress', NOW())"
        );
        return $stmt->execute([
            $data['patient_id'],
            $data['title'],
            $data['target_value'] ?? 1,
            $data['category'] ?? 'Other'
        ]);
    }

    public function updateGoalProgress(int $goalId, float $progress): bool {
        $status = $progress >= 100.0 ? 'Completed' : 'In-Progress';
        $stmt = $this->db->prepare(
            "UPDATE wellness_goals SET progress = ?, status = ? WHERE goal_id = ?"
        );
        return $stmt->execute([$progress, $status, $goalId]);
    }

    // ── Moods ─────────────────────────────────────────────────────────────
    public function getMoodHistory(int $patientId, int $days = 7): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM mood_entries
             WHERE patient_id = ? AND entry_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             ORDER BY entry_date DESC"
        );
        $stmt->execute([$patientId, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function logMood(int $patientId, int $score, string $label, string $note = ''): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO mood_entries (patient_id, mood_score, mood_label, note, entry_date)
             VALUES (?, ?, ?, ?, CURDATE())
             ON DUPLICATE KEY UPDATE mood_score = VALUES(mood_score), mood_label = VALUES(mood_label), note = VALUES(note)"
        );
        return $stmt->execute([$patientId, $score, $label, $note]);
    }

    public function getTodayMood(int $patientId): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM mood_entries WHERE patient_id = ? AND entry_date = CURDATE() LIMIT 1"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // ── Resources ─────────────────────────────────────────────────────────
    public function getResources(): array {
        $stmt = $this->db->prepare("SELECT * FROM wellness_resources ORDER BY title");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getResourcesByMoodScore(int $moodScore): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM wellness_resources WHERE suggested_mood_score <= ? ORDER BY title"
        );
        $stmt->execute([$moodScore]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function logResourceUsage(int $patientId, int $resourceId, int $duration): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO resource_usage_logs (patient_id, resource_id, duration_minutes, completed_at)
             VALUES (?, ?, ?, NOW())"
        );
        return $stmt->execute([$patientId, $resourceId, $duration]);
    }

    public function checkResourceAccess(int $patientId, int $resourceId): bool {
        $stmt = $this->db->prepare(
            "SELECT is_allowed FROM resource_access_control
             WHERE patient_id = ? AND resource_id = ? LIMIT 1"
        );
        $stmt->execute([$patientId, $resourceId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (bool)$row['is_allowed'] : true; // default allowed
    }
}

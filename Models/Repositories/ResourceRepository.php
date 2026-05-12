<?php
require_once __DIR__ . '/../../Core/SingletonDatabase.php';

/**
 * ResourceRepository — UC 23, 24 wellness resource queries.
 */
class ResourceRepository {

    private $db;

    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->prepare("SELECT * FROM wellness_resources ORDER BY category, title");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getByCategory(string $category): array {
        $stmt = $this->db->prepare("SELECT * FROM wellness_resources WHERE category = ? ORDER BY title");
        $stmt->execute([$category]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getByMoodScore(int $score): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM wellness_resources WHERE suggested_mood_score <= ? ORDER BY title"
        );
        $stmt->execute([$score]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function logUsage(int $patientId, int $resourceId, int $duration): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO resource_usage_logs (patient_id, resource_id, duration_minutes, completed_at) VALUES (?, ?, ?, NOW())"
        );
        return $stmt->execute([$patientId, $resourceId, $duration]);
    }

    public function getUsageHistory(int $patientId): array {
        $stmt = $this->db->prepare(
            "SELECT rl.*, wr.title, wr.category FROM resource_usage_logs rl
             JOIN wellness_resources wr ON wr.resource_id = rl.resource_id
             WHERE rl.patient_id = ? ORDER BY rl.completed_at DESC"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function checkAccess(int $patientId, int $resourceId): bool {
        $stmt = $this->db->prepare(
            "SELECT is_allowed FROM resource_access_control WHERE patient_id = ? AND resource_id = ? LIMIT 1"
        );
        $stmt->execute([$patientId, $resourceId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (bool)$row['is_allowed'] : true;
    }

    public function getResourcesByGoalCategories(int $patientId): array {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT wr.* FROM wellness_resources wr
             INNER JOIN wellness_goals wg ON wr.category = wg.category
             WHERE wg.patient_id = ? AND wg.status = 'In-Progress'
             ORDER BY wr.category, wr.title"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getResourcesGroupedByCategory(int $patientId): array {
        $resources = $this->getResourcesByGoalCategories($patientId);
        $grouped = [];
        
        foreach ($resources as $resource) {
            $category = $resource['category'];
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $resource;
        }
        
        return $grouped;
    }
}

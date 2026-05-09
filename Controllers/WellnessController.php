<?php
// Controllers/WellnessController.php
require_once __DIR__ . '/../Models/Repositories/WellnessRepository.php';
require_once __DIR__ . '/../Models/Services/NotificationService.php';

/**
 * WellnessController — UC 21, 26: Track Wellness Goals & Award Achievements.
 */
class WellnessController {

    private WellnessRepository  $repo;
    private NotificationService $notifier;

    public function __construct(WellnessRepository $repo, NotificationService $notifier) {
        $this->repo     = $repo;
        $this->notifier = $notifier;
    }

    /**
     * +updateGoalProgress(goalId, progress) : void
     */
    public function updateGoalProgress(int $goalId, float $progress): array {
        $ok = $this->repo->updateGoalProgress($goalId, $progress);
        if ($ok && $progress >= 100.0) {
            // Find patient for notification
            $db = SingletonDatabase::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT patient_id FROM wellness_goals WHERE goal_id = ?");
            $stmt->execute([$goalId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $this->checkMilestone((int)$row['patient_id']);
            }
        }
        return ['success' => $ok, 'message' => $ok ? 'Progress updated.' : 'Update failed.'];
    }

    /**
     * +checkMilestone(patientId) : void
     */
    public function checkMilestone(int $patientId): void {
        $goals = $this->repo->fetchGoals($patientId);
        $completed = array_filter($goals, fn($g) => ($g['status'] ?? '') === 'Completed');
        $count = count($completed);

        // Badge thresholds
        if ($count === 1) {
            $this->awardBadge($patientId, 'First Goal Completed');
        } elseif ($count === 5) {
            $this->awardBadge($patientId, 'Five Goals Achieved');
        } elseif ($count === 10) {
            $this->awardBadge($patientId, 'Wellness Champion');
        }
    }

    /**
     * +computeProgressPercent(goalId) : Float
     */
    public function computeProgressPercent(int $goalId): float {
        $db = SingletonDatabase::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT progress, target_value FROM wellness_goals WHERE goal_id = ?");
        $stmt->execute([$goalId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || (int)$row['target_value'] === 0) {
            return 0.0;
        }
        return round(((float)$row['progress'] / (float)$row['target_value']) * 100, 2);
    }

    /**
     * +resetStreak(goalId) : void
     */
    public function resetStreak(int $goalId): void {
        $this->repo->updateGoalProgress($goalId, 0.0);
    }

    /**
     * +awardBadge(patientId, badgeType) : void
     */
    public function awardBadge(int $patientId, string $badgeType): void {
        $this->notifier->publishEvent('BADGE_AWARDED', [
            'patient_id' => $patientId,
            'badge_type' => $badgeType,
        ]);
        $this->notifier->queueNotification(
            $patientId,
            "Congratulations! You earned the \"{$badgeType}\" badge!",
            'BadgeAwarded'
        );
    }

    public function createGoal(int $patientId, string $title, int $targetDays, string $category): array {
        $ok = $this->repo->saveGoal([
            'patient_id'   => $patientId,
            'title'        => $title,
            'target_value' => $targetDays,
            'category'     => $category,
        ]);
        return ['success' => $ok, 'message' => $ok ? 'Goal created.' : 'Failed.'];
    }

    public function getGoals(int $patientId): array {
        return $this->repo->fetchGoals($patientId);
    }
}

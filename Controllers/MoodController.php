<?php
// Controllers/MoodController.php
require_once __DIR__ . '/../Models/Repositories/WellnessRepository.php';
require_once __DIR__ . '/../Models/Repositories/ResourceRepository.php';

/**
 * MoodController — UC 23, 24, 27: Wellness Content, Mindfulness, Sleep/Mood Correlation.
 */
class MoodController {

    private WellnessRepository $wellnessRepo;
    private ResourceRepository $resourceRepo;

    public function __construct(WellnessRepository $wellnessRepo, ResourceRepository $resourceRepo) {
        $this->wellnessRepo = $wellnessRepo;
        $this->resourceRepo = $resourceRepo;
    }

    /**
     * +recommendContent(patientId) : array
     * UC 23: Suggest wellness content based on recent mood.
     */
    public function recommendContent(int $patientId): array {
        $todayMood = $this->wellnessRepo->getTodayMood($patientId);
        $score = $todayMood ? (int)$todayMood['mood_score'] : 3;
        return $this->resourceRepo->getByMoodScore($score);
    }

    /**
     * +analyzeMood(patientId) : array
     * UC 27: Get mood history + weekly average + direction.
     */
    public function analyzeMood(int $patientId): array {
        $history = $this->wellnessRepo->getMoodHistory($patientId, 30);
        $weeklyAvg = $this->computeWeeklyAverage($patientId);
        $direction = $this->getProgressDirection($patientId);

        return [
            'history'       => $history,
            'weekly_avg'    => $weeklyAvg,
            'direction'     => $direction,
            'today'         => $this->wellnessRepo->getTodayMood($patientId),
        ];
    }

    /**
     * +computeWeeklyAverage(patientId) : Float
     */
    public function computeWeeklyAverage(int $patientId): float {
        $entries = $this->wellnessRepo->getMoodHistory($patientId, 7);
        if (empty($entries)) {
            return 0.0;
        }
        $sum = array_sum(array_column($entries, 'mood_score'));
        return round($sum / count($entries), 2);
    }

    /**
     * +getProgressDirection(patientId) : String
     * "Improving", "Declining", or "Stable"
     */
    public function getProgressDirection(int $patientId): string {
        $entries = $this->wellnessRepo->getMoodHistory($patientId, 14);
        if (count($entries) < 4) {
            return 'Stable';
        }

        $half = (int)(count($entries) / 2);
        $recentHalf = array_slice($entries, 0, $half);
        $olderHalf  = array_slice($entries, $half);

        $recentAvg = array_sum(array_column($recentHalf, 'mood_score')) / count($recentHalf);
        $olderAvg  = array_sum(array_column($olderHalf, 'mood_score')) / count($olderHalf);

        $diff = $recentAvg - $olderAvg;
        if ($diff > 0.5) return 'Improving';
        if ($diff < -0.5) return 'Declining';
        return 'Stable';
    }

    /**
     * UC 24: Log mindfulness session.
     */
    public function logMindfulnessSession(int $patientId, int $resourceId, int $duration): array {
        if (!$this->resourceRepo->checkAccess($patientId, $resourceId)) {
            return ['success' => false, 'message' => 'Access denied to this resource.'];
        }

        $ok = $this->resourceRepo->logUsage($patientId, $resourceId, $duration);
        return ['success' => $ok, 'message' => $ok ? 'Session logged.' : 'Logging failed.'];
    }
}

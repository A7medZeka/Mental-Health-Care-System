<?php
// Controllers/IntakeAnalyzer.php
require_once __DIR__ . '/../Core/SingletonDatabase.php';
require_once __DIR__ . '/../Models/IntakeForm.php';
require_once __DIR__ . '/../Models/Repositories/PatientProfileRepository.php';
require_once __DIR__ . '/../Models/Services/NotificationService.php';

/**
 * IntakeAnalyzer — UC 1: Determine Level of Care.
 *
 * SD flow:
 *   Patient → IntakeAnalyzer.submitAssessment(formData)
 *     → validateResponses()
 *     → calculateScore()
 *     → determineLevelOfCare()
 *     → [alt] safetyScore ≤ threshold → applyOverrideRule()
 *     → PatientRepository.updateLevelOfCare()
 *     → return levelOfCare
 *
 * Constructor injection: PatientProfileRepository, NotificationService
 */
class IntakeAnalyzer {

    private PatientProfileRepository $patientRepo;
    private NotificationService      $notifier;
    private $db;

    // ── Dimension weights per CD3 ────────────────────────────────────────
    private const DIMENSIONS = [
        'depression'  => 0.25,
        'anxiety'     => 0.25,
        'trauma'      => 0.20,
        'substance'   => 0.15,
        'functioning' => 0.15,
    ];

    // ── Level thresholds ─────────────────────────────────────────────────
    private const THRESHOLDS = [
        'Outpatient'         => 40,
        'Intensive Outpatient' => 60,
        'Partial Hospitalization' => 80,
        'Inpatient'          => 100,
    ];

    private const SAFETY_OVERRIDE_THRESHOLD = 3; // score ≤ 3 triggers override

    public function __construct(
        PatientProfileRepository $patientRepo,
        NotificationService      $notifier
    ) {
        $this->patientRepo = $patientRepo;
        $this->notifier    = $notifier;
        $this->db          = SingletonDatabase::getInstance()->getConnection();
    }

    /**
     * Main entry point — called by PatientDashboardController.handleSubmitIntake()
     */
    public function submitAssessment(int $patientId, array $formData): array {
        // SD Step 1: validateResponses
        if (!$this->validateResponses($formData)) {
            return ['success' => false, 'message' => 'Invalid or incomplete responses.'];
        }

        // SD Step 2: calculateScore
        $score = $this->calculateScore($formData);

        // SD Step 3: determineLevelOfCare
        $level = $this->determineLevelOfCare($score);

        // SD Step 4 [alt]: check safety override
        $safetyScore = $formData['safety_score'] ?? null;
        if ($safetyScore !== null && (float)$safetyScore <= self::SAFETY_OVERRIDE_THRESHOLD) {
            $level = $this->applyOverrideRule((float)$safetyScore);
        }

        // SD Step 5: persist
        try {
            // Ensure patient row exists
            $this->db->prepare("INSERT IGNORE INTO patients (patient_id) VALUES (?)")->execute([$patientId]);

            // Save intake form (immutable — new row every time)
            $responses = json_encode($formData);
            $this->db->prepare(
                "INSERT INTO intake_forms (patient_id, total_score, submission_date, respones)
                 VALUES (?, ?, NOW(), ?)
                 ON DUPLICATE KEY UPDATE total_score = VALUES(total_score), submission_date = NOW(), respones = VALUES(respones)"
            )->execute([$patientId, $score, $responses]);

            // Update level of care
            $this->patientRepo->updateLevelOfCare($patientId, $level);

            // Update user status to Screened
            $this->patientRepo->updateTriageStatus($patientId, 'Screened');

            // Notify
            $this->notifier->queueNotification(
                $patientId,
                "Your intake assessment is complete. Level of care: {$level}.",
                'IntakeComplete'
            );

            return [
                'success' => true,
                'message' => 'Assessment saved.',
                'score'   => $score,
                'level'   => $level
            ];
        } catch (\Exception $e) {
            error_log('[IntakeAnalyzer] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to save assessment.'];
        }
    }

    /**
     * +validateResponses(formData) : Boolean
     */
    public function validateResponses(array $formData): bool {
        if (empty($formData)) {
            return false;
        }
        // Must have at least one dimension answered
        foreach (array_keys(self::DIMENSIONS) as $dim) {
            if (isset($formData[$dim]) && is_numeric($formData[$dim])) {
                return true;
            }
        }
        // Fallback: accept if total_score is provided
        return isset($formData['total_score']) && is_numeric($formData['total_score']);
    }

    /**
     * +calculateScore(formData) : Float
     */
    public function calculateScore(array $formData): float {
        // If pre-computed score is supplied, use it
        if (isset($formData['total_score']) && is_numeric($formData['total_score'])) {
            return (float)$formData['total_score'];
        }

        $total = 0.0;
        foreach (self::DIMENSIONS as $dim => $weight) {
            $dimAvg = $this->computeDimensionAverage($formData, $dim);
            $total += $dimAvg * $weight;
        }
        return round($total, 2);
    }

    /**
     * +determineLevelOfCare(score) : String
     */
    public function determineLevelOfCare(float $score): string {
        foreach (self::THRESHOLDS as $level => $maxScore) {
            if ($score <= $maxScore) {
                return $level;
            }
        }
        return 'Inpatient';
    }

    /**
     * +applyOverrideRule(safetyScore) : String
     * When safety score ≤ threshold, override to highest care.
     */
    public function applyOverrideRule(float $safetyScore): string {
        if ($safetyScore <= 1) {
            return 'Inpatient';
        }
        return 'Partial Hospitalization';
    }

    /**
     * +computeDimensionAverage(responses, dim) : Float
     */
    public function computeDimensionAverage(array $responses, string $dim): float {
        if (isset($responses[$dim]) && is_numeric($responses[$dim])) {
            return (float)$responses[$dim];
        }
        // Look for sub-items like depression_1, depression_2, etc.
        $values = [];
        foreach ($responses as $key => $val) {
            if (strpos($key, $dim . '_') === 0 && is_numeric($val)) {
                $values[] = (float)$val;
            }
        }
        return count($values) > 0 ? array_sum($values) / count($values) : 0.0;
    }
}

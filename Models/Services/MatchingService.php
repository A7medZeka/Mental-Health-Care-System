<?php
/* very very very important to read the comment
below
*/
// check the documentation pls if there is a math relation for matching
require_once __DIR__ . '/../../Core/SingletonDatabase.php';
require_once __DIR__ . '/../Therapist.php';
require_once __DIR__ . '/../TherapistMatch.php';
require_once __DIR__ . '/../TherapistMatchDetails.php';
class MatchingService {
    private SingletonDatabase $db;

    public function __construct() {
        // تحقيق علاقة الـ "uses" للـ Singleton
        $this->db = SingletonDatabase::getInstance();
    }

    /**
     * @return Therapist[]
     */
    public function filterTherapists(Patient $patient): array {
        $sql = "SELECT * FROM therapists WHERE specialization = ?";
        // استخدام دالة execute اللي إنت كاتبها في SingletonDatabase.php
        $stmt = $this->db->execute($sql, [$patient->getPrefSpecialization()]);
        $rows = $stmt->fetchAll();

        $therapists = [];
        foreach ($rows as $row) {
            // تحويل المصفوفة لكائن Therapist لتحقيق الـ Association
            $therapists[] = new Therapist($row);
        }
        return $therapists;
    }
    /**
     * @param Therapist[] $candidates
     * @return array
     */
    public function rankTherapists(array $candidates, Patient $patient): array {
        $ranked = [];
        foreach ($candidates as $therapist) {
            $details = $this->populateMatchDetails($therapist, $patient);
            $score = $details->getTotalWeightedScore();
            $ranked[] = [
                'therapist'  => $therapist,
                'details'    => $details, // تخزين التفاصيل
                'score'      => $score,
                'patient_id' => $patient->getUserId() // حفظ الـ ID عشان نستخدمه في الـ select بدون تغيير الـ Parameters
            ];
        }
        usort($ranked, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        return $ranked;
    }
    private function populateMatchDetails(Therapist $therapist, Patient $patient): TherapistMatchDetails {
        $spec_score = 0.0;
        $lang_score = 0.0;

        if (strtolower($therapist->getSpecialization()) === strtolower($patient->getPrefSpecialization())) {
            $spec_score = 100.0; // 100 * 0.40 = 40 (مثال لحساب الـ weight)
        }
        // افتراض أن المريض لديه دالة getPrefLanguage
        if (stripos($therapist->getLanguages(), $patient->getPrefLanguage()) !== false) {
            $lang_score = 100.0; // 100 * 0.20 = 20
        }
        return new TherapistMatchDetails([
            'specialization_score' => $spec_score,
            'language_score'       => $lang_score,
            'gender_score'         => 0.0,
            'availability_score'   => 0.0,
            'cultural_score'       => 0.0
        ]);
    }
    public function computeMatchScore(Therapist $therapist, Patient $patient): float {
        $details = $this->populateMatchDetails($therapist, $patient);
        return $details->getTotalWeightedScore();
    }
    public function selectBestMatch(array $ranked): ?TherapistMatch {
        if (empty($ranked)) {
            return null; // لا يوجد معالجين
        }
        $best = $ranked[0];
        $bestTherapist = $best['therapist'];
        $bestDetails   = $best['details'];
        $patientId     = $best['patient_id'] ?? 0;

        $matchData = [
            'patient_id'   => $patientId,
            'therapist_id' => $bestTherapist->getTherapistId(),
            'status'       => 'Pending'
        ];
        return new TherapistMatch($matchData, $bestDetails);
    }
}
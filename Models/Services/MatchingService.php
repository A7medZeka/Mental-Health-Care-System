<?php
/**
 * MatchingService — UC 2: Match Patient with Therapist.
 *
 * SD flow:
 *   Patient → MatchingService.filterTherapists(patient)
 *     → rankTherapists(candidates, patient)
 *       → populateMatchDetails(therapist, patient)  [private]
 *     → selectBestMatch(ranked)
 *     → return TherapistMatch
 *
 * IMPORTANT: read the documentation if there is a math relation for matching
 */
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
        $prefSpec    = trim((string)$patient->getPrefSpecialization());
        $prefLang    = trim((string)$patient->getPrefLanguage());
        $prefGender  = trim((string)$patient->getPrefTherapistGender());
        $prefCulture = trim((string)$patient->getPrefCulturalBackground());

        $baseSql = "SELECT t.*, u.gender AS gender, t.Cultural AS cultural
                    FROM therapists t
                    JOIN users u ON u.user_id = t.therapist_id
                    WHERE t.is_verified = 1
                      AND u.status = 'Active'";

        $sql = $baseSql;
        $params = [];
        $filtersApplied = false;

        if (!$this->isNoPreference($prefSpec) && strtolower($prefSpec) !== 'other') {
            $sql .= " AND t.specialization = ?";
            $params[] = $prefSpec;
            $filtersApplied = true;
        }
        if (!$this->isNoPreference($prefLang) && strtolower($prefLang) !== 'other') {
            $sql .= " AND t.languages LIKE ?";
            $params[] = '%' . $prefLang . '%';
            $filtersApplied = true;
        }
        if (!$this->isNoPreference($prefGender)) {
            $g = strtolower($prefGender);
            if ($g === 'male' || $g === 'female') {
                $sql .= " AND LOWER(u.gender) = ?";
                $params[] = $g;
                $filtersApplied = true;
            }
        }
        if (!$this->isNoPreference($prefCulture) && strtolower($prefCulture) !== 'other') {
            $sql .= " AND t.Cultural = ?";
            $params[] = $prefCulture;
            $filtersApplied = true;
        }

        $stmt = $this->db->execute($sql, $params);
        $rows = $stmt->fetchAll();
        if (empty($rows) && $filtersApplied) {
            $rows = $this->db->execute($baseSql)->fetchAll();
        }

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
        $prefSpec    = trim((string)$patient->getPrefSpecialization());
        $prefLang    = trim((string)$patient->getPrefLanguage());
        $prefGender  = trim((string)$patient->getPrefTherapistGender());
        $prefCulture = trim((string)$patient->getPrefCulturalBackground());

        $spec_score = ($this->isNoPreference($prefSpec) || strtolower($prefSpec) === 'other') ? 50.0 : 0.0;
        $lang_score = ($this->isNoPreference($prefLang) || strtolower($prefLang) === 'other') ? 50.0 : 0.0;
        $gender_score = $this->isNoPreference($prefGender) ? 50.0 : 0.0;
        $cultural_score = ($this->isNoPreference($prefCulture) || strtolower($prefCulture) === 'other') ? 50.0 : 0.0;

        if (!$this->isNoPreference($prefSpec) && strtolower($prefSpec) !== 'other') {
            if (strtolower($therapist->getSpecialization()) === strtolower($prefSpec)) {
                $spec_score = 100.0;
            }
        }
        if (!$this->isNoPreference($prefLang) && strtolower($prefLang) !== 'other') {
            if (stripos($therapist->getLanguages(), $prefLang) !== false) {
                $lang_score = 100.0;
            }
        }
        if (!$this->isNoPreference($prefGender)) {
            $g = strtolower($prefGender);
            if (($g === 'male' || $g === 'female') && strtolower($therapist->getGender()) === $g) {
                $gender_score = 100.0;
            }
        }
        if (!$this->isNoPreference($prefCulture) && strtolower($prefCulture) !== 'other') {
            if (strtolower($therapist->getCultural()) === strtolower($prefCulture)) {
                $cultural_score = 100.0;
            }
        }

        $availability_score = trim($therapist->getAvailabilitySchedule()) !== '' ? 100.0 : 0.0;

        return new TherapistMatchDetails([
            'specialization_score' => $spec_score,
            'language_score'       => $lang_score,
            'gender_score'         => $gender_score,
            'availability_score'   => $availability_score,
            'cultural_score'       => $cultural_score
        ]);
    }

    private function isNoPreference(string $value): bool
    {
        $v = strtolower(trim($value));
        if ($v === '') return true;
        $v = str_replace([' ', '_', '-'], '', $v);
        return $v === 'nopreference' || $v === 'noprefrence';
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

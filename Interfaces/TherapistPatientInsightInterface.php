<?php
// داخل ملف TherapistPatientInsightInterface.php
interface TherapistPatientInsightInterface {
    public function getPatientMoodEntries($patient_id, $limit = 10);
    // 👇 ضيف السطر ده
    public function getMoodSleepCorrelation($patient_id);
}
<?php

/**
 * Interface for patient wellness and mental health operations
 * Implements ISP by focusing only on wellness-related operations
 */
interface PatientWellnessInterface {
    
    /**
     * Log mood entry for patient
     * @param int $patient_id
     * @param int $mood_score
     * @param string $mood_label
     * @param string $notes
     * @return bool
     */
    public function logMood($patient_id, $mood_score, $mood_label, $notes = '');
    
    /**
     * Get mood history for patient
     * @param int $patient_id
     * @param int $days
     * @return array
     */
    public function getMoodHistory($patient_id, $days = 30);
    
    /**
     * Get wellness goals for patient
     * @param int $patient_id
     * @return array
     */
    public function getMyGoals($patient_id);
    
    /**
     * Create a new wellness goal
     * @param int $patient_id
     * @param string $goal_title
     * @param int $target_days
     * @param string $category
     * @return bool
     */
    public function createGoal($patient_id, $goal_title, $target_days, $category);
    
    /**
     * Update goal progress
     * @param int $goal_id
     * @param int $progress
     * @return bool
     */
    public function updateGoalProgress($goal_id, $progress);
}

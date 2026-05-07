<?php

/**
 * Interface for patient journal operations
 * Implements ISP by focusing only on journal-related operations
 */
interface PatientJournalInterface {
    
    /**
     * Get journal entries for patient
     * @param int $patient_id
     * @param int $limit
     * @return array
     */
    public function getJournalEntries($patient_id, $limit = 10);
    
    /**
     * Create a new journal entry
     * @param int $patient_id
     * @param string $title
     * @param string $content
     * @return bool
     */
    public function createJournalEntry($patient_id, $title, $content);
}

<?php

/**
 * Interface for therapist management operations
 * Implements ISP by focusing only on therapist-related admin operations
 */
interface AdminTherapistManagerInterface {
    
    /**
     * Retrieve all therapists with their details
     * @return array
     */
    public function getAllTherapists(): array;
    
    /**
     * Get total count of therapists
     * @return int
     */
    public function getTotalTherapists(): int;
    
    /**
     * Get specific therapist by ID
     * @param int $therapist_id
     * @return array|null
     */
    public function getTherapistById(int $therapist_id): ?array;
    
    /**
     * Reject a therapist application (set status to Inactive)
     * @param int $therapist_id
     * @return bool
     */
    public function rejectTherapist(int $therapist_id): bool;
    
    /**
     * Remove a therapist from the system (cascade delete)
     * @param int $therapist_id
     * @return bool
     */
    public function removeTherapist(int $therapist_id): bool;
    
    /**
     * Get all active therapists ordered by license expiry
     * @return array
     */
    public function getActiveTherapists(): array;
}

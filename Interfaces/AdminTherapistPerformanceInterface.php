<?php

/**
 * Interface for therapist performance analytics operations
 * Implements ISP by focusing only on performance-related operations
 */
interface AdminTherapistPerformanceInterface {
    
    /**
     * Get list of verified therapists
     * @return array
     */
    public function getVerifiedTherapistList(): array;
    
    /**
     * Get average rating and review count for therapist
     * @param int $therapist_id
     * @return array
     */
    public function getTherapistRankingStat(int $therapist_id): array;
    
    /**
     * Get comprehensive performance details for a therapist
     * @param int $therapist_id
     * @return array|null
     */
    public function getTherapistPerformanceDetail(int $therapist_id): ?array;
    
    /**
     * Get rating breakdown for a therapist
     * @param int $therapist_id
     * @return array
     */
    public function getTherapistRatingBreakdown(int $therapist_id): array;
    
    /**
     * Get recent feedback comments for a therapist
     * @param int $therapist_id
     * @param int $limit
     * @return array
     */
    public function getTherapistRecentFeedback(int $therapist_id, int $limit = 5): array;
    
    /**
     * Get top-rated therapists
     * @param int $limit
     * @return array
     */
    public function getTopRatedTherapists(int $limit = 10): array;
}

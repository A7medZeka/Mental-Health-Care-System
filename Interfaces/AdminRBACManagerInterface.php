<?php

/**
 * Interface for RBAC (Role-Based Access Control) management operations
 * Implements ISP by focusing only on role and user management operations
 */
interface AdminRBACManagerInterface {
    
    /**
     * Returns every user ordered by role then username, ready for the RBAC table
     * @return array
     */
    public function getAllUsersForView(): array;
    
    /**
     * Validates the transition rules and performs the role promotion
     * @param int $targetId
     * @param string $newRole
     * @return array ['success' => bool, 'message' => string, ...]
     */
    public function promoteUser(int $targetId, string $newRole): array;
    
    /**
     * Deletes a user and, if they are a Therapist, their therapists-table row
     * @param int $targetId
     * @return array ['success' => bool, 'message' => string, ...]
     */
    public function deleteUser(int $targetId): array;
    
    /**
     * Helper: expose transition map to the view layer (read-only)
     * @return array
     */
    public function getAllowedTransitions(): array;
}

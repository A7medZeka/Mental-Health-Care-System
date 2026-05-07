<?php

/**
 * Interface for basic user repository operations
 * Implements Interface Segregation Principle by focusing only on core user operations
 */
interface UserRepositoryInterface {
    
    /**
     * Get user by ID
     * @param int $user_id
     * @return array|null
     */
    public function getUserById($user_id);
    
    /**
     * Get user by email
     * @param string $email
     * @return array|null
     */
    public function getUserByEmail($email);
    
    /**
     * Get user age
     * @param int $user_id
     * @return string
     */
    public function getUserAge($user_id);
    
    /**
     * Get user gender
     * @param int $user_id
     * @return string
     */
    public function getUserGender($user_id);
}

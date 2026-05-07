<?php

require_once __DIR__ . '/../Core/SingletonDatabase.php';
require_once __DIR__ . '/../Interfaces/UserRepositoryInterface.php';
require_once __DIR__ . '/../Models/User.php';

/**
 * Handles authentication operations (login, logout, session management)
 * Follows Single Responsibility Principle - only handles authentication
 */
class AuthenticationController {
    
    private UserRepositoryInterface $userRepository;
    
    public function __construct(UserRepositoryInterface $userRepository = null) {
        // Use dependency injection with fallback
        $this->userRepository = $userRepository ?? new User();
    }
    
    /**
     * Handle user login
     * @param string $email
     * @param string $password
     * @return array ['success' => bool, 'message' => string, 'user' => array|null]
     */
    public function login(string $email, string $password): array {
        // Validate inputs
        if (empty($email) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Email and password are required.'
            ];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Invalid email format.'
            ];
        }
        
        // Authenticate user
        $user = $this->userRepository->getUserByEmail($email);
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return [
                'success' => false,
                'message' => 'Invalid email or password.'
            ];
        }
        
        // Set session
        $this->createUserSession($user);
        
        return [
            'success' => true,
            'message' => 'Login successful.',
            'user' => [
                'user_id' => $user['user_id'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ];
    }
    
    /**
     * Handle user logout
     * @return array ['success' => bool, 'message' => string]
     */
    public function logout(): array {
        // Destroy session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
            session_regenerate_id(true);
        }
        
        return [
            'success' => true,
            'message' => 'Logged out successfully.'
        ];
    }
    
    /**
     * Check if user is logged in
     * @return bool
     */
    public function isLoggedIn(): bool {
        return !empty($_SESSION['user_id']);
    }
    
    /**
     * Get current logged-in user
     * @return array|null
     */
    public function getCurrentUser(): ?array {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'user_id' => $_SESSION['user_id'],
            'first_name' => $_SESSION['first_name'],
            'last_name' => $_SESSION['last_name'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role']
        ];
    }
    
    /**
     * Require user to be logged in
     * @param string $requiredRole
     * @return bool
     */
    public function requireLogin(string $requiredRole = null): bool {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        if ($requiredRole && $_SESSION['role'] !== $requiredRole) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Create user session data
     * @param array $user
     */
    private function createUserSession(array $user): void {
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['role'] = $user['role'];
    }
}

<?php
/**
 * AuthHelper - Centralized Authentication Path Management
 * Eliminates hardcoded authentication URLs in Views
 */

class AuthHelper {
    private static $authPaths = [
        'login' => '../Auth/login.php',
        'logout' => '../Auth/logout.php',
        'signup' => '../Auth/signup.php',
        'therapist_register' => '../Auth/therapist-register.php',
        'forgot_password' => '../Auth/forgot-password.php'
    ];
    
    private static $roleRedirects = [
        'Admin' => '../Admin/dashboard.php',
        'Patient' => '../Patient/dashboard.php',
        'Therapist' => '../Therapist/dashboard.php',
        'Moderator' => '../Moderator/dashboard.php'
    ];
    
    /**
     * Get login URL
     */
    public static function getLoginUrl(): string {
        return self::$authPaths['login'];
    }
    
    /**
     * Get logout URL
     */
    public static function getLogoutUrl(): string {
        return self::$authPaths['logout'];
    }
    
    /**
     * Get signup URL
     */
    public static function getSignupUrl(): string {
        return self::$authPaths['signup'];
    }
    
    /**
     * Get therapist registration URL
     */
    public static function getTherapistRegisterUrl(): string {
        return self::$authPaths['therapist_register'];
    }
    
    /**
     * Get forgot password URL
     */
    public static function getForgotPasswordUrl(): string {
        return self::$authPaths['forgot_password'];
    }
    
    /**
     * Redirect to login page
     */
    public static function redirectToLogin(): void {
        header('Location: ' . self::getLoginUrl());
        exit();
    }
    
    /**
     * Redirect to logout page
     */
    public static function redirectToLogout(): void {
        header('Location: ' . self::getLogoutUrl());
        exit();
    }
    
    /**
     * Redirect to role dashboard
     */
    public static function redirectToRoleDashboard(string $role): void {
        $url = self::$roleRedirects[$role] ?? self::getLoginUrl();
        header('Location: ' . $url);
        exit();
    }
    
    /**
     * Get role dashboard URL
     */
    public static function getRoleDashboardUrl(string $role): string {
        return self::$roleRedirects[$role] ?? self::getLoginUrl();
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool {
        return !empty($_SESSION['user_id']);
    }
    
    /**
     * Check if user has specific role
     */
    public static function hasRole(string $role): bool {
        return ($_SESSION['role'] ?? '') === $role;
    }
    
    /**
     * Require login - redirect if not logged in
     */
    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            self::redirectToLogin();
        }
    }
    
    /**
     * Require specific role - redirect if not matching
     */
    public static function requireRole(string $requiredRole): void {
        self::requireLogin();
        
        if (!self::hasRole($requiredRole)) {
            self::redirectToRoleDashboard($_SESSION['role']);
        }
    }
    
    /**
     * Get current user role
     */
    public static function getCurrentRole(): string {
        return $_SESSION['role'] ?? '';
    }
    
    /**
     * Get current user ID
     */
    public static function getCurrentUserId(): int {
        return (int)($_SESSION['user_id'] ?? 0);
    }
    
    /**
     * Get current user data
     */
    public static function getCurrentUser(): array {
        return [
            'user_id' => $_SESSION['user_id'] ?? 0,
            'first_name' => $_SESSION['first_name'] ?? '',
            'last_name' => $_SESSION['last_name'] ?? '',
            'email' => $_SESSION['email'] ?? '',
            'role' => $_SESSION['role'] ?? ''
        ];
    }
    
    /**
     * Update authentication paths (for configuration)
     */
    public static function setAuthPath(string $key, string $path): void {
        self::$authPaths[$key] = $path;
    }
    
    /**
     * Update role redirect (for configuration)
     */
    public static function setRoleRedirect(string $role, string $path): void {
        self::$roleRedirects[$role] = $path;
    }
    
    /**
     * Generate authentication links for public pages
     */
    public static function generateAuthLinks(): array {
        return [
            'login' => '<a href="' . self::getLoginUrl() . '" class="btn btn-primary-custom px-3 py-2">Log In</a>',
            'signup' => '<a href="' . self::getSignupUrl() . '" class="btn btn-primary-custom btn-lg px-4 py-2 fw-semibold">
                <i class="bi bi-person-plus me-1"></i> Join as a Patient
            </a>',
            'therapist_register' => '<a href="' . self::getTherapistRegisterUrl() . '" class="btn btn-accent btn-lg px-4 py-2 fw-semibold">
                <i class="bi bi-briefcase me-1"></i> Apply as a Therapist
            </a>'
        ];
    }
    
    /**
     * Generate quick links for footer
     */
    public static function generateQuickLinks(): string {
        $links = [
            'signup' => '<a href="' . self::getSignupUrl() . '">Join as a Patient</a>',
            'therapist_register' => '<a href="' . self::getTherapistRegisterUrl() . '">Apply as a Therapist</a>',
            'login' => '<a href="' . self::getLoginUrl() . '">Log In</a>'
        ];
        
        $html = '<ul class="list-unstyled small">';
        foreach ($links as $key => $link) {
            $html .= "<li class=\"mb-2\">{$link}</li>";
        }
        $html .= '</ul>';
        
        return $html;
    }
}
?>

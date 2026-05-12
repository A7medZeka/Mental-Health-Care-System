<?php
/**
 * RBACController - Role-Based Access Control Controller
 * Handles user role management and permissions
 */

require_once __DIR__ . '/../Core/SingletonDatabase.php';
require_once __DIR__ . '/../Core/Validation.php';
require_once __DIR__ . '/../Models/Admin.php';
require_once __DIR__ . '/../Models/Dashboard.php';

class RBACController {
    private $database;
    private $rbacManager;
    
    public function __construct() {
        $this->database = SingletonDatabase::getInstance();
        $this->rbacManager = new AdminRBACManager();
        $this->initializeRBACManager();
    }
    
    /**
     * Initialize RBACManager with current user session data
     */
    private function initializeRBACManager(): void {
        // Set the current user data in the RBAC manager
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!empty($_SESSION['user_id'])) {
            $userData = [
                'user_id' => $_SESSION['user_id'],
                'role' => $_SESSION['role'] ?? '',
                'username' => $_SESSION['username'] ?? '',
                'email' => $_SESSION['email'] ?? '',
                'first_name' => $_SESSION['first_name'] ?? '',
                'last_name' => $_SESSION['last_name'] ?? '',
                'age' => $_SESSION['age'] ?? '',
                'gender' => $_SESSION['gender'] ?? '',
                'phone_number' => $_SESSION['phone_number'] ?? '',
                'city' => $_SESSION['city'] ?? '',
                'nationalID' => $_SESSION['national_id'] ?? ''
            ];
            
            $this->rbacManager->setCurrentUser($userData);
        }
    }
    
    /**
     * Handle RBAC operations
     */
    public function handleRequest(): void {
        session_start();
        
        // Verify admin access
        if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit();
        }

        // Check and push roles to ensure consistency
        try {
            $this->checkAndPushAllRoles();
        } catch (Exception $e) {
            error_log('Role check failed: ' . $e->getMessage());
            // Continue without failing
        }
        
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'update_role':
                $this->updateUserRole();
                break;
            case 'delete_user':
                $this->deleteUser();
                break;
            default:
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
                break;
        }
    }
    
    /**
     * Check and push all roles to ensure database consistency
     */
    private function checkAndPushAllRoles(): void {
        $dashboard = new Dashboard();
        $dashboard->checkAndPushRoles();
    }
    
    /**
     * Update user role using AdminRBACManager
     */
    private function updateUserRole(): void {
        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        $newRole = trim($_POST['new_role'] ?? '');
        
        if (!$userId || empty($newRole)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
            exit();
        }
        
        // Use AdminRBACManager to handle role promotion
        $result = $this->rbacManager->promoteUser($userId, $newRole);
        
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    
    /**
     * Delete user using AdminRBACManager
     */
    private function deleteUser(): void {
        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        
        if (!$userId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
            exit();
        }
        
        // Prevent self-deletion
        if ($userId == $_SESSION['user_id']) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Cannot delete your own account']);
            exit();
        }
        
        // Use AdminRBACManager to handle deletion
        $result = $this->rbacManager->deleteUser($userId);
        
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    
        
    /**
     * Get all users with their roles for RBAC management
     */
    public function getAllUsers(): array {
        return $this->rbacManager->getAllUsersForView();
    }
    
    /**
     * Get allowed role transitions
     */
    public function getAllowedTransitions(): array {
        return $this->rbacManager->getAllowedTransitions();
    }
}

// Handle direct POST requests to this controller
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new RBACController();
    $controller->handleRequest();
}
?>

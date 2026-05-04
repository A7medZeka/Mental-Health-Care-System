<?php
require_once __DIR__ . '/../Core/Validation.php';
require_once __DIR__ . '/../Models/Dashboard.php';
require_once __DIR__ . '/../Models/Admin.php';

class AdminDashboardController {
    private $dashboardModel;
    
    public function __construct() {
        $this->dashboardModel = new Dashboard();
    }
    
    public function handleRequest() {
        session_start();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        if (empty($_SESSION['user_id'])) {
            header('Location: ../Auth/login.php');
            exit();
        }
        
        checkMethod($method);
        
        if ($_SESSION['role'] !== 'Admin') {
            $map = [
                'Patient'   => '../Patient/dashboard.php',
                'Therapist' => '../Therapist/dashboard.php',
                'Moderator' => '../Moderator/dashboard.php',
            ];
            header('Location: ' . ($map[$_SESSION['role']] ?? '../Auth/login.php'));
            exit();
        }
        
        return $this->dashboardModel->getAdminDashboardData();
    }
    
    public function getUserData($user_id) {
        $adminModel = new Admin();
        return [
            'first_name' => $_SESSION['first_name'] ?? 'Admin',
            'last_name' => $_SESSION['last_name'] ?? '',
            'email' => $_SESSION['email'] ?? '',
            'age' => $adminModel->getUserAge($user_id),
            'gender' => $adminModel->getUserGender($user_id),
            'role' => $_SESSION['role'] ?? 'Admin'
        ];
    }
}

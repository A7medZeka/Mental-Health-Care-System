<?php
require_once __DIR__ . '/../Core/Validation.php';
require_once __DIR__ . '/../Models/Dashboard.php';
require_once __DIR__ . '/../Models/Patient.php';

class PatientDashboardController {
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
        
        if ($_SESSION['role'] !== 'Patient') {
            $map = [
                'Admin'     => '../Admin/dashboard.php',
                'Therapist' => '../Therapist/dashboard.php',
                'Moderator' => '../Moderator/dashboard.php',
            ];
            header('Location: ' . ($map[$_SESSION['role']] ?? '../Auth/login.php'));
            exit();
        }
        
        return $this->dashboardModel->getPatientDashboardData($_SESSION['user_id']);
    }
    
    public function getRecentActivity() {
        return $this->dashboardModel->getRecentActivity($_SESSION['user_id']);
    }
    
    public function getOnboardingChecklist() {
        return $this->dashboardModel->getOnboardingChecklist($_SESSION['user_id']);
    }
}

<?php
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Admin.php';
require_once __DIR__ . '/Patient.php';

class Dashboard {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function getPatientDashboardData($user_id) {
        $user_data = $this->userModel->getUserById($user_id);
        
        return [
            'first_name' => $user_data['first_name'] ?? 'Patient',
            'last_name' => $user_data['last_name'] ?? '',
            'email' => $user_data['email'] ?? '',
            'age' => $this->userModel->getUserAge($user_id),
            'gender' => $this->userModel->getUserGender($user_id),
            'next_appointment' => 'May 5, 2026',
            'today_mood' => 'Good',
            'active_goals' => 3,
            'pending_actions' => 2,
            'onboarding_progress' => 60
        ];
    }
    
    public function getAdminDashboardData() {
        $adminModel = new Admin();
        return [
            'total_patients' => $adminModel->getTotalPatients(),
            'pending_therapists' => $adminModel->getPendingTherapists(),
            'high_risk_alerts' => $adminModel->getAuditLogsCount()
        ];
    }
    
    public function getRecentActivity($user_id) {
        return [
            ['Session completed with Dr. Hassan', 'Apr 28, 2026'],
            ['Mood logged: Anxious', 'Apr 27, 2026'],
            ['Wellness goal updated: Meditation', 'Apr 26, 2026'],
            ['Journal entry added', 'Apr 25, 2026'],
            ['Appointment booked for May 5', 'Apr 24, 2026']
        ];
    }
    
    public function getOnboardingChecklist($user_id) {
        return [
            ['Create Profile', 'Complete your personal information', 'Completed'],
            ['Submit Intake Form', 'Answer clinical assessment questions', 'Pending'],
            ['Verify Insurance', 'Add your insurance provider details', 'Completed'],
            ['Sign Legal Consents', 'Review and sign required documents', 'Completed'],
            ['Add Payment Method', 'Set up billing for sessions', 'Pending'],
            ['Receive Therapist Match', 'Awaiting intake form completion', 'Locked']
        ];
    }
}

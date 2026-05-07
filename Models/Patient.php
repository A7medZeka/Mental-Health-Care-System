<?php
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/../Interfaces/PatientRepositoryInterface.php';
require_once __DIR__ . '/../Interfaces/PatientWellnessInterface.php';
require_once __DIR__ . '/../Interfaces/PatientJournalInterface.php';
require_once __DIR__ . '/../Interfaces/PatientIntakeInterface.php';

class Patient extends User implements PatientRepositoryInterface, PatientWellnessInterface, PatientJournalInterface, PatientIntakeInterface {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getMyTherapist($patient_id) {
        $stmt = $this->conn->prepare("SELECT t.* FROM users t JOIN users p ON t.user_id = p.assigned_therapist WHERE p.user_id = ?");
        $stmt->execute([$patient_id]);
        return $stmt->fetch();
    }
    
    public function getMyAppointments($patient_id) {
        $stmt = $this->conn->prepare("SELECT * FROM sessions WHERE patient_id = ? ORDER BY session_date ASC");
        $stmt->execute([$patient_id]);
        return $stmt->fetchAll();
    }
    
    public function getUpcomingAppointments($patient_id) {
        $stmt = $this->conn->prepare("SELECT * FROM sessions WHERE patient_id = ? AND session_date >= CURDATE() AND status = 'Scheduled' ORDER BY session_date ASC");
        $stmt->execute([$patient_id]);
        return $stmt->fetchAll();
    }
    
    public function getPastAppointments($patient_id) {
        $stmt = $this->conn->prepare("SELECT * FROM sessions WHERE patient_id = ? AND (session_date < CURDATE() OR status = 'Completed') ORDER BY session_date DESC");
        $stmt->execute([$patient_id]);
        return $stmt->fetchAll();
    }
    
    public function bookAppointment($patient_id, $therapist_id, $session_date, $session_type) {
        $stmt = $this->conn->prepare("INSERT INTO sessions (patient_id, therapist_id, session_date, session_type, status) VALUES (?, ?, ?, ?, 'Scheduled')");
        return $stmt->execute([$patient_id, $therapist_id, $session_date, $session_type]);
    }
    
    public function cancelAppointment($session_id, $patient_id) {
        $stmt = $this->conn->prepare("UPDATE sessions SET status = 'Cancelled' WHERE session_id = ? AND patient_id = ?");
        return $stmt->execute([$session_id, $patient_id]);
    }
    
    public function logMood($patient_id, $mood_score, $mood_label, $notes = '') {
        $stmt = $this->conn->prepare("INSERT INTO mood_entries (patient_id, mood_score, mood_label, notes, entry_date) VALUES (?, ?, ?, ?, CURDATE())");
        return $stmt->execute([$patient_id, $mood_score, $mood_label, $notes]);
    }
    
    public function getMoodHistory($patient_id, $days = 30) {
        $stmt = $this->conn->prepare("SELECT * FROM mood_entries WHERE patient_id = ? AND entry_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY) ORDER BY entry_date ASC");
        $stmt->execute([$patient_id, $days]);
        return $stmt->fetchAll();
    }
    
    public function getMyGoals($patient_id) {
        $stmt = $this->conn->prepare("SELECT * FROM wellness_goals WHERE patient_id = ? ORDER BY created_at DESC");
        $stmt->execute([$patient_id]);
        return $stmt->fetchAll();
    }
    
    public function createGoal($patient_id, $goal_title, $target_days, $category) {
        $stmt = $this->conn->prepare("INSERT INTO wellness_goals (patient_id, goal_title, target_days, category, status) VALUES (?, ?, ?, ?, 'Active')");
        return $stmt->execute([$patient_id, $goal_title, $target_days, $category]);
    }
    
    public function updateGoalProgress($goal_id, $progress) {
        $stmt = $this->conn->prepare("UPDATE wellness_goals SET progress = ?, updated_at = NOW() WHERE goal_id = ?");
        return $stmt->execute([$progress, $goal_id]);
    }
    
    public function getJournalEntries($patient_id, $limit = 10) {
        $stmt = $this->conn->prepare("SELECT * FROM journal_entries WHERE patient_id = ? ORDER BY entry_date DESC LIMIT ?");
        $stmt->execute([$patient_id, $limit]);
        return $stmt->fetchAll();
    }
    
    public function createJournalEntry($patient_id, $title, $content) {
        $stmt = $this->conn->prepare("INSERT INTO journal_entries (patient_id, title, content, entry_date) VALUES (?, ?, ?, CURDATE())");
        return $stmt->execute([$patient_id, $title, $content]);
    }
    
    public function getIntakeFormStatus($patient_id) {
        $stmt = $this->conn->prepare("SELECT * FROM intake_forms WHERE patient_id = ?");
        $stmt->execute([$patient_id]);
        return $stmt->fetch();
    }
    
    public function submitIntakeForm($patient_id, $form_data) {
        $stmt = $this->conn->prepare("INSERT INTO intake_forms (patient_id, form_data, submitted_date) VALUES (?, ?, CURDATE())");
        return $stmt->execute([$patient_id, json_encode($form_data)]);
    }
}

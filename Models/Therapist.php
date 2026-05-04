<?php
require_once __DIR__ . '/User.php';

class Therapist extends User {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getMyPatients($therapist_id) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE assigned_therapist = ? AND role = 'Patient' ORDER BY last_name, first_name");
        $stmt->execute([$therapist_id]);
        return $stmt->fetchAll();
    }
    
    public function getPatientSessions($patient_id) {
        $stmt = $this->conn->prepare("SELECT * FROM sessions WHERE patient_id = ? ORDER BY session_date DESC");
        $stmt->execute([$patient_id]);
        return $stmt->fetchAll();
    }
    
    public function createSession($patient_id, $therapist_id, $session_date, $session_type) {
        $stmt = $this->conn->prepare("INSERT INTO sessions (patient_id, therapist_id, session_date, session_type, status) VALUES (?, ?, ?, ?, 'Scheduled')");
        return $stmt->execute([$patient_id, $therapist_id, $session_date, $session_type]);
    }
    
    public function updateSessionNotes($session_id, $notes) {
        $stmt = $this->conn->prepare("UPDATE sessions SET notes = ?, status = 'Completed' WHERE session_id = ?");
        return $stmt->execute([$notes, $session_id]);
    }
    
    public function getTherapistSchedule($therapist_id) {
        $stmt = $this->conn->prepare("SELECT * FROM sessions WHERE therapist_id = ? AND session_date >= CURDATE() ORDER BY session_date ASC");
        $stmt->execute([$therapist_id]);
        return $stmt->fetchAll();
    }
    
    public function getTherapistStats($therapist_id) {
        $stmt = $this->conn->prepare("SELECT 
            COUNT(*) as total_sessions,
            COUNT(CASE WHEN status = 'Completed' THEN 1 END) as completed_sessions,
            COUNT(CASE WHEN status = 'Scheduled' THEN 1 END) as upcoming_sessions
            FROM sessions WHERE therapist_id = ?");
        $stmt->execute([$therapist_id]);
        return $stmt->fetch();
    }
    
    public function getPatientMoodEntries($patient_id, $limit = 10) {
        $stmt = $this->conn->prepare("SELECT * FROM mood_entries WHERE patient_id = ? ORDER BY entry_date DESC LIMIT ?");
        $stmt->execute([$patient_id, $limit]);
        return $stmt->fetchAll();
    }
}

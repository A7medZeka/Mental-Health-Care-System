<?php
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Admin.php';
require_once __DIR__ . '/Patient.php';

class Dashboard
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // ── Patient dashboard summary ──────────────────────────────────────────
    public function getPatientDashboardData(int $userId): array
    {
        $conn = SingletonDatabase::getInstance()->getConnection();

        // Basic user row
        $userData = $this->userModel->getUserById($userId);

        // Next upcoming appointment
        $apptStmt = $conn->prepare(
            "SELECT a.appointment_date, u.first_name, u.last_name
             FROM   appointments a
             JOIN   users u ON u.user_id = a.therapist_id
             WHERE  a.patient_id = ? AND a.appointment_date >= NOW()
               AND  a.status IN ('Scheduled','Confirmed')
             ORDER  BY a.appointment_date ASC LIMIT 1"
        );
        $apptStmt->execute([$userId]);
        $nextAppt = $apptStmt->fetch(PDO::FETCH_ASSOC);

        // Today's mood
        $moodStmt = $conn->prepare(
            "SELECT mood_label, mood_score
             FROM mood_entries WHERE patient_id = ? AND entry_date = CURDATE()"
        );
        $moodStmt->execute([$userId]);
        $todayMood = $moodStmt->fetch(PDO::FETCH_ASSOC);

        // Active goal count
        $goalStmt = $conn->prepare(
            "SELECT COUNT(*) AS cnt FROM wellness_goals
             WHERE patient_id = ? AND status = 'In-Progress'"
        );
        $goalStmt->execute([$userId]);
        $activeGoals = (int)($goalStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

        // Pending actions: unsigned consents + unpaid invoices
        $consentStmt = $conn->prepare(
            "SELECT COUNT(*) AS cnt FROM consents
             WHERE patient_id = ? AND signed_date IS NULL"
        );
        $consentStmt->execute([$userId]);
        $unsignedConsents = (int)($consentStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

        $unpaidStmt = $conn->prepare(
            "SELECT COUNT(*) AS cnt FROM payments WHERE patient_id = ? AND status = 'Unpaid'"
        );
        $unpaidStmt->execute([$userId]);
        $unpaidCount = (int)($unpaidStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

        // Onboarding progress
        $intakeStmt = $conn->prepare(
            "SELECT form_id FROM intake_forms WHERE patient_id = ? LIMIT 1"
        );
        $intakeStmt->execute([$userId]);
        $hasIntake = (bool)$intakeStmt->fetch();

        $insStmt = $conn->prepare(
            "SELECT insurance_id FROM insurance WHERE patient_id = ? LIMIT 1"
        );
        $insStmt->execute([$userId]);
        $hasInsurance = (bool)$insStmt->fetch();

        $consentSignedStmt = $conn->prepare(
            "SELECT COUNT(*) AS cnt FROM consents WHERE patient_id = ? AND signed_date IS NOT NULL"
        );
        $consentSignedStmt->execute([$userId]);
        $signedCount = (int)($consentSignedStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

        $hasCard = false;
        $cardStmt = $conn->prepare(
            "SELECT payment_id FROM payments WHERE patient_id = ? AND card_number IS NOT NULL LIMIT 1"
        );
        $cardStmt->execute([$userId]);
        $hasCard = (bool)$cardStmt->fetch();

        $hasTherapist = (bool)$conn->prepare(
            "SELECT match_id FROM therapist_matches WHERE patient_id = ? AND status='Accepted' LIMIT 1"
        )->execute([$userId]);
        $tmStmt = $conn->prepare(
            "SELECT match_id FROM therapist_matches WHERE patient_id = ? AND status='Accepted' LIMIT 1"
        );
        $tmStmt->execute([$userId]);
        $hasTherapist = (bool)$tmStmt->fetch();

        $completedSteps = 1 // profile always exists
            + ($hasIntake    ? 1 : 0)
            + ($hasInsurance ? 1 : 0)
            + ($signedCount  > 0 ? 1 : 0)
            + ($hasCard      ? 1 : 0)
            + ($hasTherapist ? 1 : 0);

        $progress = (int)round(($completedSteps / 6) * 100);

        // Next appointment label
        $nextApptLabel = 'None scheduled';
        if ($nextAppt) {
            $nextApptLabel = date('M j, Y · g:i A', strtotime($nextAppt['appointment_date']))
                . ' · Dr. ' . $nextAppt['first_name'] . ' ' . $nextAppt['last_name'];
        }

        return [
            'first_name'          => $userData['first_name']  ?? 'Patient',
            'last_name'           => $userData['last_name']   ?? '',
            'email'               => $userData['email']       ?? '',
            'age'                 => $this->userModel->getUserAge($userId),
            'gender'              => $this->userModel->getUserGender($userId),
            'next_appointment'    => $nextApptLabel,
            'today_mood'          => $todayMood['mood_label'] ?? 'Not logged',
            'today_mood_score'    => $todayMood['mood_score'] ?? null,
            'active_goals'        => $activeGoals,
            'pending_actions'     => $unsignedConsents + $unpaidCount,
            'onboarding_progress' => $progress,
        ];
    }

    // ── Admin dashboard summary ────────────────────────────────────────────
    public function getAdminDashboardData(): array
    {
        $patientManager   = new AdminPatientManager();
        $therapistManager = new AdminTherapistManager();
        $auditManager     = new AdminAuditManager();

        return [
            'total_patients'   => $patientManager->getTotalPatients(),
            'total_therapists' => $therapistManager->getTotalTherapists(),
            'high_risk_alerts' => $auditManager->getAuditLogsCount(),
        ];
    }

    // ── Recent activity (real DB data) ────────────────────────────────────
    public function getRecentActivity(int $userId): array
    {
        $conn = SingletonDatabase::getInstance()->getConnection();
        $activity = [];

        // Sessions completed
        $s = $conn->prepare(
            "SELECT a.appointment_date, u.first_name, u.last_name
             FROM sessions se
             JOIN appointments a ON a.appointment_id = se.appointment_id
             JOIN users u ON u.user_id = a.therapist_id
             WHERE a.patient_id = ? AND se.session_state = 'Completed'
             ORDER BY a.appointment_date DESC LIMIT 3"
        );
        $s->execute([$userId]);
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $activity[] = [
                'icon' => 'camera-video',
                'text' => 'Session completed with Dr. ' . $r['first_name'] . ' ' . $r['last_name'],
                'date' => date('M j, Y', strtotime($r['appointment_date'])),
            ];
        }

        // Mood entries
        $m = $conn->prepare(
            "SELECT mood_label, entry_date FROM mood_entries
             WHERE patient_id = ? ORDER BY entry_date DESC LIMIT 2"
        );
        $m->execute([$userId]);
        foreach ($m->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $activity[] = [
                'icon' => 'heart-pulse',
                'text' => 'Mood logged: ' . $r['mood_label'],
                'date' => date('M j, Y', strtotime($r['entry_date'])),
            ];
        }

        // Journal entries
        $j = $conn->prepare(
            "SELECT title, created_at FROM journal_entries
             WHERE patient_id = ? ORDER BY created_at DESC LIMIT 2"
        );
        $j->execute([$userId]);
        foreach ($j->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $activity[] = [
                'icon' => 'journal-richtext',
                'text' => 'Journal entry: ' . $r['title'],
                'date' => date('M j, Y', strtotime($r['created_at'])),
            ];
        }

        // Sort by date desc
        usort($activity, fn($a, $b) => strtotime($b['date']) <=> strtotime($a['date']));

        return array_slice($activity, 0, 5);
    }

    // ── Onboarding checklist (real DB data) ──────────────────────────────
    public function getOnboardingChecklist(int $userId): array
    {
        $conn = SingletonDatabase::getInstance()->getConnection();

        $intakeStmt = $conn->prepare("SELECT form_id FROM intake_forms WHERE patient_id = ? LIMIT 1");
        $intakeStmt->execute([$userId]);
        $hasIntake = (bool)$intakeStmt->fetch();

        $insStmt = $conn->prepare("SELECT insurance_id FROM insurance WHERE patient_id = ? LIMIT 1");
        $insStmt->execute([$userId]);
        $hasInsurance = (bool)$insStmt->fetch();

        $consentStmt = $conn->prepare("SELECT consent_id FROM consents WHERE patient_id = ? AND signed_date IS NOT NULL LIMIT 1");
        $consentStmt->execute([$userId]);
        $hasSigned = (bool)$consentStmt->fetch();

        $cardStmt = $conn->prepare("SELECT payment_id FROM payments WHERE patient_id = ? AND card_number IS NOT NULL LIMIT 1");
        $cardStmt->execute([$userId]);
        $hasCard = (bool)$cardStmt->fetch();

        $tmStmt = $conn->prepare("SELECT match_id FROM therapist_matches WHERE patient_id = ? AND status='Accepted' LIMIT 1");
        $tmStmt->execute([$userId]);
        $hasTherapist = (bool)$tmStmt->fetch();

        return [
            ['Create Profile',          'Complete your personal information',        'Completed'],
            ['Submit Intake Form',       'Answer clinical assessment questions',      $hasIntake    ? 'Completed' : 'Pending'],
            ['Verify Insurance',         'Add your insurance provider details',       $hasInsurance ? 'Completed' : 'Pending'],
            ['Sign Legal Consents',      'Review and sign required documents',        $hasSigned    ? 'Completed' : 'Pending'],
            ['Add Payment Method',       'Set up billing for sessions',               $hasCard      ? 'Completed' : 'Pending'],
            ['Receive Therapist Match',  'Awaiting intake form completion',           $hasTherapist ? 'Completed' : 'Locked'],
        ];
    }
}

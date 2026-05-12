<?php
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Admin.php';
require_once __DIR__ . '/Patient.php';
require_once __DIR__ . '/Repositories/PostRepository.php';
require_once __DIR__ . '/../Core/SingletonDatabase.php';

class Dashboard {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function getModeratorDashboardData(): array {
        $postRepo = new PostRepository();
        $conn = SingletonDatabase::getInstance()->getConnection();

        $flaggedCount = count($postRepo->getFlaggedPosts());
        $crisisStmt = $conn->prepare("
            SELECT COUNT(*) as cnt 
            FROM community_posts 
            WHERE is_flagged = 1 
            AND content REGEXP 'انتحار|اموت نفسي|suicide|kill myself|انتحر'
        ");
        $crisisStmt->execute();
        $crisisCount = (int)($crisisStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

        $logStmt = $conn->prepare("
            SELECT COUNT(*) as cnt 
            FROM moderation_logs 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
        ");
        $logStmt->execute();
        $actionsLogged = (int)($logStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);
        // جلب عدد المنشورات المنشورة اليوم فعلياً
        $todayStmt = $conn->query("SELECT COUNT(*) FROM community_posts WHERE DATE(created_at) = CURDATE()");
        $totalToday = (int)$todayStmt->fetchColumn();

        // جلب عدد الحالات الخطيرة
        $crisisStmt = $conn->prepare("SELECT COUNT(*) FROM community_posts WHERE is_flagged = 1 AND content REGEXP 'انتحار|اموت نفسي|suicide'");
        $crisisStmt->execute();
        $crisisCount = (int)$crisisStmt->fetchColumn();

        return [
            'flagged_posts'  => $flaggedCount,
            'crisis_alerts'  => $crisisCount,
            'actions_logged' => $actionsLogged,
            'total_today'    => $totalToday
        ];
    }

    public function getPatientDashboardData(int $userId): array {
        $conn = SingletonDatabase::getInstance()->getConnection();
        $userData = $this->userModel->getUserById($userId);

        $apptStmt = $conn->prepare("
            SELECT a.appointment_date, u.first_name, u.last_name
            FROM   appointments a
            JOIN   users u ON u.user_id = a.therapist_id
            WHERE  a.patient_id = ? AND a.appointment_date >= NOW()
              AND  a.status IN ('Scheduled','Confirmed')
            ORDER  BY a.appointment_date ASC LIMIT 1
        ");
        $apptStmt->execute([$userId]);
        $nextAppt = $apptStmt->fetch(PDO::FETCH_ASSOC);

        $moodStmt = $conn->prepare("SELECT mood_label, mood_score FROM mood_entries WHERE patient_id = ? AND entry_date = CURDATE()");
        $moodStmt->execute([$userId]);
        $todayMood = $moodStmt->fetch(PDO::FETCH_ASSOC);

        $progress = $this->calculateOnboardingProgress($userId, $conn);

        $hasIntake       = (bool)$conn->query("SELECT 1 FROM intake_forms WHERE patient_id = $userId LIMIT 1")->fetch();
        $hasSigned       = (bool)$conn->query("SELECT 1 FROM consents WHERE patient_id = $userId AND signed_date IS NOT NULL LIMIT 1")->fetch();
        $hasPayment      = (bool)$conn->query("SELECT 1 FROM payments WHERE patient_id = $userId LIMIT 1")->fetch();
        $hasMatched      = (bool)$conn->query("SELECT 1 FROM therapist_matches WHERE patient_id = $userId AND status = 'Accepted' LIMIT 1")->fetch();
        $hasAppointment  = (bool)$conn->query("SELECT 1 FROM appointments WHERE patient_id = $userId AND status IN ('Scheduled', 'Confirmed') LIMIT 1")->fetch();

        $goalsStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM wellness_goals WHERE patient_id = ? AND status = 'In-Progress'");
        $goalsStmt->execute([$userId]);
        $activeGoals = (int)($goalsStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

        $pendingActions = 0;
        if (!$hasIntake)      $pendingActions++;
        if (!$hasSigned)      $pendingActions++;
        if (!$hasPayment)     $pendingActions++;
        if (!$hasMatched)     $pendingActions++;
        if (!$hasAppointment) $pendingActions++;

        return [
            'first_name'          => $userData['first_name']  ?? 'Patient',
            'last_name'           => $userData['last_name']   ?? '',
            'email'               => $userData['email']       ?? '',
            'age'                 => $this->userModel->getUserAge($userId),
            'gender'              => $this->userModel->getUserGender($userId),
            'next_appointment'    => $nextAppt ? date('M j, Y', strtotime($nextAppt['appointment_date'])) : 'None scheduled',
            'today_mood'          => $todayMood['mood_label'] ?? 'Not logged',
            'today_mood_score'    => $todayMood['mood_score'] ?? 0,
            'onboarding_progress' => $progress,
            'active_goals'        => $activeGoals,
            'pending_actions'     => $pendingActions,
        ];
    }

    public function getAdminDashboardData(): array {
        $patientManager   = new AdminPatientManager();
        $therapistManager = new AdminTherapistManager();

        $conn = SingletonDatabase::getInstance()->getConnection();
        $highRiskStmt = $conn->prepare("
            SELECT COUNT(*) as cnt 
            FROM users 
            WHERE role = 'Patient' AND status = 'High Risk'
        ");
        $highRiskStmt->execute();
        $highRiskAlerts = $highRiskStmt->fetchColumn();

        return [
            'total_patients'   => $patientManager->getTotalPatients(),
            'total_therapists' => $therapistManager->getTotalTherapists(),
            'high_risk_alerts' => (int)$highRiskAlerts,
            'system_health'    => 'Optimal'
        ];
    }

    private function calculateOnboardingProgress(int $userId, $conn): int {
        // Check if profile has essential information
        $profileStmt = $conn->prepare("
            SELECT first_name, last_name, phone_number, city, gender
            FROM users
            WHERE user_id = ? AND role = 'Patient'
        ");
        $profileStmt->execute([$userId]);
        $profile = $profileStmt->fetch(PDO::FETCH_ASSOC);
        $hasProfile = $profile && !empty($profile['first_name']) && !empty($profile['last_name']) &&
                     !empty($profile['phone_number']) && !empty($profile['city']) && !empty($profile['gender']);

        $hasIntake      = (bool)$conn->query("SELECT 1 FROM intake_forms WHERE patient_id = $userId LIMIT 1")->fetch();
        $hasSigned      = (bool)$conn->query("SELECT 1 FROM consents WHERE patient_id = $userId AND signed_date IS NOT NULL LIMIT 1")->fetch();
        $hasPayment     = (bool)$conn->query("SELECT 1 FROM payments WHERE patient_id = $userId LIMIT 1")->fetch();
        $hasMatched     = (bool)$conn->query("SELECT 1 FROM therapist_matches WHERE patient_id = $userId AND status = 'Accepted' LIMIT 1")->fetch();
        $hasAppointment = (bool)$conn->query("SELECT 1 FROM appointments WHERE patient_id = $userId AND status IN ('Scheduled', 'Confirmed') LIMIT 1")->fetch();

        $completedSteps = 0;
        if ($hasProfile)     $completedSteps++;
        if ($hasIntake)      $completedSteps++;
        if ($hasSigned)      $completedSteps++;
        if ($hasPayment)     $completedSteps++;
        if ($hasMatched)     $completedSteps++;
        if ($hasAppointment) $completedSteps++;

        return (int)round(($completedSteps / 6) * 100);
    }

    public function getRecentActivity(int $userId): array {
        $conn = SingletonDatabase::getInstance()->getConnection();

        $apptStmt = $conn->prepare("
            SELECT 'appointment' as type,
                   CONCAT('Appointment with ', u.first_name, ' ', u.last_name, ' on ', DATE_FORMAT(a.appointment_date, '%M %d')) as description,
                   a.appointment_date as date
            FROM appointments a
            JOIN users u ON u.user_id = a.therapist_id
            WHERE a.patient_id = ? AND a.appointment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ORDER BY a.appointment_date DESC LIMIT 5
        ");
        $apptStmt->execute([$userId]);
        $appointments = $apptStmt->fetchAll(PDO::FETCH_ASSOC);

        $moodStmt = $conn->prepare("
            SELECT 'mood' as type,
                   CONCAT('Logged mood: ', mood_label) as description,
                   entry_date as date
            FROM mood_entries
            WHERE patient_id = ? AND entry_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ORDER BY entry_date DESC LIMIT 5
        ");
        $moodStmt->execute([$userId]);
        $moods = $moodStmt->fetchAll(PDO::FETCH_ASSOC);

        $activities = array_merge($appointments, $moods);
        usort($activities, fn($a, $b) => strtotime($b['date']) <=> strtotime($a['date']));

        return array_slice($activities, 0, 10);
    }

    public function getOnboardingChecklist(int $userId): array {
        $conn = SingletonDatabase::getInstance()->getConnection();

        // Check if profile has essential information
        $profileStmt = $conn->prepare("
            SELECT first_name, last_name, phone_number, city, gender
            FROM users
            WHERE user_id = ? AND role = 'Patient'
        ");
        $profileStmt->execute([$userId]);
        $profile = $profileStmt->fetch(PDO::FETCH_ASSOC);
        $hasProfile = $profile && !empty($profile['first_name']) && !empty($profile['last_name']) &&
                     !empty($profile['phone_number']) && !empty($profile['city']) && !empty($profile['gender']);

        $hasIntake      = (bool)$conn->query("SELECT 1 FROM intake_forms WHERE patient_id = $userId LIMIT 1")->fetch();
        $hasSigned      = (bool)$conn->query("SELECT 1 FROM consents WHERE patient_id = $userId AND signed_date IS NOT NULL LIMIT 1")->fetch();
        $hasPayment     = (bool)$conn->query("SELECT 1 FROM payments WHERE patient_id = $userId LIMIT 1")->fetch();
        $hasMatched     = (bool)$conn->query("SELECT 1 FROM therapist_matches WHERE patient_id = $userId AND status = 'Accepted' LIMIT 1")->fetch();
        $hasAppointment = (bool)$conn->query("SELECT 1 FROM appointments WHERE patient_id = $userId AND status IN ('Scheduled', 'Confirmed') LIMIT 1")->fetch();

        return [
            ['Complete Profile',              'Set up your profile information',        $hasProfile     ? 'Completed' : 'Pending'],
            ['Submit Intake Form',            'Complete the mental health assessment',   $hasIntake      ? 'Completed' : 'Pending'],
            ['Sign Consent',                  'Review and sign required documents',      $hasSigned      ? 'Completed' : 'Pending'],
            ['Add Payment Method',            'Set up your payment information',         $hasPayment     ? 'Completed' : 'Pending'],
            ['Get Matched with Therapist',    'Review and accept therapist match',       $hasMatched     ? 'Completed' : 'Pending'],
            ['Schedule First Appointment',    'Book your first therapy session',         $hasAppointment ? 'Completed' : 'Pending'],
        ];
    }

    public function checkAndPushRoles(): array {
        $conn = SingletonDatabase::getInstance()->getConnection();

        $usersStmt = $conn->prepare("SELECT user_id, role FROM users");
        $usersStmt->execute();
        $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

        $pushed = [];

        foreach ($users as $user) {
            $userId = $user['user_id'];
            $role   = $user['role'];

            if ($role === 'Patient') {
                $checkStmt = $conn->prepare("SELECT patient_id FROM patients WHERE patient_id = ?");
                $checkStmt->execute([$userId]);
                if (!$checkStmt->fetch()) {
                    $conn->prepare("INSERT INTO patients (patient_id) VALUES (?)")->execute([$userId]);
                    $pushed[] = "Pushed user_id $userId to patients table";
                }
                $conn->prepare("DELETE FROM therapists WHERE therapist_id = ?")->execute([$userId]);
                $conn->prepare("DELETE FROM admins WHERE admin_id = ?")->execute([$userId]);
                $conn->prepare("DELETE FROM moderators WHERE moderator_id = ?")->execute([$userId]);

            } elseif ($role === 'Therapist') {
                $checkStmt = $conn->prepare("SELECT therapist_id FROM therapists WHERE therapist_id = ?");
                $checkStmt->execute([$userId]);
                if (!$checkStmt->fetch()) {
                    $licenseExpiryDate = date('Y-m-d', strtotime('+1 year'));
                    $conn->prepare("
                        INSERT INTO therapists 
                        (therapist_id, specialization, languages, license_status, license_expiry_date, experience_years, rating, hourly_rate, availability_schedule, credential_file_path, is_verified)
                        VALUES (?, '', 'English', 'Active', ?, 0, 0.0, 0.0, '', '', 0)
                    ")->execute([$userId, $licenseExpiryDate]);
                    $pushed[] = "Pushed user_id $userId to therapists table";
                }
                $conn->prepare("DELETE FROM patients WHERE patient_id = ?")->execute([$userId]);
                $conn->prepare("DELETE FROM admins WHERE admin_id = ?")->execute([$userId]);
                $conn->prepare("DELETE FROM moderators WHERE moderator_id = ?")->execute([$userId]);

            } elseif ($role === 'Admin') {
                $checkStmt = $conn->prepare("SELECT admin_id FROM admins WHERE admin_id = ?");
                $checkStmt->execute([$userId]);
                if (!$checkStmt->fetch()) {
                    $conn->prepare("INSERT INTO admins (admin_id) VALUES (?)")->execute([$userId]);
                    $pushed[] = "Pushed user_id $userId to admins table";
                }
                $conn->prepare("DELETE FROM patients WHERE patient_id = ?")->execute([$userId]);
                $conn->prepare("DELETE FROM therapists WHERE therapist_id = ?")->execute([$userId]);
                $conn->prepare("DELETE FROM moderators WHERE moderator_id = ?")->execute([$userId]);

            } elseif ($role === 'Moderator') {
                $checkStmt = $conn->prepare("SELECT moderator_id FROM moderators WHERE moderator_id = ?");
                $checkStmt->execute([$userId]);
                if (!$checkStmt->fetch()) {
                    $conn->prepare("INSERT INTO moderators (moderator_id) VALUES (?)")->execute([$userId]);
                    $pushed[] = "Pushed user_id $userId to moderators table";
                }
                $conn->prepare("DELETE FROM patients WHERE patient_id = ?")->execute([$userId]);
                $conn->prepare("DELETE FROM therapists WHERE therapist_id = ?")->execute([$userId]);
                $conn->prepare("DELETE FROM admins WHERE admin_id = ?")->execute([$userId]);
            }
        }

        return $pushed;
    }
}
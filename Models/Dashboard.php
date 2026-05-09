<?php
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Admin.php';
require_once __DIR__ . '/Patient.php';
require_once __DIR__ . '/Repositories/PostRepository.php';
require_once __DIR__ . '/../Core/SingletonDatabase.php';
class Dashboard
{
    private User $userModel;
    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * 1. ملخص بيانات لوحة تحكم المشرف (Moderator)
     * يعتمد على الـ PostRepository لتطبيق مبدأ الـ SOLID
     */
    public function getModeratorDashboardData(): array
    {
        $postRepo = new PostRepository();
        $conn = SingletonDatabase::getInstance()->getConnection();

        // أ. جلب عدد المنشورات المنتظرة للمراجعة (Flagged)
        $flaggedCount = count($postRepo->getFlaggedPosts());

        // ب. جلب عدد تنبيهات الأزمات بناءً على كلمات دلالية (Crisis Alerts)
        $crisisStmt = $conn->prepare("
            SELECT COUNT(*) as cnt 
            FROM community_posts 
            WHERE is_flagged = 1 
            AND content REGEXP 'انتحار|اموت نفسي|suicide|kill myself|انتحر'
        ");
        $crisisStmt->execute();
        $crisisCount = (int)($crisisStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

        // ج. جلب عدد العمليات التي تمت في آخر شهر (Actions Logged)
        $logStmt = $conn->prepare("
            SELECT COUNT(*) as cnt 
            FROM moderation_logs 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
        ");
        $logStmt->execute();
        $actionsLogged = (int)($logStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

        return [
            'flagged_posts'  => $flaggedCount,
            'crisis_alerts'  => $crisisCount,
            'actions_logged' => $actionsLogged,
            'total_today'    => 47 // رقم افتراضي أو يمكن حسابه بـ Count للمنشورات اليومية
        ];
    }

    /**
     * 2. ملخص بيانات لوحة تحكم المريض (Patient)
     */
    public function getPatientDashboardData(int $userId): array
    {
        $conn = SingletonDatabase::getInstance()->getConnection();
        $userData = $this->userModel->getUserById($userId);

        // موعد الجلسة القادم
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

        // حالة المزاج اليوم
        $moodStmt = $conn->prepare("SELECT mood_label, mood_score FROM mood_entries WHERE patient_id = ? AND entry_date = CURDATE()");
        $moodStmt->execute([$userId]);
        $todayMood = $moodStmt->fetch(PDO::FETCH_ASSOC);

        // حساب نسبة الإنجاز في الـ Onboarding
        $progress = $this->calculateOnboardingProgress($userId, $conn);

        return [
            'first_name'          => $userData['first_name']  ?? 'Patient',
            'last_name'           => $userData['last_name']   ?? '',
            'age'                 => $this->userModel->getUserAge($userId),
            'next_appointment'    => $nextAppt ? date('M j, Y', strtotime($nextAppt['appointment_date'])) : 'None scheduled',
            'today_mood'          => $todayMood['mood_label'] ?? 'Not logged',
            'onboarding_progress' => $progress,
        ];
    }

    /**
     * 3. ملخص بيانات لوحة تحكم المدير (Admin)
     */
    public function getAdminDashboardData(): array
    {
        // AdminPatientManager و AdminTherapistManager هي كلاسات مساعدة للـ Admin
        $patientManager   = new AdminPatientManager();
        $therapistManager = new AdminTherapistManager();

        return [
            'total_patients'   => $patientManager->getTotalPatients(),
            'total_therapists' => $therapistManager->getTotalTherapists(),
            'system_health'    => 'Optimal'
        ];
    }

    /**
     * دالة مساعدة لحساب تقدم المريض في إعداد حسابه
     */
    private function calculateOnboardingProgress(int $userId, $conn): int
    {
        $hasIntake = (bool)$conn->query("SELECT 1 FROM intake_forms WHERE patient_id = $userId LIMIT 1")->fetch();
        $hasSigned = (bool)$conn->query("SELECT 1 FROM consents WHERE patient_id = $userId AND signed_date IS NOT NULL LIMIT 1")->fetch();

        $completedSteps = 1 + ($hasIntake ? 1 : 0) + ($hasSigned ? 1 : 0);
        return (int)round(($completedSteps / 3) * 100);
    }
}
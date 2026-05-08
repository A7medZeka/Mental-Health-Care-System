<?php
require_once __DIR__ . '/../Core/SingletonDatabase.php';
require_once __DIR__ . '/../Core/Validation.php';
require_once __DIR__ . '/../Core/ImmutablePattern.php';
require_once __DIR__ . '/../Core/ObserverPattern.php';
require_once __DIR__ . '/../Models/Dashboard.php';
require_once __DIR__ . '/../Models/Patient.php';

class PatientDashboardController
{
    private Dashboard                  $dashboardModel;
    private Patient                    $patientModel;
    private PatientAppointmentManager  $apptManager;
    private PatientWellnessManager     $wellnessManager;
    private PatientJournalManager      $journalManager;
    private PatientPaymentManager      $paymentManager;
    private PatientConsentManager      $consentManager;
    private PatientResourceManager     $resourceManager;
    private PatientNotificationManager $notifManager;

    public function __construct()
    {
        SingletonDatabase::getInstance();

        $this->dashboardModel  = new Dashboard();
        $this->patientModel    = new Patient();
        $this->apptManager     = new PatientAppointmentManager();
        $this->wellnessManager = new PatientWellnessManager();
        $this->journalManager  = new PatientJournalManager();
        $this->paymentManager  = new PatientPaymentManager();
        $this->consentManager  = new PatientConsentManager();
        $this->resourceManager = new PatientResourceManager();
        $this->notifManager    = new PatientNotificationManager();
    }


    public function handleRequest(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        checkMethod($method);

        $this->requireLogin();
        $this->requirePatientRole();

        if ($method === 'POST') {
            $this->handlePost();
        }

        return $this->getDashboardData();
    }

    private function requireLogin(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ../Auth/login.php');
            exit();
        }
    }

    private function requirePatientRole(): void
    {
        if ($_SESSION['role'] === 'Patient') return;

        $redirect = [
            'Admin'     => '../Admin/dashboard.php',
            'Therapist' => '../Therapist/dashboard.php',
            'Moderator' => '../Moderator/dashboard.php',
        ];
        header('Location: ' . ($redirect[$_SESSION['role']] ?? '../Auth/login.php'));
        exit();
    }


    private function handlePost(): void
    {
        $action = $_POST['action'] ?? '';

        match ($action) {
            // Profile
            'update_profile'     => $this->handleUpdateProfile(),
            'update_preferences' => $this->handleUpdatePreferences(),

            // Appointments
            'book_appointment'   => $this->handleBookAppointment(),
            'cancel_appointment' => $this->handleCancelAppointment(),

            // Mood
            'log_mood'           => $this->handleLogMood(),

            // Goals
            'create_goal'        => $this->handleCreateGoal(),
            'update_goal'        => $this->handleUpdateGoalProgress(),

            // Journal
            'create_journal'     => $this->handleCreateJournal(),
            'toggle_privacy'     => $this->handleTogglePrivacy(),

            // Payments & Insurance
            'save_card'          => $this->handleSaveCard(),
            'save_insurance'     => $this->handleSaveInsurance(),
            'submit_dispute'     => $this->handleSubmitDispute(),

            // Consents
            'sign_consent'       => $this->handleSignConsent(),

            // Resources
            'log_resource'       => $this->handleLogResource(),

            // Notifications
            'mark_read'          => $this->handleMarkNotificationsRead(),

            // Intake Form
            'submit_intake'      => $this->handleSubmitIntake(),

            // Forum
            'post_forum'         => $this->handlePostForum(),
            'react_post'         => $this->handleReactPost(),
            'flag_post'          => $this->handleFlagPost(),
            'load_posts'         => $this->handleLoadPosts(),

            default              => $this->jsonError('Unknown action.'),
        };
    }


    private function handleUpdateProfile(): void
    {
        header('Content-Type: application/json');
        $patientId = (int)$_SESSION['user_id'];
        $data = array_filter([
            'first_name'   => trim($_POST['first_name']   ?? ''),
            'last_name'    => trim($_POST['last_name']    ?? ''),
            'phone_number' => trim($_POST['phone_number'] ?? ''),
            'city'         => trim($_POST['city']         ?? ''),
            'gender'       => trim($_POST['gender']       ?? ''),
        ]);
        echo json_encode($this->patientModel->updateProfile($patientId, $data));
        exit();
    }

    private function handleUpdatePreferences(): void
    {
        header('Content-Type: application/json');
        $patientId = (int)$_SESSION['user_id'];
        $prefs = array_filter([
            'pref_language'            => trim($_POST['pref_language']            ?? ''),
            'pref_therapist_gender'    => trim($_POST['pref_therapist_gender']    ?? ''),
            'pref_cultural_background' => trim($_POST['pref_cultural_background'] ?? ''),
            'pref_specialization'      => trim($_POST['pref_specialization']      ?? ''),
        ]);
        echo json_encode($this->patientModel->updatePreferences($patientId, $prefs));
        exit();
    }

    private function handleBookAppointment(): void
    {
        header('Content-Type: application/json');
        $patientId   = (int)$_SESSION['user_id'];
        $therapistId = filter_input(INPUT_POST, 'therapist_id', FILTER_VALIDATE_INT);
        $date        = trim($_POST['appointment_date'] ?? '');
        $type        = trim($_POST['session_type']     ?? 'Video Session');

        if (!$therapistId) {
            echo json_encode(['success' => false, 'message' => 'Invalid therapist ID.']);
            exit();
        }
        if (empty($date) || strtotime($date) < time()) {
            echo json_encode(['success' => false, 'message' => 'Please choose a future date.']);
            exit();
        }

        echo json_encode($this->apptManager->bookAppointment($patientId, $therapistId, $date, $type));
        exit();
    }

    private function handleCancelAppointment(): void
    {
        header('Content-Type: application/json');
        $patientId = (int)$_SESSION['user_id'];
        $apptId    = filter_input(INPUT_POST, 'appointment_id', FILTER_VALIDATE_INT);

        if (!$apptId) {
            echo json_encode(['success' => false, 'message' => 'Invalid appointment ID.']);
            exit();
        }

        echo json_encode($this->apptManager->cancelAppointment($apptId, $patientId));
        exit();
    }


    private function handleLogMood(): void
    {
        header('Content-Type: application/json');
        $patientId  = (int)$_SESSION['user_id'];
        $moodScore  = filter_input(INPUT_POST, 'mood_score', FILTER_VALIDATE_INT);
        $moodLabel  = trim($_POST['mood_label'] ?? '');
        $notes      = trim($_POST['notes']      ?? '');

        $validLabels = ['Excellent','Good','Neutral','Low','Anxious'];
        if (!$moodScore || $moodScore < 1 || $moodScore > 5) {
            echo json_encode(['success' => false, 'message' => 'Invalid mood score (1-5).']);
            exit();
        }
        if (!in_array($moodLabel, $validLabels, true)) {
            echo json_encode(['success' => false, 'message' => 'Invalid mood label.']);
            exit();
        }

        $ok = $this->wellnessManager->logMood($patientId, $moodScore, $moodLabel, $notes);
        echo json_encode(['success' => (bool)$ok, 'message' => $ok ? 'Mood logged.' : 'Failed to log mood.']);
        exit();
    }


    private function handleCreateGoal(): void
    {
        header('Content-Type: application/json');
        $patientId  = (int)$_SESSION['user_id'];
        $title      = trim($_POST['goal_title']   ?? '');
        $targetDays = filter_input(INPUT_POST, 'target_days', FILTER_VALIDATE_INT);
        $category   = trim($_POST['category']     ?? 'Other');

        if (empty($title)) {
            echo json_encode(['success' => false, 'message' => 'Goal title is required.']);
            exit();
        }

        $ok = $this->wellnessManager->createGoal($patientId, $title, $targetDays ?: 1, $category);
        echo json_encode(['success' => (bool)$ok, 'message' => $ok ? 'Goal created.' : 'Failed to create goal.']);
        exit();
    }

    private function handleUpdateGoalProgress(): void
    {
        header('Content-Type: application/json');
        $goalId   = filter_input(INPUT_POST, 'goal_id', FILTER_VALIDATE_INT);
        $progress = filter_input(INPUT_POST, 'progress', FILTER_VALIDATE_FLOAT);

        if (!$goalId) {
            echo json_encode(['success' => false, 'message' => 'Invalid goal ID.']);
            exit();
        }

        $ok = $this->wellnessManager->updateGoalProgress($goalId, $progress ?? 0);
        echo json_encode(['success' => (bool)$ok, 'message' => $ok ? 'Progress updated.' : 'Update failed.']);
        exit();
    }

    private function handleCreateJournal(): void
    {
        header('Content-Type: application/json');
        $patientId = (int)$_SESSION['user_id'];
        $title     = trim($_POST['journalTitle']   ?? '');
        $content   = trim($_POST['journalContent'] ?? '');
        $privacy   = trim($_POST['privacy']        ?? 'Private');

        if (empty($title) || empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Title and content are required.']);
            exit();
        }

        $ok = $this->journalManager->createJournalEntry($patientId, $title, $content, $privacy);
        echo json_encode(['success' => (bool)$ok, 'message' => $ok ? 'Journal entry saved.' : 'Save failed.']);
        exit();
    }

    private function handleTogglePrivacy(): void
    {
        header('Content-Type: application/json');
        $patientId = (int)$_SESSION['user_id'];
        $entryId   = filter_input(INPUT_POST, 'entry_id', FILTER_VALIDATE_INT);

        if (!$entryId) {
            echo json_encode(['success' => false, 'message' => 'Invalid entry ID.']);
            exit();
        }

        echo json_encode($this->journalManager->togglePrivacy($entryId, $patientId));
        exit();
    }

    private function handleSaveCard(): void
    {
        header('Content-Type: application/json');
        $patientId = (int)$_SESSION['user_id'];
        echo json_encode($this->paymentManager->saveCard($patientId, $_POST));
        exit();
    }

    private function handleSaveInsurance(): void
    {
        header('Content-Type: application/json');
        $patientId = (int)$_SESSION['user_id'];
        echo json_encode($this->paymentManager->saveInsurance($patientId, $_POST));
        exit();
    }

    private function handleSubmitDispute(): void
    {
        header('Content-Type: application/json');
        $patientId    = (int)$_SESSION['user_id'];
        $appointmentId = filter_input(INPUT_POST, 'appointment_id', FILTER_VALIDATE_INT);
        $reason       = trim($_POST['reason']      ?? '');
        $description  = trim($_POST['description'] ?? '');

        if (!$appointmentId) {
            echo json_encode(['success' => false, 'message' => 'Invalid appointment.']);
            exit();
        }

        echo json_encode($this->paymentManager->submitDispute($patientId, $appointmentId, $reason, $description));
        exit();
    }

    private function handleSignConsent(): void
    {
        header('Content-Type: application/json');
        $patientId = (int)$_SESSION['user_id'];
        $docName   = trim($_POST['document_name']    ?? '');
        $version   = trim($_POST['document_version'] ?? '1.0');

        if (empty($docName)) {
            echo json_encode(['success' => false, 'message' => 'Document name required.']);
            exit();
        }

        echo json_encode($this->consentManager->signConsent($patientId, $docName, $version));
        exit();
    }

    private function handleLogResource(): void
    {
        header('Content-Type: application/json');
        $patientId  = (int)$_SESSION['user_id'];
        $resourceId = filter_input(INPUT_POST, 'resource_id', FILTER_VALIDATE_INT);
        $duration   = filter_input(INPUT_POST, 'duration',    FILTER_VALIDATE_INT) ?? 0;

        if (!$resourceId) {
            echo json_encode(['success' => false, 'message' => 'Invalid resource ID.']);
            exit();
        }

        $ok = $this->resourceManager->logResourceUsage($patientId, $resourceId, $duration);
        echo json_encode(['success' => $ok]);
        exit();
    }


    private function handleMarkNotificationsRead(): void
    {
        header('Content-Type: application/json');
        $patientId = (int)$_SESSION['user_id'];
        $ok = $this->notifManager->markAllRead($patientId);
        echo json_encode(['success' => $ok]);
        exit();
    }


    public function getDashboardData(): array
    {
        $uid = (int)$_SESSION['user_id'];
        return $this->dashboardModel->getPatientDashboardData($uid);
    }

    public function getUserData(): array
    {
        $uid = (int)$_SESSION['user_id'];

        // Use ImmutableUserFactory for type-safe, read-only profile snapshot
        $factory = new ImmutableUserFactory();
        $immutable = $factory->createPatientFromId($uid);

        if ($immutable) {
            return $immutable->toArray() + [
                'role' => $_SESSION['role'] ?? 'Patient',
            ];
        }

        // Fallback
        return [
            'user_id'    => $uid,
            'first_name' => $_SESSION['first_name'] ?? 'Patient',
            'last_name'  => $_SESSION['last_name']  ?? '',
            'email'      => $_SESSION['email']       ?? '',
            'role'       => $_SESSION['role']        ?? 'Patient',
            'age'        => $this->patientModel->getUserAge($uid),
            'gender'     => $this->patientModel->getUserGender($uid),
        ];
    }

    public function getProfileData(): ?array
    {
        return $this->patientModel->getProfile((int)$_SESSION['user_id']);
    }

    public function getMyTherapist(): ?array
    {
        return $this->apptManager->getMyTherapist((int)$_SESSION['user_id']);
    }

    public function getUpcomingAppointments(): array
    {
        return $this->apptManager->getUpcomingAppointments((int)$_SESSION['user_id']);
    }

    public function getPastAppointments(): array
    {
        return $this->apptManager->getPastAppointments((int)$_SESSION['user_id']);
    }

    public function getAvailableTherapists(): array
    {
        return $this->apptManager->getAvailableTherapists();
    }

    public function getMoodHistory(int $days = 7): array
    {
        return $this->wellnessManager->getMoodHistory((int)$_SESSION['user_id'], $days);
    }

    public function getTodayMood(): ?array
    {
        return $this->wellnessManager->getTodayMood((int)$_SESSION['user_id']);
    }

    public function getGoals(): array
    {
        return $this->wellnessManager->getMyGoals((int)$_SESSION['user_id']);
    }

    public function getJournalEntries(int $limit = 10): array
    {
        return $this->journalManager->getJournalEntries((int)$_SESSION['user_id'], $limit);
    }

    public function getPayments(): array
    {
        return $this->paymentManager->getPayments((int)$_SESSION['user_id']);
    }

    public function getInsurance(): ?array
    {
        return $this->paymentManager->getInsurance((int)$_SESSION['user_id']);
    }

    public function getConsents(): array
    {
        return $this->consentManager->getConsents((int)$_SESSION['user_id']);
    }

    public function getResources(): array
    {
        return $this->resourceManager->getResources((int)$_SESSION['user_id']);
    }

    public function getNotifications(): array
    {
        return $this->notifManager->getNotifications((int)$_SESSION['user_id']);
    }

    public function getUnreadNotifCount(): int
    {
        return $this->notifManager->getUnreadCount((int)$_SESSION['user_id']);
    }

    public function getIntakeStatus(): ?array
    {
        return $this->patientModel->getIntakeFormStatus((int)$_SESSION['user_id']);
    }

    public function getRecentActivity(): array
    {
        return $this->dashboardModel->getRecentActivity((int)$_SESSION['user_id']);
    }

    public function getOnboardingChecklist(): array
    {
        return $this->dashboardModel->getOnboardingChecklist((int)$_SESSION['user_id']);
    }


    private function jsonError(string $message): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit();
    }

    private function handleSubmitIntake(): void
    {
        header('Content-Type: application/json');
        $userId     = (int)$_SESSION['user_id'];
        $totalScore = filter_input(INPUT_POST, 'total_score', FILTER_VALIDATE_FLOAT);
        $level      = trim($_POST['level']     ?? '');
        $responses  = trim($_POST['responses'] ?? '');

        if ($totalScore === false || $totalScore < 0 || $totalScore > 100) {
            echo json_encode(['success' => false, 'message' => 'Invalid score.']);
            exit();
        }

        try {
            $conn = SingletonDatabase::getInstance()->getConnection();

            $conn->prepare(
                "INSERT IGNORE INTO patients (patient_id) VALUES (?)"
            )->execute([$userId]);

            $conn->prepare(
                "INSERT INTO intake_forms (patient_id, total_score, submission_date, respones)
                 VALUES (?, ?, NOW(), ?)
                 ON DUPLICATE KEY UPDATE
                   total_score     = VALUES(total_score),
                   submission_date = NOW(),
                   respones        = VALUES(respones)"
            )->execute([$userId, $totalScore, $responses]);

            if (!empty($level)) {
                $conn->prepare(
                    "UPDATE patients SET level_of_care = ? WHERE patient_id = ?"
                )->execute([$level, $userId]);
            }

            $conn->prepare(
                "UPDATE users SET status = 'Screened'
                 WHERE user_id = ? AND (status = 'Registered' OR status IS NULL)"
            )->execute([$userId]);

            echo json_encode(['success' => true, 'message' => 'Assessment saved.', 'score' => $totalScore]);
        } catch (Exception $e) {
            error_log('[Intake] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to save assessment. Details: ' . $e->getMessage()]);
        }
        exit();
    }


    private function handlePostForum(): void
    {
        header('Content-Type: application/json');
        $patientId  = (int)$_SESSION['user_id'];
        $content    = trim($_POST['content']    ?? '');
        $category   = trim($_POST['category']   ?? 'General support');
        $pseudonym  = trim($_POST['pseudonym']  ?? '');
        $isCrisis   = (int)($_POST['is_crisis'] ?? 0);

        if (empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Content cannot be empty.']);
            exit();
        }

        // Validate category against ENUM
        $validCategories = ['General support', 'Anxiety &stress', 'Depression', 'Recovery Journey', 'Gratitude'];
        if (!in_array($category, $validCategories, true)) {
            $category = 'General support';
        }

        try {
            $conn = SingletonDatabase::getInstance()->getConnection();
            $stmt = $conn->prepare(
                "INSERT INTO community_posts (user_id, author_pseudonym, category, content, is_flagged, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([$patientId, $pseudonym ?: 'Anonymous', $category, $content, $isCrisis]);
            $postId = $conn->lastInsertId();

            // If crisis keywords — auto-flag + insert moderation log
            if ($isCrisis) {
                $conn->prepare(
                    "INSERT INTO moderation_logs (post_id, action, actioned_by, note, created_at)
                     VALUES (?, 'auto-flagged', 0, 'Crisis keywords detected', NOW())"
                )->execute([$postId]);
            }

            echo json_encode(['success' => true, 'message' => 'Posted.', 'post_id' => $postId]);
        } catch (Exception $e) {
            error_log('[Forum Post] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to post.']);
        }
        exit();
    }

    private function handleReactPost(): void
    {
        header('Content-Type: application/json');
        $postId    = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
        $reactionType = trim($_POST['reaction_type'] ?? 'heart');

        if (!$postId) { echo json_encode(['success' => false, 'message' => 'Invalid post.']); exit(); }

        $col = $reactionType === 'hug' ? 'smile_count' : 'like_count';

        try {
            $conn = SingletonDatabase::getInstance()->getConnection();
            $conn->prepare("UPDATE community_posts SET $col = $col + 1 WHERE post_id = ?")->execute([$postId]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'React failed.']);
        }
        exit();
    }

    private function handleFlagPost(): void
    {
        header('Content-Type: application/json');
        $postId    = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
        $patientId = (int)$_SESSION['user_id'];

        if (!$postId) { echo json_encode(['success' => false, 'message' => 'Invalid post.']); exit(); }

        try {
            $conn = SingletonDatabase::getInstance()->getConnection();
            $conn->prepare("UPDATE community_posts SET is_flagged = 1 WHERE post_id = ?")->execute([$postId]);
            $conn->prepare(
                "INSERT INTO moderation_logs (post_id, action, actioned_by, note, created_at)
                 VALUES (?, 'flagged', ?, 'Flagged by patient', NOW())"
            )->execute([$postId, $patientId]);
            echo json_encode(['success' => true, 'message' => 'Reported.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Flag failed.']);
        }
        exit();
    }

    private function handleLoadPosts(): void
    {
        header('Content-Type: application/json');
        $category = trim($_POST['category'] ?? 'all');
        $offset   = max(0, (int)($_POST['offset'] ?? 0));
        $limit    = 10;

        try {
            $conn = SingletonDatabase::getInstance()->getConnection();
            $where = $category !== 'all' ? "WHERE cp.category = ?" : "WHERE 1=1";
            $params = $category !== 'all' ? [$category] : [];
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $conn->prepare(
                "SELECT cp.post_id, cp.author_pseudonym, cp.category, cp.content,
                        cp.like_count, cp.smile_count, cp.is_flagged, cp.created_at
                 FROM community_posts cp
                 $where
                 ORDER BY cp.created_at DESC
                 LIMIT ? OFFSET ?"
            );
            $stmt->execute($params);
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'posts' => $posts]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'posts' => [], 'message' => $e->getMessage()]);
        }
        exit();
    }
}

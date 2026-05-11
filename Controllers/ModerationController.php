<?php
session_start();
require_once __DIR__ . '/../Core/Validation.php';
require_once __DIR__ . '/../Core/SingletonDatabase.php';
require_once __DIR__ . '/../Models/Repositories/PostRepository.php';
require_once __DIR__ . '/../Models/Repositories/ModeratorRepository.php';
require_once __DIR__ . '/../Models/Services/NotificationService.php';
require_once __DIR__ . '/../Models/Services/CrisisService.php';
require_once __DIR__ . '/../Models/Services/ModerationService.php';
class ModerationController {
    private $moderationService;

    public function __construct(ModerationService $modService) {
        $this->moderationService = $modService;
    }

    public function handleModerationAction() {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (!checkMethod($method) || empty($_SESSION['user_id']) || $_SESSION['role'] !== 'Moderator') {
            header("HTTP/1.1 403 Forbidden");
            exit(json_encode(['success' => false, 'error' => 'Unauthorized']));
        }

        $action = trim($_POST['action'] ?? '');
        if ($action === 'escalate') {
            $this->escalatePost();
        } else {
            $this->updatePostState($action);
        }
    }

    public function escalatePost() {
        $postId = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
        $content = trim($_POST['content'] ?? '');

        if (!$postId || empty($content)) {
            exit(json_encode(['success' => false, 'error' => 'empty_data']));
        }

        $safeContent = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        $result = $this->moderationService->handleFlaggedPost($postId, $safeContent);
        if ($result === 'crisis_handled') {
            echo json_encode([
                'success' => true,
                'redirect' => '../Views/Moderator/safety-audit.php?alert=active'
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'status' => 'escalated'
            ]);
        }
        exit();
    }

    private function updatePostState(string $action) {
        $postId = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
        $note = trim($_POST['note'] ?? '');

        if (!$postId) {
            exit(json_encode(['success' => false, 'error' => 'Missing post ID']));
        }

        try {
            // استدعاء دالة التقييم المرتبطة بالـ State Machine وعلاقة Evaluates
            $this->moderationService->evaluateAndTransition($postId, $action, $note);

            echo json_encode([
                'success' => true,
                'status' => $action,
                'message' => 'Post evaluation completed successfully'
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit();
    }
}

// =========================================================================
// التعديل في منطقة الـ Bootstrap بالأسفل:
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = SingletonDatabase::getInstance()->getConnection();

    // 1. إنشاء الـ Repositories
    $repo = new PostRepository();
    $modRepo = new ModeratorRepository(); // إضافة الكلاس الجديد

    // 2. إنشاء الـ Services
    $crisis = new CrisisService(new NotificationService());

    // 3. حقن التبعيات (Dependency Injection) بالترتيب الجديد للـ Constructor
    $modService = new ModerationService($repo, $modRepo, $crisis);

    // 4. تشغيل الـ Controller
    $controller = new ModerationController($modService);
    $controller->handleModerationAction();
}
?>
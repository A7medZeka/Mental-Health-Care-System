<?php
require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Core/Validation.php';
require_once __DIR__ . '/../Core/ImmutableObjects.php';
require_once __DIR__ . '/../Models/Dashboard.php';
require_once __DIR__ . '/../Models/Admin.php';
require_once __DIR__ . '/RBACController.php';
require_once __DIR__ . '/DisputeController.php';
require_once __DIR__ . '/../Models/Repositories/DisputeRepository.php';
require_once __DIR__ . '/../Models/Services/NotificationService.php';

class AdminDashboardController {

    private Dashboard           $dashboardModel;
    private AdminPatientManager $patientManager;
    private AdminRBACManager    $rbacManager;     

    public function __construct() {
        SingletonDatabase::getInstance();
        $this->dashboardModel = new Dashboard();
        $this->patientManager = new AdminPatientManager();
        $this->rbacManager    = new AdminRBACManager();
    }

    // =========================================================================
    // Entry point
    // =========================================================================
    public function handleRequest(): array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        checkMethod($method);           // Core/Validation helper

        $this->requireLogin();
        $this->requireAdminRole();

        if ($method === 'POST') {
            $this->handlePost();
        }

        return $this->getDashboardData();
    }

    // =========================================================================
    // Auth guards  (Validation responsibility)
    // =========================================================================
    private function requireLogin(): void {
        if (empty($_SESSION['user_id'])) {
            header('Location: ../Auth/login.php');
            exit();
        }
    }

    private function requireAdminRole(): void {
        if ($_SESSION['role'] === 'Admin') {
            return;
        }

        $redirect = [
            'Patient'   => '../Patient/dashboard.php',
            'Therapist' => '../Therapist/dashboard.php',
            'Moderator' => '../Moderator/dashboard.php',
        ];
        header('Location: ' . ($redirect[$_SESSION['role']] ?? '../Auth/login.php'));
        exit();
    }

    // =========================================================================
    // POST dispatcher  (routes only – no business logic here)
    // =========================================================================
    private function handlePost(): void {
        $action = $_POST['action'] ?? '';

        match ($action) {
            'update_status'    => $this->handleUpdateStatus(),
            'upload_intake'    => $this->handleUploadIntake(),
            'update_role'      => $this->handleUpdateRole(),
            'delete_user'      => $this->handleDeleteUser(),
            'resolve_dispute'  => $this->handleResolveDispute(),   // UC 34
            'audit_action'     => $this->handleAuditAction(),      // UC 35
            default            => $this->jsonError('Unknown action.'),
        };
    }

    // =========================================================================
    // Action: update_status
    // =========================================================================
    private function handleUpdateStatus(): void {
        header('Content-Type: application/json');

        $patientId     = filter_input(INPUT_POST, 'patient_id', FILTER_VALIDATE_INT);
        $newStatus     = trim($_POST['new_status']     ?? '');
        $currentStatus = trim($_POST['current_status'] ?? '');

        if (!$patientId) {
            echo json_encode(['success' => false, 'message' => 'Invalid patient ID.']);
            exit();
        }

        echo json_encode(
            $this->patientManager->updatePatientStatus($patientId, $currentStatus, $newStatus)
        );
        exit();
    }

    // =========================================================================
    // Action: upload_intake
    // =========================================================================
    private function handleUploadIntake(): void {
        header('Content-Type: application/json');

        $patientId = filter_input(INPUT_POST, 'patient_id', FILTER_VALIDATE_INT);

        if (!$patientId) {
            echo json_encode(['success' => false, 'message' => 'Invalid patient ID.']);
            exit();
        }

        if (!isset($_FILES['intakeFile']) || $_FILES['intakeFile']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error.']);
            exit();
        }

        echo json_encode(
            $this->patientManager->uploadIntakeForm($patientId, $_FILES['intakeFile'], $_SESSION['user_id'])
        );
        exit();
    }

    // =========================================================================
    // Action: update_role  –  validate inputs here, delegate logic to model
    // =========================================================================
    private function handleUpdateRole(): void {
        header('Content-Type: application/json');

        $targetId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        $newRole  = trim($_POST['new_role'] ?? '');

        // ── Input validation (controller responsibility) ──────────────────────
        if (!$targetId) {
            echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
            exit();
        }

        if ($targetId === (int)$_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'You cannot change your own role.']);
            exit();
        }

        // ── Delegate business logic to model ─────────────────────────────────
        echo json_encode($this->rbacManager->promoteUser($targetId, $newRole));
        exit();
    }

    // =========================================================================
    // Action: delete_user  –  validate inputs here, delegate logic to model
    // =========================================================================
    private function handleDeleteUser(): void {
        header('Content-Type: application/json');

        $targetId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

        // ── Input validation (controller responsibility) ──────────────────────
        if (!$targetId) {
            echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
            exit();
        }

        if ($targetId === (int)$_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'You cannot delete your own account.']);
            exit();
        }

        // ── Delegate business logic to model ─────────────────────────────────
        echo json_encode($this->rbacManager->deleteUser($targetId));
        exit();
    }

    // =========================================================================
    // Data providers
    // =========================================================================
    public function getDashboardData(): array {
        return $this->dashboardModel->getAdminDashboardData();
    }

    public function getPatientsViewData(): array {
        $patients = $this->patientManager->getAllPatients();
        return [
            'patients'      => $patients,
            'totalPatients' => count($patients),
            'featured'      => $patients[0] ?? null,
        ];
    }

    public function getRBACViewData(): array {
        return $this->rbacManager->getAllUsersForView();
    }

    public function getUserData(): array {
        $userId     = $_SESSION['user_id'] ?? 0;
        $adminModel = new Admin();

        return [
            'first_name' => $_SESSION['first_name'] ?? 'Admin',
            'last_name'  => $_SESSION['last_name']  ?? '',
            'email'      => $_SESSION['email']       ?? '',
            'age'        => $adminModel->getUserAge($userId),
            'gender'     => $adminModel->getUserGender($userId),
            'role'       => $_SESSION['role']        ?? 'Admin',
        ];
    }

    // =========================================================================
    // Helpers
    // =========================================================================
    private function jsonError(string $message): void {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit();
    }

    // =========================================================================
    // Action: resolve_dispute — UC 34
    // =========================================================================
    private function handleResolveDispute(): void {
        header('Content-Type: application/json');

        $disputeId = filter_input(INPUT_POST, 'dispute_id', FILTER_VALIDATE_INT);
        $action    = trim($_POST['resolution'] ?? '');
        $adminId   = (int)($_SESSION['user_id'] ?? 0);

        if (!$disputeId || empty($action)) {
            echo json_encode(['success' => false, 'message' => 'Dispute ID and resolution action required.']);
            exit();
        }

        $disputeCtrl = new DisputeController(
            new DisputeRepository(),
            new NotificationService()
        );
        echo json_encode($disputeCtrl->resolveDispute($disputeId, $action, $adminId));
        exit();
    }

    // =========================================================================
    // Action: audit_action — UC 35
    // =========================================================================
    private function handleAuditAction(): void {
        header('Content-Type: application/json');

        $subAction = trim($_POST['sub_action'] ?? '');
        $auditMgr  = new AdminAuditManager();

        if ($subAction === 'purge') {
            $logId = filter_input(INPUT_POST, 'log_id', FILTER_VALIDATE_INT);
            echo json_encode($auditMgr->requestDataPurge($logId ?? 0));
        } elseif ($subAction === 'verify_integrity') {
            echo json_encode($auditMgr->verifyIntegrityPolicy());
        } else {
            echo json_encode(['success' => false, 'message' => 'Unknown audit sub-action.']);
        }
        exit();
    }
}
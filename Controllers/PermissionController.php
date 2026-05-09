<?php
// Controllers/PermissionController.php
require_once __DIR__ . '/../Models/Services/AuthorizationService.php';

class PermissionController {
    private AuthorizationService $authService;

    public function __construct() {
        $this->authService = new AuthorizationService();
    }

    // الدالة اللي الـ UI بيكلمها
    public function updatePermissions(int $therapistId, int $patientId, array $permissions): bool {
        if ($patientId <= 0 || empty($permissions)) {
            return false;
        }

        // توجيه الطلب للـ Service
        return $this->authService->validateAndAuthorize($therapistId, $patientId, $permissions);
    }
}
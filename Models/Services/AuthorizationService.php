<?php
// Models/Services/AuthorizationService.php
require_once __DIR__ . '/../Repositories/AccessRulesRepository.php';
require_once __DIR__ . '/../Repositories/TherapistRepository.php';

class AuthorizationService {
    private AccessRulesRepository $accessRepo;
    private TherapistRepository $therapistRepo;
    public function __construct() {
        $this->accessRepo = new AccessRulesRepository();
        $this->therapistRepo = new TherapistRepository();
    }
    public function validateAndAuthorize(int $therapistId, int $patientId, array $permissions): bool {

        // 1. Validate Action: هل المريض ده تبع الثيرابيست ده فعلاً؟
        $myPatients = $this->therapistRepo->getMyPatients($therapistId);
        $isAuthorized = false;
        foreach ($myPatients as $patient) {
            if ($patient['user_id'] == $patientId) {
                $isAuthorized = true;
                break;
            }
        }
        // لو مش تبعه، ارفض العملية (Permission Denied)
        if (!$isAuthorized) {
            return false;
        }
        $success = true;
        foreach ($permissions as $resourceId => $status) {
            $accessValue = ($status === 'on' || $status == 1) ? 1 : 0;
            $result = $this->accessRepo->updatePatientResourceAccess($patientId, $resourceId, $accessValue);
            if (!$result) $success = false;
        }
        return $success;
    }
}
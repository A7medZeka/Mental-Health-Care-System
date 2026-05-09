<?php
require_once __DIR__ . '/../Core/SingletonDatabase.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/../Interfaces/AdminPatientManagerInterface.php';
require_once __DIR__ . '/../Interfaces/AdminTherapistManagerInterface.php';
require_once __DIR__ . '/../Interfaces/AdminTherapistLicenseManagerInterface.php';
require_once __DIR__ . '/../Interfaces/AdminTherapistPerformanceInterface.php';
require_once __DIR__ . '/../Interfaces/AdminAuditManagerInterface.php';
require_once __DIR__ . '/../Interfaces/AdminRBACManagerInterface.php';

class Admin extends User {
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get admin profile information using User variables
     * @return array
     */
    public function getAdminProfile(): array {
        return [
            'user_id' => $this->user_id,
            'username' => $this->username,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'role' => $this->role,
            'phone_number' => $this->phone_number,
            'city' => $this->city,
            'created_at' => $this->created_at
        ];
    }
    
    /**
     * Update admin profile using User variables
     * @param array $data
     * @return bool
     */
    public function updateAdminProfile(array $data): bool {
        $allowedFields = ['first_name', 'last_name', 'phone_number', 'city'];
        $updateFields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $updateFields[] = "$key = ?";
                $params[] = $value;
                
                // Update the object property
                $this->$key = $value;
            }
        }
        
        if (empty($updateFields)) {
            return false;
        }
        
        $params[] = $this->user_id;
        
        $stmt = $this->conn->prepare(
            "UPDATE users SET " . implode(', ', $updateFields) . " WHERE user_id = ?"
        );
        return $stmt->execute($params);
    }
    
    /**
     * Get admin full name using User variables
     * @return string
     */
    public function getAdminFullName(): string {
        return trim($this->first_name . ' ' . $this->last_name);
    }
    
    /**
     * Validate admin access using User role variable
     * @return bool
     */
    public function validateAdminAccess(): bool {
        return $this->role === 'Admin';
    }
    
    /**
     * Get admin contact info using User variables
     * @return array
     */
    public function getAdminContactInfo(): array {
        return [
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'city' => $this->city
        ];
    }
}

// =============================================================================
// Patient Management – all patient business logic & DB operations
// UC 5 — Manage Patient Triage Status (EDIT FLOW ONLY)
// =============================================================================
class AdminPatientManager extends Admin implements AdminPatientManagerInterface {

    private const VALID_STATUSES = ['Registered', 'Screened', 'Matched', 'Active', 'Waitlisted'];
    private const VALID_FLOW = [
        'Registered' => 'Screened',
        'Screened'   => 'Matched',
        'Matched'    => 'Active',
        'Active'     => null,
        // SD-EXTENSION: Waitlisted can be reached from Screened or Matched
        'Waitlisted' => 'Matched',
    ];

    // SD-EXTENSION: Additional allowed transitions for Waitlisted state
    private const VALID_FLOW_EXTENDED = [
        'Screened' => ['Matched', 'Waitlisted'],
        'Matched'  => ['Active', 'Waitlisted'],
    ];

    private const MAX_FILE_SIZE  = 5 * 1024 * 1024;
    private const ALLOWED_MIME   = 'application/pdf';
    private const UPLOAD_DIR     = __DIR__ . '/../Views/Admin/uploads/intake/';
    private const UPLOAD_DB_PATH = 'uploads/intake/';

    public function getTotalPatients(): int {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total FROM users WHERE role = 'Patient'"
        );
        $stmt->execute();
        return (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    }

    public function getAllPatients(): array {
        $stmt = $this->conn->prepare(
            "SELECT user_id, first_name, last_name, username, email, status, created_at
             FROM users
             WHERE role = 'Patient'
             ORDER BY created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getPatientById(int $patient_id): ?array {
        $stmt = $this->conn->prepare(
            "SELECT * FROM users WHERE user_id = ? AND role = 'Patient'"
        );
        $stmt->execute([$patient_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getActivePatients(): array {
        $stmt = $this->conn->prepare(
            "SELECT * FROM users
             WHERE role = 'Patient' AND status = 'Active'
             ORDER BY created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * UC 5 SD flow:
     *   Admin → updatePatientStatus(patientId, currentStatus, newStatus)
     *     → SD Step 1: RBACController.checkAccess() [EDIT: added RBAC gate]
     *     → SD Step 2: validate newStatus against VALID_STATUSES
     *     → SD Step 3: validate transition against VALID_FLOW
     *     → SD Step 4: [alt] extended flow check for Waitlisted
     *     → SD Step 5: fetch patient, verify current status matches
     *     → SD Step 6: persist status change
     *     → SD Step 7: log audit entry
     *     → return result
     */
    public function updatePatientStatus(int $patientId, string $currentStatus, string $newStatus): array {
        // SD Step 1: RBAC gate — verify caller has Admin role
        if (!$this->validateAdminAccess()) {
            return ['success' => false, 'message' => 'Access denied. Admin role required.'];
        }

        // SD Step 2: validate target status
        if (!in_array($newStatus, self::VALID_STATUSES, true)) {
            return ['success' => false, 'message' => 'Invalid status value.'];
        }

        // SD Step 3 + 4: validate transition (standard + extended Waitlisted)
        $allowedNext = self::VALID_FLOW[$currentStatus] ?? null;
        $extendedAllowed = self::VALID_FLOW_EXTENDED[$currentStatus] ?? [];

        if ($newStatus !== $allowedNext && !in_array($newStatus, $extendedAllowed, true)) {
            $validOptions = array_filter(array_merge([$allowedNext], $extendedAllowed));
            return [
                'success' => false,
                'message' => sprintf(
                    "Invalid transition. From '%s' you can only move to: %s.",
                    $currentStatus,
                    !empty($validOptions) ? implode(', ', $validOptions) : 'nowhere'
                ),
            ];
        }

        // SD Step 5: fetch patient and verify current status
        $stmt = $this->conn->prepare(
            "SELECT user_id, status FROM users WHERE user_id = ? AND role = 'Patient'"
        );
        $stmt->execute([$patientId]);
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$patient) {
            return ['success' => false, 'message' => 'Patient not found.'];
        }

        if ($patient['status'] !== $currentStatus) {
            return ['success' => false, 'message' => 'Status mismatch. Please refresh the page.'];
        }

        // SD Step 6: persist
        $update = $this->conn->prepare(
            "UPDATE users SET status = ? WHERE user_id = ? AND role = 'Patient'"
        );
        $update->execute([$newStatus, $patientId]);

        // SD Step 7: audit log
        $this->logAuditEntry('patient_status_update', [
            'patient_id' => $patientId,
            'old_status' => $currentStatus,
            'new_status' => $newStatus,
            'admin_id' => $this->user_id,
            'admin_name' => $this->getAdminFullName()
        ]);

        return [
            'success' => true,
            'message' => "Status updated to '{$newStatus}' successfully.",
        ];
    }

    public function uploadIntakeForm(int $patientId, array $file, int $uploadedBy): array {
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if ($mimeType !== self::ALLOWED_MIME) {
            return ['success' => false, 'message' => 'Only PDF files are allowed.'];
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            return ['success' => false, 'message' => 'File exceeds the 5 MB size limit.'];
        }

        if (!is_dir(self::UPLOAD_DIR) && !mkdir(self::UPLOAD_DIR, 0750, true)) {
            return ['success' => false, 'message' => 'Server error: could not create upload directory.'];
        }

        $safeFilename = sprintf('intake_%d_%d.pdf', $patientId, time());
        $destination  = self::UPLOAD_DIR . $safeFilename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'message' => 'Failed to save file. Please try again.'];
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO intake_forms (patient_id, file_path, uploaded_by, uploaded_at)
             VALUES (?, ?, ?, NOW())"
        );
        $stmt->execute([$patientId, self::UPLOAD_DB_PATH . $safeFilename, $uploadedBy]);

        return ['success' => true, 'message' => 'Intake form uploaded successfully.'];
    }
    
    /**
     * Log audit entry using admin's User variables
     * @param string $action
     * @param array $details
     * @return bool
     */
    private function logAuditEntry(string $action, array $details): bool {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO audit_logs (action, details, admin_id, admin_name, created_at) 
                 VALUES (?, ?, ?, ?, NOW())"
            );
            return $stmt->execute([
                $action,
                json_encode($details),
                $this->user_id,
                $this->getAdminFullName()
            ]);
        } catch (Exception $e) {
            error_log('Failed to log audit entry: ' . $e->getMessage());
            return false;
        }
    }
}

// =============================================================================
// RBAC Management – role promotion and user deletion
// =============================================================================
class AdminRBACManager extends Admin implements AdminRBACManagerInterface {

    /**
     * Allowed role-promotion paths.
     * null  = this role is locked and cannot be promoted.
     */
    private const ALLOWED_TRANSITIONS = [
        'Therapist' => 'Moderator',
        'Moderator' => 'Admin',
        'Patient'   => null,
        'Admin'     => null,
    ];

    // =========================================================================
    // Query: all users needed by the RBAC view
    // =========================================================================

    /**
     * Returns every user ordered by role then username,
     * ready for the RBAC table.
     */
    public function getAllUsersForView(): array {
        $stmt = $this->conn->query(
            'SELECT user_id, username, email, `role` FROM users ORDER BY role, username'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // =========================================================================
    // Business logic: promote a user to the next allowed role
    // RBAC Panel SD flow:
    //   Admin → promoteUser(targetId, newRole)
    //     → SD Step 1: RBAC access check
    //     → SD Step 2: fetch current role
    //     → SD Step 3: validate transition rules
    //     → SD Step 4: [alt] conflict check — cannot promote self
    //     → SD Step 5: persist role change
    //     → SD Step 6: log RBAC action
    // =========================================================================

    /**
     * Validates the transition rules and performs the UPDATE.
     *
     * @param int    $targetId  user_id of the user being promoted
     * @param string $newRole   requested new role (comes from POST, already trimmed)
     * @return array ['success' => bool, 'message' => string, ...]
     */
    public function promoteUser(int $targetId, string $newRole): array {
        // SD Step 1: RBAC access check
        if (!$this->validateAdminAccess()) {
            return ['success' => false, 'message' => 'Access denied. Admin role required.'];
        }
        // ── Fetch current state ───────────────────────────────────────────────
        $stmt = $this->conn->prepare('SELECT `role`, username FROM users WHERE user_id = ?');
        $stmt->execute([$targetId]);
        $target = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$target) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        $currentRole = $target['role'];
        $allowedNext = self::ALLOWED_TRANSITIONS[$currentRole] ?? null;

        // ── Transition rules ──────────────────────────────────────────────────
        if ($allowedNext === null) {
            return [
                'success' => false,
                'message' => "\"{$currentRole}\" role cannot be changed.",
            ];
        }

        if ($newRole !== $allowedNext) {
            return [
                'success' => false,
                'message' => "A {$currentRole} can only be promoted to {$allowedNext}.",
            ];
        }

        // ── Persist ───────────────────────────────────────────────────────────
        $update = $this->conn->prepare("UPDATE users SET `role` = ? WHERE user_id = ?");
        $update->execute([$newRole, $targetId]);

        // Log the promotion using admin's User variables
        $this->logRBACAction('user_promotion', [
            'target_user_id' => $targetId,
            'target_username' => $target['username'],
            'old_role' => $currentRole,
            'new_role' => $newRole,
            'admin_id' => $this->user_id,
            'admin_name' => $this->getAdminFullName()
        ]);

        return [
            'success'  => true,
            'message'  => "\"{$target['username']}\" promoted from {$currentRole} to {$newRole}.",
            'new_role' => $newRole,
        ];
    }

    // =========================================================================
    // Business logic: delete a non-Admin user
    // =========================================================================

    /**
     * Deletes a user and, if they are a Therapist, their therapists-table row.
     * Admin accounts are always protected.
     *
     * @param int $targetId  user_id of the user to delete
     * @return array ['success' => bool, 'message' => string, ...]
     */
    public function deleteUser(int $targetId): array {
        // ── Fetch target ──────────────────────────────────────────────────────
        $stmt = $this->conn->prepare('SELECT `role`, username FROM users WHERE user_id = ?');
        $stmt->execute([$targetId]);
        $target = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$target) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        // ── Guard: Admins are undeletable ─────────────────────────────────────
        if ($target['role'] === 'Admin') {
            return ['success' => false, 'message' => 'Admin accounts cannot be deleted.'];
        }

        // ── Delete (cascade-safe) ─────────────────────────────────────────────
        try {
            $delete = $this->conn->prepare(
                "DELETE FROM users WHERE user_id = ? AND `role` != 'Admin'"
            );
            $delete->execute([$targetId]);

            if ($delete->rowCount() === 0) {
                return ['success' => false, 'message' => 'User not found or cannot be deleted.'];
            }

            // If the deleted user was a Therapist, clean the therapists table too
            if ($target['role'] === 'Therapist') {
                $this->conn->prepare("DELETE FROM therapists WHERE therapist_id = ?")
                           ->execute([$targetId]);
            }

            // Log the deletion using admin's User variables
            $this->logRBACAction('user_deletion', [
                'target_user_id' => $targetId,
                'target_username' => $target['username'],
                'target_role' => $target['role'],
                'admin_id' => $this->user_id,
                'admin_name' => $this->getAdminFullName()
            ]);

            return [
                'success' => true,
                'message' => "User \"{$target['username']}\" has been deleted.",
                'user_id' => $targetId,
                'role'    => $target['role'],
            ];

        } catch (Exception $e) {
            error_log('[AdminRBACManager::deleteUser] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // =========================================================================
    // Helper: expose transition map to the view layer (read-only)
    // =========================================================================
    public function getAllowedTransitions(): array {
        return self::ALLOWED_TRANSITIONS;
    }
    
    /**
     * Log RBAC action using admin's User variables
     * @param string $action
     * @param array $details
     * @return bool
     */
    private function logRBACAction(string $action, array $details): bool {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO audit_logs (action, details, admin_id, admin_name, created_at) 
                 VALUES (?, ?, ?, ?, NOW())"
            );
            return $stmt->execute([
                $action,
                json_encode($details),
                $this->user_id,
                $this->getAdminFullName()
            ]);
        } catch (Exception $e) {
            error_log('Failed to log RBAC action: ' . $e->getMessage());
            return false;
        }
    }
}

// =============================================================================
// Audit Management — UC 35: Execute Data Purge / Audit Trail
// =============================================================================
class AdminAuditManager extends Admin implements AdminAuditManagerInterface {

    public function getAuditLogsCount(): int {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM audit_logs");
        $stmt->execute();
        return (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    }

    public function getAuditLogs(?int $limit = null, ?int $offset = null): array {
        $query  = "SELECT * FROM audit_logs ORDER BY created_at DESC";
        $params = [];

        if ($limit !== null) {
            $query   .= " LIMIT ?";
            $params[] = $limit;

            if ($offset !== null) {
                $query   .= " OFFSET ?";
                $params[] = $offset;
            }
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * UC 35 SD flow:
     *   Admin → AdminAuditManager.verifyIntegrityPolicy()
     *     → [alt] DELETE/UPDATE request → DENIED (audit logs are immutable)
     *     → return rejection message
     *
     * This method explicitly rejects any attempt to delete or modify audit logs.
     * AuditLog records are INSERT-ONLY by design.
     */
    public function verifyIntegrityPolicy(): array {
        return [
            'success'  => false,
            'message'  => 'Audit log records are immutable. DELETE and UPDATE operations are denied by integrity policy.',
            'policy'   => 'INSERT_ONLY',
            'enforced' => true,
        ];
    }

    /**
     * UC 35: Attempt to purge → always denied.
     */
    public function requestDataPurge(int $logId): array {
        // SD Step: verify integrity policy first
        $policy = $this->verifyIntegrityPolicy();
        if ($policy['enforced']) {
            return [
                'success' => false,
                'message' => 'Data purge request DENIED. ' . $policy['message'],
            ];
        }
        // This code is unreachable by design — audit logs cannot be purged
        return ['success' => false, 'message' => 'Unexpected state.'];
    }
}

// =============================================================================
// Therapist Management
// =============================================================================
class AdminTherapistManager extends Admin implements AdminTherapistManagerInterface {

    public function getAllTherapists(): array {
        $stmt = $this->conn->prepare("
            SELECT u.*, t.specialization, t.license_expiry_date, t.is_verified,
                   t.credential_file_path, t.experience_years, t.availability_schedule
            FROM   users u
            LEFT JOIN therapists t ON u.user_id = t.therapist_id
            WHERE  u.role = 'Therapist'
            ORDER  BY u.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getTotalTherapists(): int {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total FROM users WHERE role = 'Therapist'"
        );
        $stmt->execute();
        return (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    }

    public function getTherapistById(int $therapist_id): ?array {
        $stmt = $this->conn->prepare("
            SELECT u.*, t.specialization, t.license_expiry_date, t.is_verified,
                   t.credential_file_path, t.experience_years, t.availability_schedule
            FROM   users u
            LEFT JOIN therapists t ON u.user_id = t.therapist_id
            WHERE  u.user_id = ? AND u.role = 'Therapist'
        ");
        $stmt->execute([$therapist_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function rejectTherapist(int $therapist_id): bool {
        $stmt = $this->conn->prepare(
            "UPDATE users SET status = 'Inactive' WHERE user_id = ? AND role = 'Therapist'"
        );
        $stmt->execute([$therapist_id]);
        return $stmt->rowCount() > 0;
    }

    public function removeTherapist(int $therapist_id): bool {
        $this->conn->beginTransaction();
        try {
            $check = $this->conn->prepare(
                "SELECT user_id FROM users WHERE user_id = ? AND role = 'Therapist'"
            );
            $check->execute([$therapist_id]);
            if (!$check->fetch(PDO::FETCH_ASSOC)) {
                $this->conn->rollBack();
                return false;
            }

            $this->conn->prepare("DELETE FROM therapists WHERE therapist_id = ?")->execute([$therapist_id]);
            $this->conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'Therapist'")->execute([$therapist_id]);
            $this->conn->commit();
            return true;

        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log('[AdminTherapistManager::removeTherapist] ' . $e->getMessage());
            return false;
        }
    }

    public function getActiveTherapists(): array {
        $stmt = $this->conn->prepare("
            SELECT t.therapist_id, u.first_name, u.last_name, u.email,
                   t.specialization, t.license_expiry_date, t.is_verified,
                   t.credential_file_path, t.experience_years, t.availability_schedule
            FROM   therapists t
            JOIN   users u ON u.user_id = t.therapist_id
            WHERE  u.role = 'Therapist' AND u.status = 'Active'
            ORDER  BY t.license_expiry_date ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

// =============================================================================
// Therapist Licence Management
// =============================================================================
/**
 * UC 6 — Audit Therapist Credentials (EDIT FLOW ONLY)
 *
 * SD flow:
 *   Admin → renewTherapistLicense(therapist_id, new_expiry, credential_path)
 *     → SD Step 1: validate expiry date is in the future
 *     → SD Step 2: [alt] expired → return error
 *     → SD Step 3: persist renewal
 *     → SD Step 4: log audit entry for credential action
 */
class AdminTherapistLicenseManager extends AdminTherapistManager implements AdminTherapistLicenseManagerInterface {

    public function renewTherapistLicense(int $therapist_id, string $new_expiry, ?string $credential_path = null): bool {
        // SD Step 1: validate expiry date is in the future
        $expiryTime = strtotime($new_expiry);
        if ($expiryTime === false || $expiryTime <= time()) {
            // SD Step 2 [alt]: expired or invalid date — reject
            error_log("[UC6] License renewal rejected: expiry date '{$new_expiry}' is not in the future.");
            return false;
        }

        // SD Step 3: persist renewal
        if ($credential_path) {
            $stmt = $this->conn->prepare(
                "UPDATE therapists
                 SET license_expiry_date = ?, credential_file_path = ?, is_verified = 1
                 WHERE therapist_id = ?"
            );
            $stmt->execute([$new_expiry, $credential_path, $therapist_id]);
        } else {
            $stmt = $this->conn->prepare(
                "UPDATE therapists
                 SET license_expiry_date = ?, is_verified = 1
                 WHERE therapist_id = ?"
            );
            $stmt->execute([$new_expiry, $therapist_id]);
        }

        // SD Step 4: log audit entry for every credential action
        $this->logCredentialAudit($therapist_id, $new_expiry, $credential_path);

        return $stmt->rowCount() > 0;
    }

    public function isTherapistVerified(int $therapist_id): bool {
        $stmt = $this->conn->prepare(
            "SELECT is_verified FROM therapists WHERE therapist_id = ?"
        );
        $stmt->execute([$therapist_id]);
        return (bool)(($stmt->fetch(PDO::FETCH_ASSOC)['is_verified'] ?? 0));
    }

    public function getExpiringLicenses(int $days = 30): array {
        $stmt = $this->conn->prepare("
            SELECT t.therapist_id, u.username, u.email, t.license_expiry_date
            FROM   therapists t
            JOIN   users u ON u.user_id = t.therapist_id
            WHERE  u.status = 'Active'
              AND  DATEDIFF(t.license_expiry_date, CURDATE()) <= ?
              AND  DATEDIFF(t.license_expiry_date, CURDATE()) > 0
            ORDER  BY t.license_expiry_date ASC
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * SD Step 4 helper: log every credential action to audit_logs.
     */
    private function logCredentialAudit(int $therapistId, string $newExpiry, ?string $credPath): void {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO audit_logs (action, severity, description, user_id, created_at)
                 VALUES (?, ?, ?, ?, NOW())"
            );
            $stmt->execute([
                'CREDENTIAL_RENEWAL',
                'Info',
                json_encode([
                    'therapist_id'   => $therapistId,
                    'new_expiry'     => $newExpiry,
                    'credential_path'=> $credPath,
                    'admin_id'       => $this->user_id,
                ]),
                $this->user_id
            ]);
        } catch (\Exception $e) {
            error_log('[UC6 Audit] ' . $e->getMessage());
        }
    }
}

// =============================================================================
// Therapist Performance Analytics
// =============================================================================
class AdminTherapistPerformance extends AdminTherapistManager implements AdminTherapistPerformanceInterface {

    public function getVerifiedTherapistList(): array {
        $stmt = $this->conn->prepare("
            SELECT t.therapist_id, u.username AS therapist_name
            FROM   therapists t
            JOIN   users u ON u.user_id = t.therapist_id
            WHERE  t.is_verified = 1
            ORDER  BY u.username
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getTherapistRankingStat(int $therapist_id): array {
        $stmt = $this->conn->prepare("
            SELECT ROUND(AVG(rating), 1) AS avg_rating, COUNT(*) AS cnt
            FROM   therapist_reviews
            WHERE  therapist_id = ?
        ");
        $stmt->execute([$therapist_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['avg_rating' => 0, 'cnt' => 0];
    }

    public function getTherapistPerformanceDetail(int $therapist_id): ?array {
        $stmtName = $this->conn->prepare("
            SELECT u.username AS therapist_name
            FROM   therapists t
            JOIN   users u ON u.user_id = t.therapist_id
            WHERE  t.therapist_id = ? AND t.is_verified = 1
        ");
        $stmtName->execute([$therapist_id]);
        $nameData = $stmtName->fetch(PDO::FETCH_ASSOC);
        if (!$nameData) {
            return null;
        }

        $stmtRev = $this->conn->prepare("
            SELECT COUNT(review_id) AS total_reviews, ROUND(AVG(rating), 1) AS avg_rating
            FROM   therapist_reviews
            WHERE  therapist_id = ?
        ");
        $stmtRev->execute([$therapist_id]);
        $revData = $stmtRev->fetch(PDO::FETCH_ASSOC);

        return [
            'therapist_id'   => $therapist_id,
            'therapist_name' => $nameData['therapist_name'],
            'total_reviews'  => (int)($revData['total_reviews'] ?? 0),
            'avg_rating'     => (float)($revData['avg_rating']  ?? 0),
            'total_sessions' => 0,
            'no_show_rate'   => 0,
        ];
    }

    public function getTherapistRatingBreakdown(int $therapist_id): array {
        $stmt = $this->conn->prepare("
            SELECT rating, COUNT(*) AS count
            FROM   therapist_reviews
            WHERE  therapist_id = ?
            GROUP  BY rating
            ORDER  BY rating DESC
        ");
        $stmt->execute([$therapist_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getTherapistRecentFeedback(int $therapist_id, int $limit = 5): array {
        $stmt = $this->conn->prepare("
            SELECT rating, comment, created_at
            FROM   therapist_reviews
            WHERE  therapist_id = ? AND comment IS NOT NULL AND comment <> ''
            ORDER  BY created_at DESC
            LIMIT  ?
        ");
        $stmt->execute([$therapist_id, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getTopRatedTherapists(int $limit = 10): array {
        $stmt = $this->conn->prepare("
            SELECT t.therapist_id, u.username, u.first_name, u.last_name,
                   ROUND(AVG(tr.rating), 1) AS avg_rating,
                   COUNT(tr.review_id)      AS total_reviews
            FROM   therapists t
            JOIN   users u  ON u.user_id = t.therapist_id
            LEFT JOIN therapist_reviews tr ON t.therapist_id = tr.therapist_id
            WHERE  t.is_verified = 1 AND u.status = 'Active'
            GROUP  BY t.therapist_id
            ORDER  BY avg_rating DESC
            LIMIT  ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

// =============================================================================
// THERAPIST ACTIONS HANDLER – all therapist management actions from admin
// =============================================================================
class AdminTherapistActionsHandler extends AdminTherapistManager {
    
    public function handleTherapistAction($action, $id, $postData = [], $files = []) {
        switch ($action) {
            case 'get_therapist':
                return $this->getTherapistDetails($id);
                
            case 'update_status':
                return $this->updateTherapistStatus($id, $postData['status'] ?? '');
                
            case 'delete':
                return $this->deleteTherapist($id);
                
            case 'renew':
                return $this->renewTherapistLicense($id, $postData, $files);
                
            case 'remove_therapist':
                return $this->removeTherapist($id);
                
                            
            default:
                return ['success' => false, 'message' => 'Unknown action'];
        }
    }
    
    private function getTherapistDetails($id) {
        $stmt = $this->conn->prepare(
            "SELECT u.*, t.specialization, t.experience_years, t.availability_schedule, 
                   t.credential_file_path, t.is_verified 
            FROM users u 
            LEFT JOIN therapists t ON u.user_id = t.therapist_id 
            WHERE u.user_id = ? AND u.role = 'Therapist'"
        );
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        if (!$data) {
            return ['success' => false, 'message' => 'Therapist not found'];
        }
        
        // Create immutable therapist object if available
        if (class_exists('ImmutableTherapistData')) {
            $therapist = ImmutableTherapistData::fromDatabase($data, $data);
            return ['success' => true, 'data' => $therapist->toArray()];
        }
        
        return ['success' => true, 'data' => $data];
    }
    
    private function updateTherapistStatus($id, $newStatus) {
        $validStatuses = ['Active', 'Inactive', 'Suspended'];
        
        if (!in_array($newStatus, $validStatuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }
        
        $stmt = $this->conn->prepare(
            "UPDATE users SET status = ? WHERE user_id = ? AND role = 'Therapist'"
        );
        $stmt->execute([$newStatus, $id]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => true, 'message' => 'Status updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update status'];
        }
    }
    
    private function deleteTherapist($id) {
        $this->conn->beginTransaction();
        try {
            // Delete from therapists table first
            $stmt = $this->conn->prepare("DELETE FROM therapists WHERE therapist_id = ?");
            $stmt->execute([$id]);
            
            // Delete from users table
            $stmt = $this->conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'Therapist'");
            $stmt->execute([$id]);
            
            $this->conn->commit();
            return ['success' => true, 'message' => 'Therapist deleted successfully'];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Failed to delete therapist'];
        }
    }
    
    private function renewTherapistLicense($id, $postData, $files) {
        $new_expiry = trim($postData['new_expiry'] ?? '');
        $parsed = $new_expiry ? strtotime($new_expiry) : false;

        if (!$parsed || $parsed <= time()) {
            return ['success' => false, 'message' => 'A valid future expiry date is required.'];
        }

        // Optional credential file
        $credential_path = null;
        if (!empty($files['credential']['tmp_name'])) {
            $upload_dir = __DIR__ . '/../uploads/credentials/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
            $ext = strtolower(pathinfo($files['credential']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed, true)) {
                return ['success' => false, 'message' => 'Invalid file type. Allowed: PDF, JPG, PNG.'];
            }

            $filename = 'therapist_' . $id . '_' . time() . '.' . $ext;
            if (move_uploaded_file($files['credential']['tmp_name'], $upload_dir . $filename)) {
                $credential_path = 'uploads/credentials/' . $filename;
            }
        }

        // Use AdminTherapistLicenseManager for license renewal
        $licenseManager = new AdminTherapistLicenseManager();
        $ok = $licenseManager->renewTherapistLicense($id, date('Y-m-d', $parsed), $credential_path);
        
        return [
            'success' => $ok,
            'message' => $ok ? 'License renewed successfully.' : 'Renewal failed.',
        ];
    }
    
    }

// =============================================================================
// USER ACTIONS HANDLER – all user management actions from admin
// =============================================================================
class AdminUserActionsHandler extends Admin {
    
    public function handleUserAction($action, $id, $postData = []) {
        switch ($action) {
            case 'delete_user':
                return $this->deleteUser($id, $postData['role'] ?? '');
                
            case 'update_user_status':
                return $this->updateUserStatus($id, $postData['status'] ?? '', $postData['role'] ?? '');
                
            default:
                return ['success' => false, 'message' => 'Invalid action'];
        }
    }
    
    private function deleteUser($id, $role) {
        if (!in_array($role, ['Patient', 'Therapist', 'Moderator'])) {
            return ['success' => false, 'message' => 'Invalid user role'];
        }
        
        $this->conn->beginTransaction();
        try {
            // Check if user exists
            $stmt = $this->conn->prepare(
                "SELECT user_id, role FROM users WHERE user_id = ? AND role = ?"
            );
            $stmt->execute([$id, $role]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Handle role-specific deletions
            if ($role === 'Therapist') {
                // Delete from therapists table first
                $stmt = $this->conn->prepare("DELETE FROM therapists WHERE therapist_id = ?");
                $stmt->execute([$id]);
            } elseif ($role === 'Patient') {
                // Delete from patients table if it exists
                $stmt = $this->conn->prepare("DELETE FROM patients WHERE patient_id = ?");
                $stmt->execute([$id]);
            }
            
            // Delete from users table (role is stored in users table)
            $stmt = $this->conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$id]);
            
            $this->conn->commit();
            return ['success' => true, 'message' => 'User deleted successfully'];
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log('[User Actions] Delete failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete user'];
        }
    }
    
    private function updateUserStatus($id, $newStatus, $role) {
        $validStatuses = ['Active', 'Inactive', 'Suspended'];
        $validRoles = ['Patient', 'Therapist', 'Moderator'];
        
        if (!in_array($newStatus, $validStatuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }
        
        if (!in_array($role, $validRoles)) {
            return ['success' => false, 'message' => 'Invalid user role'];
        }
        
        $stmt = $this->conn->prepare(
            "UPDATE users SET status = ? WHERE user_id = ? AND role = ?"
        );
        $stmt->execute([$newStatus, $id, $role]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => true, 'message' => 'Status updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update status'];
        }
    }
}

// =============================================================================
// ADMIN AJAX ENDPOINT - Direct handling of AJAX requests
// =============================================================================
if (isset($_POST['ajax_admin_action']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Disable error display for JSON responses
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    
    session_start();
    header('Content-Type: application/json; charset=utf-8');
    
    // Custom error handler
    function handleError($errno, $errstr, $errfile, $errline) {
        error_log("PHP Error: [$errno] $errstr in $errfile on line $errline");
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Internal server error']);
        exit();
    }
    set_error_handler('handleError');
    
    // Authentication check
    if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
    
    $action = trim($_POST['action'] ?? '');
    $id     = (int)($_POST['id']     ?? 0);
    
    if (!$action || $id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit();
    }
    
    // Route to appropriate handler
    try {
        if (strpos($action, 'therapist') !== false || in_array($action, ['get_therapist', 'update_status', 'delete', 'renew', 'remove_therapist'])) {
            $handler = new AdminTherapistActionsHandler();
            $result = $handler->handleTherapistAction($action, $id, $_POST, $_FILES);
        } elseif (in_array($action, ['delete_user', 'update_user_status'])) {
            $handler = new AdminUserActionsHandler();
            $result = $handler->handleUserAction($action, $id, $_POST);
        } else {
            $result = ['success' => false, 'message' => 'Unknown action'];
        }
        
        echo json_encode($result);
    } catch (Exception $e) {
        error_log('Admin AJAX Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Request failed']);
    }
    
    exit();
}
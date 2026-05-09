<?php
require_once __DIR__ . '/Admin.php';

/**
 * SuperAdmin — extends Admin per CD3 hierarchy.
 *
 * User → Admin → SuperAdmin
 * Adds only super_admin_id as defined in CD3.
 */
class SuperAdmin extends Admin {

    private int $superAdminId;

    public function __construct() {
        parent::__construct();
        $this->superAdminId = 0;
    }

    public function getSuperAdminId(): int { return $this->superAdminId; }

    public function setSuperAdminId(int $id): void { $this->superAdminId = $id; }

    /**
     * SuperAdmin can access all RBAC functions.
     */
    public function validateSuperAdminAccess(): bool {
        return $this->role === 'Admin'; // SuperAdmin is stored as Admin role in DB
    }
}

<?php
/**
 * ResourceAccessControl — Patient entity (UC 24).
 * Gates access to premium content.
 */
class ResourceAccessControl {

    private int  $accessId;
    private int  $patientId;
    private int  $resourceId;
    private bool $isAllowed;

    public function __construct(array $data = []) {
        $this->accessId   = (int)($data['access_id'] ?? 0);
        $this->patientId  = (int)($data['patient_id'] ?? 0);
        $this->resourceId = (int)($data['resource_id'] ?? 0);
        $this->isAllowed  = (bool)($data['is_allowed'] ?? true);
    }

    public function getAccessId(): int   { return $this->accessId; }
    public function getPatientId(): int  { return $this->patientId; }
    public function getResourceId(): int { return $this->resourceId; }
    public function isAllowed(): bool    { return $this->isAllowed; }
}

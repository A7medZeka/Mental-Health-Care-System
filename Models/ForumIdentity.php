<?php
/**
 * ForumIdentity — Patient entity (UC 28).
 * Maps patient_id ↔ pseudonym for anonymous forum.
 */
class ForumIdentity {

    private int    $mappingId;
    private int    $patientId;
    private string $pseudonym;
    private string $createdAt;

    public function __construct(array $data = []) {
        $this->mappingId = (int)($data['mapping_id'] ?? 0);
        $this->patientId = (int)($data['patient_id'] ?? 0);
        $this->pseudonym = $data['pseudonym'] ?? '';
        $this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
    }

    public function getMappingId(): int   { return $this->mappingId; }
    public function getPatientId(): int   { return $this->patientId; }
    public function getPseudonym(): string { return $this->pseudonym; }
    public function getCreatedAt(): string { return $this->createdAt; }
}

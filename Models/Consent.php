<?php
/**
 * Consent — Patient entity (UC 7).
 */
class Consent {

    private int    $consentId;
    private int    $patientId;
    private string $documentName;
    private string $documentVersion;
    private ?string $signedDate;
    private string $timestamp;

    public function __construct(array $data = []) {
        $this->consentId       = (int)($data['consent_id'] ?? 0);
        $this->patientId       = (int)($data['patient_id'] ?? 0);
        $this->documentName    = $data['document_name'] ?? '';
        $this->documentVersion = $data['document_version'] ?? '1.0';
        $this->signedDate      = $data['signed_date'] ?? null;
        $this->timestamp       = $data['timestamp'] ?? date('Y-m-d H:i:s');
    }

    public function getConsentId(): int           { return $this->consentId; }
    public function getPatientId(): int           { return $this->patientId; }
    public function getDocumentName(): string     { return $this->documentName; }
    public function getDocumentVersion(): string  { return $this->documentVersion; }
    public function getSignedDate(): ?string      { return $this->signedDate; }
    public function getTimestamp(): string         { return $this->timestamp; }
    public function isSigned(): bool               { return $this->signedDate !== null; }
}

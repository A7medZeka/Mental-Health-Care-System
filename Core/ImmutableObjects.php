<?php

/*
 * Immutable Data Objects Implementation
 * Creates immutable user and patient objects with type safety
 */

require_once __DIR__ . '/ImmutablePattern.php';

class ImmutablePatientData extends ImmutableUser {
    private $nationalId;
    private $phoneNumber;
    private $dateOfBirth;
    private $city;

    public function __construct(
        int $userId,
        string $username,
        string $email,
        string $firstName,
        string $lastName,
        string $role,
        string $status,
        string $createdAt,
        ?string $updatedAt = null,
        ?string $nationalId = null,
        ?string $phoneNumber = null,
        ?string $dateOfBirth = null,
        ?string $city = null
    ) {
        parent::__construct($userId, $username, $email, $firstName, $lastName, $role, $status, $createdAt, $updatedAt);
        $this->nationalId = $nationalId;
        $this->phoneNumber = $phoneNumber;
        $this->dateOfBirth = $dateOfBirth;
        $this->city = $city;
    }

    public function getNationalId(): ?string {
        return $this->nationalId;
    }

    public function getPhoneNumber(): ?string {
        return $this->phoneNumber;
    }

    public function getDateOfBirth(): ?string {
        return $this->dateOfBirth;
    }

    public function getCity(): ?string {
        return $this->city;
    }

    public function toArray(): array {
        return array_merge(parent::toArray(), [
            'national_id' => $this->nationalId,
            'phone_number' => $this->phoneNumber,
            'date_of_birth' => $this->dateOfBirth,
            'city' => $this->city
        ]);
    }

    public static function fromDatabase(array $data): self {
        return new self(
            (int)$data['user_id'],
            $data['username'],
            $data['email'],
            $data['first_name'],
            $data['last_name'],
            $data['role'],
            $data['status'],
            $data['created_at'],
            $data['updated_at'],
            $data['national_id'] ?? null,
            $data['phone_number'] ?? null,
            $data['date_of_birth'] ?? null,
            $data['city'] ?? null
        );
    }
}

class ImmutableTherapistData extends ImmutableUser {
    private $specialization;
    private $experienceYears;
    private $availabilitySchedule;
    private $credentialFilePath;
    private $isVerified;

    public function __construct(
        int $userId,
        string $username,
        string $email,
        string $firstName,
        string $lastName,
        string $role,
        string $status,
        string $createdAt,
        ?string $updatedAt = null,
        ?string $specialization = null,
        ?int $experienceYears = null,
        ?string $availabilitySchedule = null,
        ?string $credentialFilePath = null,
        ?bool $isVerified = null
    ) {
        parent::__construct($userId, $username, $email, $firstName, $lastName, $role, $status, $createdAt, $updatedAt);
        $this->specialization = $specialization;
        $this->experienceYears = $experienceYears;
        $this->availabilitySchedule = $availabilitySchedule;
        $this->credentialFilePath = $credentialFilePath;
        $this->isVerified = $isVerified;
    }

    public function getSpecialization(): ?string {
        return $this->specialization;
    }

    public function getExperienceYears(): ?int {
        return $this->experienceYears;
    }

    public function getAvailabilitySchedule(): ?string {
        return $this->availabilitySchedule;
    }

    public function getCredentialFilePath(): ?string {
        return $this->credentialFilePath;
    }

    public function getIsVerified(): ?bool {
        return $this->isVerified;
    }

    public function toArray(): array {
        return array_merge(parent::toArray(), [
            'specialization' => $this->specialization,
            'experience_years' => $this->experienceYears,
            'availability_schedule' => $this->availabilitySchedule,
            'credential_file_path' => $this->credentialFilePath,
            'is_verified' => $this->isVerified
        ]);
    }

    public static function fromDatabase(array $userData, array $therapistData = []): self {
        return new self(
            (int)$userData['user_id'],
            $userData['username'],
            $userData['email'],
            $userData['first_name'],
            $userData['last_name'],
            $userData['role'],
            $userData['status'],
            $userData['created_at'],
            $userData['updated_at'],
            $therapistData['specialization'] ?? null,
            isset($therapistData['experience_years']) ? (int)$therapistData['experience_years'] : null,
            $therapistData['availability_schedule'] ?? null,
            $therapistData['credential_file_path'] ?? null,
            isset($therapistData['is_verified']) ? (bool)$therapistData['is_verified'] : null
        );
    }
}

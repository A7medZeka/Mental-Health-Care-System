<?php

/**
 * Immutable Pattern Implementation for User/Patient Data Objects
 * Ensures data integrity by preventing modification after creation
 */

// Base Immutable User Class
abstract class ImmutableUser {
    protected $userId;
    protected $username;
    protected $email;
    protected $firstName;
    protected $lastName;
    protected $role;
    protected $status;
    protected $createdAt;
    protected $updatedAt;

    public function __construct(
        int $userId,
        string $username,
        string $email,
        string $firstName,
        string $lastName,
        string $role,
        string $status,
        string $createdAt,
        ?string $updatedAt = null
    ) {
        $this->userId = $userId;
        $this->username = $username;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->role = $role;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt ?? $createdAt;
    }

    // Getters only - no setters for immutability
    public function getUserId(): int {
        return $this->userId;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getFirstName(): string {
        return $this->firstName;
    }

    public function getLastName(): string {
        return $this->lastName;
    }

    public function getFullName(): string {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getRole(): string {
        return $this->role;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function getCreatedAt(): string {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string {
        return $this->updatedAt;
    }

    // Create a new instance with modified data (returns new object)
    public function withStatus(string $newStatus): self {
        return new static(
            $this->userId,
            $this->username,
            $this->email,
            $this->firstName,
            $this->lastName,
            $this->role,
            $newStatus,
            $this->createdAt,
            date('Y-m-d H:i:s')
        );
    }

    public function withEmail(string $newEmail): self {
        return new static(
            $this->userId,
            $this->username,
            $newEmail,
            $this->firstName,
            $this->lastName,
            $this->role,
            $this->status,
            $this->createdAt,
            date('Y-m-d H:i:s')
        );
    }

    // Convert to array
    public function toArray(): array {
        return [
            'user_id' => $this->userId,
            'username' => $this->username,
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'role' => $this->role,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }

    // Convert to JSON
    public function toJson(): string {
        return json_encode($this->toArray());
    }

    // Prevent cloning
    private function __clone() {}

    // Magic method to prevent property modification
    public function __set($name, $value) {
        throw new Exception("Cannot modify immutable object properties");
    }

    // Magic method for read-only property access
    public function __get($name) {
        $method = 'get' . str_replace('_', '', ucwords($name, '_'));
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        throw new Exception("Property '$name' does not exist or is not accessible");
    }
}

// Immutable Patient Class
class ImmutablePatientRecord extends ImmutableUser {
    private $age;
    private $gender;
    private $phone;
    private $address;
    private $medicalHistory;
    private $assignedTherapistId;

    public function __construct(
        int $userId,
        string $username,
        string $email,
        string $firstName,
        string $lastName,
        string $status,
        string $createdAt,
        ?int $age = null,
        ?string $gender = null,
        ?string $phone = null,
        ?string $address = null,
        ?string $medicalHistory = null,
        ?int $assignedTherapistId = null,
        ?string $updatedAt = null
    ) {
        parent::__construct($userId, $username, $email, $firstName, $lastName, 'Patient', $status, $createdAt, $updatedAt);
        $this->age = $age;
        $this->gender = $gender;
        $this->phone = $phone;
        $this->address = $address;
        $this->medicalHistory = $medicalHistory;
        $this->assignedTherapistId = $assignedTherapistId;
    }

    // Additional getters for patient-specific data
    public function getAge(): ?int {
        return $this->age;
    }

    public function getGender(): ?string {
        return $this->gender;
    }

    public function getPhone(): ?string {
        return $this->phone;
    }

    public function getAddress(): ?string {
        return $this->address;
    }

    public function getMedicalHistory(): ?string {
        return $this->medicalHistory;
    }

    public function getAssignedTherapistId(): ?int {
        return $this->assignedTherapistId;
    }

    // Create new instances with modified data
    public function withAssignedTherapist(?int $therapistId): self {
        return new self(
            $this->userId,
            $this->username,
            $this->email,
            $this->firstName,
            $this->lastName,
            $this->status,
            $this->createdAt,
            $this->age,
            $this->gender,
            $this->phone,
            $this->address,
            $this->medicalHistory,
            $therapistId,
            date('Y-m-d H:i:s')
        );
    }

    public function withMedicalHistory(string $medicalHistory): self {
        return new self(
            $this->userId,
            $this->username,
            $this->email,
            $this->firstName,
            $this->lastName,
            $this->status,
            $this->createdAt,
            $this->age,
            $this->gender,
            $this->phone,
            $this->address,
            $medicalHistory,
            $this->assignedTherapistId,
            date('Y-m-d H:i:s')
        );
    }

    public function toArray(): array {
        return array_merge(parent::toArray(), [
            'age' => $this->age,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'address' => $this->address,
            'medical_history' => $this->medicalHistory,
            'assigned_therapist_id' => $this->assignedTherapistId
        ]);
    }
}

// Immutable Therapist Class
class ImmutableTherapist extends ImmutableUser {
    private $specialization;
    private $license;
    private $experience;
    private $verified;

    public function __construct(
        int $userId,
        string $username,
        string $email,
        string $firstName,
        string $lastName,
        string $status,
        string $createdAt,
        ?string $specialization = null,
        ?string $license = null,
        ?int $experience = null,
        bool $verified = false,
        ?string $updatedAt = null
    ) {
        parent::__construct($userId, $username, $email, $firstName, $lastName, 'Therapist', $status, $createdAt, $updatedAt);
        $this->specialization = $specialization;
        $this->license = $license;
        $this->experience = $experience;
        $this->verified = $verified;
    }

    public function getSpecialization(): ?string {
        return $this->specialization;
    }

    public function getLicense(): ?string {
        return $this->license;
    }

    public function getExperience(): ?int {
        return $this->experience;
    }

    public function isVerified(): bool {
        return $this->verified;
    }

    public function withVerification(bool $verified): self {
        return new self(
            $this->userId,
            $this->username,
            $this->email,
            $this->firstName,
            $this->lastName,
            $this->status,
            $this->createdAt,
            $this->specialization,
            $this->license,
            $this->experience,
            $verified,
            date('Y-m-d H:i:s')
        );
    }

    public function toArray(): array {
        return array_merge(parent::toArray(), [
            'specialization' => $this->specialization,
            'license' => $this->license,
            'experience' => $this->experience,
            'verified' => $this->verified
        ]);
    }
}

// Factory class for creating immutable objects from database data
class ImmutableUserFactory {
    private $database;

    public function __construct() {
        $this->database = SingletonDatabase::getInstance();
    }

    public function createPatientFromId(int $patientId): ?ImmutablePatientRecord {
        $stmt = $this->database->execute(
            "SELECT * FROM users WHERE user_id = ? AND role = 'Patient'",
            [$patientId]
        );
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return new ImmutablePatientRecord(
            $data['user_id'],
            $data['username'],
            $data['email'],
            $data['first_name'],
            $data['last_name'],
            $data['status'],
            $data['created_at'],
            $data['age'] ?? null,
            $data['gender'] ?? null,
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['medical_history'] ?? null,
            $data['assigned_therapist_id'] ?? null,
            date('Y-m-d H:i:s')
        );
    }

    public function createTherapistFromId(int $therapistId): ?ImmutableTherapist {
        $stmt = $this->database->execute(
            "SELECT * FROM users WHERE user_id = ? AND role = 'Therapist'",
            [$therapistId]
        );
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return new ImmutableTherapist(
            (int)$data['user_id'],
            $data['username'],
            $data['email'],
            $data['first_name'],
            $data['last_name'],
            $data['status'],
            $data['created_at'],
            $data['specialization'] ?? null,
            $data['license'] ?? null,
            (int)($data['experience'] ?? 0),
            (bool)($data['verified'] ?? false),
            $data['updated_at'] ?? null
        );
    }

    public function createPatientFromArray(array $data): ImmutablePatientRecord {
        return new ImmutablePatientRecord(
            $data['user_id'],
            $data['username'],
            $data['email'],
            $data['first_name'],
            $data['last_name'],
            $data['status'],
            $data['created_at'],
            $data['age'] ?? null,
            $data['gender'] ?? null,
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['medical_history'] ?? null,
            $data['assigned_therapist_id'] ?? null,
            $data['updated_at'] ?? null
        );
    }
}

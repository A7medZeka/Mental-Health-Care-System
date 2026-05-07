<?php

session_start();

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Core/Validation.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Views/Auth/login.php');
    exit();
}

$db     = getConnection();
$action = trim($_POST['action'] ?? '');

function redirectWith(string $location, string $type, string $message, string $activeForm = ''): void {
    $_SESSION[$type . '_message'] = $message;
    if ($activeForm !== '') {
        $_SESSION['active_form'] = $activeForm;
    }
    header('Location: ' . $location);
    exit();
}

// =============================================================================
// PARENT FORM CONTROLLER CLASS
// =============================================================================
class FormController {
    
    protected $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Common validation methods
    protected function validateEmail($email) {
        return validateEmail($email);
    }
    
    protected function validatePassword($password, $confirmPassword) {
        return validatePassword($password) && validateConfirmPassword($password, $confirmPassword);
    }
    
    protected function validateName($name) {
        return validateName($name);
    }
    
    protected function validateNationalID($nationalID) {
        return validateNationalID($nationalID);
    }
    
    protected function validateDateOfBirth($dob) {
        return validateDateOfBirth($dob);
    }
    
    protected function validateAge($age) {
        return validateAge($age);
    }
    
    protected function validatePhoneNumber($phone) {
        return validatePhoneNumber($phone);
    }
    
    // Common database methods
    protected function checkEmailExists($email) {
        $stmt = $this->db->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->rowCount() > 0;
    }
    
    protected function checkNationalIDExists($nationalID) {
        $stmt = $this->db->prepare('SELECT user_id FROM users WHERE national_id = ? LIMIT 1');
        $stmt->execute([$nationalID]);
        return $stmt->rowCount() > 0;
    }
    
    protected function generateUsername($firstName, $lastName) {
        $baseUsername = strtolower($firstName . '.' . $lastName);
        $username = $baseUsername;
        $suffix = 1;
        
        do {
            $uChk = $this->db->prepare('SELECT user_id FROM users WHERE username = ? LIMIT 1');
            $uChk->execute([$username]);
            if ($uChk->rowCount() === 0) break;
            $username = $baseUsername . $suffix++;
        } while (true);
        
        return $username;
    }
    
    protected function getUserByEmail($email) {
        $stmt = $this->db->prepare(
            'SELECT user_id, first_name, last_name, username, email, password_hash, role
             FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    protected function updateUserPassword($userId, $passwordHash) {
        $update = $this->db->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?');
        return $update->execute([$passwordHash, $userId]);
    }
}

// =============================================================================
// LOGIN CONTROLLER - CHILD CLASS
// =============================================================================
class LoginController extends FormController {
    
    public function handleLogin() {
        $email    = trim($_POST['email']    ?? '');
        $password =      $_POST['password'] ?? '';

        if (empty($email) || empty($password))
            redirectWith('../Views/Auth/login.php', 'error', 'Please enter both email and password.', 'login');
            
        if (!$this->checkEmailExists($email))
            redirectWith('../Views/Auth/login.php', 'error', 'Email does not exist, please register first.', 'login');

        $user = $this->getUserByEmail($email);

        if ($user && password_verify($password, $user['password_hash'])) {
            $this->createSession($user);
            $this->redirectToDashboard($user['role']);
        } else {
            redirectWith('../Views/Auth/login.php', 'error', 'Invalid email or password.', 'login');
        }
    }
    
    private function createSession($user) {
        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['user_id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name']  = $user['last_name'];
        $_SESSION['user_name']  = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['username']   = $user['username'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['email']      = $user['email'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['role']       = $user['role'];
    }
    
    private function redirectToDashboard($role) {
        $redirectMap = [
            'Admin'     => '../Views/Admin/dashboard.php',
            'Therapist' => '../Views/Therapist/dashboard.php',
            'Patient'   => '../Views/Patient/dashboard.php',
            'Moderator' => '../Views/Moderator/dashboard.php',
        ];
        
        header('Location: ' . ($redirectMap[$role] ?? '../Views/Auth/login.php'));
        exit();
    }
}

// =============================================================================
// REGISTER CONTROLLER - CHILD CLASS
// =============================================================================
class RegisterController extends FormController {
    
    public function handlePatientRegister() {
        $firstName = trim($_POST['firstName']  ?? '');
        $lastName = trim($_POST['lastName']  ?? '');
        $email = trim($_POST['signupEmail']  ?? '');
        $nationalID = trim($_POST['nationalID']  ?? '');
        $city = trim($_POST['city'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $dob = trim($_POST['dob'] ?? '');
        $genderRaw = $_POST['gender'] ?? '';
        $password = $_POST['signupPassword']  ?? '';
        $confirmPass = $_POST['confirmPassword'] ?? '';
        
        $genderMap = ['male' => 'Male', 'female' => 'Female', 'prefer_not' => 'Other'];
        $gender = $genderMap[$genderRaw] ?? '';
        
        // Validation
        $error = $this->validatePatientData($firstName, $lastName, $email, $nationalID, $city, $phone, $dob, $gender, $password, $confirmPass);
        
        if ($error) redirectWith('../Views/Auth/signup.php', 'error', $error);
        
        // Check duplicates
        if ($this->checkEmailExists($email))
            redirectWith('../Views/Auth/signup.php', 'error', 'This email is already registered. Please log in.');
            
        if ($this->checkNationalIDExists($nationalID))
            redirectWith('../Views/Auth/signup.php', 'error', 'This National ID is already registered.');

        // Create user
        $username = $this->generateUsername($firstName, $lastName);
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $this->insertPatient($firstName, $lastName, $username, $email, $passwordHash, $nationalID, $phone, $dob, $gender, $city);
            redirectWith('../Views/Auth/login.php', 'success', 'Account created successfully! Please log in.', 'login');
        } catch (PDOException $e) {
            error_log('[Register] ' . $e->getMessage());
            redirectWith('../Views/Auth/signup.php', 'error', 'Registration failed. Please try again.');
        }
    }
    
    private function validatePatientData($firstName, $lastName, $email, $nationalID, $city, $phone, $dob, $gender, $password, $confirmPass) {
        if (!$this->validateName($firstName)) return 'Invalid first name. Use letters only (min 2 characters).';
        if (!$this->validateName($lastName)) return 'Invalid last name. Use letters only (min 2 characters).';
        if (!$this->validateNationalID($nationalID)) return 'Invalid National ID. Must be 14 digits starting with 2 or 3.';
        if (!$this->validateEmail($email)) return 'Invalid email format.';
        if (empty($city)) return 'City is required.';
        if (!empty($phone) && !$this->validatePhoneNumber($phone)) return 'Invalid phone. Use Egyptian format: 01XXXXXXXXX.';
        if (!$this->validateDateOfBirth($dob)) return 'Invalid date of birth.';
        if (empty($nationalID)) return 'National ID is required.';
        
        $age = $dob ? (int) ((new DateTime())->diff(new DateTime($dob))->y) : 0;
        if (!$this->validateAge($age)) return 'Invalid age. Must be between 18 and 120.';
        if (empty($gender)) return 'Please select a valid gender.';
        if (!$this->validatePassword($password, $confirmPass)) return 'Weak password or passwords do not match.';
        
        return '';
    }
    
    private function insertPatient($firstName, $lastName, $username, $email, $passwordHash, $nationalID, $phone, $dob, $gender, $city) {
        $stmt = $this->db->prepare(
            'INSERT INTO users
                (first_name, last_name, username, email, password_hash,
                 national_id, phone_number, date_of_birth, gender, city, role)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        
        return $stmt->execute([
            $firstName, $lastName, $username, $email, $passwordHash,
            $nationalID, ($phone !== '' ? $phone : null),
            $dob, $gender, $city, 'Patient',
        ]);
    }
}

// =============================================================================
// THERAPIST CONTROLLER - CHILD CLASS
// =============================================================================
class TherapistController extends FormController {
    
    public function handleTherapistRegister() {
        $firstName = trim($_POST['firstName']?? '');
        $lastName = trim($_POST['lastName']?? '');
        $email = trim($_POST['email']?? '');
        $nationalID = trim($_POST['nationalID']?? '');
        $city = trim($_POST['city']?? '');
        $phone = trim($_POST['phone']?? '');
        $dob = trim($_POST['dob']?? '');
        $genderRaw = $_POST['gender']?? '';
        $specialization = trim($_POST['specialization']?? '');
        $licenseStatus = trim($_POST['licenseStatus']?? '');
        $yearsOfExperience = trim($_POST['yearsOfExperience'] ?? '');
        $availabilitySchedule = trim($_POST['availabilitySchedule'] ?? '');
        $password =$_POST['password']?? '';
        $confirmPass =$_POST['confirmPassword']?? '';
        
        $genderMap = ['male' => 'Male', 'female' => 'Female', 'prefer_not' => 'Other'];
        $gender = $genderMap[$genderRaw] ?? '';
        
        // Validation
        $error = $this->validateTherapistData($firstName, $lastName, $email, $nationalID, $city, $phone, $dob, $gender, $specialization, $licenseStatus, $yearsOfExperience, $availabilitySchedule, $password, $confirmPass);
        
        if ($error) redirectWith('../Views/Auth/therapist-register.php', 'error', $error);

        // Handle credential file upload
        $credentialPath = $this->handleCredentialUpload();
        if (!$credentialPath) return;

        // Check duplicates
        if ($this->checkEmailExists($email))
            redirectWith('../Views/Auth/therapist-register.php', 'error', 'This email is already registered.');

        // Admin adding therapist - go directly to main tables
        $username = $this->generateUsername($firstName, $lastName);
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $this->insertTherapistDirectly($firstName, $lastName, $username, $email, $passwordHash, $nationalID, $phone, $dob, $gender, $city, $specialization, $licenseStatus, $yearsOfExperience, $availabilitySchedule, $credentialPath);
            redirectWith('../Views/Auth/therapist-register.php', 'success', 'Therapist added successfully!');
        } catch (PDOException $e) {
            error_log('[Therapist Register] ' . $e->getMessage());
            redirectWith('../Views/Auth/therapist-register.php', 'error', 'Submission failed. Please try again.');
        }
    }
    
    private function validateTherapistData($firstName, $lastName, $email, $nationalID, $city, $phone, $dob, $gender, $specialization, $licenseStatus, $yearsOfExperience, $availabilitySchedule, $password, $confirmPass) {
        if (!$this->validateName($firstName)) return 'Invalid first name.';
        if (!$this->validateNationalID($nationalID)) return 'Invalid National ID. Must be 14 digits starting with 2 or 3.';
        if (!$this->validateName($lastName)) return 'Invalid last name.';
        if (!$this->validateEmail($email)) return 'Invalid email format.';
        if (empty($nationalID)) return 'National ID is required.';
        if (empty($city)) return 'City is required.';
        if (!empty($phone) && !$this->validatePhoneNumber($phone)) return 'Invalid phone. Use Egyptian format: 01XXXXXXXXX.';
        if (!$this->validateDateOfBirth($dob)) return 'Invalid date of birth.';
        if (empty($gender)) return 'Please select a valid gender.';
        if (empty($specialization)) return 'Specialization is required.';
        if (empty($licenseStatus)) return 'License status is required.';
        if (!is_numeric($yearsOfExperience) || $yearsOfExperience < 0 || $yearsOfExperience > 60) return 'Years of experience must be 0–60.';
        if (empty($availabilitySchedule)) return 'Availability schedule is required.';
        if (!$this->validatePassword($password, $confirmPass)) return 'Weak password or passwords do not match.';
        
        $age = $dob ? (int) ((new DateTime())->diff(new DateTime($dob))->y) : 0;
        if (!$this->validateAge($age)) return 'Invalid age. Must be between 18 and 120.';
        
        return '';
    }
    
    private function handleCredentialUpload() {
        if (empty($_FILES['credentialFile']['tmp_name'])) {
            redirectWith('../Views/Auth/therapist-register.php', 'error', 'Please upload your credentials PDF.');
            return false;
        }
        
        $file = $_FILES['credentialFile'];
        $mimeType = mime_content_type($file['tmp_name']);
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            redirectWith('../Views/Auth/therapist-register.php', 'error', 'File upload error. Please try again.');
            return false;
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            redirectWith('../Views/Auth/therapist-register.php', 'error', 'Credential file must be under 5 MB.');
            return false;
        } elseif ($mimeType !== 'application/pdf') {
            redirectWith('../Views/Auth/therapist-register.php', 'error', 'Only PDF files are accepted for credentials.');
            return false;
        }
        
        $uploadDir = __DIR__ . '/../uploads/credentials/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file['name']));
        $credentialPath = 'uploads/credentials/' . $safeName;
        
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $safeName)) {
            redirectWith('../Views/Auth/therapist-register.php', 'error', 'Failed to save credential file. Please try again.');
            return false;
        }
        
        return $credentialPath;
    }
    
    private function insertTherapistDirectly($firstName, $lastName, $username, $email, $passwordHash, $nationalID, $phone, $dob, $gender, $city, $specialization, $licenseStatus, $yearsOfExperience, $availabilitySchedule, $credentialPath) {
        $this->db->beginTransaction();
        try {
            // Insert into users table first
            $userStmt = $this->db->prepare(
                'INSERT INTO users 
                    (first_name, last_name, username, email, password_hash,
                     national_id, phone_number, date_of_birth, gender, city, role, status, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
            );
            
            $userStmt->execute([
                $firstName, $lastName, $username, $email, $passwordHash,
                $nationalID, ($phone !== '' ? $phone : null),
                $dob, $gender, $city,
                'Therapist', 'Active'
            ]);
            
            $userId = $this->db->lastInsertId();
            $licenseExpiryDate = date('Y-m-d', strtotime('+1 year'));
            // Insert into therapists table
            $therapistStmt = $this->db->prepare(
                'INSERT INTO therapists 
                    (therapist_id, specialization, languages , license_expiry_date, experience_years, availability_schedule,
                     credential_file_path, is_verified)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            
            $therapistStmt->execute([
                $userId,
                $specialization,
                'English',
                $licenseExpiryDate,
                (int)$yearsOfExperience,
                $availabilitySchedule,
                $credentialPath,
                1
            ]);
            
            $this->db->commit();
            return true;
            
        } catch (PDOException $e) {
                error_log('[Therapist Register] ' . $e->getMessage());
                redirectWith('../Views/Auth/therapist-register.php', 'error', 
                    'Submission failed: ' . $e->getMessage()); // Temporarily show error
            }
    }
    
    private function insertPendingTherapist($firstName, $lastName, $username, $email, $passwordHash, $nationalID, $phone, $dob, $gender, $city, $specialization, $licenseStatus, $yearsOfExperience, $availabilitySchedule, $credentialPath) {
        $stmt = $this->db->prepare(
            'INSERT INTO pending_therapists
                (first_name, last_name, username, email, password_hash,
                 national_id, phone_number, date_of_birth, gender, city,
                 specialization, license_status, years_of_experience,
                 availability_schedule, credential_file_path, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        
        return $stmt->execute([
            $firstName, $lastName, $username, $email, $passwordHash,
            $nationalID, ($phone !== '' ? $phone : null),
            $dob, $gender, $city,
            $specialization, $licenseStatus, (int)$yearsOfExperience,
            $availabilitySchedule, $credentialPath, 'Pending',
        ]);
    }
}

// =============================================================================
// PASSWORD RESET CONTROLLER - CHILD CLASS
// =============================================================================
class PasswordResetController extends FormController {
    
    public function handlePasswordReset() {
        $contact = trim($_POST['email_or_phone']   ?? '');
        
        if (empty($contact))
            redirectWith('../Views/Auth/forgot-password.php', 'error', 'Please enter your email address or phone number.');
            
        if (!$this->validateEmail($contact) && !$this->validatePhoneNumber($contact))
            redirectWith('../Views/Auth/forgot-password.php', 'error', 'Please enter a valid email address or phone number.');
            
        $newPassword = $_POST['new_password']     ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';
        
        if (!$this->checkEmailExists($contact))
            redirectWith('../Views/Auth/login.php', 'error', 'Email does not exist, please register first.', 'login');

        $error = '';
        if (empty($contact)) $error = 'Please enter your email address or phone number.';
        if (!$this->validatePassword($newPassword, $confirmPass)) $error = 'Weak password or passwords do not match.';

        if ($error) redirectWith('../Views/Auth/forgot-password.php', 'error', $error);

        $user = $this->findUserByContact($contact);
        if (!$user)
            redirectWith('../Views/Auth/forgot-password.php', 'error', 'No account found with that email or phone number.');

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

        try {
            $this->updateUserPassword($user['user_id'], $newHash);
            redirectWith('../Views/Auth/login.php', 'success', 'Password reset successfully! Please log in.', 'login');
        } catch (PDOException $e) {
            error_log('[Reset Password] ' . $e->getMessage());
            redirectWith('../Views/Auth/forgot-password.php', 'error', 'Password reset failed. Please try again.');
        }
    }
    
    private function findUserByContact($contact) {
        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            $stmt = $this->db->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$contact]);
        } else {
            $phone = preg_replace('/[\s\-+]/', '', $contact);
            $stmt = $this->db->prepare('SELECT user_id FROM users WHERE phone_number = ? LIMIT 1');
            $stmt->execute([$phone]);
        }
        
        return $stmt->fetch();
    }
}

// =============================================================================
// MAIN ROUTER - INSTANTIATES AND DELEGATES TO CHILD CLASSES
// =============================================================================
$action = trim($_POST['action'] ?? '');

switch ($action) {
    case 'login':
        $loginController = new LoginController($db);
        $loginController->handleLogin();
        break;
        
    case 'register':
        $registerController = new RegisterController($db);
        $registerController->handlePatientRegister();
        break;
        
    case 'register_therapist':
        $therapistController = new TherapistController($db);
        $therapistController->handleTherapistRegister();
        break;
        
    case 'reset_password':
        $passwordResetController = new PasswordResetController($db);
        $passwordResetController->handlePasswordReset();
        break;
        
    default:
        header('Location: ../Views/Auth/login.php');
        exit();
}
?>

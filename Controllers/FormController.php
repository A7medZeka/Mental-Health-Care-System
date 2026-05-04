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

//! PATIENT REGISTRATION
if ($action === 'register') {

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
    $gender    = $genderMap[$genderRaw] ?? '';
    $error = '';
    if      (!validateName($firstName)) $error = 'Invalid first name. Use letters only (min 2 characters).';
    if  (!validateName($lastName)) $error = 'Invalid last name. Use letters only (min 2 characters).';
    if  (!validateNationalID($nationalID)) $error = 'Invalid National ID. Must be 14 digits starting with 2 or 3.';
    if  (!validateEmail($email)) $error = 'Invalid email format.';
    if  (empty($city)) $error = 'City is required.';
    if  (!empty($phone) && !validatePhoneNumber($phone)) $error = 'Invalid phone. Use Egyptian format: 01XXXXXXXXX.';
    if  (!validateDateOfBirth($dob)) $error = 'Invalid date of birth.';
    $age = $dob ? (int) ((new DateTime())->diff(new DateTime($dob))->y) : 0;
    if  (empty($nationalID)) $error = 'National ID is required.';
    if  (!validateAge($age)) $error = 'Invalid age. Must be between 18 and 120.';
    if  (empty($gender)) $error = 'Please select a valid gender.';
    if  (!validatePassword($password)) $error = 'Weak password. Min 8 chars with uppercase, lowercase, number & special character.';
    if  (!validateConfirmPassword($password, $confirmPass))  $error = 'Passwords do not match.';
    
    if ($error) redirectWith('../Views/Auth/signup.php', 'error', $error);
    
    $chk = $db->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
    $chk->execute([$email]);
    if ($chk->rowCount() > 0)
        redirectWith('../Views/Auth/signup.php', 'error', 'This email is already registered. Please log in.');
    
    $chkNID = $db->prepare('SELECT user_id FROM users WHERE national_id = ? LIMIT 1');
    $chkNID->execute([$nationalID]);
    if ($chkNID->rowCount() > 0)
        redirectWith('../Views/Auth/signup.php', 'error', 'This National ID is already registered.');

    $baseUsername = strtolower($firstName . '.' . $lastName);
    $username     = $baseUsername;
    $suffix       = 1;
    do {
        $uChk = $db->prepare('SELECT user_id FROM users WHERE username = ? LIMIT 1');
        $uChk->execute([$username]);
        if ($uChk->rowCount() === 0) break;
        $username = $baseUsername . $suffix++;
    } while (true);

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare(
        'INSERT INTO users
            (first_name, last_name, username, email, password_hash,
             national_id, phone_number, date_of_birth, gender, city, role)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    try {
        $stmt->execute([
            $firstName, $lastName, $username, $email, $passwordHash,
            $nationalID, ($phone !== '' ? $phone : null),
            $dob, $gender, $city, 'Patient',
        ]);
        redirectWith('../Views/Auth/login.php', 'success', 'Account created successfully! Please log in.', 'login');
    } catch (PDOException $e) {
        error_log('[Register] ' . $e->getMessage());
        redirectWith('../Views/Auth/signup.php', 'error', 'Registration failed. Please try again.');
    }
}

//! THERAPIST REGISTRATION
elseif ($action === 'register_therapist') {

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
    $gender    = $genderMap[$genderRaw] ?? '';
    $error = '';
    if      (!validateName($firstName)) $error = 'Invalid first name.';
    if  (!validateNationalID($nationalID)) $error = 'Invalid National ID. Must be 14 digits starting with 2 or 3.';
    if  (!validateName($lastName)) $error = 'Invalid last name.';
    if  (!validateEmail($email)) $error = 'Invalid email format.';
    if  (empty($nationalID)) $error = 'National ID is required.';
    if  (empty($city)) $error = 'City is required.';
    if  (!empty($phone) && !validatePhoneNumber($phone)) $error = 'Invalid phone. Use Egyptian format: 01XXXXXXXXX.';
    if  (!validateDateOfBirth($dob)) $error = 'Invalid date of birth.';
    if  (empty($gender)) $error = 'Please select a valid gender.';
    if  (empty($specialization)) $error = 'Specialization is required.';
    if  (empty($licenseStatus)) $error = 'License status is required.';
    if  (!is_numeric($yearsOfExperience) || $yearsOfExperience < 0 || $yearsOfExperience > 60)  $error = 'Years of experience must be 0–60.';
    if  (empty($availabilitySchedule)) $error = 'Availability schedule is required.';
    if  (!validatePassword($password)) $error = 'Weak password. Min 8 chars with uppercase, lowercase, number & special character.';
    if  (!validateConfirmPassword($password, $confirmPass)) $error = 'Passwords do not match.';
    $age = $dob ? (int) ((new DateTime())->diff(new DateTime($dob))->y) : 0;
    if  (!validateAge($age)) $error = 'Invalid age. Must be between 18 and 120.';

    $credentialPath = null;
    if (!$error) {
        if (empty($_FILES['credentialFile']['tmp_name'])) {
            $error = 'Please upload your credentials PDF.';
        } else {
            $file     = $_FILES['credentialFile'];
            $mimeType = mime_content_type($file['tmp_name']);

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'File upload error. Please try again.';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $error = 'Credential file must be under 5 MB.';
            } elseif ($mimeType !== 'application/pdf') {
                $error = 'Only PDF files are accepted for credentials.';
            } else {
                $uploadDir = __DIR__ . '/../uploads/credentials/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $safeName       = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file['name']));
                $credentialPath = 'uploads/credentials/' . $safeName;
                if (!move_uploaded_file($file['tmp_name'], $uploadDir . $safeName))
                    $error = 'Failed to save credential file. Please try again.';
            }
        }
    }

    if ($error) redirectWith('../Views/Auth/therapist-register.php', 'error', $error);

    $chkUsers = $db->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
    $chkUsers->execute([$email]);
    if ($chkUsers->rowCount() > 0)
        redirectWith('../Views/Auth/therapist-register.php', 'error', 'This email is already registered.');

    $chkPend = $db->prepare('SELECT id FROM pending_therapists WHERE email = ? LIMIT 1');
    $chkPend->execute([$email]);
    if ($chkPend->rowCount() > 0)
        redirectWith('../Views/Auth/therapist-register.php', 'error', 'An application with this email is already under review.');

    $baseUsername = strtolower($firstName . '.' . $lastName);
    $username     = $baseUsername;
    $suffix       = 1;
    do {
        $uChk = $db->prepare('SELECT id FROM pending_therapists WHERE username = ? LIMIT 1');
        $uChk->execute([$username]);
        if ($uChk->rowCount() === 0) break;
        $username = $baseUsername . $suffix++;
    } while (true);

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare(
        'INSERT INTO pending_therapists
            (first_name, last_name, username, email, password_hash,
             national_id, phone_number, date_of_birth, gender, city,
             specialization, license_status, years_of_experience,
             availability_schedule, credential_file_path, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    try {
        $stmt->execute([
            $firstName, $lastName, $username, $email, $passwordHash,
            $nationalID, ($phone !== '' ? $phone : null),
            $dob, $gender, $city,
            $specialization, $licenseStatus, (int)$yearsOfExperience,
            $availabilitySchedule, $credentialPath, 'Pending',
        ]);
        redirectWith(
            '../Views/Auth/therapist-register.php', 'success',
            'Application submitted! You will be notified once an admin reviews your credentials.'
        );
    } catch (PDOException $e) {
        error_log('[Therapist Register] ' . $e->getMessage());
        redirectWith('../Views/Auth/therapist-register.php', 'error', 'Submission failed. Please try again.');
    }
}

//! LOGIN
elseif ($action === 'login') {

    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    if (empty($email) || empty($password))
        redirectWith('../Views/Auth/login.php', 'error', 'Please enter both email and password.', 'login');
    $stmt = $db->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->rowCount() === 0)
        redirectWith('../Views/Auth/login.php', 'error', 'Email is not exist , please register first.', 'login');

    $stmt = $db->prepare(
        'SELECT user_id, first_name, last_name, username, email, password_hash, role
         FROM users WHERE email = ? LIMIT 1'
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {

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

        $redirectMap = [
            'Admin'     => '../Views/Admin/dashboard.php',
            'Therapist' => '../Views/Therapist/dashboard.php',
            'Patient'   => '../Views/Patient/dashboard.php',
            'Moderator' => '../Views/Moderator/dashboard.php',
        ];

        header('Location: ' . ($redirectMap[$user['role']] ?? '../Views/Auth/login.php'));
        exit();

    } else {
        // ✅ FIX: was 'error_message' which produced key 'error_message_message' — now correctly 'error'
        redirectWith('../Views/Auth/login.php', 'error', 'Invalid email or password.', 'login');
    }
}

//! PASSWORD RESET
elseif ($action === 'reset_password') {

    $contact     = trim($_POST['email_or_phone']   ?? '');
    if (empty($contact))
        redirectWith('../Views/Auth/forgot-password.php', 'error', 'Please enter your email address or phone number.');
    if (!validateEmail($contact) && !validatePhoneNumber($contact))
        redirectWith('../Views/Auth/forgot-password.php', 'error', 'Please enter a valid email address or phone number.');
    $newPassword =      $_POST['new_password']     ?? '';
    $confirmPass =      $_POST['confirm_password'] ?? '';
    $stmt = $db->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$contact]);
    if ($stmt->rowCount() === 0)
        redirectWith('../Views/Auth/login.php', 'error', 'Email is not exist , please register first.', 'login');

    $error = '';
    if      (empty($contact)) $error = 'Please enter your email address or phone number.';
    elseif  (!validatePassword($newPassword)) $error = 'Weak password. Min 8 chars with uppercase, lowercase, number & special character.';
    elseif  (!validateConfirmPassword($newPassword, $confirmPass)) $error = 'Passwords do not match.';

    if ($error) redirectWith('../Views/Auth/forgot-password.php', 'error', $error);

    if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
        $stmt = $db->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$contact]);
    } else {
        $phone = preg_replace('/[\s\-+]/', '', $contact);
        $stmt  = $db->prepare('SELECT user_id FROM users WHERE phone_number = ? LIMIT 1');
        $stmt->execute([$phone]);
    }

    $user = $stmt->fetch();
    if (!$user)
        redirectWith('../Views/Auth/forgot-password.php', 'error', 'No account found with that email or phone number.');

    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $update  = $db->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?');

    try {
        $update->execute([$newHash, $user['user_id']]);
        redirectWith('../Views/Auth/login.php', 'success', 'Password reset successfully! Please log in.', 'login');
    } catch (PDOException $e) {
        error_log('[Reset Password] ' . $e->getMessage());
        redirectWith('../Views/Auth/forgot-password.php', 'error', 'Password reset failed. Please try again.');
    }
}

else {
    header('Location: ../Views/Auth/login.php');
    exit();
}
<?php
session_start();
require_once "Validation.php";

unset($_SESSION['error_message'], $_SESSION['success_message']);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

// ─── DB Connection ─────────────────────────────────────────────────────────
try {
    $connection = new PDO("mysql:host=localhost;dbname=holisticmentalhealth;charset=utf8mb4", "root", "");
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("DB connection failed: " . $e->getMessage());
    $_SESSION['error_message'] = "A server error occurred. Please try again later.";
    header("Location: index.php");
    exit();
}

$action = $_POST['action'] ?? '';

// ══════════════════════════════════════════════════════════════════════════════
//  REGISTER
// ══════════════════════════════════════════════════════════════════════════════
if ($action === "register") {

    $name         = trim($_POST['username']         ?? '');
    $email        = trim($_POST['email']            ?? '');
    $phone        = trim($_POST['phone']            ?? '');
    $gender       =      $_POST['gender']           ?? '';
    $age          = trim($_POST['age']              ?? '');
    $dob          = trim($_POST['date']             ?? '');
    $password     =      $_POST['password']         ?? '';
    $confirm_pass =      $_POST['confirm_password'] ?? '';

    // ── Validation ────────────────────────────────────────────────────────
    if (!validateName($name)) {
        $_SESSION['error_message'] = "Invalid name. Use letters only (min 2 characters).";

    } elseif (!validateEmail($email)) {
        $_SESSION['error_message'] = "Invalid email format.";

    } elseif (!validatePhoneNumber($phone)) {
        $_SESSION['error_message'] = "Invalid phone number. Use Egyptian format: 01XXXXXXXXX (starts with 010 / 011 / 012 / 015).";

    } elseif (!validateGender($gender)) {
        $_SESSION['error_message'] = "Please select a valid gender.";

    } elseif (!validateAge($age)) {
        $_SESSION['error_message'] = "Invalid age. Must be a number between 1 and 120.";

    } elseif (!validateDateOfBirth($dob)) {
        $_SESSION['error_message'] = "Invalid date of birth. Use YYYY-MM-DD format and make sure it's not a future date.";

    } elseif (!validatePassword($password)) {
        $_SESSION['error_message'] = "Weak password. Must be at least 8 characters and include: uppercase letter, lowercase letter, number, and special character (e.g. @, #, !).";

    } elseif (!validateConfirmPassword($password, $confirm_pass)) {
        $_SESSION['error_message'] = "Passwords do not match.";
    }

    if (isset($_SESSION['error_message'])) {
        $_SESSION['active_form'] = 'register';
        header("Location: index.php");
        exit();
    }

    // ── Check duplicate email ─────────────────────────────────────────────
    $check = $connection->prepare("SELECT `id` FROM `users` WHERE `email` = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        $_SESSION['error_message'] = "This email is already registered. Please login.";
        $_SESSION['active_form'] = 'login';
        header("Location: index.php");
        exit();
    }

    // ── Insert ────────────────────────────────────────────────────────────
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $role = 'Patient'; // All self-registered users are Patients

    $stmt = $connection->prepare(
        "INSERT INTO `users` ('user_id',`email`, `password`, `first_name`,`last_name`, `phone`, `gender`, `age`, `date`, `role`)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    try {
        $stmt->execute([$email, $hashedPassword, $name, $name, $phone, $gender, (int)$age, $dob, $role]);
        $_SESSION['success_message'] = "Registered successfully! Please login.";
        $_SESSION['active_form'] = 'login';
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        error_log("Registration failed: " . $e->getMessage());
        $_SESSION['error_message'] = "Registration failed. Please try again.";
        $_SESSION['active_form'] = 'register';
        header("Location: index.php");
        exit();
    }
}

// ══════════════════════════════════════════════════════════════════════════════
//  LOGIN
// ══════════════════════════════════════════════════════════════════════════════
elseif ($action === "login") {

    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['error_message'] = "Please enter both email and password.";
        $_SESSION['active_form'] = 'login';
        header("Location: index.php");
        exit();
    }

    $stmt = $connection->prepare(
        "SELECT `id`, `name`, `email`, `password`, `role`, `age`, `date` FROM `users` WHERE `email` = ?"
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);

        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role'] ?? 'Patient';
        $_SESSION['user_age']   = $user['age'];
        $_SESSION['user_dob']   = $user['date'];
        /*
        Admin
        Therapist
        Patient
        Moderator
        */
        if($_SESSION['user_role'] === 'Admin') {
            header("Location: admin-dashboard.php");
        } elseif($_SESSION['user_role'] === 'Therapist') {
            header("Location: therapist-dashboard.php");
        } 
        elseif($_SESSION['user_role'] === 'Patient') {
            header("Location: patient-dashboard.php");
        }
        else {
            header("Location: moderator-dashboard.php");
        }
        exit();
    } else {
        $_SESSION['error_message'] = "Invalid email or password.";
        $_SESSION['active_form'] = 'login';
        header("Location: index.php");
        exit();
    }
}

// ── Unknown action ─────────────────────────────────────────────────────────
else {
    header("Location: index.php");
    exit();
}
?>

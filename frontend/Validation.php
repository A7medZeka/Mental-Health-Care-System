<?php

/* ================= METHOD ================= */
function checkMethod($method) {
    return $method === 'POST';
}

/* ================= EMAIL ================= */
function validateEmail($input) {
    if (!isset($input)) return false;
    $input = trim($input);
    if (empty($input)) return false;
    return filter_var($input, FILTER_VALIDATE_EMAIL) !== false;
}

/* ================= PASSWORD ================= */
function validatePassword($input) {
    if (!isset($input)) return false;
    if (empty($input)) return false;
    if (strlen($input) < 8) return false;
    if (!preg_match('/[A-Z]/', $input)) return false;
    if (!preg_match('/[a-z]/', $input)) return false;
    if (!preg_match('/[0-9]/', $input)) return false;
    if (!preg_match('/[\W_]/', $input)) return false;
    if (preg_match('/\s/', $input)) return false;
    return true;
}

/* ================= CONFIRM PASSWORD ================= */
function validateConfirmPassword($password, $confirmPassword) {
    return $password === $confirmPassword;
}

/* ================= NAME ================= */
function validateName($input) {
    if (!isset($input)) return false;
    $input = trim($input);
    if ($input === '') return false;
    return (bool) preg_match("/^[\p{L}]+(?:[\s'_-]?[\p{L}]+)*$/u", $input);
}

/* ================= PHONE (Egypt format) ================= */
function validatePhoneNumber($input) {
    if (!isset($input)) return false;
    $input = trim($input);
    if (empty($input)) return false;
    return (bool) preg_match('/^01[0125][0-9]{8}$/', $input);
}

/* ================= GENDER ================= */
function validateGender($input) {
    if (!isset($input)) return false;
    $input = trim($input);
    if (empty($input)) return false;
    return in_array($input, ['male', 'female'], true);
}

/* ================= AGE ================= */
function validateAge($input) {
    if (!isset($input)) return false;
    $input = trim($input);
    if ($input === '') return false;
    if (!ctype_digit((string)$input)) return false;
    $age = (int)$input;
    return ($age >= 1 && $age <= 120);
}

/* ================= DATE OF BIRTH ================= */
function validateDateOfBirth($input) {
    if (!isset($input)) return false;
    $input = trim($input);
    if (empty($input)) return false;
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $input)) return false;
    $parts = explode('-', $input);
    if (!checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0])) return false;
    // Must not be in the future
    $today = new DateTime('today');
    $dob   = new DateTime($input);
    return $dob <= $today;
}
?>

<?php

/**
 * Extended Validation class with all required validation functions
 * This resolves the missing functions issue
 */
class ValidationExtended {
    
    // Email validation
    public static function validateEmail($input) {
        if (!isset($input)) return false;
        $input = trim($input);
        if (empty($input)) return false;
        return filter_var($input, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    // Password validation
    public static function validatePassword($input) {
        if (!isset($input)) return false;
        $input = trim($input);
        if (empty($input)) return false;
        if (strlen($input) < 8) return false;
        if (!preg_match('/[A-Z]/', $input)) return false;
        if (!preg_match('/[a-z]/', $input)) return false;
        if (!preg_match('/[0-9]/', $input)) return false;
        if (!preg_match('/[\W_]/', $input)) return false;
        if (!preg_match('/\s/', $input)) return false;
        return true;
    }
    
    // Confirm password validation
    public static function validateConfirmPassword($password, $confirmPassword) {
        return $password === $confirmPassword;
    }
    
    // Name validation
    public static function validateName($input) {
        if (!isset($input)) return false;
        $input = trim($input);
        if ($input === '') return false;
        return (bool) preg_match("/^[\p{L}]+(?:[\s'_-]?[\p{L}]+)*$/u", $input);
    }
    
    // National ID validation
    public static function validateNationalID($input) {
        if (!isset($input)) return false;
        $input = trim($input);
        if (empty($input)) return false;
        return (bool) preg_match('/^(2\d{2}|3\d{2})\d{11}$/', $input);
    }
    
    // Phone validation
    public static function validatePhoneNumber($input) {
        if (!isset($input)) return false;
        $input = trim($input);
        if (empty($input)) return true; // Phone is optional
        return (bool) preg_match('/^01[0125][0-9]{8}$/', $input);
    }
    
    // Date of birth validation
    public static function validateDateOfBirth($input) {
        if (!isset($input)) return false;
        $input = trim($input);
        if (empty($input)) return false;
        
        $parts = explode('-', $input);
        if (count($parts) !== 3) return false;
        
        $year = (int)$parts[0];
        $month = (int)$parts[1];
        $day = (int)$parts[2];
        
        return checkdate($year, $month, $day);
    }
    
    // Age validation
    public static function validateAge($age) {
        return is_numeric($age) && $age >= 18 && $age <= 120;
    }
    
    // Gender validation
    public static function validateGender($input) {
        if (!isset($input)) return false;
        $input = trim($input);
        if (empty($input)) return false;
        return in_array($input, ['male', 'female'], true);
    }
    
    // Role validation
    public static function validateRole($input) {
        if (!isset($input)) return false;
        $input = trim($input);
        if (empty($input)) return false;
        return in_array($input, ['Patient', 'Therapist', 'Moderator', 'Admin'], true);
    }
}

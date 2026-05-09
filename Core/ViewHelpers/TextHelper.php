<?php
/**
 * TextHelper - Centralized Text Management and Internationalization
 * Eliminates hardcoded text in Views and supports multiple languages
 */

class TextHelper {
    private static $currentLanguage = 'en';
    private static $texts = [
        'en' => [
            // Navigation
            'nav_dashboard' => 'Dashboard',
            'nav_patients' => 'Manage Patients',
            'nav_add_therapist' => 'Add Therapist',
            'nav_therapists' => 'Therapists Verification',
            'nav_rbac' => 'RBAC Settings',
            'nav_performance' => 'Therapist Performance',
            'nav_safety_logs' => 'Safety Logs',
            'nav_onboarding' => 'Onboarding Checklist',
            'nav_my_therapist' => 'My Therapist',
            'nav_appointments' => 'Appointments',
            'nav_sessions' => 'Sessions',
            'nav_mood_tracker' => 'Mood Tracker',
            'nav_wellness_goals' => 'Wellness Goals',
            'nav_journal' => 'My Journal',
            'nav_resources' => 'Wellness Resources',
            'nav_forum' => 'Community Forum',
            'nav_payments' => 'Payments & Insurance',
            'nav_consents' => 'Legal Consents',
            'nav_emergency' => '🆘 Emergency Help',
            'nav_forum_moderation' => 'Forum Moderation',
            'nav_safety_audit' => 'Safety Audit Log',
            'nav_clinical_insights' => 'Clinical Insights',
            'nav_manage_sessions' => 'Manage Sessions',
            'nav_manage_patients' => 'Manage Patients',
            
            // Common
            'logout' => 'Logout',
            'login' => 'Log In',
            'signup' => 'Sign Up',
            'join_patient' => 'Join as a Patient',
            'apply_therapist' => 'Apply as a Therapist',
            'apply_now' => 'Apply Now',
            'already_member' => 'Already a Member?',
            
            // Dashboard
            'dashboard_overview' => 'Dashboard Overview',
            'total_patients' => 'Total Patients',
            'total_therapists' => 'Total Therapists',
            'high_risk_alerts' => 'High Risk Alerts',
            'next_appointment' => 'Next Appointment',
            'todays_mood' => 'Today\'s Mood',
            'active_goals' => 'Active Goals',
            'pending_actions' => 'Pending Actions',
            
            // Notifications
            'system_reminder' => 'System Reminder',
            'upcoming_session' => 'Upcoming Session',
            'urgent_patient_noshow' => 'URGENT: Patient No-Show Detected',
            'welfare_options' => 'Welfare Options',
            'select_action' => 'Select Action to Log',
            'action_notes' => 'Action Notes',
            'submit_action' => 'Submit Action & Save Log',
            'patient_joined_late' => 'Patient Joined Late (Override)',
            'mark_false_alarm' => 'Mark as False Alarm',
            
            // Forms
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'password' => 'Password',
            'confirm_password' => 'Confirm Password',
            'phone_number' => 'Phone Number',
            'date_of_birth' => 'Date of Birth',
            'gender' => 'Gender',
            'city' => 'City',
            'specialization' => 'Specialization',
            'years_of_experience' => 'Years of Experience',
            
            // Actions
            'save' => 'Save',
            'cancel' => 'Cancel',
            'edit' => 'Edit',
            'delete' => 'Delete',
            'view' => 'View',
            'update' => 'Update',
            'create' => 'Create',
            'submit' => 'Submit',
            'search' => 'Search',
            'filter' => 'Filter',
            'sort' => 'Sort',
            
            // Status
            'active' => 'Active',
            'inactive' => 'Inactive',
            'pending' => 'Pending',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'scheduled' => 'Scheduled',
            'confirmed' => 'Confirmed',
            'verified' => 'Verified',
            'unverified' => 'Unverified',
            
            // Messages
            'success' => 'Success',
            'error' => 'Error',
            'warning' => 'Warning',
            'info' => 'Information',
            'loading' => 'Loading...',
            'no_data' => 'No data available',
            'confirm_delete' => 'Are you sure you want to delete this item?',
            
            // Time
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'this_week' => 'This Week',
            'this_month' => 'This Month',
            'last_week' => 'Last Week',
            'last_month' => 'Last Month',
            
            // Emergency
            'emergency_help' => '🆘 Emergency Help',
            'crisis_detected' => 'Crisis Keyword Detected',
            'get_help_now' => 'Get Help Now',
            'call_emergency' => 'Call Emergency Services'
        ],
        'ar' => [
            // Arabic translations (basic set for demonstration)
            'nav_dashboard' => 'لوحة التحكم',
            'nav_patients' => 'إدارة المرضى',
            'logout' => 'تسجيل الخروج',
            'login' => 'تسجيل الدخول',
            'signup' => 'التسجيل',
            'total_patients' => 'إجمالي المرضى',
            'total_therapists' => 'إجمالي المعالجين',
            'emergency_help' => '🆘 المساعدة الطارئة',
            'get_help_now' => 'احصل على المساعدة الآن'
        ]
    ];
    
    /**
     * Set current language
     */
    public static function setLanguage(string $language): void {
        if (isset(self::$texts[$language])) {
            self::$currentLanguage = $language;
        }
    }
    
    /**
     * Get current language
     */
    public static function getCurrentLanguage(): string {
        return self::$currentLanguage;
    }
    
    /**
     * Get text by key
     */
    public static function get(string $key, array $params = []): string {
        $text = self::$texts[self::$currentLanguage][$key] ?? $key;
        
        if (!empty($params)) {
            foreach ($params as $param => $value) {
                $text = str_replace('{' . $param . '}', $value, $text);
            }
        }
        
        return $text;
    }
    
    /**
     * Get text with HTML escaping
     */
    public static function getEscaped(string $key, array $params = []): string {
        return htmlspecialchars(self::get($key, $params));
    }
    
    /**
     * Check if text key exists
     */
    public static function has(string $key): bool {
        return isset(self::$texts[self::$currentLanguage][$key]);
    }
    
    /**
     * Add or update text
     */
    public static function set(string $key, string $text, string $language = null): void {
        $lang = $language ?? self::$currentLanguage;
        if (!isset(self::$texts[$lang])) {
            self::$texts[$lang] = [];
        }
        self::$texts[$lang][$key] = $text;
    }
    
    /**
     * Get all texts for current language
     */
    public static function getAllTexts(): array {
        return self::$texts[self::$currentLanguage] ?? [];
    }
    
    /**
     * Generate language selector
     */
    public static function generateLanguageSelector(): string {
        $html = '<div class="language-selector">';
        $html .= '<select class="form-select form-select-sm" onchange="changeLanguage(this.value)">';
        
        foreach (self::$texts as $lang => $texts) {
            $selected = ($lang === self::$currentLanguage) ? 'selected' : '';
            $html .= '<option value="' . $lang . '" ' . $selected . '>' . strtoupper($lang) . '</option>';
        }
        
        $html .= '</select>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get navigation text
     */
    public static function getNavText(string $key): string {
        return self::get('nav_' . $key);
    }
    
    /**
     * Get status text
     */
    public static function getStatusText(string $status): string {
        return self::get($status);
    }
    
    /**
     * Get action text
     */
    public static function getActionText(string $action): string {
        return self::get($action);
    }
    
    /**
     * Get form label
     */
    public static function getFormLabel(string $field): string {
        return self::get($field);
    }
    
    /**
     * Get message text
     */
    public static function getMessage(string $type, string $key = null): string {
        if ($key) {
            return self::get($key);
        }
        return self::get($type);
    }
    
    /**
     * Format text with parameters
     */
    public static function format(string $key, array $params = []): string {
        return self::get($key, $params);
    }
    
    /**
     * Get plural form (basic implementation)
     */
    public static function plural(string $key, int $count): string {
        if ($count === 1) {
            return self::get($key . '_singular');
        }
        return self::get($key . '_plural') ?? self::get($key);
    }
    
    /**
     * Load texts from file
     */
    public static function loadFromFile(string $filePath, string $language = null): void {
        if (!file_exists($filePath)) {
            return;
        }
        
        $lang = $language ?? self::$currentLanguage;
        $texts = include $filePath;
        
        if (is_array($texts)) {
            if (!isset(self::$texts[$lang])) {
                self::$texts[$lang] = [];
            }
            self::$texts[$lang] = array_merge(self::$texts[$lang], $texts);
        }
    }
    
    /**
     * Export texts to file
     */
    public static function exportToFile(string $filePath, string $language = null): void {
        $lang = $language ?? self::$currentLanguage;
        $texts = self::$texts[$lang] ?? [];
        
        $content = "<?php\nreturn " . var_export($texts, true) . ";\n";
        file_put_contents($filePath, $content);
    }
    
    /**
     * Get available languages
     */
    public static function getAvailableLanguages(): array {
        return array_keys(self::$texts);
    }
    
    /**
     * Add new language
     */
    public static function addLanguage(string $language, array $texts = []): void {
        self::$texts[$language] = $texts;
    }
}
?>

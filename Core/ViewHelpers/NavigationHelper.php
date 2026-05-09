<?php
/**
 * NavigationHelper - Dynamic Navigation Generation
 * Eliminates hardcoded navigation links in Views
 */

class NavigationHelper {
    private static $navigationConfig = [
        'Admin' => [
            'dashboard' => ['icon' => 'speedometer2', 'label' => 'Dashboard', 'url' => 'dashboard.php'],
            'patients' => ['icon' => 'people', 'label' => 'Manage Patients', 'url' => 'patients.php'],
            'add_therapist' => ['icon' => 'plus-circle', 'label' => 'Add Therapist', 'url' => '../Auth/therapist-register.php'],
            'therapists' => ['icon' => 'person-badge', 'label' => 'Therapists Verification', 'url' => 'therapists.php'],
            'rbac' => ['icon' => 'shield-lock', 'label' => 'RBAC Settings', 'url' => 'rbac.php'],
            'performance' => ['icon' => 'bar-chart-line', 'label' => 'Therapist Performance', 'url' => 'performance.php'],
            'safety_logs' => ['icon' => 'journal-medical', 'label' => 'Safety Logs', 'url' => 'safety-logs.php']
        ],
        'Patient' => [
            'dashboard' => ['icon' => 'speedometer2', 'label' => 'Dashboard', 'section' => 'section-dashboard'],
            'onboarding' => ['icon' => 'clipboard-check', 'label' => 'Onboarding Checklist', 'section' => 'section-onboarding'],
            'therapist' => ['icon' => 'person-check', 'label' => 'My Therapist', 'section' => 'section-therapist'],
            'appointments' => ['icon' => 'calendar-event', 'label' => 'Appointments', 'section' => 'section-appointments'],
            'sessions' => ['icon' => 'camera-video', 'label' => 'Sessions', 'section' => 'section-sessions'],
            'mood' => ['icon' => 'heart-pulse', 'label' => 'Mood Tracker', 'section' => 'section-mood'],
            'goals' => ['icon' => 'bullseye', 'label' => 'Wellness Goals', 'section' => 'section-goals'],
            'journal' => ['icon' => 'journal-richtext', 'label' => 'My Journal', 'section' => 'section-journal'],
            'resources' => ['icon' => 'stars', 'label' => 'Wellness Resources', 'section' => 'section-resources'],
            'forum' => ['icon' => 'chat-square-heart', 'label' => 'Community Forum', 'url' => 'forum.php'],
            'payments' => ['icon' => 'credit-card', 'label' => 'Payments & Insurance', 'section' => 'section-payments'],
            'consents' => ['icon' => 'file-earmark-check', 'label' => 'Legal Consents', 'section' => 'section-consents'],
            'emergency' => ['icon' => 'telephone-fill', 'label' => '🆘 Emergency Help', 'section' => 'section-emergency', 'style' => 'color:#dc3545;']
        ],
        'Therapist' => [
            'dashboard' => ['icon' => 'house-door', 'label' => 'Dashboard', 'url' => 'dashboard.php'],
            'sessions' => ['icon' => 'calendar-event', 'label' => 'Manage Sessions', 'url' => 'sessions.php'],
            'patients' => ['icon' => 'people', 'label' => 'Manage Patients', 'url' => 'patients.php'],
            'insights' => ['icon' => 'graph-up', 'label' => 'Clinical Insights', 'url' => 'insights.php']
        ],
        'Moderator' => [
            'dashboard' => ['icon' => 'speedometer2', 'label' => 'Dashboard', 'url' => 'dashboard.php'],
            'forum' => ['icon' => 'shield-exclamation', 'label' => 'Forum Moderation', 'url' => 'forum.php', 'badge' => 'navBadgeForum'],
            'performance' => ['icon' => 'bar-chart-line', 'label' => 'Therapist Performance', 'url' => 'performance.php'],
            'safety_audit' => ['icon' => 'journal-medical', 'label' => 'Safety Audit Log', 'url' => 'safety-audit.php', 'badge' => 'navBadgeAudit']
        ]
    ];
    
    /**
     * Generate navigation menu for a specific role
     */
    public static function generateNavigation(string $role, string $activePage = ''): string {
        if (!isset(self::$navigationConfig[$role])) {
            return '';
        }
        
        $navItems = self::$navigationConfig[$role];
        $html = '<ul class="nav flex-column mb-auto">';
        
        foreach ($navItems as $key => $item) {
            $isActive = ($key === $activePage) ? 'active' : '';
            $style = isset($item['style']) ? $item['style'] : '';
            
            if (isset($item['section'])) {
                // Patient navigation with sections
                $html .= self::generateSectionNavItem($item, $isActive, $style);
            } else {
                // Regular navigation item
                $html .= self::generateRegularNavItem($item, $isActive, $style);
            }
        }
        
        $html .= '</ul>';
        return $html;
    }
    
    /**
     * Generate regular navigation item
     */
    private static function generateRegularNavItem(array $item, string $isActive, string $style = ''): string {
        $activeClass = $isActive ? 'active' : '';
        $styleAttr = $style ? " style=\"{$style}\"" : '';
        $badge = isset($item['badge']) ? "<span class=\"badge bg-danger ms-auto\" id=\"{$item['badge']}\">5</span>" : '';
        
        return "
            <li class=\"nav-item\">
                <a class=\"nav-link {$activeClass}\" href=\"{$item['url']}\"{$styleAttr}>
                    <i class=\"bi bi-{$item['icon']} me-2\"></i> {$item['label']}{$badge}
                </a>
            </li>";
    }
    
    /**
     * Generate section navigation item (for patient dashboard)
     */
    private static function generateSectionNavItem(array $item, string $isActive, string $style = ''): string {
        $activeClass = $isActive ? 'active' : '';
        $styleAttr = $style ? " style=\"{$style}\"" : '';
        
        return "
            <li class=\"nav-item\">
                <a class=\"nav-link {$activeClass}\" data-section=\"{$item['section']}\" href=\"#\"{$styleAttr} onclick=\"showSection('{$item['section']}'); return false;\">
                    <i class=\"bi bi-{$item['icon']} me-2\"{$styleAttr}></i><span{$styleAttr}>{$item['label']}</span>
                </a>
            </li>";
    }
    
    /**
     * Generate logout link
     */
    public static function generateLogoutLink(): string {
        return '<a href="' . AuthHelper::getLogoutUrl() . '" class="btn btn-outline-danger w-100 mt-3 d-flex align-items-center justify-content-center">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
        </a>';
    }
    
    /**
     * Get navigation configuration for a role
     */
    public static function getNavigationConfig(string $role): array {
        return self::$navigationConfig[$role] ?? [];
    }
    
    /**
     * Add or update navigation item
     */
    public static function setNavigationItem(string $role, string $key, array $config): void {
        if (!isset(self::$navigationConfig[$role])) {
            self::$navigationConfig[$role] = [];
        }
        self::$navigationConfig[$role][$key] = $config;
    }
    
    /**
     * Remove navigation item
     */
    public static function removeNavigationItem(string $role, string $key): void {
        if (isset(self::$navigationConfig[$role][$key])) {
            unset(self::$navigationConfig[$role][$key]);
        }
    }
    
    /**
     * Check if navigation item exists
     */
    public static function hasNavigationItem(string $role, string $key): bool {
        return isset(self::$navigationConfig[$role][$key]);
    }
}
?>

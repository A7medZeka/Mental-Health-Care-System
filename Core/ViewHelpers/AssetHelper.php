<?php
/**
 * AssetHelper - Centralized Asset Management
 * Eliminates hardcoded asset URLs and enables versioning
 */

class AssetHelper {
    private static $assetConfig = [
        'css' => [
            'bootstrap' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
            'custom' => '../../assets/css/style.css',
            'version' => '1.0.0'
        ],
        'js' => [
            'bootstrap' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
            'icons' => 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css',
            'custom' => '../../assets/js/main.js',
            'version' => '1.0.0'
        ],
        'fonts' => [
            'google_fonts' => 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
            'fallback' => 'Arial, sans-serif'
        ],
        'images' => [
            'base_path' => '../../assets/images/',
            'logo' => 'logo.png',
            'favicon' => 'favicon.ico'
        ]
    ];
    
    private static $localFallbacks = [
        'css' => [
            'bootstrap' => '../../assets/css/bootstrap.min.css'
        ],
        'js' => [
            'bootstrap' => '../../assets/js/bootstrap.bundle.min.js',
            'icons' => '../../assets/css/bootstrap-icons.css'
        ]
    ];
    
    /**
     * Generate CSS includes
     */
    public static function generateCSSIncludes(): string {
        $html = '';
        
        // Google Fonts
        $html .= '<link href="' . self::$assetConfig['fonts']['google_fonts'] . '" rel="stylesheet">' . "\n";
        
        // Bootstrap CSS
        $html .= '<!-- Bootstrap 5 CSS -->' . "\n";
        $html .= '<link href="' . self::$assetConfig['css']['bootstrap'] . '" rel="stylesheet">' . "\n";
        
        // Custom CSS with version
        $customCss = self::$assetConfig['css']['custom'];
        $version = self::$assetConfig['css']['version'];
        $html .= '<!-- Custom CSS -->' . "\n";
        $html .= '<link rel="stylesheet" href="' . $customCss . '?v=' . $version . '">' . "\n";
        
        return $html;
    }
    
    /**
     * Generate JS includes
     */
    public static function generateJSIncludes(): string {
        $html = '';
        
        // Bootstrap JS
        $html .= '<!-- Bootstrap JS -->' . "\n";
        $html .= '<script src="' . self::$assetConfig['js']['bootstrap'] . '"></script>' . "\n";
        
        // Custom JS with version
        $customJs = self::$assetConfig['js']['custom'];
        $version = self::$assetConfig['js']['version'];
        $html .= '<!-- Custom JS -->' . "\n";
        $html .= '<script src="' . $customJs . '?v=' . $version . '"></script>' . "\n";
        
        return $html;
    }
    
    /**
     * Generate complete head section
     */
    public static function generateHeadSection(string $title, array $meta = []): string {
        $defaultMeta = [
            'charset' => 'UTF-8',
            'viewport' => 'width=device-width, initial-scale=1.0',
            'description' => 'Mental Health Care System - Professional therapy services',
            'keywords' => 'mental health, therapy, counseling, wellness'
        ];
        
        $meta = array_merge($defaultMeta, $meta);
        
        $html = '<head>' . "\n";
        $html .= '    <meta charset="' . $meta['charset'] . '">' . "\n";
        $html .= '    <meta name="viewport" content="' . $meta['viewport'] . '">' . "\n";
        $html .= '    <title>' . htmlspecialchars($title) . '</title>' . "\n";
        $html .= '    <meta name="description" content="' . htmlspecialchars($meta['description']) . '">' . "\n";
        $html .= '    <meta name="keywords" content="' . htmlspecialchars($meta['keywords']) . '">' . "\n";
        
        // CSS includes
        $html .= self::generateCSSIncludes();
        
        $html .= '</head>' . "\n";
        
        return $html;
    }
    
    /**
     * Get asset URL with versioning
     */
    public static function getAssetUrl(string $type, string $asset): string {
        if (!isset(self::$assetConfig[$type][$asset])) {
            return '';
        }
        
        $url = self::$assetConfig[$type][$asset];
        
        // Add version parameter for cache busting
        if ($type === 'css' || $type === 'js') {
            $version = self::$assetConfig[$type]['version'] ?? '1.0.0';
            $url .= '?v=' . $version;
        }
        
        return $url;
    }
    
    /**
     * Get image URL
     */
    public static function getImageUrl(string $imageName): string {
        $basePath = self::$assetConfig['images']['base_path'];
        return $basePath . $imageName;
    }
    
    /**
     * Generate image tag
     */
    public static function generateImageTag(string $imageName, array $attributes = []): string {
        $src = self::getImageUrl($imageName);
        $defaultAttrs = [
            'alt' => pathinfo($imageName, PATHINFO_FILENAME),
            'class' => ''
        ];
        
        $attributes = array_merge($defaultAttrs, $attributes);
        
        $html = '<img src="' . $src . '"';
        foreach ($attributes as $key => $value) {
            if ($key === 'class' && empty($value)) continue;
            $html .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }
        $html .= '>';
        
        return $html;
    }
    
    /**
     * Update asset configuration
     */
    public static function setAsset(string $type, string $key, string $value): void {
        if (!isset(self::$assetConfig[$type])) {
            self::$assetConfig[$type] = [];
        }
        self::$assetConfig[$type][$key] = $value;
    }
    
    /**
     * Set asset version
     */
    public static function setVersion(string $type, string $version): void {
        if (isset(self::$assetConfig[$type])) {
            self::$assetConfig[$type]['version'] = $version;
        }
    }
    
    /**
     * Get local fallback URL
     */
    public static function getLocalFallback(string $type, string $asset): string {
        if (!isset(self::$localFallbacks[$type][$asset])) {
            return '';
        }
        
        return self::$localFallbacks[$type][$asset];
    }
    
    /**
     * Generate asset with fallback
     */
    public static function generateAssetWithFallback(string $type, string $asset): string {
        $cdnUrl = self::getAssetUrl($type, $asset);
        $localUrl = self::getLocalFallback($type, $asset);
        
        if ($type === 'css') {
            return '<link href="' . $cdnUrl . '" rel="stylesheet" onerror="this.href=\'' . $localUrl . '\'">';
        } elseif ($type === 'js') {
            return '<script src="' . $cdnUrl . '" onerror="this.src=\'' . $localUrl . '\'"></script>';
        }
        
        return '';
    }
    
    /**
     * Generate font family CSS
     */
    public static function generateFontFamily(): string {
        $googleFonts = self::$assetConfig['fonts']['google_fonts'];
        $fallback = self::$assetConfig['fonts']['fallback'];
        
        return '<link href="' . $googleFonts . '" rel="stylesheet">
<style>
body {
    font-family: "Inter", ' . $fallback . ';
}
</style>';
    }
    
    /**
     * Check if asset exists (for development)
     */
    public static function assetExists(string $type, string $asset): bool {
        if ($type === 'images') {
            $path = self::getImageUrl($asset);
            return file_exists($path);
        }
        
        return isset(self::$assetConfig[$type][$asset]);
    }
    
    /**
     * Get all asset configuration
     */
    public static function getAssetConfig(): array {
        return self::$assetConfig;
    }
}
?>

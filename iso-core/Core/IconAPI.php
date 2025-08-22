<?php
/**
 * IconAPI Service Class
 * 
 * Provides methods for efficiently loading and displaying icons
 * For use by themes and plugins
 * 
 * @package Isotone
 * @since 0.3.0
 */

class IconAPI
{
    /**
     * Cache for loaded icons to prevent multiple loads
     */
    private static $cache = [];
    
    /**
     * Base URL for icon API endpoint
     */
    private static $apiUrl = null;
    
    /**
     * Get the icon API base URL
     */
    private static function getApiUrl()
    {
        if (self::$apiUrl === null) {
            // Determine base URL dynamically
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $basePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(dirname(__DIR__)));
            self::$apiUrl = $protocol . '://' . $host . $basePath . '/iso-api/icons.php';
        }
        return self::$apiUrl;
    }
    
    /**
     * Get an icon URL for lazy loading
     * 
     * @param string $name Icon name
     * @param string $style Icon style (outline, solid, micro)
     * @param array $params Additional parameters (size, class, color)
     * @return string Icon URL
     */
    public static function getIconUrl($name, $style = 'outline', $params = [])
    {
        $url = self::getApiUrl();
        $queryParams = [
            'name' => $name,
            'style' => $style
        ];
        
        // Add optional parameters
        if (isset($params['size'])) {
            $queryParams['size'] = $params['size'];
        }
        if (isset($params['class'])) {
            $queryParams['class'] = $params['class'];
        }
        if (isset($params['color'])) {
            $queryParams['color'] = $params['color'];
        }
        
        return $url . '?' . http_build_query($queryParams);
    }
    
    /**
     * Get an icon as an img tag for lazy loading
     * 
     * @param string $name Icon name
     * @param string $style Icon style (outline, solid, micro)
     * @param array $attributes HTML attributes for the img tag
     * @return string HTML img element
     */
    public static function getIconImg($name, $style = 'outline', $attributes = [])
    {
        $url = self::getIconUrl($name, $style, $attributes);
        
        // Default attributes
        $defaultAttrs = [
            'src' => $url,
            'alt' => $name . ' icon',
            'loading' => 'lazy',
            'decoding' => 'async'
        ];
        
        // Merge with provided attributes
        $attributes = array_merge($defaultAttrs, $attributes);
        
        // Remove API-specific attributes from HTML
        unset($attributes['icon'], $attributes['style'], $attributes['color']);
        
        // Build HTML
        $html = '<img';
        foreach ($attributes as $key => $value) {
            $html .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }
        $html .= '>';
        
        return $html;
    }
    
    /**
     * Get inline SVG (loads immediately, no lazy loading)
     * Uses caching to prevent multiple loads of the same icon
     * 
     * @param string $name Icon name
     * @param string $style Icon style (outline, solid, micro)
     * @param array $attributes SVG attributes
     * @return string SVG element or empty string if not found
     */
    public static function getIconSvg($name, $style = 'outline', $attributes = [])
    {
        $cacheKey = $name . '_' . $style;
        
        // Check cache first
        if (isset(self::$cache[$cacheKey])) {
            $svgPath = self::$cache[$cacheKey];
        } else {
            // Load the appropriate library only when needed
            $svgPath = self::loadIconPath($name, $style);
            self::$cache[$cacheKey] = $svgPath;
        }
        
        if (empty($svgPath)) {
            return '';
        }
        
        // Build SVG with attributes
        return self::buildSvg($svgPath, $style, $attributes);
    }
    
    /**
     * Load icon path from library
     */
    private static function loadIconPath($name, $style)
    {
        $libraryPath = dirname(__DIR__) . '/Core/';
        
        switch ($style) {
            case 'outline':
                require_once $libraryPath . 'IconLibrary.php';
                return IconLibrary::getIconPath($name);
                
            case 'solid':
                require_once $libraryPath . 'IconLibrarySolid.php';
                return IconLibrarySolid::getIconPath($name);
                
            case 'micro':
                require_once $libraryPath . 'IconLibraryMicro.php';
                return IconLibraryMicro::getIconPath($name);
                
            default:
                return '';
        }
    }
    
    /**
     * Build SVG element from path
     */
    private static function buildSvg($svgPath, $style, $attributes)
    {
        // Default attributes based on style
        $defaults = [];
        
        switch ($style) {
            case 'outline':
                $defaults = [
                    'xmlns' => 'http://www.w3.org/2000/svg',
                    'fill' => 'none',
                    'viewBox' => '0 0 24 24',
                    'stroke-width' => '1.5',
                    'stroke' => 'currentColor',
                    'aria-hidden' => 'true'
                ];
                break;
                
            case 'solid':
                $defaults = [
                    'xmlns' => 'http://www.w3.org/2000/svg',
                    'viewBox' => '0 0 24 24',
                    'fill' => 'currentColor',
                    'aria-hidden' => 'true'
                ];
                break;
                
            case 'micro':
                $defaults = [
                    'xmlns' => 'http://www.w3.org/2000/svg',
                    'viewBox' => '0 0 16 16',
                    'fill' => 'currentColor',
                    'aria-hidden' => 'true'
                ];
                break;
        }
        
        // Merge attributes
        $attributes = array_merge($defaults, $attributes);
        
        // Build SVG
        $svg = '<svg';
        foreach ($attributes as $key => $value) {
            $svg .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }
        $svg .= '>' . $svgPath . '</svg>';
        
        return $svg;
    }
    
    /**
     * Preload specific icons into cache
     * Useful for icons that are used frequently
     * 
     * @param array $icons Array of ['name' => 'icon-name', 'style' => 'outline']
     */
    public static function preloadIcons($icons)
    {
        foreach ($icons as $icon) {
            $name = $icon['name'] ?? '';
            $style = $icon['style'] ?? 'outline';
            
            if (!empty($name)) {
                $cacheKey = $name . '_' . $style;
                if (!isset(self::$cache[$cacheKey])) {
                    self::$cache[$cacheKey] = self::loadIconPath($name, $style);
                }
            }
        }
    }
    
    /**
     * Clear icon cache
     */
    public static function clearCache()
    {
        self::$cache = [];
    }
    
    /**
     * Get all cached icons (for debugging)
     */
    public static function getCachedIcons()
    {
        return array_keys(self::$cache);
    }
}
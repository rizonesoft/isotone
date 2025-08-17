<?php
/**
 * Theme Service
 * Handles theme management operations
 * 
 * @package Isotone
 */

namespace Isotone\Services;

use RedBeanPHP\R;
use ZipArchive;

class ThemeService
{
    private $themesPath;
    private $activeThemeOption = 'active_theme';
    
    public function __construct()
    {
        $this->themesPath = dirname(dirname(__DIR__)) . '/iso-content/themes';
        
        // Ensure themes directory exists
        if (!is_dir($this->themesPath)) {
            mkdir($this->themesPath, 0755, true);
        }
    }
    
    /**
     * Get all installed themes
     * 
     * @return array
     */
    public function getAllThemes(): array
    {
        $themes = [];
        
        if (!is_dir($this->themesPath)) {
            return $themes;
        }
        
        $directories = scandir($this->themesPath);
        
        foreach ($directories as $dir) {
            if ($dir === '.' || $dir === '..' || !is_dir($this->themesPath . '/' . $dir)) {
                continue;
            }
            
            $themeInfo = $this->getThemeInfo($dir);
            if ($themeInfo) {
                $themes[] = $themeInfo;
            }
        }
        
        return $themes;
    }
    
    /**
     * Get theme information from style.css
     * 
     * @param string $themeSlug
     * @return array|null
     */
    public function getThemeInfo(string $themeSlug): ?array
    {
        $themePath = $this->themesPath . '/' . $themeSlug;
        $styleFile = $themePath . '/style.css';
        
        if (!file_exists($styleFile)) {
            return null;
        }
        
        $styleContent = file_get_contents($styleFile);
        
        // Parse theme headers (WordPress-style)
        $headers = [
            'name' => 'Theme Name',
            'uri' => 'Theme URI',
            'author' => 'Author',
            'author_uri' => 'Author URI',
            'description' => 'Description',
            'version' => 'Version',
            'license' => 'License',
            'license_uri' => 'License URI',
            'text_domain' => 'Text Domain',
            'tags' => 'Tags'
        ];
        
        $themeInfo = [
            'slug' => $themeSlug,
            'path' => $themePath
        ];
        
        foreach ($headers as $field => $regex) {
            if (preg_match('/^[ \t\/*#@]*' . preg_quote($regex, '/') . ':(.*)$/mi', $styleContent, $match)) {
                $themeInfo[$field] = trim($match[1]);
            } else {
                $themeInfo[$field] = '';
            }
        }
        
        // Set default values
        if (empty($themeInfo['name'])) {
            $themeInfo['name'] = ucfirst($themeSlug);
        }
        
        if (empty($themeInfo['version'])) {
            $themeInfo['version'] = '1.0.0';
        }
        
        if (empty($themeInfo['description'])) {
            $themeInfo['description'] = 'A theme for Isotone';
        }
        
        return $themeInfo;
    }
    
    /**
     * Get the active theme
     * 
     * @return array|null
     */
    public function getActiveTheme(): ?array
    {
        $setting = R::findOne('settings', 'setting_key = ?', [$this->activeThemeOption]);
        
        if (!$setting) {
            // Default to first available theme or neutron if it exists
            $themes = $this->getAllThemes();
            
            // Look for neutron theme first
            foreach ($themes as $theme) {
                if ($theme['slug'] === 'neutron') {
                    $this->activateTheme('neutron');
                    return $theme;
                }
            }
            
            // Otherwise use first theme
            if (!empty($themes)) {
                $firstTheme = $themes[0];
                $this->activateTheme($firstTheme['slug']);
                return $firstTheme;
            }
            
            return null;
        }
        
        $activeSlug = $setting->setting_value;
        return $this->getThemeInfo($activeSlug);
    }
    
    /**
     * Activate a theme
     * 
     * @param string $themeSlug
     * @return bool
     */
    public function activateTheme(string $themeSlug): bool
    {
        // Verify theme exists
        $themeInfo = $this->getThemeInfo($themeSlug);
        if (!$themeInfo) {
            return false;
        }
        
        // Save to database
        $setting = R::findOne('settings', 'setting_key = ?', [$this->activeThemeOption]);
        
        if (!$setting) {
            $setting = R::dispense('settings');
            $setting->setting_key = $this->activeThemeOption;
            $setting->setting_type = 'theme';
        }
        
        $setting->setting_value = $themeSlug;
        $setting->updated_at = date('Y-m-d H:i:s');
        
        R::store($setting);
        
        // Trigger theme activation hook
        $this->triggerThemeHook($themeSlug, 'activate');
        
        return true;
    }
    
    /**
     * Delete a theme
     * 
     * @param string $themeSlug
     * @return bool
     */
    public function deleteTheme(string $themeSlug): bool
    {
        // Don't delete active theme
        $activeTheme = $this->getActiveTheme();
        if ($activeTheme && $activeTheme['slug'] === $themeSlug) {
            return false;
        }
        
        $themePath = $this->themesPath . '/' . $themeSlug;
        
        if (!is_dir($themePath)) {
            return false;
        }
        
        // Trigger deactivation hook
        $this->triggerThemeHook($themeSlug, 'deactivate');
        
        // Recursively delete theme directory
        return $this->deleteDirectory($themePath);
    }
    
    /**
     * Upload and install a theme from ZIP file
     * 
     * @param array $uploadedFile $_FILES array element
     * @return array ['success' => bool, 'message' => string]
     */
    public function uploadTheme(array $uploadedFile): array
    {
        // Check for upload errors
        if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'message' => 'Upload failed with error code: ' . $uploadedFile['error']
            ];
        }
        
        // Verify ZIP file
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $uploadedFile['tmp_name']);
        finfo_close($finfo);
        
        if ($mimeType !== 'application/zip') {
            return [
                'success' => false,
                'message' => 'Invalid file type. Please upload a ZIP file.'
            ];
        }
        
        // Extract ZIP
        $zip = new ZipArchive();
        if ($zip->open($uploadedFile['tmp_name']) !== true) {
            return [
                'success' => false,
                'message' => 'Failed to open ZIP file.'
            ];
        }
        
        // Get theme folder name from ZIP
        $themeFolderName = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (strpos($filename, '/') !== false) {
                $parts = explode('/', $filename);
                $themeFolderName = $parts[0];
                break;
            }
        }
        
        if (!$themeFolderName) {
            $zip->close();
            return [
                'success' => false,
                'message' => 'Invalid theme structure in ZIP file.'
            ];
        }
        
        // Check if theme already exists
        if (is_dir($this->themesPath . '/' . $themeFolderName)) {
            $zip->close();
            return [
                'success' => false,
                'message' => 'Theme already exists. Please delete it first.'
            ];
        }
        
        // Extract to themes directory
        $extractResult = $zip->extractTo($this->themesPath);
        $zip->close();
        
        if (!$extractResult) {
            return [
                'success' => false,
                'message' => 'Failed to extract theme files.'
            ];
        }
        
        // Verify theme has style.css
        if (!file_exists($this->themesPath . '/' . $themeFolderName . '/style.css')) {
            $this->deleteDirectory($this->themesPath . '/' . $themeFolderName);
            return [
                'success' => false,
                'message' => 'Invalid theme: missing style.css file.'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Theme uploaded successfully!'
        ];
    }
    
    /**
     * Trigger theme activation/deactivation hooks
     * 
     * @param string $themeSlug
     * @param string $action 'activate' or 'deactivate'
     */
    private function triggerThemeHook(string $themeSlug, string $action): void
    {
        $functionsFile = $this->themesPath . '/' . $themeSlug . '/functions.php';
        
        if (file_exists($functionsFile)) {
            // Include theme functions
            require_once $functionsFile;
            
            // Call activation/deactivation function if it exists
            $functionName = 'theme_' . $action;
            if (function_exists($functionName)) {
                call_user_func($functionName);
            }
        }
    }
    
    /**
     * Recursively delete a directory
     * 
     * @param string $dir
     * @return bool
     */
    private function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        
        return rmdir($dir);
    }
}
<?php
/**
 * Isotone - Version Management System
 * 
 * @copyright  2025 Rizonetech (Pty) Ltd
 * @license    MIT License
 * @author     Rizonetech Development Team
 */

declare(strict_types=1);

namespace Isotone\Core;

use Isotone\Services\DatabaseService;

class Version
{
    /**
     * Minimum PHP version required
     */
    public const MIN_PHP_VERSION = '8.3.0';
    
    /**
     * Version configuration file
     */
    private const VERSION_FILE = __DIR__ . '/../../iso-automation/version.json';
    
    /**
     * Version data cache
     */
    private static ?array $versionData = null;
    
    /**
     * Development stage constants
     */
    public const STAGE_ALPHA = 'alpha';
    public const STAGE_BETA = 'beta';
    public const STAGE_RC = 'rc';
    public const STAGE_STABLE = 'stable';
    
    /**
     * Load version data from file
     */
    private static function loadVersionData(): array
    {
        if (self::$versionData === null) {
            if (file_exists(self::VERSION_FILE)) {
                $json = file_get_contents(self::VERSION_FILE);
                self::$versionData = json_decode($json, true);
            } else {
                // Fallback defaults
                self::$versionData = [
                    'current' => '0.1.0-alpha',
                    'schema' => '1.0.0',
                    'codename' => 'Genesis',
                    'release_date' => '2025-01-13',
                    'history' => []
                ];
            }
        }
        return self::$versionData;
    }
    
    /**
     * Save version data to file
     */
    private static function saveVersionData(array $data): bool
    {
        self::$versionData = $data;
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return file_put_contents(self::VERSION_FILE, $json) !== false;
    }
    
    /**
     * Get current version information
     */
    public static function current(): array
    {
        $data = self::loadVersionData();
        return [
            'version' => $data['current'],
            'schema' => $data['schema'],
            'codename' => $data['codename'],
            'stage' => self::getStage($data['current']),
            'release_date' => $data['release_date'],
            'php_version' => PHP_VERSION,
            'php_required' => self::MIN_PHP_VERSION
        ];
    }
    
    /**
     * Get version stage (alpha, beta, rc, stable)
     */
    public static function getStage(?string $version = null): string
    {
        if ($version === null) {
            $data = self::loadVersionData();
            $version = $data['current'];
        }
        
        if (strpos($version, '-alpha') !== false) {
            return self::STAGE_ALPHA;
        } elseif (strpos($version, '-beta') !== false) {
            return self::STAGE_BETA;
        } elseif (strpos($version, '-rc') !== false) {
            return self::STAGE_RC;
        }
        return self::STAGE_STABLE;
    }
    
    /**
     * Compare versions using semantic versioning
     */
    public static function compare(string $version1, string $version2): int
    {
        return version_compare($version1, $version2);
    }
    
    /**
     * Check if current version meets requirement
     */
    public static function meets(string $requirement): bool
    {
        $data = self::loadVersionData();
        return version_compare($data['current'], $requirement, '>=');
    }
    
    /**
     * Get version history
     */
    public static function getHistory(): array
    {
        $data = self::loadVersionData();
        $history = [];
        foreach ($data['history'] as $item) {
            $history[$item['version']] = $item;
        }
        return $history;
    }
    
    /**
     * Get features for a specific version
     */
    public static function getFeatures(?string $version = null): array
    {
        $data = self::loadVersionData();
        $version = $version ?? $data['current'];
        
        foreach ($data['history'] as $item) {
            if ($item['version'] === $version) {
                return $item['features'] ?? [];
            }
        }
        return [];
    }
    
    /**
     * Check if PHP version meets requirements
     */
    public static function checkPHPVersion(): bool
    {
        return version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '>=');
    }
    
    /**
     * Get semantic version parts
     */
    public static function parse(?string $version = null): array
    {
        if ($version === null) {
            $data = self::loadVersionData();
            $version = $data['current'];
        }
        
        // Remove pre-release identifiers for parsing
        $cleanVersion = preg_replace('/-.*$/', '', $version);
        $parts = explode('.', $cleanVersion);
        
        return [
            'major' => (int)($parts[0] ?? 0),
            'minor' => (int)($parts[1] ?? 0),
            'patch' => (int)($parts[2] ?? 0),
            'prerelease' => self::getPrerelease($version),
            'full' => $version
        ];
    }
    
    /**
     * Get pre-release identifier
     */
    private static function getPrerelease(string $version): ?string
    {
        if (preg_match('/-(.+)$/', $version, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    /**
     * Generate next version number
     */
    public static function getNextVersion(string $type = 'patch'): string
    {
        $current = self::parse();
        
        switch ($type) {
            case 'major':
                return sprintf('%d.0.0', $current['major'] + 1);
            case 'minor':
                return sprintf('%d.%d.0', $current['major'], $current['minor'] + 1);
            case 'patch':
            default:
                return sprintf('%d.%d.%d', 
                    $current['major'], 
                    $current['minor'], 
                    $current['patch'] + 1
                );
        }
    }
    
    /**
     * Bump version number
     */
    public static function bump(string $type = 'patch', ?string $stage = null, ?string $codename = null): string
    {
        $data = self::loadVersionData();
        $current = self::parse($data['current']);
        
        // Calculate new version
        switch ($type) {
            case 'major':
                $newVersion = sprintf('%d.0.0', $current['major'] + 1);
                break;
            case 'minor':
                $newVersion = sprintf('%d.%d.0', $current['major'], $current['minor'] + 1);
                break;
            case 'patch':
            default:
                $newVersion = sprintf('%d.%d.%d', 
                    $current['major'], 
                    $current['minor'], 
                    $current['patch'] + 1
                );
        }
        
        // Preserve current stage unless explicitly specified
        if ($stage !== null) {
            $newVersion .= '-' . $stage;
        } elseif ($current['prerelease'] !== null) {
            // Keep the current stage if no new stage specified
            $newVersion .= '-' . $current['prerelease'];
        }
        
        // Update version data
        $oldVersion = $data['current'];
        $data['current'] = $newVersion;
        $data['release_date'] = date('Y-m-d');
        
        if ($codename !== null) {
            $data['codename'] = $codename;
        }
        
        // Add to history
        $data['history'][] = [
            'version' => $newVersion,
            'date' => date('Y-m-d'),
            'codename' => $data['codename'],
            'features' => [],
            'breaking_changes' => [],
            'from_version' => $oldVersion
        ];
        
        // Save changes
        self::saveVersionData($data);
        
        return $newVersion;
    }
    
    /**
     * Set version directly
     */
    public static function set(string $version, ?string $codename = null): bool
    {
        $data = self::loadVersionData();
        $oldVersion = $data['current'];
        
        $data['current'] = $version;
        $data['release_date'] = date('Y-m-d');
        
        if ($codename !== null) {
            $data['codename'] = $codename;
        }
        
        // Add to history if different
        if ($version !== $oldVersion) {
            $data['history'][] = [
                'version' => $version,
                'date' => date('Y-m-d'),
                'codename' => $data['codename'],
                'features' => [],
                'breaking_changes' => [],
                'from_version' => $oldVersion
            ];
        }
        
        return self::saveVersionData($data);
    }
    
    /**
     * Add features to current version
     */
    public static function addFeatures(array $features): bool
    {
        $data = self::loadVersionData();
        
        // Find current version in history
        foreach ($data['history'] as &$item) {
            if ($item['version'] === $data['current']) {
                $item['features'] = array_merge($item['features'] ?? [], $features);
                return self::saveVersionData($data);
            }
        }
        
        return false;
    }
    
    /**
     * Check for updates (placeholder for future implementation)
     */
    public static function checkForUpdates(): array
    {
        $data = self::loadVersionData();
        // In future, this would check a remote API
        return [
            'current' => $data['current'],
            'latest' => $data['current'],
            'update_available' => false,
            'update_url' => null
        ];
    }
    
    /**
     * Get compatibility information
     */
    public static function getCompatibility(): array
    {
        $data = self::loadVersionData();
        return [
            'php' => [
                'required' => self::MIN_PHP_VERSION,
                'current' => PHP_VERSION,
                'compatible' => self::checkPHPVersion()
            ],
            'database' => [
                'schema_version' => $data['schema'],
                'compatible' => true // Check actual DB version in future
            ],
            'extensions' => [
                'required' => [
                    'pdo' => extension_loaded('pdo'),
                    'mbstring' => extension_loaded('mbstring'),
                    'json' => extension_loaded('json'),
                    'openssl' => extension_loaded('openssl')
                ]
            ]
        ];
    }
    
    /**
     * Format version for display
     */
    public static function format(?string $version = null, bool $includeCodename = true): string
    {
        $data = self::loadVersionData();
        $version = $version ?? $data['current'];
        $formatted = "v{$version}";
        
        if ($includeCodename && $version === $data['current']) {
            $formatted .= " (" . $data['codename'] . ")";
        }
        
        return $formatted;
    }
    
    /**
     * Get version badge HTML
     */
    public static function getBadge(): string
    {
        $stage = self::getStage();
        $badgeConfig = match($stage) {
            'alpha' => ['bg' => '#DC2626', 'text' => '#FFFFFF'], // Red background, white text
            'beta' => ['bg' => '#D97706', 'text' => '#FFFFFF'], // Orange background, white text  
            'rc' => ['bg' => '#0EA5E9', 'text' => '#FFFFFF'], // Blue background, white text
            default => ['bg' => '#16A34A', 'text' => '#FFFFFF'] // Green background, white text (stable)
        };
        
        return sprintf(
            '<span style="background: %s; color: %s; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; letter-spacing: 0.05em; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">%s</span>',
            $badgeConfig['bg'],
            $badgeConfig['text'],
            strtoupper($stage)
        );
    }
}
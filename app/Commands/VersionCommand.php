<?php
/**
 * Isotone - Version CLI Command
 * 
 * @copyright  2025 Rizonetech (Pty) Ltd
 * @license    MIT License
 * @author     Rizonetech Development Team
 */

declare(strict_types=1);

namespace Isotone\Commands;

use Isotone\Core\Version;
use Isotone\Core\Migration;

class VersionCommand
{
    /**
     * Display version information
     */
    public static function show(): void
    {
        $info = Version::current();
        
        echo "\n";
        echo "Isotone " . Version::format() . "\n";
        echo str_repeat('=', 40) . "\n\n";
        
        echo "Core Version:    " . $info['version'] . "\n";
        echo "Stage:           " . ucfirst($info['stage']) . "\n";
        echo "Codename:        " . $info['codename'] . "\n";
        echo "Release Date:    " . $info['release_date'] . "\n";
        echo "Schema Version:  " . $info['schema'] . "\n";
        echo "\n";
        
        echo "Environment:\n";
        echo "  PHP Version:   " . $info['php_version'] . "\n";
        echo "  PHP Required:  " . $info['php_required'] . "\n";
        echo "  OS:            " . PHP_OS . "\n";
        echo "\n";
    }
    
    /**
     * Check compatibility
     */
    public static function check(): void
    {
        $compat = Version::getCompatibility();
        
        echo "\nCompatibility Check\n";
        echo str_repeat('=', 40) . "\n\n";
        
        // PHP Version
        $phpStatus = $compat['php']['compatible'] ? '✓' : '✗';
        echo "PHP Version:     $phpStatus\n";
        echo "  Required:      " . $compat['php']['required'] . "\n";
        echo "  Current:       " . $compat['php']['current'] . "\n\n";
        
        // Extensions
        echo "Extensions:\n";
        foreach ($compat['extensions']['required'] as $ext => $loaded) {
            $status = $loaded ? '✓' : '✗';
            echo "  $ext: $status\n";
        }
        echo "\n";
        
        // Database
        echo "Database:\n";
        echo "  Schema:        " . $compat['database']['schema_version'] . "\n";
        $dbStatus = $compat['database']['compatible'] ? '✓' : '✗';
        echo "  Compatible:    $dbStatus\n";
        echo "\n";
    }
    
    /**
     * List version history
     */
    public static function history(): void
    {
        $history = Version::getHistory();
        
        echo "\nVersion History\n";
        echo str_repeat('=', 40) . "\n\n";
        
        foreach ($history as $version => $details) {
            echo "v$version - {$details['codename']} ({$details['date']})\n";
            foreach ($details['features'] as $feature) {
                echo "  • $feature\n";
            }
            if (!empty($details['breaking_changes'])) {
                echo "  Breaking Changes:\n";
                foreach ($details['breaking_changes'] as $change) {
                    echo "    ⚠ $change\n";
                }
            }
            echo "\n";
        }
    }
}
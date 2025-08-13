<?php
/**
 * Isotone CMS - Changelog Generator
 * 
 * @copyright  2025 Rizonetech (Pty) Ltd
 * @license    MIT License
 * @author     Rizonetech Development Team
 */

declare(strict_types=1);

namespace Isotone\Commands;

use Isotone\Core\Version;

class ChangelogCommand
{
    /**
     * Generate changelog from version history
     */
    public static function generate(): string
    {
        $history = Version::getHistory();
        $changelog = "# Changelog\n\n";
        $changelog .= "All notable changes to Isotone CMS will be documented in this file.\n\n";
        $changelog .= "The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),\n";
        $changelog .= "and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).\n\n";
        
        // Sort versions in reverse order (newest first)
        $versions = array_keys($history);
        usort($versions, function($a, $b) {
            return version_compare($b, $a);
        });
        
        foreach ($versions as $version) {
            $item = $history[$version];
            $changelog .= "## [{$version}]";
            
            if (!empty($item['codename'])) {
                $changelog .= " - {$item['codename']}";
            }
            
            $changelog .= " - {$item['date']}\n\n";
            
            // Added features
            if (!empty($item['features'])) {
                $changelog .= "### Added\n";
                foreach ($item['features'] as $feature) {
                    $changelog .= "- {$feature}\n";
                }
                $changelog .= "\n";
            }
            
            // Changed items
            if (!empty($item['changed'])) {
                $changelog .= "### Changed\n";
                foreach ($item['changed'] as $change) {
                    $changelog .= "- {$change}\n";
                }
                $changelog .= "\n";
            }
            
            // Fixed bugs
            if (!empty($item['fixed'])) {
                $changelog .= "### Fixed\n";
                foreach ($item['fixed'] as $fix) {
                    $changelog .= "- {$fix}\n";
                }
                $changelog .= "\n";
            }
            
            // Breaking changes
            if (!empty($item['breaking_changes'])) {
                $changelog .= "### ⚠ BREAKING CHANGES\n";
                foreach ($item['breaking_changes'] as $breaking) {
                    $changelog .= "- {$breaking}\n";
                }
                $changelog .= "\n";
            }
            
            // Deprecated
            if (!empty($item['deprecated'])) {
                $changelog .= "### Deprecated\n";
                foreach ($item['deprecated'] as $deprecated) {
                    $changelog .= "- {$deprecated}\n";
                }
                $changelog .= "\n";
            }
            
            // Security
            if (!empty($item['security'])) {
                $changelog .= "### Security\n";
                foreach ($item['security'] as $security) {
                    $changelog .= "- {$security}\n";
                }
                $changelog .= "\n";
            }
        }
        
        return $changelog;
    }
    
    /**
     * Save changelog to file
     */
    public static function save(): bool
    {
        $changelog = self::generate();
        $file = dirname(__DIR__, 2) . '/CHANGELOG.md';
        return file_put_contents($file, $changelog) !== false;
    }
    
    /**
     * Display changelog
     */
    public static function show(): void
    {
        echo self::generate();
    }
}
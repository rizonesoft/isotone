<?php
/**
 * Hooks Implementation Analyzer
 * 
 * Scans codebase for hook implementations and compares with SystemHooks
 * 
 * @package Isotone\Automation\Analyzers
 */

namespace Isotone\Automation\Analyzers;

use Isotone\Core\SystemHooks;

class HooksAnalyzer
{
    private $implementedHooks = [
        'actions' => [],
        'filters' => []
    ];
    
    private $systemHooks = [];
    private $scanStats = [
        'files_scanned' => 0,
        'hooks_found' => 0,
        'system_hooks_total' => 0,
        'system_hooks_implemented' => 0,
        'orphan_hooks' => 0
    ];
    
    /**
     * Directories to scan for hooks
     */
    private $scanDirectories = [
        'app',
        'iso-admin',
        'iso-content/themes',
        'iso-content/plugins'
    ];
    
    private $rootPath;
    private $quiet = false;
    
    public function __construct()
    {
        $this->rootPath = dirname(dirname(dirname(__DIR__)));
    }
    
    /**
     * Set quiet mode
     */
    public function setQuiet($quiet = true)
    {
        $this->quiet = $quiet;
    }
    
    /**
     * Run the analysis
     */
    public function analyze()
    {
        if (!$this->quiet) {
            echo "🔍 Hook Implementation Scanner\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        }
        
        // Load system hooks
        if (!$this->quiet) {
            echo "📋 Step 1/6: Loading system-defined hooks...\n";
        }
        $this->loadSystemHooks();
        
        // Scan codebase for implementations
        if (!$this->quiet) {
            echo "🔎 Step 2/6: Scanning codebase for hook implementations...\n";
        }
        $this->scanCodebase();
        
        // Analyze and compare
        if (!$this->quiet) {
            echo "📊 Step 3/6: Analyzing hook usage patterns...\n";
        }
        $this->analyzeImplementation();
        
        // Generate outputs
        if (!$this->quiet) {
            echo "📝 Step 4/6: Generating implementation report...\n";
        }
        $this->generateImplementationReport();
        
        if (!$this->quiet) {
            echo "💾 Step 5/6: Updating hooks.json...\n";
        }
        $this->generateHooksJson();
        
        if (!$this->quiet) {
            echo "📚 Step 6/6: Updating API reference documentation...\n";
        }
        $this->updateApiReference();
        
        if (!$this->quiet) {
            echo "\n✅ Hook documentation generated successfully!\n";
            $this->printSummary();
        }
        
        return $this->scanStats;
    }
    
    /**
     * Load system-defined hooks
     */
    private function loadSystemHooks()
    {
        if (!$this->quiet) {
            echo "📚 Loading system hooks...\n";
        }
        
        // Load SystemHooks class if not already loaded
        $systemHooksFile = $this->rootPath . '/app/Core/SystemHooks.php';
        if (file_exists($systemHooksFile)) {
            require_once $systemHooksFile;
        }
        
        $this->systemHooks = SystemHooks::getSystemHooks();
        $this->scanStats['system_hooks_total'] = count($this->systemHooks);
        
        if (!$this->quiet) {
            echo "   Found " . $this->scanStats['system_hooks_total'] . " system-defined hooks\n\n";
        }
    }
    
    /**
     * Scan the codebase for hook usage
     */
    private function scanCodebase()
    {
        if (!$this->quiet) {
            echo "🔎 Scanning codebase for hook implementations...\n";
        }
        
        foreach ($this->scanDirectories as $dir) {
            $fullPath = $this->rootPath . '/' . $dir;
            if (is_dir($fullPath)) {
                if (!$this->quiet) {
                    echo "   Scanning: $dir/\n";
                }
                $this->scanDirectory($fullPath);
            }
        }
        
        if (!$this->quiet) {
            echo "\n";
        }
    }
    
    /**
     * Scan a directory recursively
     */
    private function scanDirectory($dir)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $this->scanFile($file->getPathname());
            }
        }
    }
    
    /**
     * Scan a PHP file for hooks
     */
    private function scanFile($filepath)
    {
        $content = file_get_contents($filepath);
        $relativePath = str_replace($this->rootPath . '/', '', $filepath);
        $this->scanStats['files_scanned']++;
        
        // Find do_action calls
        if (preg_match_all(
            '/do_action\s*\(\s*[\'"]([^\'"]+)[\'"]/s',
            $content,
            $matches,
            PREG_OFFSET_CAPTURE
        )) {
            foreach ($matches[1] as $match) {
                $hook = $match[0];
                $line = $this->getLineNumber($content, $match[1]);
                
                if (!isset($this->implementedHooks['actions'][$hook])) {
                    $this->implementedHooks['actions'][$hook] = [];
                    $this->scanStats['hooks_found']++;
                }
                
                $this->implementedHooks['actions'][$hook][] = [
                    'file' => $relativePath,
                    'line' => $line
                ];
            }
        }
        
        // Find apply_filters calls
        if (preg_match_all(
            '/apply_filters\s*\(\s*[\'"]([^\'"]+)[\'"]/',
            $content,
            $matches,
            PREG_OFFSET_CAPTURE
        )) {
            foreach ($matches[1] as $match) {
                $hook = $match[0];
                $line = $this->getLineNumber($content, $match[1]);
                
                if (!isset($this->implementedHooks['filters'][$hook])) {
                    $this->implementedHooks['filters'][$hook] = [];
                    $this->scanStats['hooks_found']++;
                }
                
                $this->implementedHooks['filters'][$hook][] = [
                    'file' => $relativePath,
                    'line' => $line
                ];
            }
        }
    }
    
    /**
     * Get line number from offset
     */
    private function getLineNumber($content, $offset)
    {
        return substr_count(substr($content, 0, $offset), "\n") + 1;
    }
    
    /**
     * Analyze implementation vs system hooks
     */
    private function analyzeImplementation()
    {
        if (!$this->quiet) {
            echo "📊 Analyzing implementation coverage...\n";
        }
        
        // Count implemented system hooks
        foreach ($this->systemHooks as $hookName => $hookData) {
            $type = $hookData['type'];
            if ($type === 'action' && isset($this->implementedHooks['actions'][$hookName])) {
                $this->scanStats['system_hooks_implemented']++;
            } elseif ($type === 'filter' && isset($this->implementedHooks['filters'][$hookName])) {
                $this->scanStats['system_hooks_implemented']++;
            }
        }
        
        // Find orphan hooks (implemented but not in system)
        $allImplemented = array_merge(
            array_keys($this->implementedHooks['actions']),
            array_keys($this->implementedHooks['filters'])
        );
        
        foreach ($allImplemented as $hook) {
            if (!SystemHooks::isSystemHook($hook)) {
                $this->scanStats['orphan_hooks']++;
            }
        }
        
        if (!$this->quiet) {
            echo "   System hooks implemented: " . $this->scanStats['system_hooks_implemented'] . "/" . $this->scanStats['system_hooks_total'] . "\n";
            echo "   Orphan hooks found: " . $this->scanStats['orphan_hooks'] . "\n\n";
        }
    }
    
    /**
     * Save stats to JSON file
     */
    private function saveStatsToJson()
    {
        // Save stats to JSON file instead for now
        $statsFile = $this->rootPath . '/storage/hook-stats.json';
        
        // Ensure directory exists
        $dir = dirname($statsFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Calculate coverage
        $coverage = $this->scanStats['system_hooks_total'] > 0 
            ? round(($this->scanStats['system_hooks_implemented'] / $this->scanStats['system_hooks_total']) * 100, 1)
            : 0;
        
        $stats = [
            'generated_at' => date('Y-m-d H:i:s'),
            'system_hooks_defined' => $this->scanStats['system_hooks_total'],
            'system_hooks_implemented' => $this->scanStats['system_hooks_implemented'],
            'implementation_coverage' => $coverage,
            'orphan_hooks' => $this->scanStats['orphan_hooks'],
            'files_scanned' => $this->scanStats['files_scanned'],
            'hooks_found' => $this->scanStats['hooks_found']
        ];
        
        file_put_contents($statsFile, json_encode($stats, JSON_PRETTY_PRINT));
        
        if (!$this->quiet) {
            echo "   ✅ Hook statistics saved to storage/hook-stats.json\n";
        }
        
    }
    
    /**
     * Generate implementation report (saves to JSON files)
     */
    private function generateImplementationReport()
    {
        if (!$this->quiet) {
            echo "📝 Saving hook statistics...\n";
        }
        
        // Save stats to JSON file
        $this->saveStatsToJson();
        
        // No longer generate HOOKS.md file
        return;
        
        $content = "# Hook Implementation Report\n\n";
        $content .= "> Auto-generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Summary
        $content .= "## Summary\n\n";
        $coverage = $this->scanStats['system_hooks_total'] > 0 
            ? round(($this->scanStats['system_hooks_implemented'] / $this->scanStats['system_hooks_total']) * 100, 1)
            : 0;
        
        $content .= "- **System Hooks Defined**: " . $this->scanStats['system_hooks_total'] . "\n";
        $content .= "- **System Hooks Implemented**: " . $this->scanStats['system_hooks_implemented'] . "\n";
        $content .= "- **Implementation Coverage**: " . $coverage . "%\n";
        $content .= "- **Orphan Hooks**: " . $this->scanStats['orphan_hooks'] . " (implemented but not in system)\n";
        $content .= "- **Files Scanned**: " . $this->scanStats['files_scanned'] . "\n\n";
        
        // Implementation Status by Category
        $content .= "## System Hooks Implementation Status\n\n";
        
        $categories = [
            'Initialization' => ['init', 'iso_loaded', 'after_setup_theme'],
            'Content' => ['iso_head', 'iso_footer', 'iso_before_content', 'iso_after_content', 'the_content', 'the_title'],
            'Admin' => ['admin_init', 'admin_menu', 'admin_head', 'admin_footer'],
            'User & Auth' => ['iso_login', 'iso_logout', 'iso_register_user'],
            'Database' => ['iso_before_save', 'iso_after_save', 'iso_before_delete', 'iso_after_delete'],
            'Theme & Plugin' => ['iso_theme_activation', 'iso_plugin_activation', 'widgets_init'],
            'API & AJAX' => ['rest_api_init', 'iso_ajax_', 'iso_rest_before_request']
        ];
        
        foreach ($categories as $category => $patterns) {
            $content .= "### $category\n\n";
            $content .= "| Hook | Type | Status | Locations |\n";
            $content .= "|------|------|--------|----------|\n";
            
            foreach ($this->systemHooks as $hookName => $hookData) {
                // Check if hook belongs to this category
                $inCategory = false;
                foreach ($patterns as $pattern) {
                    if (strpos($hookName, $pattern) !== false) {
                        $inCategory = true;
                        break;
                    }
                }
                
                if (!$inCategory) continue;
                
                $type = ucfirst($hookData['type']);
                $implemented = false;
                $locations = [];
                
                if ($hookData['type'] === 'action' && isset($this->implementedHooks['actions'][$hookName])) {
                    $implemented = true;
                    $locations = $this->implementedHooks['actions'][$hookName];
                } elseif ($hookData['type'] === 'filter' && isset($this->implementedHooks['filters'][$hookName])) {
                    $implemented = true;
                    $locations = $this->implementedHooks['filters'][$hookName];
                }
                
                $status = $implemented ? '✅' : '❌';
                $locationStr = '';
                if (!empty($locations)) {
                    $firstLoc = $locations[0];
                    $locationStr = "`{$firstLoc['file']}:{$firstLoc['line']}`";
                    if (count($locations) > 1) {
                        $locationStr .= " +" . (count($locations) - 1) . " more";
                    }
                }
                
                $content .= "| `$hookName` | $type | $status | $locationStr |\n";
            }
            
            $content .= "\n";
        }
        
        // Orphan Hooks Section
        if ($this->scanStats['orphan_hooks'] > 0) {
            $content .= "## Orphan Hooks\n\n";
            $content .= "These hooks are implemented in the code but not defined in SystemHooks:\n\n";
            
            foreach ($this->implementedHooks['actions'] as $hook => $locations) {
                if (!SystemHooks::isSystemHook($hook)) {
                    $content .= "- **$hook** (action) - `{$locations[0]['file']}:{$locations[0]['line']}`\n";
                }
            }
            
            foreach ($this->implementedHooks['filters'] as $hook => $locations) {
                if (!SystemHooks::isSystemHook($hook)) {
                    $content .= "- **$hook** (filter) - `{$locations[0]['file']}:{$locations[0]['line']}`\n";
                }
            }
            
            $content .= "\n";
        }
        
        // Implementation Details
        $content .= "## Implementation Details\n\n";
        $content .= "### Implemented Hooks\n\n";
        
        foreach ($this->implementedHooks['actions'] as $hook => $locations) {
            if (SystemHooks::isSystemHook($hook)) {
                $content .= "#### `$hook` (action)\n";
                foreach ($locations as $loc) {
                    $content .= "- `{$loc['file']}:{$loc['line']}`\n";
                }
                $content .= "\n";
            }
        }
        
        foreach ($this->implementedHooks['filters'] as $hook => $locations) {
            if (SystemHooks::isSystemHook($hook)) {
                $content .= "#### `$hook` (filter)\n";
                foreach ($locations as $loc) {
                    $content .= "- `{$loc['file']}:{$loc['line']}`\n";
                }
                $content .= "\n";
            }
        }
        
        file_put_contents($reportFile, $content);
        
        if (!$this->quiet) {
            echo "   Generated: HOOKS.md\n";
        }
    }
    
    /**
     * Generate JSON data for Hooks Explorer
     */
    private function generateHooksJson()
    {
        if (!$this->quiet) {
            echo "📄 Generating hooks data for explorer...\n";
        }
        
        $jsonFile = $this->rootPath . '/storage/hooks-implementation.json';
        
        // Ensure directory exists
        $dir = dirname($jsonFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $data = [
            'generated' => date('Y-m-d H:i:s'),
            'stats' => $this->scanStats,
            'implementations' => $this->implementedHooks,
            'system_hooks' => []
        ];
        
        // Add implementation status to system hooks
        foreach ($this->systemHooks as $hookName => $hookData) {
            $implemented = false;
            $locations = [];
            
            if ($hookData['type'] === 'action' && isset($this->implementedHooks['actions'][$hookName])) {
                $implemented = true;
                $locations = $this->implementedHooks['actions'][$hookName];
            } elseif ($hookData['type'] === 'filter' && isset($this->implementedHooks['filters'][$hookName])) {
                $implemented = true;
                $locations = $this->implementedHooks['filters'][$hookName];
            }
            
            $data['system_hooks'][$hookName] = [
                'type' => $hookData['type'],
                'description' => $hookData['description'],
                'since' => $hookData['since'],
                'implemented' => $implemented,
                'locations' => $locations
            ];
        }
        
        file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
        
        if (!$this->quiet) {
            echo "   Generated: storage/hooks-implementation.json\n";
        }
    }
    
    /**
     * Update API reference documentation
     */
    private function updateApiReference()
    {
        if (!$this->quiet) {
            echo "📚 Updating API reference...\n";
        }
        
        $apiFile = $this->rootPath . '/user-docs/development/api-reference.md';
        
        // Ensure directory exists
        $dir = dirname($apiFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $content = "# Isotone Hooks API Reference\n\n";
        $content .= "> Auto-generated: " . date('Y-m-d H:i:s') . "\n\n";
        $content .= "## Overview\n\n";
        $content .= "This document lists all hooks that are actually implemented in the Isotone codebase.\n\n";
        
        // Actions
        if (!empty($this->implementedHooks['actions'])) {
            $content .= "## Actions\n\n";
            
            foreach ($this->implementedHooks['actions'] as $hook => $locations) {
                $systemHook = SystemHooks::getHookInfo($hook);
                
                $content .= "### `$hook`\n\n";
                
                if ($systemHook) {
                    $content .= $systemHook['description'] . "\n\n";
                    $content .= "**Since:** " . $systemHook['since'] . "\n\n";
                }
                
                $content .= "**Usage:**\n";
                $content .= "```php\n";
                $content .= "add_action('$hook', 'your_callback_function', 10, 1);\n";
                $content .= "```\n\n";
                
                $content .= "**Fired in:**\n";
                foreach ($locations as $loc) {
                    $content .= "- `{$loc['file']}:{$loc['line']}`\n";
                }
                $content .= "\n";
            }
        }
        
        // Filters
        if (!empty($this->implementedHooks['filters'])) {
            $content .= "## Filters\n\n";
            
            foreach ($this->implementedHooks['filters'] as $hook => $locations) {
                $systemHook = SystemHooks::getHookInfo($hook);
                
                $content .= "### `$hook`\n\n";
                
                if ($systemHook) {
                    $content .= $systemHook['description'] . "\n\n";
                    $content .= "**Since:** " . $systemHook['since'] . "\n\n";
                }
                
                $content .= "**Usage:**\n";
                $content .= "```php\n";
                $content .= "add_filter('$hook', 'your_filter_function', 10, 1);\n";
                $content .= "```\n\n";
                
                $content .= "**Applied in:**\n";
                foreach ($locations as $loc) {
                    $content .= "- `{$loc['file']}:{$loc['line']}`\n";
                }
                $content .= "\n";
            }
        }
        
        file_put_contents($apiFile, $content);
        
        if (!$this->quiet) {
            echo "   Generated: user-docs/development/api-reference.md\n";
        }
    }
    
    /**
     * Print summary
     */
    private function printSummary()
    {
        echo "\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "📊 Summary\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        
        $coverage = $this->scanStats['system_hooks_total'] > 0 
            ? round(($this->scanStats['system_hooks_implemented'] / $this->scanStats['system_hooks_total']) * 100, 1)
            : 0;
        
        echo "Files scanned:        " . $this->scanStats['files_scanned'] . "\n";
        echo "Hooks found:          " . $this->scanStats['hooks_found'] . "\n";
        echo "System hooks:         " . $this->scanStats['system_hooks_total'] . "\n";
        echo "Implemented:          " . $this->scanStats['system_hooks_implemented'] . "\n";
        echo "Coverage:             " . $coverage . "%\n";
        
        if ($this->scanStats['orphan_hooks'] > 0) {
            echo "⚠️  Orphan hooks:       " . $this->scanStats['orphan_hooks'] . " (not in SystemHooks)\n";
        }
        
        $notImplemented = $this->scanStats['system_hooks_total'] - $this->scanStats['system_hooks_implemented'];
        if ($notImplemented > 0) {
            echo "❌ Not implemented:    " . $notImplemented . " system hooks\n";
        }
        
        echo "\n";
        echo "📁 Updated Resources:\n";
        echo "   - storage/hook-stats.json (hook statistics)\n";
        echo "   - storage/hooks-implementation.json (data for Hooks Explorer)\n";
        echo "   - user-docs/development/api-reference.md (API documentation)\n";
        echo "\n";
        echo "📊 View statistics in: Admin → Development → Hooks Explorer\n";
    }
    
    /**
     * Get scan results
     */
    public function getResults()
    {
        return [
            'stats' => $this->scanStats,
            'implemented' => $this->implementedHooks,
            'system_hooks' => $this->systemHooks
        ];
    }
}
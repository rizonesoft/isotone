<?php
/**
 * Hooks Command
 * 
 * CLI commands for managing and documenting hooks
 * 
 * @package Isotone\Commands
 */

namespace Isotone\Commands;

use Isotone\Core\Hook;

class HooksCommand
{
    /**
     * Scan codebase for hook usage
     */
    public function scan()
    {
        echo "Scanning for hook usage...\n";
        
        $results = [
            'actions' => [],
            'filters' => [],
            'total_files' => 0,
            'files_with_hooks' => []
        ];
        
        // Directories to scan
        $directories = [
            __DIR__ . '/../../app',
            __DIR__ . '/../../iso-admin',
            __DIR__ . '/../../iso-content/themes',
            __DIR__ . '/../../iso-content/plugins'
        ];
        
        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                $this->scanDirectory($dir, $results);
            }
        }
        
        // Display results
        echo "\n=== Hook Scan Results ===\n";
        echo "Total files scanned: {$results['total_files']}\n";
        echo "Files with hooks: " . count($results['files_with_hooks']) . "\n";
        echo "\nActions found: " . count($results['actions']) . "\n";
        
        foreach ($results['actions'] as $action => $locations) {
            echo "  - {$action} (" . count($locations) . " occurrences)\n";
        }
        
        echo "\nFilters found: " . count($results['filters']) . "\n";
        
        foreach ($results['filters'] as $filter => $locations) {
            echo "  - {$filter} (" . count($locations) . " occurrences)\n";
        }
        
        // Save results to JSON
        $jsonFile = __DIR__ . '/../../iso-automation/storage/hooks-scan.json';
        file_put_contents($jsonFile, json_encode($results, JSON_PRETTY_PRINT));
        echo "\nDetailed results saved to: iso-automation/storage/hooks-scan.json\n";
    }
    
    /**
     * Scan a directory recursively
     */
    private function scanDirectory($dir, &$results)
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            
            $results['total_files']++;
            $content = file_get_contents($file->getPathname());
            $relativePath = str_replace(dirname(__DIR__, 2) . '/', '', $file->getPathname());
            
            // Find do_action calls
            if (preg_match_all('/do_action\s*\(\s*[\'"]([^\'"\)]+)[\'"]/', $content, $matches)) {
                foreach ($matches[1] as $action) {
                    if (!isset($results['actions'][$action])) {
                        $results['actions'][$action] = [];
                    }
                    $results['actions'][$action][] = $relativePath;
                    $results['files_with_hooks'][$relativePath] = true;
                }
            }
            
            // Find apply_filters calls
            if (preg_match_all('/apply_filters\s*\(\s*[\'"]([^\'"\)]+)[\'"]/', $content, $matches)) {
                foreach ($matches[1] as $filter) {
                    if (!isset($results['filters'][$filter])) {
                        $results['filters'][$filter] = [];
                    }
                    $results['filters'][$filter][] = $relativePath;
                    $results['files_with_hooks'][$relativePath] = true;
                }
            }
        }
        
        $results['files_with_hooks'] = array_keys($results['files_with_hooks']);
    }
    
    /**
     * Generate hook documentation
     */
    public function docs()
    {
        echo "Generating hook documentation...\n";
        
        // Load HOOKS.md
        $hooksFile = __DIR__ . '/../../HOOKS.md';
        $content = file_get_contents($hooksFile);
        
        // Get current hook stats
        $stats = Hook::getHookStats();
        $allHooks = Hook::getAllHooks();
        
        // Count implemented hooks
        $implementedActions = count($allHooks['actions']);
        $implementedFilters = count($allHooks['filters']);
        $totalImplemented = $implementedActions + $implementedFilters;
        
        // Update statistics in HOOKS.md
        $date = date('Y-m-d H:i:s');
        $content = preg_replace(
            '/\*\*Last Updated\*\*: .+/',
            "**Last Updated**: {$date}",
            $content
        );
        
        $content = preg_replace(
            '/\*\*Hooks Implemented\*\*: \d+/',
            "**Hooks Implemented**: {$totalImplemented}",
            $content
        );
        
        // Calculate percentage (assuming 100 planned hooks)
        $percentage = round(($totalImplemented / 100) * 100);
        $content = preg_replace(
            '/\*\*Implementation Progress\*\*: \d+%/',
            "**Implementation Progress**: {$percentage}%",
            $content
        );
        
        // Save updated file
        file_put_contents($hooksFile, $content);
        
        echo "Documentation updated!\n";
        echo "- Actions implemented: {$implementedActions}\n";
        echo "- Filters implemented: {$implementedFilters}\n";
        echo "- Total progress: {$percentage}%\n";
    }
    
    /**
     * Validate hook implementation
     */
    public function validate()
    {
        echo "Validating hook implementation...\n\n";
        
        $errors = [];
        $warnings = [];
        
        // Check if critical hooks are implemented
        $criticalHooks = [
            'init',
            'iso_head',
            'iso_footer',
            'the_content',
            'admin_menu',
            'admin_init'
        ];
        
        foreach ($criticalHooks as $hook) {
            if (!Hook::hasAction($hook) && !Hook::hasFilter($hook)) {
                $warnings[] = "Critical hook '{$hook}' has no callbacks registered";
            }
        }
        
        // Scan for hook usage
        $scanResults = json_decode(
            file_get_contents(__DIR__ . '/../../iso-automation/storage/hooks-scan.json'),
            true
        );
        
        if ($scanResults) {
            // Check if called hooks have handlers
            foreach ($scanResults['actions'] as $action => $locations) {
                if (!Hook::hasAction($action)) {
                    $warnings[] = "Action '{$action}' is called but has no handlers";
                }
            }
            
            foreach ($scanResults['filters'] as $filter => $locations) {
                if (!Hook::hasFilter($filter)) {
                    $warnings[] = "Filter '{$filter}' is applied but has no handlers";
                }
            }
        }
        
        // Display results
        if (empty($errors) && empty($warnings)) {
            echo "✅ All hooks validated successfully!\n";
        } else {
            if (!empty($errors)) {
                echo "❌ Errors found:\n";
                foreach ($errors as $error) {
                    echo "  - {$error}\n";
                }
                echo "\n";
            }
            
            if (!empty($warnings)) {
                echo "⚠️  Warnings:\n";
                foreach ($warnings as $warning) {
                    echo "  - {$warning}\n";
                }
            }
        }
        
        return empty($errors);
    }
    
    /**
     * List all registered hooks
     */
    public function list()
    {
        $hooks = Hook::getAllHooks();
        
        echo "=== Registered Hooks ===\n\n";
        
        if (!empty($hooks['actions'])) {
            echo "Actions:\n";
            foreach ($hooks['actions'] as $tag => $priorities) {
                $count = 0;
                foreach ($priorities as $callbacks) {
                    $count += count($callbacks);
                }
                echo "  - {$tag} ({$count} callbacks)\n";
            }
            echo "\n";
        } else {
            echo "No actions registered.\n\n";
        }
        
        if (!empty($hooks['filters'])) {
            echo "Filters:\n";
            foreach ($hooks['filters'] as $tag => $priorities) {
                $count = 0;
                foreach ($priorities as $callbacks) {
                    $count += count($callbacks);
                }
                echo "  - {$tag} ({$count} callbacks)\n";
            }
        } else {
            echo "No filters registered.\n";
        }
    }
    
    /**
     * Test hook system
     */
    public function test()
    {
        echo "Testing hook system...\n\n";
        
        // Test action
        echo "Testing actions:\n";
        $actionFired = false;
        add_action('test_action', function() use (&$actionFired) {
            $actionFired = true;
            echo "  ✅ Action callback executed\n";
        });
        
        do_action('test_action');
        
        if (!$actionFired) {
            echo "  ❌ Action failed to fire\n";
        }
        
        // Test filter
        echo "\nTesting filters:\n";
        add_filter('test_filter', function($value) {
            return $value . ' filtered';
        });
        
        $result = apply_filters('test_filter', 'original');
        
        if ($result === 'original filtered') {
            echo "  ✅ Filter successfully applied\n";
        } else {
            echo "  ❌ Filter failed: expected 'original filtered', got '{$result}'\n";
        }
        
        // Test priority
        echo "\nTesting priority:\n";
        $order = [];
        
        add_action('priority_test', function() use (&$order) {
            $order[] = 'priority_20';
        }, 20);
        
        add_action('priority_test', function() use (&$order) {
            $order[] = 'priority_10';
        }, 10);
        
        add_action('priority_test', function() use (&$order) {
            $order[] = 'priority_5';
        }, 5);
        
        do_action('priority_test');
        
        if ($order === ['priority_5', 'priority_10', 'priority_20']) {
            echo "  ✅ Priority order correct\n";
        } else {
            echo "  ❌ Priority order incorrect: " . implode(', ', $order) . "\n";
        }
        
        // Test remove action
        echo "\nTesting remove action:\n";
        $removeTest = function() {
            echo "  This should not appear\n";
        };
        
        add_action('remove_test', $removeTest);
        remove_action('remove_test', $removeTest);
        do_action('remove_test');
        echo "  ✅ Action successfully removed\n";
        
        // Test current hook
        echo "\nTesting current hook detection:\n";
        add_action('current_test', function() {
            $current = current_action();
            if ($current === 'current_test') {
                echo "  ✅ Current action correctly detected: {$current}\n";
            } else {
                echo "  ❌ Current action incorrect: {$current}\n";
            }
        });
        
        do_action('current_test');
        
        echo "\n✅ Hook system test complete!\n";
    }
}
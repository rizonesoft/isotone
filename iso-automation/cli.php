#!/usr/bin/env php
<?php
/**
 * Isotone Automation CLI
 * 
 * Central entry point for all automation tasks
 * 
 * @package Isotone\Automation
 */

// Check if running from CLI
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

// Bootstrap
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load config if exists
$configFile = dirname(__DIR__) . '/config.php';
if (file_exists($configFile)) {
    require_once $configFile;
}

// Initialize RedBeanPHP if configured (optional - automation works without DB)
// Temporarily disabled - database is optional for automation
// Uncomment when MySQL is accessible from WSL
/*
if (defined('DB_HOST') && defined('DB_NAME')) {
    try {
        @\RedBeanPHP\R::setup(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
            DB_USER,
            DB_PASSWORD
        );
    } catch (\Exception | \PDOException $e) {
        // Database is optional for automation
        // Continue without database features
    }
}
*/

use Isotone\Automation\Core\AutomationEngine;

// Parse command
$command = $argv[1] ?? 'help';
$options = array_slice($argv, 2);

// Parse options
$parsedOptions = [];
$quiet = false;

foreach ($options as $option) {
    if ($option === '--quiet' || $option === '-q') {
        $quiet = true;
    } elseif (strpos($option, '--') === 0) {
        $parts = explode('=', substr($option, 2), 2);
        $parsedOptions[$parts[0]] = $parts[1] ?? true;
    }
}

// Initialize engine
$engine = new AutomationEngine();

try {
    $engine->initialize();
    
    if ($quiet) {
        $engine->setQuietMode(true);
    }
    
    // Handle commands
    switch ($command) {
        case 'check:docs':
        case 'docs:check':
            $result = $engine->execute('check:docs', $parsedOptions);
            exit($result ? 0 : 1);
            
        case 'update:docs':
        case 'docs:update':
            $result = $engine->execute('update:docs', $parsedOptions);
            exit($result ? 0 : 1);
            
        case 'generate:hooks':
        case 'hooks:generate':
        case 'docs:hooks':
        case 'hooks:scan':
            // New hooks:scan command using HooksAnalyzer
            require_once __DIR__ . '/src/Analyzers/HooksAnalyzer.php';
            $analyzer = new \Isotone\Automation\Analyzers\HooksAnalyzer();
            if ($quiet) {
                $analyzer->setQuiet(true);
            }
            $result = $analyzer->analyze();
            exit(0);
            
        case 'sync:ide':
        case 'ide:sync':
            $result = $engine->execute('sync:ide', $parsedOptions);
            exit($result ? 0 : 1);
            
        case 'sync:user-docs':
        case 'docs:sync':
            $result = $engine->execute('sync:user-docs', $parsedOptions);
            exit($result ? 0 : 1);
            
        case 'validate:rules':
        case 'rules:validate':
            $result = $engine->execute('validate:rules', $parsedOptions);
            exit($result ? 0 : 1);
            
        case 'status':
        case 'automation:status':
            $result = $engine->execute('status', $parsedOptions);
            exit($result ? 0 : 1);
            
        case 'cache:clear':
            $cacheManager = $engine->getCacheManager();
            
            if (!$quiet) {
                echo "🗑️  Cache Clear Operation\n";
                echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
                echo "📋 Step 1/3: Analyzing cache directories...\n";
            }
            
            $stats = $cacheManager->getStatistics();
            $filesBeforeClear = $stats['total_files_cached'];
            $sizeBeforeClear = $stats['disk_cache_size'];
            
            if (!$quiet) {
                echo "   Found {$filesBeforeClear} cached files (" . formatBytes($sizeBeforeClear) . ")\n\n";
                echo "🧹 Step 2/3: Clearing cache files...\n";
            }
            
            $cacheManager->clearCache();
            
            if (!$quiet) {
                echo "   Cache files removed\n\n";
                echo "✨ Step 3/3: Verifying cache is empty...\n";
                $newStats = $cacheManager->getStatistics();
                echo "   Remaining files: {$newStats['total_files_cached']}\n\n";
                echo "✅ Cache cleared successfully!\n";
                echo "   Freed up: " . formatBytes($sizeBeforeClear) . "\n";
            }
            exit(0);
            
        case 'cache:stats':
            $cacheManager = $engine->getCacheManager();
            $stats = $cacheManager->getStatistics();
            
            echo "📊 Cache Statistics:\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "  Total files cached: {$stats['total_files_cached']}\n";
            echo "  Memory cache size: {$stats['memory_cache_size']}\n";
            echo "  Disk cache size: " . formatBytes($stats['disk_cache_size']) . "\n";
            echo "  Oldest cache: {$stats['oldest_cache']}\n";
            echo "  Newest cache: {$stats['newest_cache']}\n";
            exit(0);
            
        case 'rules:export':
            $ruleEngine = $engine->getRuleEngine();
            $format = $parsedOptions['format'] ?? 'yaml';
            echo $ruleEngine->exportRules($format);
            exit(0);
            
        case 'help':
        case '--help':
        case '-h':
        default:
            showHelp();
            exit(0);
    }
    
} catch (Exception $e) {
    if (!$quiet) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        
        if (isset($parsedOptions['debug'])) {
            echo "\nStack trace:\n";
            echo $e->getTraceAsString() . "\n";
        }
    }
    
    exit(1);
}

/**
 * Show help message
 */
function showHelp(): void
{
    echo "\n";
    echo "╔═══════════════════════════════════════╗\n";
    echo "║     Isotone Automation Module         ║\n";
    echo "╚═══════════════════════════════════════╝\n";
    echo "\n";
    echo "Usage: php iso-automation/cli.php <command> [options]\n\n";
    echo "Commands:\n";
    echo "  check:docs          Check documentation integrity\n";
    echo "  update:docs         Update documentation from code\n";
    echo "  generate:hooks      Generate hooks documentation\n";
    echo "  sync:ide           Sync IDE rules from CLAUDE.md\n";
    echo "  sync:user-docs     Sync user documentation\n";
    echo "  validate:rules     Validate automation rules\n";
    echo "  status             Show automation status\n";
    echo "  cache:clear        Clear all caches\n";
    echo "  cache:stats        Show cache statistics\n";
    echo "  rules:export       Export rules (--format=yaml|json|markdown)\n";
    echo "  help               Show this help message\n";
    echo "\n";
    echo "Options:\n";
    echo "  --quiet, -q        Suppress output\n";
    echo "  --debug            Show debug information\n";
    echo "\n";
    echo "Examples:\n";
    echo "  php iso-automation/cli.php check:docs\n";
    echo "  php iso-automation/cli.php update:docs --quiet\n";
    echo "  php iso-automation/cli.php rules:export --format=markdown\n";
    echo "\n";
}

/**
 * Format bytes to human readable
 */
function formatBytes(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}
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
            
        case 'validate:rules':
        case 'rules:validate':
            $result = $engine->execute('validate:rules', $parsedOptions);
            exit($result ? 0 : 1);
            
        case 'rules:list':
            $ruleEngine = $engine->getRuleEngine();
            $rules = $ruleEngine->getAllRules();
            echo "📋 Isotone Automation Rules\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            
            // Group rules by priority
            $priorityGroups = [];
            foreach ($rules as $name => $rule) {
                $priority = $rule['priority'] ?? 50;
                if (!isset($priorityGroups[$priority])) {
                    $priorityGroups[$priority] = [];
                }
                $priorityGroups[$priority][$name] = $rule;
            }
            
            // Sort by priority (highest first)
            krsort($priorityGroups);
            
            foreach ($priorityGroups as $priority => $group) {
                echo "Priority $priority:\n";
                foreach ($group as $name => $rule) {
                    $status = $rule['enabled'] ?? true ? '✅' : '❌';
                    $desc = $rule['description'] ?? 'No description';
                    echo "  $status $name - $desc\n";
                }
                echo "\n";
            }
            exit(0);
            
        case 'rules:search':
            $searchTerm = $argv[2] ?? '';
            if (empty($searchTerm)) {
                echo "❌ Please provide a search term\n";
                echo "Usage: php iso-automation/cli.php rules:search <term>\n";
                exit(1);
            }
            
            $ruleEngine = $engine->getRuleEngine();
            $rules = $ruleEngine->getAllRules();
            $matches = [];
            
            // Search in rule names, descriptions, and content
            foreach ($rules as $name => $rule) {
                $searchContent = json_encode($rule);
                if (stripos($name, $searchTerm) !== false || 
                    stripos($searchContent, $searchTerm) !== false) {
                    $matches[$name] = $rule;
                }
            }
            
            if (empty($matches)) {
                echo "No rules found matching '$searchTerm'\n";
            } else {
                echo "🔍 Rules matching '$searchTerm':\n";
                echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
                foreach ($matches as $name => $rule) {
                    $priority = $rule['priority'] ?? 50;
                    $status = $rule['enabled'] ?? true ? '✅' : '❌';
                    $desc = $rule['description'] ?? 'No description';
                    echo "$status [$priority] $name\n";
                    echo "   $desc\n\n";
                }
            }
            exit(0);
            
        case 'rules:check':
            $ruleName = $argv[2] ?? '';
            if (empty($ruleName)) {
                echo "❌ Please provide a rule name\n";
                echo "Usage: php iso-automation/cli.php rules:check <rule_name>\n";
                exit(1);
            }
            
            $ruleEngine = $engine->getRuleEngine();
            $rule = $ruleEngine->getRule($ruleName);
            
            if ($rule === null) {
                echo "❌ Rule '$ruleName' not found\n";
                exit(1);
            }
            
            echo "📋 Rule: $ruleName\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "Priority: " . ($rule['priority'] ?? 50) . "\n";
            echo "Enabled: " . ($rule['enabled'] ?? true ? 'Yes' : 'No') . "\n";
            echo "Description: " . ($rule['description'] ?? 'No description') . "\n";
            
            if (isset($rule['context'])) {
                echo "Context: " . implode(', ', (array)$rule['context']) . "\n";
            }
            
            if (isset($rule['rules']) && is_array($rule['rules'])) {
                echo "\n📌 Rules:\n";
                foreach ($rule['rules'] as $r) {
                    echo "  • $r\n";
                }
            }
            
            if (isset($rule['violations']) && is_array($rule['violations'])) {
                echo "\n⚠️  Violations:\n";
                foreach ($rule['violations'] as $v) {
                    if (is_array($v)) {
                        echo "  • Pattern: " . ($v['pattern'] ?? 'N/A') . "\n";
                        echo "    Severity: " . ($v['severity'] ?? 'warning') . "\n";
                        echo "    Message: " . ($v['message'] ?? '') . "\n";
                    }
                }
            }
            
            echo "\n";
            exit(0);
            
        case 'rules:export':
            $format = $parsedOptions['format'] ?? 'yaml';
            $ruleEngine = $engine->getRuleEngine();
            $output = $ruleEngine->exportRules($format);
            
            if (isset($parsedOptions['output'])) {
                file_put_contents($parsedOptions['output'], $output);
                echo "✅ Rules exported to " . $parsedOptions['output'] . "\n";
            } else {
                echo $output;
            }
            exit(0);
            
        case 'status':
        case 'automation:status':
            $result = $engine->execute('status', $parsedOptions);
            exit($result ? 0 : 1);
            
            
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
    echo "\n";
    echo "Rules Commands:\n";
    echo "  rules:list         List all automation rules\n";
    echo "  rules:search       Search for rules by keyword\n";
    echo "  rules:check        Show details of a specific rule\n";
    echo "  rules:validate     Validate all rules\n";
    echo "  rules:export       Export rules (--format=yaml|json|markdown)\n";
    echo "\n";
    echo "System Commands:\n";
    echo "  status             Show automation status\n";
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
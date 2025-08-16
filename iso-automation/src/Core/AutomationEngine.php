<?php
/**
 * Isotone Automation Engine
 * 
 * Central orchestrator for all automation tasks
 * 
 * @package Isotone\Automation
 */

namespace Isotone\Automation\Core;

use Isotone\Automation\Rules\RuleEngine;
use Exception;

class AutomationEngine
{
    private RuleEngine $ruleEngine;
    private array $analyzers = [];
    private array $generators = [];
    private array $executionLog = [];
    private bool $quietMode = false;
    
    public function __construct()
    {
        $this->ruleEngine = new RuleEngine();
    }
    
    /**
     * Initialize the automation engine
     */
    public function initialize(): void
    {
        $this->ruleEngine->loadRules();
        
        $this->loadAnalyzers();
        $this->loadGenerators();
    }
    
    /**
     * Set quiet mode for minimal output
     */
    public function setQuietMode(bool $quiet = true): void
    {
        $this->quietMode = $quiet;
    }
    
    /**
     * Execute an automation task
     */
    public function execute(string $task, array $options = []): bool
    {
        $this->log("Executing task: $task");
        
        try {
            $startTime = microtime(true);
            
            // Execute based on task type
            $result = match($task) {
                'check:docs' => $this->checkDocumentation($options),
                'update:docs' => $this->updateDocumentation($options),
                'generate:hooks' => $this->generateHooksDocs($options),
                'sync:ide' => $this->syncIdeRules($options),
                'validate:rules' => $this->validateRules($options),
                'status' => $this->showStatus($options),
                default => throw new Exception("Unknown task: $task")
            };
            
            $executionTime = microtime(true) - $startTime;
            
            $this->log(sprintf("Task completed in %.2f seconds", $executionTime));
            
            return $result;
            
        } catch (Exception $e) {
            $this->logError("Task failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check documentation integrity
     */
    private function checkDocumentation(array $options): bool
    {
        require_once dirname(__DIR__) . '/Analyzers/DocumentationAnalyzer.php';
        
        $analyzer = new \Isotone\Automation\Analyzers\DocumentationAnalyzer($this);
        
        // Run the analysis
        $result = $analyzer->analyze();
        
        // Report if not in quiet mode
        if (!$this->quietMode) {
            $analyzer->report();
        }
        
        return $result;
    }
    
    /**
     * Update documentation
     */
    private function updateDocumentation(array $options): bool
    {
        // Check if DocUpdater exists, otherwise use DocumentationGenerator
        $docUpdaterPath = dirname(__DIR__) . '/Generators/DocUpdater.php';
        if (file_exists($docUpdaterPath)) {
            require_once $docUpdaterPath;
            $updater = new \Isotone\Automation\Generators\DocUpdater();
            $updater->setQuietMode($this->quietMode);
            return $updater->run();
        } else {
            // Fallback to DocumentationGenerator
            require_once dirname(__DIR__) . '/Generators/DocumentationGenerator.php';
            $generator = new \Isotone\Automation\Generators\DocumentationGenerator($this);
            $generator->generate();
            if (!$this->quietMode) {
                $generator->report();
            }
            return true;
        }
    }
    
    /**
     * Generate hooks documentation
     */
    private function generateHooksDocs(array $options): bool
    {
        // Fallback to original script for now
        $scriptPath = dirname(dirname(__DIR__)) . '/scripts/generate-hooks-docs.php';
        if (file_exists($scriptPath)) {
            ob_start();
            include $scriptPath;
            ob_end_clean();
            return true;
        }
        return false;
    }
    
    /**
     * Sync IDE rules
     */
    private function syncIdeRules(array $options): bool
    {
        // Check if IdeRuleSync exists, otherwise use IdeGenerator
        $ideRuleSyncPath = dirname(__DIR__) . '/Generators/IdeRuleSync.php';
        if (file_exists($ideRuleSyncPath)) {
            require_once $ideRuleSyncPath;
            $sync = new \Isotone\Automation\Generators\IdeRuleSync();
            $sync->setQuietMode($this->quietMode);
            return $sync->run();
        } else {
            // Fallback to IdeGenerator
            require_once dirname(__DIR__) . '/Generators/IdeGenerator.php';
            $generator = new \Isotone\Automation\Generators\IdeGenerator($this);
            $generator->generate();
            if (!$this->quietMode) {
                $generator->report();
            }
            return true;
        }
    }
    
    /**
     * Validate automation rules
     */
    private function validateRules(array $options): bool
    {
        $result = $this->ruleEngine->validate();
        
        if (!$this->quietMode) {
            $this->ruleEngine->report();
        }
        
        return $result;
    }
    
    /**
     * Show automation status (simplified)
     */
    private function showStatus(array $options): bool
    {
        if (!$this->quietMode) {
            echo "\n🔍 Isotone Automation Status\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            
            echo "\n🔧 System Status:\n";
            echo "  ✅ Automation Engine: Operational\n";
            echo "  ✅ Rule Engine: " . count($this->ruleEngine->getAllRules()) . " rules loaded\n";
            echo "  ✅ Analyzers: " . count($this->analyzers) . " loaded\n";
            echo "  ✅ Generators: " . count($this->generators) . " loaded\n";
            
            echo "\n";
        }
        
        return true;
    }
    
    /**
     * Get rule engine instance
     */
    public function getRuleEngine(): RuleEngine
    {
        return $this->ruleEngine;
    }
    
    /**
     * Load all analyzers
     */
    private function loadAnalyzers(): void
    {
        $analyzerDir = dirname(__DIR__) . '/Analyzers';
        
        foreach (glob($analyzerDir . '/*.php') as $file) {
            $className = 'Isotone\\Automation\\Analyzers\\' . basename($file, '.php');
            
            if (class_exists($className)) {
                $name = strtolower(str_replace('Analyzer', '', basename($file, '.php')));
                $this->analyzers[$name] = new $className($this);
            }
        }
    }
    
    /**
     * Load all generators
     */
    private function loadGenerators(): void
    {
        $generatorDir = dirname(__DIR__) . '/Generators';
        
        foreach (glob($generatorDir . '/*.php') as $file) {
            $className = 'Isotone\\Automation\\Generators\\' . basename($file, '.php');
            
            if (class_exists($className)) {
                $name = strtolower(str_replace('Generator', '', basename($file, '.php')));
                $this->generators[$name] = new $className($this);
            }
        }
    }
    
    /**
     * Get execution log
     */
    public function getExecutionLog(): array
    {
        return $this->executionLog;
    }
    
    /**
     * Log a message
     */
    private function log(string $message): void
    {
        $this->executionLog[] = [
            'time' => date('Y-m-d H:i:s'),
            'type' => 'info',
            'message' => $message
        ];
        
        if (!$this->quietMode) {
            echo "ℹ️  $message\n";
        }
    }
    
    /**
     * Log an error
     */
    private function logError(string $message): void
    {
        $this->executionLog[] = [
            'time' => date('Y-m-d H:i:s'),
            'type' => 'error',
            'message' => $message
        ];
        
        if (!$this->quietMode) {
            echo "❌ $message\n";
        }
    }
}
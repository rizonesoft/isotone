<?php
/**
 * Isotone Automation Engine
 * 
 * Central orchestrator for all automation tasks
 * 
 * @package Isotone\Automation
 */

namespace Isotone\Automation\Core;

use Isotone\Automation\Storage\StateManager;
use Isotone\Automation\Storage\CacheManager;
use Isotone\Automation\Rules\RuleEngine;
use Exception;

class AutomationEngine
{
    private StateManager $stateManager;
    private CacheManager $cacheManager;
    private RuleEngine $ruleEngine;
    private array $analyzers = [];
    private array $generators = [];
    private array $executionLog = [];
    private bool $quietMode = false;
    
    public function __construct()
    {
        $this->stateManager = new StateManager();
        $this->cacheManager = new CacheManager();
        $this->ruleEngine = new RuleEngine();
    }
    
    /**
     * Initialize the automation engine
     */
    public function initialize(): void
    {
        $this->stateManager->initialize();
        $this->cacheManager->initialize();
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
            
            // Record execution start (if database available)
            $executionId = $this->stateManager->startExecution($task, $options);
            
            // Get modified files since last run
            $modifiedFiles = $this->cacheManager->getModifiedFiles($task);
            
            // Execute based on task type
            $result = match($task) {
                'check:docs' => $this->checkDocumentation($modifiedFiles, $options),
                'update:docs' => $this->updateDocumentation($modifiedFiles, $options),
                'generate:hooks' => $this->generateHooksDocs($modifiedFiles, $options),
                'sync:ide' => $this->syncIdeRules($options),
                'sync:user-docs' => $this->syncUserDocs($options),
                'validate:rules' => $this->validateRules($options),
                'status' => $this->showStatus($options),
                default => throw new Exception("Unknown task: $task")
            };
            
            $executionTime = microtime(true) - $startTime;
            
            // Record execution completion (if database available and execution was tracked)
            if ($executionId > 0) {
                $this->stateManager->completeExecution($executionId, $result, $executionTime);
            }
            
            // Update cache
            $this->cacheManager->updateCache($task);
            
            $this->log(sprintf("Task completed in %.2f seconds", $executionTime));
            
            return $result;
            
        } catch (Exception $e) {
            $this->logError("Task failed: " . $e->getMessage());
            
            if (isset($executionId) && $executionId > 0) {
                $this->stateManager->failExecution($executionId, $e->getMessage());
            }
            
            return false;
        }
    }
    
    /**
     * Check documentation integrity
     */
    private function checkDocumentation(array $modifiedFiles, array $options): bool
    {
        // Use original script directly for reliability
        $scriptPath = dirname(dirname(dirname(__DIR__))) . '/scripts/check-docs.php';
        if (!file_exists($scriptPath)) {
            $this->logError("check-docs.php script not found at: $scriptPath");
            return false;
        }
        
        // Execute the script as a separate process to avoid exit() affecting our engine
        $command = 'php ' . escapeshellarg($scriptPath);
        if ($this->quietMode) {
            $command .= ' --quiet';
        }
        $command .= ' 2>&1';
        
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        // Log output if not in quiet mode
        if (!$this->quietMode && !empty($output)) {
            foreach ($output as $line) {
                $this->log($line);
            }
        }
        
        // Return code 0 means success (no errors, possibly warnings)
        // Return code 1 means errors found
        return $returnCode === 0;
    }
    
    /**
     * Update documentation
     */
    private function updateDocumentation(array $modifiedFiles, array $options): bool
    {
        // Fallback to original script for now
        $scriptPath = dirname(dirname(__DIR__)) . '/scripts/update-docs.php';
        if (file_exists($scriptPath)) {
            ob_start();
            include $scriptPath;
            ob_end_clean();
            return true;
        }
        return false;
    }
    
    /**
     * Generate hooks documentation
     */
    private function generateHooksDocs(array $modifiedFiles, array $options): bool
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
        // Fallback to original script for now
        $scriptPath = dirname(dirname(__DIR__)) . '/scripts/sync-ide-rules.php';
        if (file_exists($scriptPath)) {
            ob_start();
            include $scriptPath;
            ob_end_clean();
            return true;
        }
        return false;
    }
    
    /**
     * Sync user documentation
     */
    private function syncUserDocs(array $options): bool
    {
        // Fallback to original script for now
        $scriptPath = dirname(dirname(__DIR__)) . '/scripts/sync-user-docs.php';
        if (file_exists($scriptPath)) {
            ob_start();
            include $scriptPath;
            ob_end_clean();
            return true;
        }
        return false;
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
     * Show automation status
     */
    private function showStatus(array $options): bool
    {
        $status = $this->stateManager->getStatus();
        
        if (!$this->quietMode) {
            $this->displayStatus($status);
        }
        
        return true;
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
     * Get state manager
     */
    public function getStateManager(): StateManager
    {
        return $this->stateManager;
    }
    
    /**
     * Get cache manager
     */
    public function getCacheManager(): CacheManager
    {
        return $this->cacheManager;
    }
    
    /**
     * Get rule engine
     */
    public function getRuleEngine(): RuleEngine
    {
        return $this->ruleEngine;
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
    
    /**
     * Display status information
     */
    private function displayStatus(array $status): void
    {
        echo "\n🔍 Isotone Automation Status\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        
        echo "\n📊 Recent Executions:\n";
        foreach ($status['recent_executions'] as $exec) {
            $icon = $exec['status'] === 'completed' ? '✅' : '❌';
            echo sprintf(
                "%s %s - %s (%.2fs)\n",
                $icon,
                $exec['task'],
                $exec['completed_at'],
                $exec['execution_time']
            );
        }
        
        echo "\n📈 Statistics:\n";
        echo "  Total Executions: {$status['stats']['total_executions']}\n";
        echo "  Success Rate: {$status['stats']['success_rate']}%\n";
        echo "  Avg Execution Time: {$status['stats']['avg_execution_time']}s\n";
        
        echo "\n🔧 System Health:\n";
        foreach ($status['health'] as $component => $health) {
            $icon = $health['status'] === 'healthy' ? '✅' : '⚠️';
            echo "  $icon $component: {$health['message']}\n";
        }
        
        echo "\n";
    }
}
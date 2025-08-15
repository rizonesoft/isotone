<?php
/**
 * Isotone Automation State Manager
 * 
 * Manages central state for all automation tasks using RedBeanPHP
 * 
 * @package Isotone\Automation
 */

namespace Isotone\Automation\Storage;

use RedBeanPHP\R;
use Exception;

class StateManager
{
    private bool $initialized = false;
    
    /**
     * Initialize the state manager
     */
    public function initialize(): void
    {
        if ($this->initialized) {
            return;
        }
        
        // Try to establish database connection if not already connected
        if (!R::testConnection()) {
            // Try to connect using config constants if available
            if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASSWORD')) {
                try {
                    @R::setup(
                        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
                        DB_USER,
                        DB_PASSWORD
                    );
                } catch (\Exception | \PDOException $e) {
                    // Database features will be disabled, but automation can still run
                    $this->initialized = true;
                    return;
                }
            } else {
                // No database config, continue without database features
                $this->initialized = true;
                return;
            }
        }
        
        // Create tables if they don't exist (RedBean will handle this automatically)
        if (R::testConnection()) {
            $this->ensureTables();
        }
        
        $this->initialized = true;
    }
    
    /**
     * Start recording an execution
     */
    public function startExecution(string $task, array $options = []): int
    {
        if (!R::testConnection()) {
            return 0; // Return dummy ID if no database
        }
        
        $execution = R::dispense('automationexecution');
        $execution->task = $task;
        $execution->options = json_encode($options);
        $execution->status = 'running';
        $execution->started_at = date('Y-m-d H:i:s');
        $execution->pid = getmypid();
        
        return R::store($execution);
    }
    
    /**
     * Complete an execution
     */
    public function completeExecution(int $executionId, bool $success, float $executionTime): void
    {
        if (!R::testConnection() || $executionId === 0) {
            return;
        }
        
        $execution = R::load('automationexecution', $executionId);
        
        if ($execution->id) {
            $execution->status = $success ? 'completed' : 'failed';
            $execution->completed_at = date('Y-m-d H:i:s');
            $execution->execution_time = $executionTime;
            $execution->success = $success;
            
            R::store($execution);
        }
    }
    
    /**
     * Mark an execution as failed
     */
    public function failExecution(int $executionId, string $error): void
    {
        if (!R::testConnection() || $executionId === 0) {
            return;
        }
        
        $execution = R::load('automationexecution', $executionId);
        
        if ($execution->id) {
            $execution->status = 'failed';
            $execution->completed_at = date('Y-m-d H:i:s');
            $execution->error_message = $error;
            $execution->success = false;
            
            R::store($execution);
        }
    }
    
    /**
     * Get automation status
     */
    public function getStatus(): array
    {
        $status = [
            'recent_executions' => [],
            'stats' => [],
            'health' => []
        ];
        
        if (!R::testConnection()) {
            // Return default status if no database
            $status['stats'] = [
                'total_executions' => 0,
                'success_rate' => 0,
                'avg_execution_time' => 0
            ];
            $status['health'] = [
                'database' => [
                    'status' => 'unavailable',
                    'message' => 'Database not configured'
                ]
            ];
            return $status;
        }
        
        // Get recent executions
        $recent = R::find('automationexecution', 
            'ORDER BY started_at DESC LIMIT 10'
        );
        
        foreach ($recent as $exec) {
            $status['recent_executions'][] = [
                'task' => $exec->task,
                'status' => $exec->status,
                'started_at' => $exec->started_at,
                'completed_at' => $exec->completed_at,
                'execution_time' => $exec->execution_time ?? 0
            ];
        }
        
        // Calculate statistics
        $total = R::count('automationexecution');
        $successful = R::count('automationexecution', 'success = 1');
        $avgTime = R::getCell(
            'SELECT AVG(execution_time) FROM automationexecution WHERE execution_time IS NOT NULL'
        );
        
        $status['stats'] = [
            'total_executions' => $total,
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
            'avg_execution_time' => round($avgTime ?? 0, 2)
        ];
        
        // Check system health
        $status['health'] = $this->checkHealth();
        
        return $status;
    }
    
    /**
     * Store a rule execution result
     */
    public function storeRuleExecution(string $ruleName, bool $passed, array $details = []): void
    {
        $rule = R::dispense('automationrule');
        $rule->name = $ruleName;
        $rule->passed = $passed;
        $rule->details = json_encode($details);
        $rule->executed_at = date('Y-m-d H:i:s');
        
        R::store($rule);
    }
    
    /**
     * Get rule execution history
     */
    public function getRuleHistory(string $ruleName = null): array
    {
        if ($ruleName) {
            $rules = R::find('automationrule', 
                'name = ? ORDER BY executed_at DESC LIMIT 100',
                [$ruleName]
            );
        } else {
            $rules = R::find('automationrule', 
                'ORDER BY executed_at DESC LIMIT 100'
            );
        }
        
        $history = [];
        foreach ($rules as $rule) {
            $history[] = [
                'name' => $rule->name,
                'passed' => (bool)$rule->passed,
                'details' => json_decode($rule->details, true),
                'executed_at' => $rule->executed_at
            ];
        }
        
        return $history;
    }
    
    /**
     * Store automation state data
     */
    public function setState(string $key, $value): void
    {
        $state = R::findOne('automationstate', 'state_key = ?', [$key]);
        
        if (!$state) {
            $state = R::dispense('automationstate');
            $state->state_key = $key;
        }
        
        $state->state_value = is_array($value) ? json_encode($value) : $value;
        $state->updated_at = date('Y-m-d H:i:s');
        
        R::store($state);
    }
    
    /**
     * Get automation state data
     */
    public function getState(string $key, $default = null)
    {
        $state = R::findOne('automationstate', 'state_key = ?', [$key]);
        
        if (!$state) {
            return $default;
        }
        
        // Try to decode JSON
        $decoded = json_decode($state->state_value, true);
        
        return $decoded !== null ? $decoded : $state->state_value;
    }
    
    /**
     * Clear old execution records
     */
    public function cleanup(int $daysToKeep = 30): int
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-$daysToKeep days"));
        
        $deleted = R::exec(
            'DELETE FROM automationexecution WHERE started_at < ?',
            [$cutoffDate]
        );
        
        return $deleted;
    }
    
    /**
     * Check system health
     */
    private function checkHealth(): array
    {
        $health = [];
        
        // Check database connection
        $health['database'] = [
            'status' => R::testConnection() ? 'healthy' : 'unhealthy',
            'message' => R::testConnection() ? 'Connected' : 'Connection failed'
        ];
        
        // Check recent failures
        $recentFailures = R::count('automationexecution', 
            'status = ? AND started_at > ?',
            ['failed', date('Y-m-d H:i:s', strtotime('-1 hour'))]
        );
        
        $health['executions'] = [
            'status' => $recentFailures < 5 ? 'healthy' : 'warning',
            'message' => $recentFailures > 0 
                ? "$recentFailures failures in last hour" 
                : 'No recent failures'
        ];
        
        // Check disk space for cache
        $cacheDir = dirname(dirname(__DIR__)) . '/cache';
        if (is_dir($cacheDir)) {
            $freeSpace = disk_free_space($cacheDir);
            $health['storage'] = [
                'status' => $freeSpace > 100 * 1024 * 1024 ? 'healthy' : 'warning',
                'message' => 'Free space: ' . $this->formatBytes($freeSpace)
            ];
        }
        
        return $health;
    }
    
    /**
     * Ensure required tables exist
     */
    private function ensureTables(): void
    {
        // RedBean will create these automatically, but we can set up indexes
        R::exec('CREATE INDEX IF NOT EXISTS idx_exec_task ON automationexecution(task)');
        R::exec('CREATE INDEX IF NOT EXISTS idx_exec_started ON automationexecution(started_at)');
        R::exec('CREATE INDEX IF NOT EXISTS idx_rule_name ON automationrule(name)');
        R::exec('CREATE INDEX IF NOT EXISTS idx_state_key ON automationstate(state_key)');
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
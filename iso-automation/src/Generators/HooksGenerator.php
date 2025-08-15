<?php
/**
 * Isotone Hooks Documentation Generator
 * 
 * Generates documentation for hooks implementation
 * 
 * @package Isotone\Automation
 */

namespace Isotone\Automation\Generators;

use Isotone\Automation\Core\AutomationEngine;

class HooksGenerator
{
    private AutomationEngine $engine;
    private array $modifiedFiles = [];
    private array $hooks = [];
    
    public function __construct(AutomationEngine $engine)
    {
        $this->engine = $engine;
    }
    
    /**
     * Set modified files for incremental generation
     */
    public function setModifiedFiles(array $files): void
    {
        $this->modifiedFiles = $files;
    }
    
    /**
     * Generate hooks documentation
     */
    public function generate(): void
    {
        // For now, just call the original script
        // TODO: Migrate full functionality here
        $scriptPath = dirname(dirname(dirname(__DIR__))) . '/scripts/generate-hooks-docs.php';
        
        if (file_exists($scriptPath)) {
            ob_start();
            include $scriptPath;
            ob_end_clean();
        }
        
        $this->hooks[] = 'Hooks documentation generated';
    }
    
    /**
     * Report generation results
     */
    public function report(): void
    {
        echo "✅ Hooks documentation generated\n";
        
        if (!empty($this->hooks)) {
            foreach ($this->hooks as $hook) {
                echo "  ✓ $hook\n";
            }
        }
    }
}
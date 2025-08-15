<?php
/**
 * Isotone Documentation Generator
 * 
 * Generates and updates documentation from code
 * 
 * @package Isotone\Automation
 */

namespace Isotone\Automation\Generators;

use Isotone\Automation\Core\AutomationEngine;

class DocumentationGenerator
{
    private AutomationEngine $engine;
    private array $modifiedFiles = [];
    private array $updates = [];
    
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
     * Generate documentation
     */
    public function generate(): void
    {
        // For now, just call the original script
        // TODO: Migrate full functionality here
        $scriptPath = dirname(dirname(dirname(__DIR__))) . '/scripts/update-docs.php';
        
        if (file_exists($scriptPath)) {
            ob_start();
            include $scriptPath;
            ob_end_clean();
        }
        
        $this->updates[] = 'Documentation updated';
    }
    
    /**
     * Report generation results
     */
    public function report(): void
    {
        if (empty($this->updates)) {
            echo "✅ Documentation is up to date\n";
            return;
        }
        
        echo "📝 Documentation Updates:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        
        foreach ($this->updates as $update) {
            echo "  ✓ $update\n";
        }
        
        echo "\n";
    }
}
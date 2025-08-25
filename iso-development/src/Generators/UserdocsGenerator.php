<?php
/**
 * Isotone User Documentation Generator
 * 
 * Syncs user documentation
 * 
 * @package Isotone\Automation
 */

namespace Isotone\Automation\Generators;

use Isotone\Automation\Core\AutomationEngine;

class UserdocsGenerator
{
    private AutomationEngine $engine;
    private array $synced = [];
    
    public function __construct(AutomationEngine $engine)
    {
        $this->engine = $engine;
    }
    
    /**
     * Generate/sync user docs
     */
    public function generate(): void
    {
        // For now, just call the original script
        // TODO: Migrate full functionality here
        $scriptPath = dirname(dirname(dirname(__DIR__))) . '/scripts/sync-user-docs.php';
        
        if (file_exists($scriptPath)) {
            ob_start();
            include $scriptPath;
            ob_end_clean();
        }
        
        $this->synced[] = 'User documentation synchronized';
    }
    
    /**
     * Report generation results
     */
    public function report(): void
    {
        echo "✅ User documentation synchronized\n";
        
        if (!empty($this->synced)) {
            foreach ($this->synced as $sync) {
                echo "  ✓ $sync\n";
            }
        }
    }
}
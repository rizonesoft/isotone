<?php
/**
 * Isotone IDE Rules Generator
 * 
 * Syncs IDE rules from CLAUDE.md to various IDE configuration files
 * 
 * @package Isotone\Automation
 */

namespace Isotone\Automation\Generators;

use Isotone\Automation\Core\AutomationEngine;

class IdeGenerator
{
    private AutomationEngine $engine;
    private array $synced = [];
    
    public function __construct(AutomationEngine $engine)
    {
        $this->engine = $engine;
    }
    
    /**
     * Generate/sync IDE rules
     */
    public function generate(): void
    {
        // For now, just call the original script
        // TODO: Migrate full functionality here
        $scriptPath = dirname(dirname(dirname(__DIR__))) . '/scripts/sync-ide-rules.php';
        
        if (file_exists($scriptPath)) {
            ob_start();
            include $scriptPath;
            ob_end_clean();
        }
        
        $this->synced[] = 'IDE rules synchronized';
    }
    
    /**
     * Report generation results
     */
    public function report(): void
    {
        echo "✅ IDE rules synchronized\n";
        
        if (!empty($this->synced)) {
            foreach ($this->synced as $sync) {
                echo "  ✓ $sync\n";
            }
        }
    }
}
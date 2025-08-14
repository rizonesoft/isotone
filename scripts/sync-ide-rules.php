<?php
/**
 * Sync IDE rule files from source templates
 * This ensures all IDE configurations stay updated
 * 
 * Run: php scripts/sync-ide-rules.php
 */

declare(strict_types=1);

class IdeRuleSync
{
    private string $rootPath;
    private array $synced = [];
    
    public function __construct()
    {
        $this->rootPath = dirname(__DIR__);
    }
    
    public function run(): void
    {
        echo "🔄 Syncing IDE rule files...\n\n";
        
        $this->syncWindsurf();
        $this->createCursorRules();
        $this->createCopilotInstructions();
        
        $this->report();
    }
    
    /**
     * Sync Windsurf rules
     */
    private function syncWindsurf(): void
    {
        $source = $this->rootPath . '/.windsurf-rules.md';
        $targetDir = $this->rootPath . '/.windsurf/rules';
        $target = $targetDir . '/development-guide.md';
        
        if (!file_exists($source)) {
            echo "⚠️  Source .windsurf-rules.md not found\n";
            return;
        }
        
        // Create directory if needed
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        // Copy file
        if (copy($source, $target)) {
            $this->synced[] = "Windsurf rules → .windsurf/rules/development-guide.md";
        } else {
            echo "❌ Failed to sync Windsurf rules\n";
        }
    }
    
    /**
     * Create Cursor rules if they don't exist
     */
    private function createCursorRules(): void
    {
        $cursorFile = $this->rootPath . '/.cursorrules';
        
        if (file_exists($cursorFile)) {
            return; // Don't overwrite existing
        }
        
        $content = <<<'CURSOR'
# Cursor IDE Rules for Isotone

## CRITICAL: This is an LLM-driven PHP project

### Required Reading:
- CLAUDE.md - Project overview and rules
- docs/LLM-DEVELOPMENT-GUIDE.md - LLM development guide
- docs/AI-CODING-STANDARDS.md - Coding standards
- docs/DOCUMENTATION-MAINTENANCE.md - Keep docs in sync

### Absolute Rules:
1. NO Node.js/npm - Pure PHP project
2. NO database migrations - RedBeanPHP handles schema
3. NO Laravel/Symfony patterns - Lightweight custom
4. ALWAYS run `composer docs:check` before completing

### Key Commands:
```bash
composer docs:check    # Check documentation
composer test         # Run tests
composer check-style  # Check PSR-12
```

### Project Facts:
- PHP 8.3+ with PSR-12
- XAMPP on Windows
- RedBeanPHP ORM
- WordPress-like hooks
- Shared hosting compatible

### Before Completing Any Task:
1. Run `composer docs:check`
2. Update documentation
3. Test at http://localhost/isotone/
CURSOR;
        
        if (file_put_contents($cursorFile, $content)) {
            $this->synced[] = "Created .cursorrules";
        }
    }
    
    /**
     * Create GitHub Copilot instructions if they don't exist
     */
    private function createCopilotInstructions(): void
    {
        $copilotDir = $this->rootPath . '/.github';
        $copilotFile = $copilotDir . '/copilot-instructions.md';
        
        if (file_exists($copilotFile)) {
            return; // Don't overwrite existing
        }
        
        // Create directory if needed
        if (!is_dir($copilotDir)) {
            mkdir($copilotDir, 0755, true);
        }
        
        $content = <<<'COPILOT'
# GitHub Copilot Instructions for Isotone

## Project Overview
Isotone is a lightweight PHP content management system designed for shared hosting, developed primarily by AI assistants.

## Critical Rules for Code Generation

### NEVER Generate:
- Node.js/npm code or dependencies
- Database migration files
- Laravel/Symfony patterns
- Complex build processes

### ALWAYS Generate:
- PHP 8.3+ compatible code
- PSR-12 compliant code style
- PHPDoc comments for all methods
- Escaped output (prevent XSS)
- Code that works on shared hosting

## Required Documentation
When working on this project, consult:
- `CLAUDE.md` - Project-specific rules
- `docs/LLM-DEVELOPMENT-GUIDE.md` - LLM guide
- `docs/AI-CODING-STANDARDS.md` - Standards

## Project Structure
```
app/          # Core application (PSR-4)
public/       # Web root
plugins/      # WordPress-like plugins
themes/       # Theme files
docs/         # Documentation
```

## Key Technologies
- PHP 8.3+
- RedBeanPHP ORM (no migrations!)
- Composer for dependencies
- WordPress-like hooks: add_action(), add_filter()

## Testing Requirements
Before suggesting code completion:
1. Check PSR-12 compliance
2. Verify no npm dependencies
3. Ensure documentation is updated
4. Run: `composer docs:check`

## Database Patterns
```php
// Use RedBeanPHP - it creates tables automatically
$item = R::dispense('tablename');
$item->field = 'value';
R::store($item);
```

## Example Code Style
```php
<?php
declare(strict_types=1);

namespace Isotone\Core;

/**
 * Class description
 */
class ClassName
{
    /**
     * Method description
     * 
     * @param string $param Description
     * @return string
     */
    public function methodName(string $param): string
    {
        return htmlspecialchars($param, ENT_QUOTES, 'UTF-8');
    }
}
```
COPILOT;
        
        if (file_put_contents($copilotFile, $content)) {
            $this->synced[] = "Created .github/copilot-instructions.md";
        }
    }
    
    /**
     * Report results
     */
    private function report(): void
    {
        if (empty($this->synced)) {
            echo "✅ No IDE rules needed syncing.\n";
            return;
        }
        
        echo "📁 IDE Rules Synced:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        foreach ($this->synced as $sync) {
            echo "  ✓ $sync\n";
        }
        echo "\n✅ IDE rule sync complete!\n";
    }
}

// Run the sync
$sync = new IdeRuleSync();
$sync->run();
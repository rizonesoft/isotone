<?php
/**
 * Documentation Auto-Updater for Isotone
 * 
 * Automatically generates and updates documentation from code
 * Run: php scripts/update-docs.php
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Isotone\Core\Application;

class DocUpdater
{
    private string $rootPath;
    private array $updates = [];
    
    public function __construct()
    {
        $this->rootPath = dirname(__DIR__);
    }
    
    public function run(): void
    {
        echo "📝 Auto-updating Isotone Documentation...\n\n";
        
        $this->updateRoutesDocumentation();
        $this->updateProjectStructure();
        $this->updateEnvironmentVariables();
        $this->updateComposerCommands();
        $this->updateFeatureStatus();
        $this->generateApiDocumentation();
        $this->updateHooksDocumentation();
        
        $this->report();
        
        // Sync to user-docs after updates
        $this->syncUserDocs();
        
        // Sync IDE rules after updates
        $this->syncIdeRules();
    }
    
    /**
     * Update routes documentation from actual routes
     */
    private function updateRoutesDocumentation(): void
    {
        $appFile = $this->rootPath . '/app/Core/Application.php';
        if (!file_exists($appFile)) {
            return;
        }
        
        $content = file_get_contents($appFile);
        
        // Extract routes
        preg_match_all(
            "/routes->add\(['\"]([^'\"]+)['\"],\s*new Route\(['\"]([^'\"]+)['\"].*?_controller['\"].*?=>\s*\[.*?['\"]([^'\"]+)['\"]/s",
            $content,
            $matches
        );
        
        $routes = [];
        for ($i = 0; $i < count($matches[1]); $i++) {
            $routes[] = [
                'name' => $matches[1][$i],
                'path' => $matches[2][$i],
                'handler' => $matches[3][$i]
            ];
        }
        
        // Generate routes documentation
        $routesDocs = "## Available Routes\n\n";
        $routesDocs .= "| Route Name | Path | Handler |\n";
        $routesDocs .= "|------------|------|---------|";
        
        foreach ($routes as $route) {
            $routesDocs .= "| {$route['name']} | `{$route['path']}` | `{$route['handler']}` |\n";
        }
        
        // Update in getting-started.md
        $this->updateDocSection(
            'docs/ROUTES.md',
            '## Available Routes',
            '## ',
            $routesDocs
        );
        
        $this->updates[] = "Updated routes documentation";
    }
    
    /**
     * Update project structure documentation
     */
    private function updateProjectStructure(): void
    {
        $structure = $this->generateDirectoryTree($this->rootPath, 0, [
            'vendor', '.git', 'node_modules', '.idea', '.vscode'
        ]);
        
        $structureDocs = "## Project Structure\n\n```\n" . $structure . "```\n";
        
        // Save to separate file
        file_put_contents(
            $this->rootPath . '/docs/PROJECT-STRUCTURE.md',
            "# Isotone Project Structure\n\n" . 
            "*Auto-generated on " . date('Y-m-d H:i:s') . "*\n\n" .
            $structureDocs
        );
        
        $this->updates[] = "Updated project structure documentation";
    }
    
    /**
     * Generate directory tree
     */
    private function generateDirectoryTree(string $dir, int $level = 0, array $ignore = []): string
    {
        if ($level > 3) return ''; // Max depth
        
        $tree = '';
        $items = scandir($dir);
        $indent = str_repeat('  ', $level);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || in_array($item, $ignore)) {
                continue;
            }
            
            $path = $dir . '/' . $item;
            
            if (is_dir($path)) {
                $tree .= $indent . $item . "/\n";
                if ($level < 2) { // Only go 2 levels deep
                    $tree .= $this->generateDirectoryTree($path, $level + 1, $ignore);
                }
            } else {
                // Only show important files
                if (preg_match('/\.(php|json|md|env\.example)$/', $item)) {
                    $tree .= $indent . $item . "\n";
                }
            }
        }
        
        return $tree;
    }
    
    /**
     * Update environment variables documentation
     */
    private function updateEnvironmentVariables(): void
    {
        $envExample = $this->rootPath . '/.env.example';
        if (!file_exists($envExample)) {
            return;
        }
        
        $lines = file($envExample);
        $vars = [];
        $currentSection = 'General';
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Section header
            if (strpos($line, '#') === 0 && strpos($line, '# ') === 0) {
                $currentSection = trim(substr($line, 2));
                continue;
            }
            
            // Variable
            if (preg_match('/^([A-Z_]+)=(.*)$/', $line, $matches)) {
                $vars[$currentSection][] = [
                    'name' => $matches[1],
                    'default' => $matches[2]
                ];
            }
        }
        
        // Generate documentation
        $envDocs = "# Environment Variables\n\n";
        $envDocs .= "*Auto-generated from .env.example*\n\n";
        
        foreach ($vars as $section => $sectionVars) {
            $envDocs .= "## $section\n\n";
            $envDocs .= "| Variable | Default | Description |\n";
            $envDocs .= "|----------|---------|-------------|\n";
            
            foreach ($sectionVars as $var) {
                $default = $var['default'] ?: '*(empty)*';
                $envDocs .= "| `{$var['name']}` | `$default` | |\n";
            }
            $envDocs .= "\n";
        }
        
        file_put_contents($this->rootPath . '/docs/ENVIRONMENT-VARIABLES.md', $envDocs);
        $this->updates[] = "Updated environment variables documentation";
    }
    
    /**
     * Update composer commands documentation
     */
    private function updateComposerCommands(): void
    {
        $composerFile = $this->rootPath . '/composer.json';
        if (!file_exists($composerFile)) {
            return;
        }
        
        $composer = json_decode(file_get_contents($composerFile), true);
        $scripts = $composer['scripts'] ?? [];
        
        $commandsDocs = "## Composer Commands\n\n";
        $commandsDocs .= "| Command | Description |\n";
        $commandsDocs .= "|---------|-------------|\n";
        
        foreach ($scripts as $name => $command) {
            // Try to determine description
            $description = $this->getCommandDescription($name);
            $commandsDocs .= "| `composer $name` | $description |\n";
        }
        
        // Update in relevant docs
        $this->updateDocSection(
            'docs/COMMANDS.md',
            '## Composer Commands',
            '## ',
            $commandsDocs
        );
        
        $this->updates[] = "Updated composer commands documentation";
    }
    
    /**
     * Get command description based on name
     */
    private function getCommandDescription(string $command): string
    {
        $descriptions = [
            'test' => 'Run all tests',
            'test:unit' => 'Run unit tests only',
            'test:integration' => 'Run integration tests',
            'analyse' => 'Run static analysis with PHPStan',
            'check-style' => 'Check code style (PSR-12)',
            'fix-style' => 'Fix code style automatically',
            'docs:check' => 'Check documentation integrity',
            'docs:update' => 'Auto-update documentation'
        ];
        
        return $descriptions[$command] ?? 'Custom command';
    }
    
    /**
     * Update feature status in README
     */
    private function updateFeatureStatus(): void
    {
        $readme = $this->rootPath . '/README.md';
        if (!file_exists($readme)) {
            return;
        }
        
        $content = file_get_contents($readme);
        
        // Check actual implementation status
        $features = [
            'Project structure' => is_dir($this->rootPath . '/app'),
            'Composer configuration' => file_exists($this->rootPath . '/composer.json'),
            'Basic routing' => file_exists($this->rootPath . '/app/Core/Application.php'),
            'Environment configuration' => file_exists($this->rootPath . '/.env.example'),
            'PSR-4 autoloading' => isset(json_decode(file_get_contents($this->rootPath . '/composer.json'), true)['autoload']['psr-4']),
        ];
        
        foreach ($features as $feature => $implemented) {
            $icon = $implemented ? '✅' : '🚧';
            
            // Update in README
            $pattern = '/[-✅🚧📋] ' . preg_quote($feature, '/') . '/';
            $replacement = $icon . ' ' . $feature;
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        // Don't actually write unless significant changes
        // file_put_contents($readme, $content);
        
        $this->updates[] = "Checked feature status";
    }
    
    /**
     * Generate API documentation
     */
    private function generateApiDocumentation(): void
    {
        $apiDocs = "# Isotone API Reference\n\n";
        $apiDocs .= "*Auto-generated on " . date('Y-m-d H:i:s') . "*\n\n";
        
        // Scan for API endpoints
        $controllers = glob($this->rootPath . '/app/Http/Controllers/*Controller.php');
        
        foreach ($controllers as $controller) {
            $className = basename($controller, '.php');
            $apiDocs .= "## $className\n\n";
            
            // Extract methods (simplified)
            $content = file_get_contents($controller);
            preg_match_all('/public function (\w+)\(/', $content, $matches);
            
            foreach ($matches[1] as $method) {
                $apiDocs .= "### `$method()`\n\n";
                $apiDocs .= "Description pending...\n\n";
            }
        }
        
        file_put_contents($this->rootPath . '/docs/API-REFERENCE.md', $apiDocs);
        $this->updates[] = "Generated API documentation";
    }
    
    /**
     * Update a section in a documentation file
     */
    private function updateDocSection(string $file, string $startMarker, string $endMarker, string $newContent): void
    {
        $fullPath = $this->rootPath . '/' . $file;
        
        if (!file_exists($fullPath)) {
            // Create new file
            file_put_contents($fullPath, $newContent);
            return;
        }
        
        $content = file_get_contents($fullPath);
        
        // Find and replace section
        $pattern = '/' . preg_quote($startMarker, '/') . '.*?' . preg_quote($endMarker, '/') . '/s';
        $content = preg_replace($pattern, $newContent . "\n\n" . $endMarker, $content);
        
        file_put_contents($fullPath, $content);
    }
    
    /**
     * Update hooks documentation from code
     */
    private function updateHooksDocumentation(): void
    {
        echo "Updating hooks documentation...\n";
        
        // Run the hooks documentation generator
        $output = [];
        $returnCode = 0;
        exec('php ' . $this->rootPath . '/scripts/generate-hooks-docs.php', $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->updates[] = "HOOKS.md - Updated with discovered hooks";
            $this->updates[] = "user-docs/development/api-reference.md - Generated API reference";
            
            // Check if there are any output messages we should show
            foreach ($output as $line) {
                if (strpos($line, 'Actions found:') !== false || 
                    strpos($line, 'Filters found:') !== false) {
                    echo "  → $line\n";
                }
            }
        } else {
            echo "⚠️  Warning: Hooks documentation update failed\n";
        }
    }
    
    /**
     * Report updates
     */
    private function report(): void
    {
        if (empty($this->updates)) {
            echo "✅ No documentation updates needed.\n";
            return;
        }
        
        echo "📄 Documentation Updated:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        foreach ($this->updates as $update) {
            echo "  ✓ $update\n";
        }
        echo "\n✅ Documentation auto-update complete!\n";
    }
    
    /**
     * Sync documentation to user-docs
     */
    private function syncUserDocs(): void
    {
        echo "\n🔄 Syncing to user-docs...\n";
        
        // Run the sync script
        $output = [];
        $returnCode = 0;
        exec('php ' . $this->rootPath . '/scripts/sync-user-docs.php', $output, $returnCode);
        
        foreach ($output as $line) {
            echo $line . "\n";
        }
        
        if ($returnCode !== 0) {
            echo "⚠️  Warning: User docs sync encountered issues\n";
        }
    }
    
    /**
     * Sync IDE rules for consistent development
     */
    private function syncIdeRules(): void
    {
        echo "\n🔧 Syncing IDE rules...\n";
        
        // Run the IDE sync script
        $output = [];
        $returnCode = 0;
        exec('php ' . $this->rootPath . '/scripts/sync-ide-rules.php', $output, $returnCode);
        
        foreach ($output as $line) {
            echo $line . "\n";
        }
        
        if ($returnCode !== 0) {
            echo "⚠️  Warning: IDE rules sync encountered issues\n";
        }
    }
}

// Run the updater
$updater = new DocUpdater();
$updater->run();
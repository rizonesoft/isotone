<?php
/**
 * Documentation Auto-Updater for Isotone
 * 
 * Automatically generates and updates documentation from code
 * 
 * @package Isotone\Automation
 */

namespace Isotone\Automation\Generators;

class DocUpdater
{
    private string $rootPath;
    private array $updates = [];
    private bool $quietMode = false;
    
    public function __construct()
    {
        // Navigate from /iso-automation/src/Generators/ to root
        $this->rootPath = dirname(dirname(dirname(__DIR__)));
    }
    
    /**
     * Set quiet mode
     */
    public function setQuietMode(bool $quiet = true): void
    {
        $this->quietMode = $quiet;
    }
    
    /**
     * Run documentation update
     */
    public function run(): bool
    {
        if (!$this->quietMode) {
            echo "📝 Auto-updating Isotone Documentation...\n\n";
        }
        
        // Update various documentation sections
        $this->updateProjectStructure();
        $this->updateComposerCommands();
        $this->updateRoutesDocumentation();
        $this->updateEnvironmentVariables();
        $this->updateHooksDocumentation(); // This uses HooksAnalyzer
        
        $this->report();
        
        return true;
    }
    
    /**
     * Update routes documentation from actual routes
     */
    private function updateRoutesDocumentation(): void
    {
        // Routes can be in different places in Isotone
        $routesFile = $this->rootPath . '/routes/web.php';
        $appFile = $this->rootPath . '/app/Core/Application.php';
        
        $routes = [];
        
        // Check routes/web.php first
        if (file_exists($routesFile)) {
            $content = file_get_contents($routesFile);
            // Extract Laravel-style routes
            preg_match_all(
                "/Route::(get|post|put|delete|patch)\(['\"]([^'\"]+)['\"],.*?\)/s",
                $content,
                $matches
            );
            
            for ($i = 0; $i < count($matches[1]); $i++) {
                $routes[] = [
                    'method' => strtoupper($matches[1][$i]),
                    'path' => $matches[2][$i]
                ];
            }
        }
        
        // Check Application.php for custom routing
        if (file_exists($appFile)) {
            $content = file_get_contents($appFile);
            // Extract custom route definitions
            preg_match_all(
                "/routes->add\(['\"]([^'\"]+)['\"],\s*new Route\(['\"]([^'\"]+)['\"]/s",
                $content,
                $matches
            );
            
            for ($i = 0; $i < count($matches[1]); $i++) {
                $routes[] = [
                    'name' => $matches[1][$i],
                    'path' => $matches[2][$i]
                ];
            }
        }
        
        if (!empty($routes)) {
            $this->updates[] = "Found " . count($routes) . " routes";
            
            // Update routes documentation in development folder
            $routesDocPath = $this->rootPath . '/user-docs/development/routes.md';
            
            if (file_exists($routesDocPath)) {
                $existingContent = file_get_contents($routesDocPath);
                
                // Build the routes section
                $routesSection = "## Available Routes\n\n";
                $routesSection .= "> Auto-generated: " . date('Y-m-d H:i:s') . "\n\n";
                
                foreach ($routes as $route) {
                    if (isset($route['method'])) {
                        $routesSection .= "- **{$route['method']}** `{$route['path']}`\n";
                    } else {
                        $routesSection .= "- `{$route['path']}` ({$route['name']})\n";
                    }
                }
                
                // Replace the routes section in the existing file
                $pattern = '/## Available Routes.*?(?=##|\z)/s';
                if (preg_match($pattern, $existingContent)) {
                    $newContent = preg_replace($pattern, $routesSection . "\n", $existingContent);
                } else {
                    // Add at the end if section doesn't exist
                    $newContent = $existingContent . "\n\n" . $routesSection;
                }
                
                if ($newContent !== $existingContent) {
                    file_put_contents($routesDocPath, $newContent);
                    $this->updates[] = "Updated routes documentation";
                }
            }
        }
    }
    
    /**
     * Update project structure documentation
     */
    private function updateProjectStructure(): void
    {
        $structure = $this->generateDirectoryTree($this->rootPath, 0, [
            'vendor', '.git', 'node_modules', '.idea', '.vscode',
            'storage/logs', 'storage/cache', 'iso-content/cache'
        ]);
        
        // Update in development/project-structure.md
        $docPath = $this->rootPath . '/user-docs/development/project-structure.md';
        if (file_exists($docPath)) {
            $content = file_get_contents($docPath);
            
            // Update the structure section
            $pattern = '/```\n(?:project-root\/|isotone\/)\n.*?```/s';
            $replacement = "```\nisotone/\n" . $structure . "```";
            
            $newContent = preg_replace($pattern, $replacement, $content);
            if ($newContent !== $content) {
                file_put_contents($docPath, $newContent);
                $this->updates[] = "Updated project structure documentation";
            }
        }
    }
    
    /**
     * Generate directory tree
     */
    private function generateDirectoryTree(string $dir, int $level = 0, array $ignore = []): string
    {
        if ($level > 3) return ''; // Limit depth
        
        $tree = '';
        $items = scandir($dir);
        $indent = str_repeat('│   ', $level);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || in_array($item, $ignore)) {
                continue;
            }
            
            // Skip hidden files/folders at root level
            if ($level === 0 && strpos($item, '.') === 0) {
                continue;
            }
            
            $path = $dir . '/' . $item;
            $isLastItem = ($item === end($items));
            $prefix = $isLastItem ? '└── ' : '├── ';
            
            if (is_dir($path)) {
                $tree .= $indent . $prefix . $item . "/\n";
                if ($level < 2) { // Only go 2 levels deep
                    $tree .= $this->generateDirectoryTree($path, $level + 1, $ignore);
                }
            } else {
                // Only show important files
                if (in_array($item, ['composer.json', 'package.json', 'README.md', 'CLAUDE.md', 'config.sample.php'])) {
                    $tree .= $indent . $prefix . $item . "\n";
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
        $configSample = $this->rootPath . '/config.sample.php';
        if (!file_exists($configSample)) {
            return;
        }
        
        $content = file_get_contents($configSample);
        preg_match_all("/define\('([A-Z_]+)',\s*'?([^')]*)'?\);/", $content, $matches);
        
        if (!empty($matches[1])) {
            $varsDoc = "## Configuration Variables\n\n";
            $varsDoc .= "| Variable | Default | Description |\n";
            $varsDoc .= "|----------|---------|-------------|\n";
            
            for ($i = 0; $i < count($matches[1]); $i++) {
                $var = $matches[1][$i];
                $default = $matches[2][$i] ?: '(empty)';
                $description = $this->getVarDescription($var);
                $varsDoc .= "| `$var` | `$default` | $description |\n";
            }
            
            // Update configuration guide if it exists
            $configDocPath = $this->rootPath . '/user-docs/configuration/config-guide.md';
            if (file_exists($configDocPath)) {
                $docContent = file_get_contents($configDocPath);
                $pattern = '/## Configuration Variables.*?(?=##|\z)/s';
                
                if (preg_match($pattern, $docContent)) {
                    $docContent = preg_replace($pattern, $varsDoc . "\n", $docContent);
                } else {
                    $docContent .= "\n\n" . $varsDoc;
                }
                
                file_put_contents($configDocPath, $docContent);
                $this->updates[] = "Updated configuration variables documentation";
            }
        }
    }
    
    /**
     * Get variable description
     */
    private function getVarDescription(string $var): string
    {
        $descriptions = [
            'DB_HOST' => 'Database host',
            'DB_NAME' => 'Database name',
            'DB_USER' => 'Database username',
            'DB_PASSWORD' => 'Database password',
            'APP_URL' => 'Application URL',
            'APP_DEBUG' => 'Debug mode (true/false)',
            'SITE_NAME' => 'Site name',
            'ADMIN_EMAIL' => 'Administrator email',
            'TIMEZONE' => 'Default timezone',
            'UPLOAD_MAX_SIZE' => 'Maximum upload size',
            'CACHE_ENABLED' => 'Enable caching',
            'SESSION_LIFETIME' => 'Session lifetime in minutes'
        ];
        
        return $descriptions[$var] ?? 'Configuration setting';
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
        
        if (!empty($scripts)) {
            $commandsDoc = "## Available Commands\n\n";
            
            foreach ($scripts as $name => $command) {
                $description = $this->getCommandDescription($name);
                $commandsDoc .= "### `composer $name`\n";
                $commandsDoc .= "$description\n\n";
                
                if (is_array($command)) {
                    $commandsDoc .= "Runs:\n";
                    foreach ($command as $cmd) {
                        $commandsDoc .= "- `$cmd`\n";
                    }
                    $commandsDoc .= "\n";
                }
            }
            
            // Update commands documentation
            $commandsDocPath = $this->rootPath . '/user-docs/development/commands.md';
            if (file_exists($commandsDocPath)) {
                $content = file_get_contents($commandsDocPath);
                $pattern = '/## Available Commands.*?(?=##|\z)/s';
                
                if (preg_match($pattern, $content)) {
                    $content = preg_replace($pattern, $commandsDoc, $content);
                } else {
                    $content .= "\n\n" . $commandsDoc;
                }
                
                file_put_contents($commandsDocPath, $content);
                $this->updates[] = "Updated composer commands documentation";
            }
        }
    }
    
    /**
     * Get command description
     */
    private function getCommandDescription(string $command): string
    {
        $descriptions = [
            'test' => 'Run all tests',
            'test:unit' => 'Run unit tests only',
            'test:integration' => 'Run integration tests',
            'analyse' => 'Run static analysis with PHPStan',
            'check-style' => 'Check code style with PHP_CodeSniffer',
            'fix-style' => 'Fix code style issues automatically',
            'docs:check' => 'Check documentation integrity',
            'docs:update' => 'Update documentation from code',
            'docs:hooks' => 'Generate hooks documentation',
            'docs:all' => 'Run all documentation tasks',
            'ide:sync' => 'Sync IDE helper files',
            'validate:rules' => 'Validate automation rules',
            'version:patch' => 'Bump patch version',
            'version:minor' => 'Bump minor version',
            'version:major' => 'Bump major version'
        ];
        
        return $descriptions[$command] ?? 'Runs the ' . $command . ' task';
    }
    
    
    /**
     * Update hooks documentation
     */
    private function updateHooksDocumentation(): void
    {
        // Run hooks analyzer
        $analyzerPath = dirname(__DIR__) . '/Analyzers/HooksAnalyzer.php';
        if (file_exists($analyzerPath)) {
            require_once $analyzerPath;
            
            // Only run if SystemHooks.php exists
            $systemHooksFile = $this->rootPath . '/app/Core/SystemHooks.php';
            if (file_exists($systemHooksFile)) {
                $analyzer = new \Isotone\Automation\Analyzers\HooksAnalyzer();
                $analyzer->setQuiet($this->quietMode);
                $analyzer->analyze();
                $this->updates[] = "Updated hooks documentation";
            } else {
                if (!$this->quietMode) {
                    echo "⚠️  Skipping hooks documentation (SystemHooks.php not found)\n";
                }
            }
        }
    }
    
    /**
     * Generate report
     */
    private function report(): void
    {
        if (!$this->quietMode) {
            echo "\n📊 Documentation Update Summary:\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            
            if (empty($this->updates)) {
                echo "No updates were necessary.\n";
            } else {
                foreach ($this->updates as $update) {
                    echo "  ✓ $update\n";
                }
            }
            
            echo "\n✅ Documentation update complete!\n";
        }
    }
}
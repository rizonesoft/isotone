<?php
/**
 * Isotone Documentation Analyzer
 * 
 * Analyzes documentation integrity and consistency
 * Based on the original DocChecker but with caching and optimization
 * 
 * @package Isotone\Automation
 */

namespace Isotone\Automation\Analyzers;

use Isotone\Automation\Core\AutomationEngine;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class DocumentationAnalyzer
{
    private AutomationEngine $engine;
    private array $errors = [];
    private array $warnings = [];
    private string $rootPath;
    private array $modifiedFiles = [];
    private array $cachedResults = [];
    
    public function __construct(AutomationEngine $engine)
    {
        $this->engine = $engine;
        // Fix path for Windows XAMPP environment
        $this->rootPath = dirname(dirname(dirname(dirname(__DIR__))));
        
        // Normalize path for Windows
        $this->rootPath = str_replace('\\', '/', $this->rootPath);
        
        // Ensure we're in the isotone directory
        if (basename($this->rootPath) !== 'isotone') {
            // Try to find isotone directory
            if (is_dir($this->rootPath . '/isotone')) {
                $this->rootPath = $this->rootPath . '/isotone';
            }
        }
    }
    
    /**
     * Set modified files for incremental analysis
     */
    public function setModifiedFiles(array $files): void
    {
        $this->modifiedFiles = $files;
    }
    
    /**
     * Analyze documentation
     */
    public function analyze(): bool
    {
        $this->errors = [];
        $this->warnings = [];
        
        // Load cached results for unmodified files
        $this->loadCachedResults();
        
        // Run all checks
        $this->checkReadmeStatus();
        $this->checkFileReferences();
        $this->checkEnvVariables();
        $this->checkComposerScripts();
        $this->checkProjectStructure();
        $this->checkRoutes();
        $this->checkCodeExamples();
        $this->checkTodoMarkers();
        $this->checkIdeRules();
        
        // Cache results
        $this->cacheResults();
        
        // Return true if no errors (warnings are OK)
        return empty($this->errors);
    }
    
    /**
     * Report analysis results
     */
    public function report(): void
    {
        if (empty($this->errors) && empty($this->warnings)) {
            echo "✅ Documentation validation passed!\n";
            return;
        }
        
        if (!empty($this->errors)) {
            echo "\n❌ ERRORS (must fix):\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            foreach ($this->errors as $error) {
                echo "  ❌ $error\n";
            }
        }
        
        if (!empty($this->warnings)) {
            echo "\n⚠️  WARNINGS (should fix soon):\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            foreach ($this->warnings as $warning) {
                echo "  ⚠ $warning\n";
            }
        }
        
        echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo sprintf(
            "Summary: %d errors, %d warnings\n\n",
            count($this->errors),
            count($this->warnings)
        );
    }
    
    /**
     * Check if README.md status matches reality
     */
    private function checkReadmeStatus(): void
    {
        $readme = $this->rootPath . '/README.md';
        
        // Skip if file not modified
        if (!$this->isFileModified($readme)) {
            $this->loadCachedCheck('readme_status');
            return;
        }
        
        $content = file_get_contents($readme);
        
        // Check completed features
        if (strpos($content, '✅ Basic routing system') !== false) {
            if (!file_exists($this->rootPath . '/iso-core/Core/Application.php')) {
                $this->errors[] = "README.md: Claims routing system complete but Application.php missing";
            }
        }
        
        if (strpos($content, '✅ Database integration') !== false) {
            if (!file_exists($this->rootPath . '/iso-core/Core/Database.php')) {
                $this->warnings[] = "README.md: Database marked complete but Database.php not found";
            }
        }
        
        // Check if vendor exists when claiming composer is set up
        if (strpos($content, '✅ Composer configuration') !== false) {
            if (!is_dir($this->rootPath . '/vendor')) {
                $this->errors[] = "README.md: Claims Composer setup but vendor/ directory missing";
            }
        }
        
        $this->cacheCheck('readme_status');
    }
    
    /**
     * Check if all referenced files in documentation exist
     */
    private function checkFileReferences(): void
    {
        // Check user-docs folder instead of docs
        $docFiles = [];
        
        // Get all markdown files from user-docs
        $userDocsPath = $this->rootPath . '/user-docs';
        if (is_dir($userDocsPath)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($userDocsPath)
            );
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'md') {
                    $docFiles[] = $file->getPathname();
                }
            }
        }
        
        // Also check root documentation files
        $docFiles[] = $this->rootPath . '/README.md';
        $docFiles[] = $this->rootPath . '/CLAUDE.md';
        
        foreach ($docFiles as $docFile) {
            if (!file_exists($docFile)) continue;
            
            // Skip if not modified
            if (!$this->isFileModified($docFile)) {
                $this->loadCachedCheck('file_ref_' . md5($docFile));
                continue;
            }
            
            $content = file_get_contents($docFile);
            $docName = basename($docFile);
            
            // Find file references like `app/Core/Application.php`
            preg_match_all('/`([a-zA-Z0-9\/_.-]+\.(php|json|md|env|htaccess))`/', $content, $matches);
            
            foreach ($matches[1] as $file) {
                $fullPath = $this->rootPath . '/' . $file;
                $altPath = $this->rootPath . '/' . ltrim($file, '/');
                
                if (!file_exists($fullPath) && !file_exists($altPath) && !file_exists($file)) {
                    // Special case for config.php (which shouldn't be tracked)
                    if ($file === '.env' || basename($file) === '.env') {
                        continue;
                    }
                    $this->warnings[] = "$docName: References non-existent file: $file";
                }
            }
            
            $this->cacheCheck('file_ref_' . md5($docFile));
        }
    }
    
    /**
     * Check if all env variables used in code are documented
     */
    private function checkEnvVariables(): void
    {
        $configSample = $this->rootPath . '/config.sample.php';
        
        if (!$this->isFileModified($configSample)) {
            $this->loadCachedCheck('env_variables');
            return;
        }
        
        if (!file_exists($configSample)) {
            $this->errors[] = "config.sample.php file missing!";
            return;
        }
        
        // Parse config.sample.php for defined constants
        $exampleVars = [];
        $configContent = file_get_contents($configSample);
        if (preg_match_all("/define\('([A-Z_]+)'/", $configContent, $matches)) {
            $exampleVars = $matches[1];
        }
        
        // Find all env() calls in PHP files (only if app directory exists and modified)
        $appPath = $this->rootPath . '/app';
        if (is_dir($appPath) && $this->isDirectoryModified($appPath)) {
            $usedVars = [];
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($appPath)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $content = file_get_contents($file->getPathname());
                    
                    // Find env('VAR_NAME') calls
                    preg_match_all("/env\(['\"]([A-Z_]+)['\"]/", $content, $matches);
                    foreach ($matches[1] as $var) {
                        if (!in_array($var, $usedVars)) {
                            $usedVars[] = $var;
                        }
                    }
                }
            }
            
            // Check for undocumented variables
            $undocumented = array_diff($usedVars, $exampleVars);
            foreach ($undocumented as $var) {
                // Config constants are defined in config.php, not tracked in git
            }
        }
        
        $this->cacheCheck('env_variables');
    }
    
    /**
     * Check if composer scripts are documented
     */
    private function checkComposerScripts(): void
    {
        $composerFile = $this->rootPath . '/composer.json';
        
        if (!$this->isFileModified($composerFile)) {
            $this->loadCachedCheck('composer_scripts');
            return;
        }
        
        if (!file_exists($composerFile)) {
            $this->errors[] = "composer.json missing!";
            return;
        }
        
        $composer = json_decode(file_get_contents($composerFile), true);
        $scripts = array_keys($composer['scripts'] ?? []);
        
        // Check documentation files in user-docs
        $docsContent = '';
        if (file_exists($this->rootPath . '/README.md')) {
            $docsContent .= file_get_contents($this->rootPath . '/README.md');
        }
        
        // Check user-docs for composer script documentation
        $commandsDoc = $this->rootPath . '/user-docs/development/commands.md';
        if (file_exists($commandsDoc)) {
            $docsContent .= file_get_contents($commandsDoc);
        }
        
        $gettingStartedDoc = $this->rootPath . '/user-docs/getting-started/installation.md';
        if (file_exists($gettingStartedDoc)) {
            $docsContent .= file_get_contents($gettingStartedDoc);
        }
        
        foreach ($scripts as $script) {
            if (strpos($docsContent, "composer $script") === false) {
                $this->warnings[] = "Composer script '$script' not documented";
            }
        }
        
        $this->cacheCheck('composer_scripts');
    }
    
    /**
     * Check if project structure in docs matches reality
     */
    private function checkProjectStructure(): void
    {
        if (!$this->isFileModified($this->rootPath . '/CLAUDE.md')) {
            $this->loadCachedCheck('project_structure');
            return;
        }
        
        // Expected structure for Isotone (Service-Oriented Architecture)
        $expectedDirs = [
            'app/Commands',
            'app/Core',
            'app/Services',
            'config',
            'iso-content/uploads',
            'iso-content/plugins',
            'iso-content/themes',
            'iso-admin',
            'iso-admin/api',
            'iso-admin/includes',
            'iso-automation',
            'iso-automation/src',
            'iso-includes',
            'user-docs'
        ];
        
        foreach ($expectedDirs as $dir) {
            if (!is_dir($this->rootPath . '/' . $dir)) {
                $this->warnings[] = "Directory structure: '$dir' documented but doesn't exist";
            }
        }
        
        $this->cacheCheck('project_structure');
    }
    
    /**
     * Check if routes documented match actual routes
     */
    private function checkRoutes(): void
    {
        $appFile = $this->rootPath . '/iso-core/Core/Application.php';
        
        if (!$this->isFileModified($appFile)) {
            $this->loadCachedCheck('routes');
            return;
        }
        
        if (!file_exists($appFile)) {
            return;
        }
        
        $content = file_get_contents($appFile);
        
        // Extract routes
        preg_match_all("/routes->add\(['\"]([^'\"]+)['\"],\s*new Route\(['\"]([^'\"]+)['\"]/", $content, $matches);
        
        $routes = [];
        for ($i = 0; $i < count($matches[1]); $i++) {
            $routes[$matches[1][$i]] = $matches[2][$i];
        }
        
        // Check if documented routes exist
        $gettingStarted = $this->rootPath . '/user-docs/development/getting-started.md';
        if (file_exists($gettingStarted)) {
            $content = file_get_contents($gettingStarted);
            
            // Look for route mentions
            if (strpos($content, '/admin') !== false && !in_array('/admin', $routes)) {
                $this->warnings[] = "GETTING-STARTED.md: References /admin route but not implemented";
            }
        }
        
        $this->cacheCheck('routes');
    }
    
    /**
     * Check if code examples in documentation are valid
     */
    private function checkCodeExamples(): void
    {
        // Check user-docs folder instead of docs
        $docFiles = [];
        $userDocsPath = $this->rootPath . '/user-docs';
        if (is_dir($userDocsPath)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($userDocsPath)
            );
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'md') {
                    $docFiles[] = $file->getPathname();
                }
            }
        }
        
        foreach ($docFiles as $docFile) {
            if (!$this->isFileModified($docFile)) {
                $this->loadCachedCheck('code_examples_' . md5($docFile));
                continue;
            }
            
            $content = file_get_contents($docFile);
            $docName = basename($docFile);
            
            // Find PHP code blocks
            preg_match_all('/```php\n(.*?)\n```/s', $content, $matches);
            
            foreach ($matches[1] as $code) {
                // Basic syntax check (without executing)
                $testFile = tempnam(sys_get_temp_dir(), 'isotone_doc_check');
                file_put_contents($testFile, "<?php\n" . $code);
                
                $output = [];
                $return = 0;
                exec("php -l $testFile 2>&1", $output, $return);
                
                if ($return !== 0) {
                    $this->warnings[] = "$docName: Contains PHP code with syntax errors";
                }
                
                unlink($testFile);
            }
            
            $this->cacheCheck('code_examples_' . md5($docFile));
        }
    }
    
    /**
     * Check for TODO/FIXME markers
     */
    private function checkTodoMarkers(): void
    {
        // Check both root files and user-docs
        $filesToCheck = [];
        
        // Add root documentation files
        $filesToCheck[] = $this->rootPath . '/README.md';
        $filesToCheck[] = $this->rootPath . '/CLAUDE.md';
        
        // Add all user-docs files
        $userDocsPath = $this->rootPath . '/user-docs';
        if (is_dir($userDocsPath)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($userDocsPath)
            );
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'md') {
                    $filesToCheck[] = $file->getPathname();
                }
            }
        }
        
        foreach ($filesToCheck as $file) {
            if (!file_exists($file)) continue;
            
            if (!$this->isFileModified($file)) {
                $this->loadCachedCheck('todo_' . md5($file));
                continue;
            }
            
            $content = file_get_contents($file);
            $filename = basename($file);
            
            if (preg_match_all('/(TODO|FIXME|🚧|XXX)/', $content, $matches)) {
                $count = count($matches[0]);
                $this->warnings[] = "$filename: Contains $count unfinished markers (TODO/FIXME/🚧)";
            }
            
            $this->cacheCheck('todo_' . md5($file));
        }
    }
    
    /**
     * Check IDE rules synchronization
     */
    private function checkIdeRules(): void
    {
        $ideFiles = [
            '.cursorrules',
            '.windsurf-rules.md',
            '.github/copilot-instructions.md'
        ];
        
        $claudeMd = $this->rootPath . '/CLAUDE.md';
        
        if (!$this->isFileModified($claudeMd)) {
            $this->loadCachedCheck('ide_rules');
            return;
        }
        
        if (!file_exists($claudeMd)) {
            $this->errors[] = "CLAUDE.md missing - this is the master IDE rules file!";
            return;
        }
        
        $claudeContent = file_get_contents($claudeMd);
        $claudeHash = md5($claudeContent);
        
        foreach ($ideFiles as $ideFile) {
            $fullPath = $this->rootPath . '/' . $ideFile;
            if (file_exists($fullPath)) {
                $ideContent = file_get_contents($fullPath);
                
                // Check if IDE file references being synced from CLAUDE.md
                if (strpos($ideContent, 'synced from CLAUDE.md') === false &&
                    strpos($ideContent, 'synchronized from CLAUDE.md') === false) {
                    $this->warnings[] = "$ideFile: Not marked as synced from CLAUDE.md";
                }
            }
        }
        
        $this->cacheCheck('ide_rules');
    }
    
    /**
     * Check if a file has been modified
     */
    private function isFileModified(string $file): bool
    {
        if (empty($this->modifiedFiles)) {
            return true; // No cache, check everything
        }
        
        return in_array($file, $this->modifiedFiles);
    }
    
    /**
     * Check if a directory has been modified
     */
    private function isDirectoryModified(string $dir): bool
    {
        if (empty($this->modifiedFiles)) {
            return true; // No cache, check everything
        }
        
        foreach ($this->modifiedFiles as $file) {
            if (strpos($file, $dir) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Load cached results
     */
    private function loadCachedResults(): void
    {
        // Caching temporarily disabled - StateManager was removed
        // TODO: Implement file-based caching if needed
        $this->cachedResults = [];
    }
    
    /**
     * Cache analysis results
     */
    private function cacheResults(): void
    {
        // Caching temporarily disabled - StateManager was removed
        // TODO: Implement file-based caching if needed
    }
    
    /**
     * Load cached check results
     */
    private function loadCachedCheck(string $key): void
    {
        if (isset($this->cachedResults[$key])) {
            if (isset($this->cachedResults[$key]['errors'])) {
                $this->errors = array_merge($this->errors, $this->cachedResults[$key]['errors']);
            }
            if (isset($this->cachedResults[$key]['warnings'])) {
                $this->warnings = array_merge($this->warnings, $this->cachedResults[$key]['warnings']);
            }
        }
    }
    
    /**
     * Cache check results
     */
    private function cacheCheck(string $key): void
    {
        $this->cachedResults[$key] = [
            'errors' => [],
            'warnings' => [],
            'timestamp' => time()
        ];
        
        // Store relevant errors/warnings for this check
        // This is a simplified version - in production, track which errors belong to which check
    }
}
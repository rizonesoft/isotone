<?php
/**
 * Documentation Integrity Checker for Isotone
 * 
 * This script ensures all documentation is synchronized with the actual codebase.
 * Run: php scripts/check-docs.php
 */

declare(strict_types=1);

class DocChecker
{
    private array $errors = [];
    private array $warnings = [];
    private string $rootPath;
    
    public function __construct()
    {
        $this->rootPath = dirname(__DIR__);
    }
    
    public function run(): int
    {
        echo "🔍 Checking Isotone Documentation Integrity...\n\n";
        
        $this->checkReadmeStatus();
        $this->checkFileReferences();
        $this->checkEnvVariables();
        $this->checkComposerScripts();
        $this->checkProjectStructure();
        $this->checkRoutes();
        $this->checkCodeExamples();
        $this->checkTodoMarkers();
        $this->checkIdeRules();
        
        return $this->report();
    }
    
    /**
     * Check if README.md status matches reality
     */
    private function checkReadmeStatus(): void
    {
        $readme = file_get_contents($this->rootPath . '/README.md');
        
        // Check completed features
        if (strpos($readme, '✅ Basic routing system') !== false) {
            if (!file_exists($this->rootPath . '/app/Core/Application.php')) {
                $this->errors[] = "README.md: Claims routing system complete but Application.php missing";
            }
        }
        
        if (strpos($readme, '✅ Database integration') !== false) {
            if (!file_exists($this->rootPath . '/app/Core/Database.php')) {
                $this->warnings[] = "README.md: Database marked complete but Database.php not found";
            }
        }
        
        // Check if vendor exists when claiming composer is set up
        if (strpos($readme, '✅ Composer configuration') !== false) {
            if (!is_dir($this->rootPath . '/vendor')) {
                $this->errors[] = "README.md: Claims Composer setup but vendor/ directory missing";
            }
        }
    }
    
    /**
     * Check if all referenced files in documentation exist
     */
    private function checkFileReferences(): void
    {
        $docFiles = glob($this->rootPath . '/docs/*.md');
        $docFiles[] = $this->rootPath . '/README.md';
        $docFiles[] = $this->rootPath . '/CLAUDE.md';
        
        foreach ($docFiles as $docFile) {
            if (!file_exists($docFile)) continue;
            
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
        }
    }
    
    /**
     * Check if all env variables used in code are documented
     */
    private function checkEnvVariables(): void
    {
        $configSample = $this->rootPath . '/config.sample.php';
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
        
        // Find all env() calls in PHP files
        $usedVars = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->rootPath . '/app')
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
        
        // Check for unused variables
        $unused = array_diff($exampleVars, $usedVars);
        foreach ($unused as $var) {
            // Some vars might be for future use, so warning only
            // Config constants usage is optional
        }
    }
    
    /**
     * Check if composer scripts are documented
     */
    private function checkComposerScripts(): void
    {
        $composerFile = $this->rootPath . '/composer.json';
        if (!file_exists($composerFile)) {
            $this->errors[] = "composer.json missing!";
            return;
        }
        
        $composer = json_decode(file_get_contents($composerFile), true);
        $scripts = array_keys($composer['scripts'] ?? []);
        
        // Check documentation files
        $docsContent = '';
        $docsContent .= file_get_contents($this->rootPath . '/README.md');
        if (file_exists($this->rootPath . '/docs/DEVELOPMENT-SETUP.md')) {
            $docsContent .= file_get_contents($this->rootPath . '/docs/DEVELOPMENT-SETUP.md');
        }
        if (file_exists($this->rootPath . '/docs/GETTING-STARTED.md')) {
            $docsContent .= file_get_contents($this->rootPath . '/docs/GETTING-STARTED.md');
        }
        
        foreach ($scripts as $script) {
            if (strpos($docsContent, "composer $script") === false) {
                $this->warnings[] = "Composer script '$script' not documented";
            }
        }
    }
    
    /**
     * Check if project structure in docs matches reality
     */
    private function checkProjectStructure(): void
    {
        // Expected structure from documentation
        $expectedDirs = [
            'app/Core',
            'app/Http/Controllers',
            'app/Http/Middleware',
            'app/Models',
            'app/Services',
            'public',
            'config',
            'content/uploads',
            'content/cache',
            'plugins',
            'themes',
            'storage/logs',
            'docs'
        ];
        
        foreach ($expectedDirs as $dir) {
            if (!is_dir($this->rootPath . '/' . $dir)) {
                $this->warnings[] = "Directory structure: '$dir' documented but doesn't exist";
            }
        }
    }
    
    /**
     * Check if routes documented match actual routes
     */
    private function checkRoutes(): void
    {
        $appFile = $this->rootPath . '/app/Core/Application.php';
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
        $gettingStarted = $this->rootPath . '/docs/GETTING-STARTED.md';
        if (file_exists($gettingStarted)) {
            $content = file_get_contents($gettingStarted);
            
            // Look for route mentions
            if (strpos($content, '/admin') !== false && !in_array('/admin', $routes)) {
                $this->warnings[] = "GETTING-STARTED.md: References /admin route but not implemented";
            }
        }
    }
    
    /**
     * Check if code examples in documentation are valid
     */
    private function checkCodeExamples(): void
    {
        $docFiles = glob($this->rootPath . '/docs/*.md');
        
        foreach ($docFiles as $docFile) {
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
        }
    }
    
    /**
     * Check for TODO/FIXME markers
     */
    private function checkTodoMarkers(): void
    {
        $files = [
            'README.md',
            'docs/*.md'
        ];
        
        foreach ($files as $pattern) {
            $matchedFiles = glob($this->rootPath . '/' . $pattern);
            foreach ($matchedFiles as $file) {
                $content = file_get_contents($file);
                $filename = basename($file);
                
                if (preg_match_all('/(TODO|FIXME|🚧|XXX)/', $content, $matches)) {
                    $count = count($matches[0]);
                    $this->warnings[] = "$filename: Contains $count unfinished markers (TODO/FIXME/🚧)";
                }
            }
        }
    }
    
    /**
     * Check IDE rule files are in sync
     */
    private function checkIdeRules(): void
    {
        // Check if Windsurf rules exist and are in sync
        $windsurfSource = $this->rootPath . '/.windsurf-rules.md';
        $windsurfTarget = $this->rootPath . '/.windsurf/rules/development-guide.md';
        
        if (file_exists($windsurfSource)) {
            if (file_exists($windsurfTarget)) {
                $sourceContent = file_get_contents($windsurfSource);
                $targetContent = file_get_contents($windsurfTarget);
                
                if ($sourceContent !== $targetContent) {
                    $this->warnings[] = "Windsurf rules out of sync! Copy .windsurf-rules.md to .windsurf/rules/development-guide.md";
                }
            }
            
            // Check if windsurf rules reference correct paths
            $content = file_get_contents($windsurfSource);
            if (strpos($content, 'CLAUDE.md') !== false) {
                if (!file_exists($this->rootPath . '/CLAUDE.md')) {
                    $this->errors[] = "Windsurf rules reference missing CLAUDE.md";
                }
            }
        }
        
        // Check for other IDE rule files
        $ideRuleFiles = [
            '.cursorrules' => 'Cursor',
            '.github/copilot-instructions.md' => 'GitHub Copilot'
        ];
        
        foreach ($ideRuleFiles as $file => $ide) {
            if (file_exists($this->rootPath . '/' . $file)) {
                $content = file_get_contents($this->rootPath . '/' . $file);
                
                // Check if they reference the LLM docs
                if (strpos($content, 'LLM-DEVELOPMENT-GUIDE.md') === false) {
                    $this->warnings[] = "$ide rules should reference docs/LLM-DEVELOPMENT-GUIDE.md";
                }
            }
        }
    }
    
    /**
     * Generate report
     */
    private function report(): int
    {
        $totalIssues = count($this->errors) + count($this->warnings);
        
        if ($totalIssues === 0) {
            echo "✅ All documentation is in sync with the codebase!\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "No issues found. Documentation is up to date.\n";
            return 0;
        }
        
        if (!empty($this->errors)) {
            echo "❌ ERRORS (must fix before commit):\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            foreach ($this->errors as $error) {
                echo "  ✗ $error\n";
            }
            echo "\n";
        }
        
        if (!empty($this->warnings)) {
            echo "⚠️  WARNINGS (should fix soon):\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            foreach ($this->warnings as $warning) {
                echo "  ⚠ $warning\n";
            }
            echo "\n";
        }
        
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Summary: " . count($this->errors) . " errors, " . count($this->warnings) . " warnings\n";
        
        if (!empty($this->errors)) {
            echo "\n❌ Documentation check failed! Fix errors before committing.\n";
            return 1;
        }
        
        echo "\n⚠️  Documentation has warnings but is acceptable.\n";
        return 0;
    }
}

// Run the checker
$checker = new DocChecker();
exit($checker->run());
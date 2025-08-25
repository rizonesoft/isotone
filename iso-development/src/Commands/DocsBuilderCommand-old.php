<?php
/**
 * Documentation HTML Builder Command
 * 
 * Builds static HTML documentation from markdown files
 * Can use VitePress if available, or falls back to PHP-based generation
 * 
 * @package Isotone\Automation
 */

namespace Isotone\Automation\Commands;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\Environment\Environment;

class DocsBuilderCommand
{
    private string $docsPath;
    private string $outputPath;
    private array $navigation = [];
    private bool $useVitePress = false;
    
    public function __construct()
    {
        $this->docsPath = dirname(dirname(dirname(__DIR__))) . '/user-docs';
        // Output to /docs folder in the root directory
        $this->outputPath = dirname(dirname(dirname(__DIR__))) . '/docs';
    }
    
    /**
     * Build HTML documentation
     */
    public function build(): int
    {
        echo "📚 Building HTML Documentation...\n\n";
        
        // Check if VitePress is available
        if ($this->checkVitePress()) {
            return $this->buildWithVitePress();
        }
        
        // Fallback to PHP-based generation
        return $this->buildWithPHP();
    }
    
    /**
     * Check if VitePress is available
     */
    private function checkVitePress(): bool
    {
        // Check if node_modules exists
        $nodeModules = $this->docsPath . '/node_modules';
        if (!is_dir($nodeModules)) {
            echo "⚠️  VitePress not installed. Using PHP fallback.\n";
            return false;
        }
        
        // Check if vitepress binary exists
        $vitepressBin = $nodeModules . '/.bin/vitepress';
        if (!file_exists($vitepressBin)) {
            echo "⚠️  VitePress binary not found. Using PHP fallback.\n";
            return false;
        }
        
        // Check if Node.js is available
        exec('node --version 2>&1', $output, $returnCode);
        if ($returnCode !== 0) {
            echo "⚠️  Node.js not available. Using PHP fallback.\n";
            return false;
        }
        
        $this->useVitePress = true;
        return true;
    }
    
    /**
     * Build documentation using VitePress
     */
    private function buildWithVitePress(): int
    {
        echo "🚀 Building with VitePress...\n";
        
        $cwd = getcwd();
        chdir($this->docsPath);
        
        // Run VitePress build
        $command = 'npm run build 2>&1';
        $output = [];
        $returnCode = 0;
        
        echo "Running: $command\n";
        exec($command, $output, $returnCode);
        
        foreach ($output as $line) {
            echo "  $line\n";
        }
        
        chdir($cwd);
        
        if ($returnCode === 0) {
            // Move VitePress output from .vitepress/dist to /docs
            $vitepressOutput = $this->docsPath . '/.vitepress/dist';
            if (is_dir($vitepressOutput)) {
                // Create docs directory if it doesn't exist
                if (!is_dir($this->outputPath)) {
                    mkdir($this->outputPath, 0755, true);
                }
                
                // Copy all files from VitePress dist to docs
                $this->copyDirectory($vitepressOutput, $this->outputPath);
                echo "\n✅ Documentation built and moved to /docs!\n";
            } else {
                echo "\n✅ Documentation built successfully!\n";
            }
            echo "📁 Output directory: " . $this->outputPath . "\n";
            
            // Count files
            $htmlFiles = glob($this->outputPath . '/*.html');
            $htmlFiles = array_merge($htmlFiles, glob($this->outputPath . '/**/*.html'));
            echo "📄 Generated " . count($htmlFiles) . " HTML files\n";
        } else {
            echo "\n❌ VitePress build failed. Falling back to PHP.\n";
            return $this->buildWithPHP();
        }
        
        return $returnCode;
    }
    
    /**
     * Build documentation using PHP (fallback)
     */
    private function buildWithPHP(): int
    {
        echo "🔧 Building with PHP Markdown processor...\n";
        
        // Create output directory
        if (!is_dir($this->outputPath)) {
            if (!mkdir($this->outputPath, 0755, true)) {
                echo "❌ Failed to create output directory: " . $this->outputPath . "\n";
                return 1;
            }
        }
        
        echo "📁 Output directory: " . $this->outputPath . "\n";
        
        // Create assets directory and generate CSS
        $this->setupAssets();
        
        // Load navigation from config
        $this->loadNavigation();
        
        // Process markdown files
        $files = $this->getMarkdownFiles();
        $processed = 0;
        $errors = 0;
        
        foreach ($files as $file) {
            echo "  Processing: " . basename($file) . "...";
            
            try {
                $this->processMarkdownFile($file);
                $processed++;
                echo " ✓\n";
            } catch (\Exception $e) {
                $errors++;
                echo " ✗ (" . $e->getMessage() . ")\n";
            }
        }
        
        // Generate index page
        $this->generateIndexPage();
        
        // Copy assets
        $this->copyAssets();
        
        echo "\n📊 Build Summary:\n";
        echo "  Files processed: $processed\n";
        if ($errors > 0) {
            echo "  Errors: $errors\n";
        }
        echo "  Output: " . $this->outputPath . "\n";
        
        return $errors > 0 ? 1 : 0;
    }
    
    /**
     * Load navigation structure
     */
    private function loadNavigation(): void
    {
        $configFile = $this->docsPath . '/.vitepress/config.js';
        if (file_exists($configFile)) {
            // Parse JavaScript config (simplified)
            $content = file_get_contents($configFile);
            
            // Extract sidebar configuration
            if (preg_match('/sidebar:\s*{([^}]+)}/s', $content, $matches)) {
                // Parse sidebar structure
                // This is a simplified parser - would need more robust parsing for production
                $this->navigation = [
                    'Getting Started' => [
                        'installation.md',
                        'configuration.md',
                        'first-steps.md'
                    ],
                    'Development' => [
                        'architecture.md',
                        'project-structure.md',
                        'bean-database-guide.md'
                    ],
                    'API Reference' => [
                        'endpoints.md',
                        'authentication.md'
                    ]
                ];
            }
        }
    }
    
    /**
     * Get all markdown files
     */
    private function getMarkdownFiles(): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->docsPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'md') {
                $path = $file->getPathname();
                // Skip hidden directories
                if (strpos($path, '/.') === false) {
                    $files[] = $path;
                }
            }
        }
        
        return $files;
    }
    
    /**
     * Process a markdown file to HTML
     */
    private function processMarkdownFile(string $filePath): void
    {
        $content = file_get_contents($filePath);
        
        // Setup CommonMark with extensions
        $environment = new Environment();
        $environment->addExtension(new FrontMatterExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new AutolinkExtension());
        $environment->addExtension(new TaskListExtension());
        
        $converter = new CommonMarkConverter([], $environment);
        $result = $converter->convert($content);
        
        // Extract front matter
        $frontMatter = [];
        if ($result instanceof \League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter) {
            $frontMatter = $result->getFrontMatter() ?? [];
        }
        
        // Determine output path - handle both Windows and Unix paths
        $relativePath = str_replace($this->docsPath, '', $filePath);
        $relativePath = str_replace('\\', '/', $relativePath); // Convert Windows backslashes
        $relativePath = ltrim($relativePath, '/\\'); // Remove leading slashes
        
        // Calculate depth for relative paths
        $depth = substr_count($relativePath, '/');
        $pathToRoot = $depth > 0 ? str_repeat('../', $depth) : './';
        
        // Generate HTML with proper relative paths
        $html = $this->generateHTMLPageWithDepth(
            $result->getContent(),
            $frontMatter['title'] ?? $this->getTitleFromPath($filePath),
            $frontMatter['description'] ?? '',
            $pathToRoot
        );
        
        $outputFile = $this->outputPath . '/' . str_replace('.md', '.html', $relativePath);
        
        // Create directory if needed
        $outputDir = dirname($outputFile);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        
        // Write HTML file
        file_put_contents($outputFile, $html);
    }
    
    /**
     * Generate complete HTML page
     */
    private function generateHTMLPage(string $content, string $title, string $description): string
    {
        $nav = $this->generateNavigation();
        
        return <<<HTML
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$title - Isotone Documentation</title>
    <meta name="description" content="$description">
    <link rel="stylesheet" href="./assets/css/documentation.css">
    <link rel="icon" href="/isotone/favicon.ico">
</head>
<body>
    <!-- Top Admin Bar -->
    <header class="admin-header">
        <div class="header-inner">
            <!-- Left side -->
            <div class="header-left">
                <!-- Logo -->
                <div class="logo-container">
                    <img src="./assets/images/logo.svg" alt="Isotone" class="isotone-logo isotone-logo-pulse">
                    <h2 class="isotone-text isotone-text-shimmer">Isotone Docs</h2>
                </div>
            </div>
            
            <!-- Right side -->
            <div class="header-right">
                <!-- Theme Toggle -->
                <button onclick="toggleTheme()" class="theme-toggle" title="Toggle theme">
                    <svg class="sun-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <svg class="moon-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                </button>
                <!-- Search -->
                <div class="search-box">
                    <input type="text" placeholder="Search docs..." id="search">
                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </header>

    <div class="main-container">
        <nav class="sidebar">
            <div class="nav-section">
                $nav
            </div>
        </nav>
        <main class="content">
            <div class="content-inner">
                $content
            </div>
        </main>
    </div>
    <script>
        // Theme toggle
        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');
            if (isDark) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }
        
        // Initialize theme
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light') {
            document.documentElement.classList.remove('dark');
        }
        
        // Search functionality
        document.getElementById('search').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            const links = document.querySelectorAll('.sidebar a');
            links.forEach(link => {
                const text = link.textContent.toLowerCase();
                const parent = link.parentElement;
                if (text.includes(query)) {
                    link.style.display = 'block';
                    if (parent) parent.style.display = 'block';
                } else {
                    link.style.display = 'none';
                }
            });
            
            // Show all sections if query is empty
            if (!query) {
                links.forEach(link => {
                    link.style.display = 'block';
                });
            }
        });
        
        // Highlight current page
        const currentPath = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.sidebar a');
        navLinks.forEach(link => {
            if (link.getAttribute('href').includes(currentPath)) {
                link.classList.add('active');
            }
        });
    </script>
    <script src="./assets/js/navigation.js"></script>
</body>
</html>
HTML;
    }
    
    /**
     * Generate HTML page with depth-aware navigation
     */
    private function generateHTMLPageWithDepth(string $content, string $title, string $description, string $pathToRoot = './'): string
    {
        $nav = $this->generateNavigationWithDepth($pathToRoot);
        $cssPath = $pathToRoot . 'assets/css/documentation.css';
        $jsPath = $pathToRoot . 'assets/js/navigation.js';
        $logoPath = $pathToRoot . 'index.html';
        
        return <<<HTML
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$title - Isotone Documentation</title>
    <meta name="description" content="$description">
    <link rel="stylesheet" href="$cssPath">
    <link rel="icon" href="/isotone/favicon.ico">
</head>
<body>
    <!-- Top Admin Bar -->
    <header class="admin-header">
        <div class="header-inner">
            <!-- Left side -->
            <div class="header-left">
                <!-- Logo -->
                <div class="logo-container">
                    <img src="${pathToRoot}assets/images/logo.svg" alt="Isotone" class="isotone-logo isotone-logo-pulse">
                    <h2 class="isotone-text isotone-text-shimmer">Isotone Docs</h2>
                </div>
            </div>
            
            <!-- Right side -->
            <div class="header-right">
                <!-- Theme Toggle -->
                <button onclick="toggleTheme()" class="theme-toggle" title="Toggle theme">
                    <svg class="sun-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <svg class="moon-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                </button>
                <!-- Search -->
                <div class="search-box">
                    <input type="text" placeholder="Search docs..." id="search">
                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </header>

    <div class="main-container">
        <nav class="sidebar">
            <div class="nav-section">
                $nav
            </div>
        </nav>
        <main class="content">
            <div class="content-inner">
                $content
            </div>
        </main>
    </div>
    <script>
        // Theme toggle
        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');
            if (isDark) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }
        
        // Initialize theme
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light') {
            document.documentElement.classList.remove('dark');
        }
        
        // Search functionality
        document.getElementById('search').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            const links = document.querySelectorAll('.sidebar a');
            links.forEach(link => {
                const text = link.textContent.toLowerCase();
                const parent = link.parentElement;
                if (text.includes(query)) {
                    link.style.display = 'block';
                    if (parent) parent.style.display = 'block';
                } else {
                    link.style.display = 'none';
                }
            });
            
            // Show all sections if query is empty
            if (!query) {
                links.forEach(link => {
                    link.style.display = 'block';
                });
            }
        });
        
        // Highlight current page
        const currentPath = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.sidebar a');
        navLinks.forEach(link => {
            if (link.getAttribute('href').includes(currentPath)) {
                link.classList.add('active');
            }
        });
    </script>
    <script src="$jsPath"></script>
</body>
</html>
HTML;
    }
    
    /**
     * Generate navigation HTML with depth-aware paths
     */
    private function generateNavigationWithDepth(string $pathToRoot = './'): string
    {
        $html = '';
        
        // Heroicons for each section
        $sectionIcons = [
            'Getting Started' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>',
            'Development' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>',
            'API Reference' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>',
            'Configuration' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>',
            'Toni AI' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>',
            'Troubleshooting' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        ];
        
        // Real navigation structure based on actual folders
        $realNavigation = [
            'Getting Started' => [
                'getting-started/installation',
                'getting-started/tech-stack'
            ],
            'Development' => [
                'development/architecture',
                'development/project-structure',
                'development/bean-database-guide',
                'development/commands',
                'development/routes',
                'development/hooks'
            ],
            'API Reference' => [
                'api-reference/endpoints',
                'api-reference/authentication',
                'api-reference/overview'
            ],
            'Configuration' => [
                'configuration/database',
                'configuration/environment',
                'configuration/config-guide'
            ],
            'Toni AI' => [
                'toni/toni-overview',
                'toni/toni-setup',
                'toni/toni-usage'
            ],
            'Troubleshooting' => [
                'troubleshooting/common-issues',
                'troubleshooting/error-reference',
                'troubleshooting/faq'
            ]
        ];
        
        foreach ($realNavigation as $section => $items) {
            $icon = $sectionIcons[$section] ?? '';
            $html .= "<h3>{$icon}{$section}</h3>\n<ul>\n";
            foreach ($items as $item) {
                $title = $this->getTitleFromPath($item);
                // Use relative paths from current depth to docs root
                $link = $item . '.html';
                $html .= "  <li><a href=\"{$pathToRoot}{$link}\">$title</a></li>\n";
            }
            $html .= "</ul>\n";
        }
        
        return $html;
    }
    
    /**
     * Generate navigation HTML
     */
    private function generateNavigation(): string
    {
        // For root level, just use ./ prefix
        return $this->generateNavigationWithDepth('./');
    }
    
    /**
     * Get title from file path
     */
    private function getTitleFromPath(string $path): string
    {
        $filename = basename($path, '.md');
        $title = str_replace('-', ' ', $filename);
        return ucwords($title);
    }
    
    /**
     * Generate index page
     */
    private function generateIndexPage(): void
    {
        $content = <<<HTML
<h1>Isotone Documentation</h1>
<p>Welcome to the Isotone documentation. Use the navigation on the left to browse topics.</p>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
    <div class="card">
        <div class="card-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
        </div>
        <h2>Getting Started</h2>
        <p>Learn how to install and configure Isotone for your project.</p>
        <ul>
            <li><a href="./getting-started/installation.html">Installation Guide</a></li>
            <li><a href="./getting-started/tech-stack.html">Technology Stack</a></li>
        </ul>
    </div>
    
    <div class="card">
        <div class="card-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
            </svg>
        </div>
        <h2>Development</h2>
        <p>Dive deep into Isotone's architecture and development practices.</p>
        <ul>
            <li><a href="./development/architecture.html">Architecture Overview</a></li>
            <li><a href="./development/bean-database-guide.html">Database Guide</a></li>
            <li><a href="./development/project-structure.html">Project Structure</a></li>
        </ul>
    </div>
    
    <div class="card">
        <div class="card-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
        </div>
        <h2>API Reference</h2>
        <p>Complete reference for all Isotone APIs and endpoints.</p>
        <ul>
            <li><a href="./api-reference/endpoints.html">REST Endpoints</a></li>
            <li><a href="./api-reference/authentication.html">Authentication</a></li>
            <li><a href="./api-reference/overview.html">API Overview</a></li>
        </ul>
    </div>
    
    <div class="card">
        <div class="card-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
        </div>
        <h2>Configuration</h2>
        <p>Configure Isotone to meet your specific requirements.</p>
        <ul>
            <li><a href="./configuration/database.html">Database Setup</a></li>
            <li><a href="./configuration/environment.html">Environment Variables</a></li>
            <li><a href="./configuration/config-guide.html">Configuration Guide</a></li>
        </ul>
    </div>
    
    <div class="card">
        <div class="card-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
        </div>
        <h2>Toni AI</h2>
        <p>Learn about Isotone's AI assistant and how to use it effectively.</p>
        <ul>
            <li><a href="./toni/toni-overview.html">Toni Overview</a></li>
            <li><a href="./toni/toni-setup.html">Setup Guide</a></li>
            <li><a href="./toni/toni-usage.html">Usage & Commands</a></li>
        </ul>
    </div>
    
    <div class="card">
        <div class="card-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h2>Troubleshooting</h2>
        <p>Find solutions to common issues and error messages.</p>
        <ul>
            <li><a href="./troubleshooting/common-issues.html">Common Issues</a></li>
            <li><a href="./troubleshooting/error-reference.html">Error Reference</a></li>
            <li><a href="./troubleshooting/faq.html">FAQ</a></li>
        </ul>
    </div>
</div>
HTML;
        
        $html = $this->generateHTMLPage(
            $content,
            'Isotone Documentation',
            'Complete documentation for Isotone - The modern content management system'
        );
        
        file_put_contents($this->outputPath . '/index.html', $html);
    }
    
    /**
     * Setup assets directory and generate CSS
     */
    private function setupAssets(): void
    {
        // Create assets directory
        $assetsDir = $this->outputPath . '/assets';
        if (!is_dir($assetsDir)) {
            mkdir($assetsDir, 0755, true);
        }
        
        // Create CSS directory at docs/css
        $cssDir = $this->outputPath . '/css';
        if (!is_dir($cssDir)) {
            mkdir($cssDir, 0755, true);
        }
        
        // Generate documentation CSS based on Isotone theme
        $this->generateDocumentationCSS($cssDir);
        
        // Copy logo if exists
        $this->copyLogo($assetsDir);
        
        echo "  ✓ Assets setup complete\n";
    }
    
    /**
     * Generate documentation CSS based on Isotone theme
     */
    private function generateDocumentationCSS(string $cssDir): void
    {
        // Check if Isotone theme CSS exists
        $themePath = dirname(dirname(dirname(__DIR__))) . '/iso-content/themes/neutron';
        $adminCssPath = dirname(dirname(dirname(__DIR__))) . '/iso-includes/css';
        
        // Generate main documentation CSS
        $css = <<<CSS
/* Isotone Documentation Styles - Enhanced Version */
/* Modern, Clean Design with Smooth Animations */

:root {
    /* Brand Colors */
    --primary: #00D9FF;
    --primary-dark: #00B8E6;
    --primary-light: #33E3FF;
    --secondary: #00FF88;
    --secondary-dark: #00CC6A;
    --secondary-light: #33FFA3;
    --accent: #FF00FF;
    
    /* Dark Theme Base */
    --dark: #0A0E27;
    --dark-lighter: #0F1332;
    --dark-card: #151A3E;
    
    /* Gray Scale */
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
    --gray-950: #0a0a0a;
    
    /* Layout */
    --sidebar-width: 280px;
    --header-height: 72px;
    
    /* Shadows */
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    --shadow-glow: 0 0 20px rgba(0, 217, 255, 0.3);
    
    /* Transitions */
    --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
    --transition-base: 300ms cubic-bezier(0.4, 0, 0.2, 1);
    --transition-slow: 500ms cubic-bezier(0.4, 0, 0.2, 1);
    
    /* Border Radius */
    --radius-sm: 0.375rem;
    --radius-md: 0.5rem;
    --radius-lg: 0.75rem;
    --radius-xl: 1rem;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Inter', Roboto, sans-serif;
    line-height: 1.7;
    color: var(--gray-900);
    background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
    margin: 0;
    padding: 0;
    min-height: 100vh;
    font-size: 16px;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* Dark mode styles */
.dark body {
    background: linear-gradient(135deg, var(--dark) 0%, var(--dark-lighter) 100%);
    color: var(--gray-100);
}

/* Enhanced Admin Header */
.admin-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: var(--header-height);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(229, 231, 235, 0.5);
    box-shadow: var(--shadow-md);
    z-index: 50;
    transition: all var(--transition-base);
}

.dark .admin-header {
    background: rgba(31, 41, 55, 0.95);
    border-bottom-color: rgba(55, 65, 81, 0.5);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
}

.header-inner {
    height: 100%;
    padding: 0 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.header-left,
.header-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

/* Logo Container */
.logo-container {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    vertical-align: middle;
    padding: 0.5rem;
    border-radius: var(--radius-lg);
    transition: all var(--transition-base);
    cursor: pointer;
}

.logo-container:hover {
    background: rgba(0, 217, 255, 0.1);
    transform: translateX(2px);
}

.isotone-logo {
    width: 36px;
    height: 36px;
    display: inline-block;
    vertical-align: middle;
    flex-shrink: 0;
    filter: drop-shadow(0 2px 4px rgba(0, 217, 255, 0.2));
    transition: all var(--transition-base);
}

.logo-container:hover .isotone-logo {
    transform: rotate(5deg) scale(1.1);
    filter: drop-shadow(0 4px 8px rgba(0, 217, 255, 0.4));
}

/* Enhanced Logo Animation */
@keyframes pulse-glow {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
        filter: drop-shadow(0 0 8px rgba(0, 217, 255, 0.4));
    }
    50% {
        opacity: 0.9;
        transform: scale(0.98);
        filter: drop-shadow(0 0 12px rgba(0, 217, 255, 0.6));
    }
}

.isotone-logo-pulse {
    animation: pulse-glow 3s ease-in-out infinite;
}

.dark .isotone-logo-pulse {
    animation: pulse-glow 3s ease-in-out infinite;
    filter: brightness(1.1) drop-shadow(0 0 10px rgba(0, 217, 255, 0.5));
}

/* Isotone Text Shimmer (from admin CSS) */
@keyframes shimmer {
    0%, 100% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
}

.isotone-text {
    font-size: 1.375rem;
    font-weight: 700;
    margin: 0;
    padding: 0;
    line-height: 36px; /* Match the logo height */
    display: inline-block;
    vertical-align: middle;
    letter-spacing: -0.02em;
}

.isotone-text-shimmer {
    background: linear-gradient(135deg, #1F2937 0%, #0891B2 50%, #059669 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: shimmer 4s ease-in-out infinite;
    background-size: 200% 200%;
}

.dark .isotone-text-shimmer {
    background: linear-gradient(135deg, #FFFFFF 0%, #00D9FF 50%, #00FF88 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: shimmer 4s ease-in-out infinite;
    background-size: 200% 200%;
}

/* Enhanced Theme Toggle Button */
.theme-toggle {
    background: linear-gradient(135deg, var(--gray-100) 0%, var(--gray-200) 100%);
    border: 1px solid var(--gray-300);
    padding: 0.625rem;
    border-radius: var(--radius-lg);
    cursor: pointer;
    transition: all var(--transition-base);
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}

.theme-toggle::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: radial-gradient(circle, rgba(0, 217, 255, 0.3) 0%, transparent 70%);
    transition: all var(--transition-base);
    transform: translate(-50%, -50%);
    border-radius: 50%;
}

.theme-toggle:hover::before {
    width: 100px;
    height: 100px;
}

.dark .theme-toggle {
    background: linear-gradient(135deg, var(--gray-700) 0%, var(--gray-600) 100%);
    border-color: var(--gray-600);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.theme-toggle:hover {
    transform: scale(1.05);
    box-shadow: var(--shadow-md);
}

.dark .theme-toggle:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

.theme-toggle svg {
    width: 20px;
    height: 20px;
    color: var(--gray-600);
    position: relative;
    z-index: 1;
    transition: all var(--transition-base);
}

.theme-toggle:hover svg {
    transform: rotate(15deg);
}

.dark .theme-toggle svg {
    color: var(--gray-300);
}

.sun-icon {
    display: block;
}

.moon-icon {
    display: none;
}

.dark .sun-icon {
    display: none;
}

.dark .moon-icon {
    display: block;
}

/* Main Container */
.main-container {
    display: flex;
    margin-top: var(--header-height);
    min-height: calc(100vh - var(--header-height));
}

/* Enhanced Sidebar Navigation */
.sidebar {
    width: var(--sidebar-width);
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-right: 1px solid rgba(229, 231, 235, 0.5);
    position: fixed;
    top: var(--header-height);
    left: 0;
    height: calc(100vh - var(--header-height));
    overflow-y: auto;
    overflow-x: hidden;
    z-index: 10;
    box-shadow: 4px 0 6px -1px rgba(0, 0, 0, 0.05);
    transition: all var(--transition-base);
}

.dark .sidebar {
    background: rgba(31, 41, 55, 0.95);
    border-right-color: rgba(55, 65, 81, 0.5);
    box-shadow: 4px 0 6px -1px rgba(0, 0, 0, 0.2);
}

/* Custom Scrollbar for Sidebar */
.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar::-webkit-scrollbar-thumb {
    background: var(--gray-300);
    border-radius: 3px;
    transition: background var(--transition-fast);
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: var(--gray-400);
}

.dark .sidebar::-webkit-scrollbar-thumb {
    background: var(--gray-600);
}

.dark .sidebar::-webkit-scrollbar-thumb:hover {
    background: var(--gray-500);
}

/* Search Box in Header */
.header-right .search-box {
    position: relative;
    display: flex;
    align-items: center;
}

.header-right .search-box input {
    padding: 0.5rem 2.5rem 0.5rem 1rem;
    border: 1px solid var(--gray-300);
    border-radius: 0.375rem;
    background: var(--gray-50);
    font-size: 0.875rem;
    width: 240px;
    transition: all 0.2s;
}

.dark .header-right .search-box input {
    background: var(--gray-700);
    border-color: var(--gray-600);
    color: var(--gray-100);
}

.header-right .search-box input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(0, 217, 255, 0.1);
    width: 320px;
}

.search-icon {
    position: absolute;
    right: 0.75rem;
    width: 18px;
    height: 18px;
    color: var(--gray-400);
    pointer-events: none;
}

.nav-section {
    padding: 1rem 1.5rem;
}

.nav-section h3 {
    color: var(--gray-500);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.25rem;
    margin-top: 0.75rem;
}

.nav-section h3:first-child {
    margin-top: 0;
}

.dark .nav-section h3 {
    color: var(--gray-400);
}

.nav-section ul {
    list-style: none;
    margin-bottom: 0.5rem;
}

.nav-section a {
    display: block;
    padding: 0.5rem 0.875rem;
    margin: 0.125rem 0.5rem;
    color: var(--gray-700);
    text-decoration: none;
    font-size: 0.9rem;
    border-radius: var(--radius-md);
    transition: all var(--transition-fast);
    position: relative;
    overflow: hidden;
}

.nav-section a::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    width: 3px;
    height: 0;
    background: var(--primary);
    transition: all var(--transition-base);
    transform: translateY(-50%);
    border-radius: 2px;
}

.dark .nav-section a {
    color: var(--gray-300);
}

.nav-section a:hover {
    background: linear-gradient(90deg, rgba(0, 217, 255, 0.1) 0%, rgba(0, 217, 255, 0.05) 100%);
    color: var(--primary);
    transform: translateX(4px);
    padding-left: 1.125rem;
}

.nav-section a:hover::before {
    height: 70%;
}

.dark .nav-section a:hover {
    background: linear-gradient(90deg, rgba(0, 217, 255, 0.15) 0%, rgba(0, 217, 255, 0.05) 100%);
    color: var(--primary-light);
}

.nav-section a.active {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    font-weight: 500;
    box-shadow: 0 2px 8px rgba(0, 217, 255, 0.3);
    transform: translateX(2px);
}

.nav-section a.active::before {
    height: 100%;
    width: 4px;
    background: white;
}

.dark .nav-section a.active {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    box-shadow: 0 2px 12px rgba(0, 217, 255, 0.4);
}

/* Enhanced Main Content */
.content {
    margin-left: var(--sidebar-width);
    flex: 1;
    padding: 2rem;
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.content-inner {
    background: white;
    border-radius: var(--radius-lg);
    padding: 3rem;
    max-width: 1200px;
    margin: 0 auto;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.1);
    border: 1px solid var(--gray-200);
    transition: all var(--transition-base);
    position: relative;
}

.content-inner::before {
    content: '';
    position: absolute;
    top: -1px;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--primary), transparent);
    opacity: 0.6;
}

.dark .content-inner {
    background: var(--gray-800);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    border-color: var(--gray-700);
}

/* Refined Typography */
h1 {
    color: var(--gray-900);
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 2rem;
    padding-bottom: 1.25rem;
    letter-spacing: -0.03em;
    line-height: 1.2;
    position: relative;
}

h1::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 80px;
    height: 4px;
    background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 100%);
    border-radius: 2px;
    transition: width var(--transition-base);
}

h1:hover::after {
    width: 120px;
}

.dark h1 {
    color: transparent;
    background: linear-gradient(135deg, var(--gray-100) 0%, var(--primary-light) 100%);
    -webkit-background-clip: text;
    background-clip: text;
}

h2 {
    color: var(--gray-800);
    font-size: 1.875rem;
    font-weight: 600;
    margin: 2.5rem 0 1rem;
    padding-top: 1rem;
}

.dark h2 {
    color: var(--gray-200);
}

h3 {
    color: var(--gray-800);
    font-size: 1.5rem;
    font-weight: 600;
    margin: 2rem 0 0.75rem;
}

.dark h3 {
    color: var(--gray-200);
}

h4 {
    color: var(--gray-700);
    font-size: 1.25rem;
    font-weight: 600;
    margin: 1.5rem 0 0.5rem;
}

.dark h4 {
    color: var(--gray-300);
}

p {
    margin-bottom: 1rem;
    line-height: 1.75;
}

.dark p {
    color: var(--gray-300);
}

a {
    color: var(--primary);
    text-decoration: none;
    transition: color 0.2s;
}

a:hover {
    color: var(--secondary);
    text-decoration: underline;
}

/* Code Blocks */
code {
    background: var(--gray-100);
    padding: 0.125rem 0.375rem;
    border-radius: 4px;
    font-family: 'Courier New', Consolas, monospace;
    font-size: 0.875em;
    color: var(--gray-800);
}

.dark code {
    background: var(--gray-700);
    color: var(--gray-200);
}

pre {
    background: var(--gray-900);
    color: var(--gray-100);
    padding: 1.5rem;
    border-radius: 8px;
    overflow-x: auto;
    margin: 1.5rem 0;
}

.dark pre {
    background: #0A0E27;
    border: 1px solid var(--gray-700);
}

pre code {
    background: none;
    padding: 0;
    color: inherit;
    font-size: 0.875rem;
    line-height: 1.5;
}

/* Tables */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5rem 0;
}

th, td {
    border: 1px solid var(--gray-200);
    padding: 0.75rem 1rem;
    text-align: left;
}

.dark th, .dark td {
    border-color: var(--gray-700);
}

th {
    background: var(--gray-50);
    font-weight: 600;
    color: var(--gray-700);
}

.dark th {
    background: var(--gray-700);
    color: var(--gray-200);
}

.dark td {
    color: var(--gray-300);
}

tr:hover {
    background: var(--gray-50);
}

.dark tr:hover {
    background: var(--gray-700);
}

/* Blockquotes */
blockquote {
    border-left: 4px solid var(--primary);
    margin: 1.5rem 0;
    padding: 1rem 1.5rem;
    background: var(--gray-50);
    border-radius: 4px;
}

.dark blockquote {
    background: var(--gray-700);
}

blockquote p {
    margin-bottom: 0;
    color: var(--gray-700);
}

.dark blockquote p {
    color: var(--gray-300);
}

/* Lists */
ul, ol {
    margin: 1rem 0 1.5rem 2rem;
}

li {
    margin-bottom: 0.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s;
    }
    
    .sidebar.open {
        transform: translateX(0);
    }
    
    .content {
        margin-left: 0;
        padding: 1rem;
    }
    
    .content-inner {
        padding: 1.5rem;
    }
    
    h1 {
        font-size: 2rem;
    }
    
    h2 {
        font-size: 1.5rem;
    }
}

/* Enhanced Cards */
.card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(249, 250, 251, 0.95) 100%);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(229, 231, 235, 0.5);
    border-radius: var(--radius-lg);
    padding: 1.75rem;
    transition: all var(--transition-base);
    position: relative;
    overflow: hidden;
}

.card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform var(--transition-base);
}

.dark .card {
    background: linear-gradient(135deg, rgba(31, 41, 55, 0.95) 0%, rgba(17, 24, 39, 0.95) 100%);
    border-color: rgba(55, 65, 81, 0.5);
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-xl);
    border-color: rgba(0, 217, 255, 0.3);
}

.card:hover::before {
    transform: scaleX(1);
}

.dark .card:hover {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
    border-color: rgba(0, 217, 255, 0.4);
}

.card h2 {
    color: var(--gray-900);
    font-size: 1.25rem;
    margin-bottom: 0.75rem;
    border: none;
    padding-top: 0;
}

.dark .card h2 {
    color: var(--gray-100);
}

.card p {
    color: var(--gray-600);
    font-size: 0.875rem;
    margin-bottom: 1rem;
}

.dark .card p {
    color: var(--gray-400);
}

.card ul {
    list-style: none;
    margin: 0;
}

.card li {
    margin-bottom: 0.5rem;
}

.card a {
    color: var(--primary);
    text-decoration: none;
    font-size: 0.875rem;
}

.card a:hover {
    color: var(--secondary);
    text-decoration: underline;
}

/* Grid Layout */
.grid {
    display: grid;
    gap: 1.5rem;
}

.grid-cols-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
}

@media (min-width: 768px) {
    .md\\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

.gap-6 {
    gap: 1.5rem;
}

.mt-8 {
    margin-top: 2rem;
}

/* Utility Classes */
.text-primary { color: var(--primary); }
.text-secondary { color: var(--secondary); }
.bg-primary { background-color: var(--primary); }
.bg-secondary { background-color: var(--secondary); }

/* Scrollbar Styling */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: var(--gray-100);
}

::-webkit-scrollbar-thumb {
    background: var(--gray-400);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--gray-500);
}
CSS;
        
        file_put_contents($cssDir . '/documentation.css', $css);
    }
    
    /**
     * Copy logo to assets
     */
    private function copyLogo(string $assetsDir): void
    {
        // Look for logo in various locations
        $possibleLogos = [
            dirname(dirname(dirname(__DIR__))) . '/iso-content/themes/neutron/assets/logo.png',
            dirname(dirname(dirname(__DIR__))) . '/iso-content/themes/neutron/assets/logo.svg',
            dirname(dirname(dirname(__DIR__))) . '/assets/logo.png',
            dirname(dirname(dirname(__DIR__))) . '/assets/logo.svg',
        ];
        
        foreach ($possibleLogos as $logoPath) {
            if (file_exists($logoPath)) {
                $filename = basename($logoPath);
                copy($logoPath, $assetsDir . '/' . $filename);
                break;
            }
        }
    }
    
    /**
     * Copy static assets
     */
    private function copyAssets(): void
    {
        // Copy any images or other assets from user-docs
        $assetsSource = $this->docsPath . '/assets';
        $assetsTarget = $this->outputPath . '/assets';
        
        if (is_dir($assetsSource)) {
            if (!is_dir($assetsTarget)) {
                mkdir($assetsTarget, 0755, true);
            }
            
            $this->copyDirectory($assetsSource, $assetsTarget);
        }
    }
    
    /**
     * Copy directory recursively
     */
    private function copyDirectory(string $source, string $target): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            $targetPath = $target . '/' . $iterator->getSubPathName();
            if ($file->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                copy($file, $targetPath);
            }
        }
    }
}
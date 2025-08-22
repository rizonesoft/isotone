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
        
        // Generate icon preview pages directly from icon libraries
        $this->generateIconPreviewPages();
        
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
        // Skip icon preview pages - they're generated directly from libraries
        $basename = basename($filePath, '.md');
        if (in_array($basename, ['icon-preview-outline', 'icon-preview-solid', 'icon-preview-micro'])) {
            return;
        }
        
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
     * Generate all icon preview pages directly from icon libraries
     */
    private function generateIconPreviewPages(): void
    {
        echo "  Generating icon preview pages...\n";
        
        $iconPages = [
            'icon-preview-outline' => 'Outline',
            'icon-preview-solid' => 'Solid',
            'icon-preview-micro' => 'Micro'
        ];
        
        foreach ($iconPages as $basename => $title) {
            echo "    Creating $title icons page...";
            
            // Create a fake file path for consistency with existing code
            $fakePath = $this->docsPath . '/icons/' . $basename . '.md';
            $this->processIconPreviewPage($fakePath, $basename);
            
            echo " ✓\n";
        }
    }
    
    /**
     * Process icon preview page with actual icon SVGs
     */
    private function processIconPreviewPage(string $filePath, string $basename): void
    {
        // Load the appropriate icon library
        $iconLibraryPath = dirname(dirname(dirname(__DIR__))) . '/iso-core/Core/';
        $iconClass = 'IconLibrary';
        $iconStyle = 'outline';
        $fillType = 'none';
        $strokeType = 'currentColor';
        
        switch ($basename) {
            case 'icon-preview-solid':
                require_once $iconLibraryPath . 'IconLibrarySolid.php';
                $iconClass = 'IconLibrarySolid';
                $iconStyle = 'solid';
                $fillType = 'currentColor';
                $strokeType = 'none';
                break;
            case 'icon-preview-micro':
                require_once $iconLibraryPath . 'IconLibraryMicro.php';
                $iconClass = 'IconLibraryMicro';
                $iconStyle = 'micro';
                $fillType = 'currentColor';
                $strokeType = 'none';
                break;
            default:
                require_once $iconLibraryPath . 'IconLibrary.php';
                break;
        }
        
        // Get all icon names from the library
        $allIconNames = $iconClass::getIconNames();
        
        // Create categories based on ALL icons in the library
        $categories = $this->organizeAllIcons($allIconNames);
        
        // Generate the HTML content
        $content = $this->generateIconGalleryHTML($iconClass, $iconStyle, $categories, $fillType, $strokeType);
        
        // Determine output path
        $relativePath = str_replace($this->docsPath, '', $filePath);
        $relativePath = str_replace('\\', '/', $relativePath);
        $relativePath = ltrim($relativePath, '/\\');
        
        // Calculate depth for relative paths
        $depth = substr_count($relativePath, '/');
        $pathToRoot = $depth > 0 ? str_repeat('../', $depth) : './';
        
        // Generate full HTML page
        $title = ucfirst(str_replace('icon-preview-', '', $basename)) . ' Icons Preview';
        $html = $this->generateHTMLPageWithDepth($content, $title, "Gallery of $iconStyle style icons", $pathToRoot);
        
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
     * Get icon categories (legacy method for backward compatibility)
     */
    private function getIconCategories(): array
    {
        return [
            'Navigation & UI' => ['menu', 'menu-alt', 'x', 'x-circle', 'chevron-left', 'chevron-right', 'chevron-up', 'chevron-down', 'arrow-left', 'arrow-right', 'arrow-up', 'arrow-down', 'external-link'],
            'Actions' => ['plus', 'plus-circle', 'minus', 'minus-circle', 'check', 'check-circle', 'trash', 'pencil', 'pencil-square', 'duplicate', 'save', 'download', 'upload', 'refresh'],
            'User & Account' => ['user', 'user-circle', 'user-group', 'identification', 'login', 'logout'],
            'Settings' => ['cog', 'cog-8-tooth', 'wrench', 'adjustments'],
            'Content' => ['document', 'document-text', 'folder', 'folder-open', 'clipboard', 'newspaper'],
            'Media' => ['photograph', 'camera', 'video-camera', 'microphone', 'play', 'pause', 'stop'],
            'Layout' => ['template', 'view-columns', 'view-grid', 'rectangle-stack', 'bars-3', 'bars-3-bottom', 'bars-3-center'],
            'Appearance' => ['swatch', 'paint-brush', 'sparkles', 'sun', 'moon', 'eye', 'eye-slash'],
            'Communication' => ['chat', 'chat-bubble', 'envelope', 'phone', 'bell'],
            'Status' => ['information-circle', 'question-mark-circle', 'exclamation-circle', 'exclamation-triangle'],
            'E-commerce' => ['shopping-cart', 'shopping-bag', 'credit-card', 'currency-dollar', 'chart-bar', 'chart-pie'],
            'Technology' => ['code', 'code-bracket', 'command-line', 'cpu-chip', 'server', 'server-stack', 'globe', 'wifi'],
            'Security' => ['lock-closed', 'lock-open', 'shield-check', 'shield-exclamation', 'key', 'finger-print'],
            'Data' => ['database', 'table-cells', 'funnel', 'magnifying-glass'],
            'Time' => ['clock', 'calendar', 'calendar-days'],
            'Location' => ['map-pin', 'map', 'globe-alt'],
            'Miscellaneous' => ['heart', 'star', 'flag', 'bookmark', 'tag', 'puzzle-piece', 'light-bulb', 'fire', 'gift', 'home']
        ];
    }
    
    /**
     * Organize ALL icons from the library into categories
     */
    private function organizeAllIcons(array $allIconNames): array
    {
        $categories = [
            'Arrows' => [],
            'Navigation' => [],
            'Actions' => [],
            'User & Account' => [],
            'Interface' => [],
            'Media' => [],
            'Communication' => [],
            'Documents & Files' => [],
            'E-commerce' => [],
            'Charts & Data' => [],
            'Device & Technology' => [],
            'Buildings & Places' => [],
            'Nature & Weather' => [],
            'Time & Calendar' => [],
            'Security' => [],
            'Status & Alerts' => [],
            'Shapes & Symbols' => [],
            'Miscellaneous' => []
        ];
        
        // Sort icons into categories based on their names
        foreach ($allIconNames as $iconName) {
            // Arrows
            if (strpos($iconName, 'arrow') !== false || strpos($iconName, 'chevron') !== false) {
                $categories['Arrows'][] = $iconName;
            }
            // User & Account
            elseif (strpos($iconName, 'user') !== false || in_array($iconName, ['identification', 'login', 'logout'])) {
                $categories['User & Account'][] = $iconName;
            }
            // Navigation
            elseif (in_array($iconName, ['menu', 'menu-alt', 'bars-2', 'bars-3', 'bars-3-bottom', 'bars-3-bottom-left', 'bars-3-bottom-right', 'bars-3-center', 'bars-3-center-left', 'bars-4', 'x', 'x-circle', 'x-mark', 'home', 'home-modern'])) {
                $categories['Navigation'][] = $iconName;
            }
            // Media
            elseif (strpos($iconName, 'camera') !== false || strpos($iconName, 'video') !== false || 
                    strpos($iconName, 'photo') !== false || strpos($iconName, 'film') !== false ||
                    strpos($iconName, 'microphone') !== false || strpos($iconName, 'speaker') !== false ||
                    strpos($iconName, 'play') !== false || strpos($iconName, 'pause') !== false || 
                    strpos($iconName, 'stop') !== false || strpos($iconName, 'forward') !== false || 
                    strpos($iconName, 'backward') !== false || strpos($iconName, 'musical') !== false ||
                    in_array($iconName, ['photograph', 'gif'])) {
                $categories['Media'][] = $iconName;
            }
            // Communication
            elseif (strpos($iconName, 'chat') !== false || strpos($iconName, 'envelope') !== false || 
                    strpos($iconName, 'phone') !== false || strpos($iconName, 'bell') !== false ||
                    strpos($iconName, 'megaphone') !== false || strpos($iconName, 'at-symbol') !== false ||
                    strpos($iconName, 'inbox') !== false || strpos($iconName, 'paper-airplane') !== false ||
                    strpos($iconName, 'rss') !== false || strpos($iconName, 'signal') !== false ||
                    in_array($iconName, ['share', 'link', 'hashtag'])) {
                $categories['Communication'][] = $iconName;
            }
            // Documents & Files
            elseif (strpos($iconName, 'document') !== false || strpos($iconName, 'folder') !== false || 
                    strpos($iconName, 'clipboard') !== false || strpos($iconName, 'paper') !== false ||
                    in_array($iconName, ['newspaper', 'book-open', 'academic-cap', 'archive-box', 'archive-box-arrow-down', 'archive-box-x-mark'])) {
                $categories['Documents & Files'][] = $iconName;
            }
            // E-commerce
            elseif (strpos($iconName, 'shopping') !== false || strpos($iconName, 'credit') !== false || 
                    strpos($iconName, 'currency') !== false || strpos($iconName, 'receipt') !== false ||
                    strpos($iconName, 'gift') !== false || strpos($iconName, 'ticket') !== false ||
                    in_array($iconName, ['banknotes', 'wallet', 'tag'])) {
                $categories['E-commerce'][] = $iconName;
            }
            // Charts & Data
            elseif (strpos($iconName, 'chart') !== false || strpos($iconName, 'presentation') !== false ||
                    strpos($iconName, 'table') !== false || strpos($iconName, 'funnel') !== false ||
                    strpos($iconName, 'magnifying') !== false || strpos($iconName, 'calculator') !== false ||
                    in_array($iconName, ['database', 'server', 'server-stack', 'queue-list', 'list-bullet', 'squares-2x2', 'rectangle-group', 'rectangle-stack', 'square-2-stack', 'square-3-stack-3d', 'squares-plus', 'circle-stack', 'scale'])) {
                $categories['Charts & Data'][] = $iconName;
            }
            // Device & Technology
            elseif (strpos($iconName, 'computer') !== false || strpos($iconName, 'device') !== false || 
                    strpos($iconName, 'cpu') !== false || strpos($iconName, 'code') !== false ||
                    strpos($iconName, 'command') !== false || strpos($iconName, 'wifi') !== false ||
                    strpos($iconName, 'printer') !== false || strpos($iconName, 'radio') !== false ||
                    strpos($iconName, 'tv') !== false || strpos($iconName, 'battery') !== false ||
                    in_array($iconName, ['globe', 'globe-alt', 'globe-americas', 'globe-asia-australia', 'globe-europe-africa', 'qr-code', 'bug-ant', 'variable', 'window'])) {
                $categories['Device & Technology'][] = $iconName;
            }
            // Buildings & Places
            elseif (strpos($iconName, 'building') !== false || strpos($iconName, 'map') !== false ||
                    in_array($iconName, ['truck'])) {
                $categories['Buildings & Places'][] = $iconName;
            }
            // Nature & Weather
            elseif (strpos($iconName, 'cloud') !== false || strpos($iconName, 'sun') !== false || 
                    strpos($iconName, 'moon') !== false || strpos($iconName, 'fire') !== false ||
                    strpos($iconName, 'bolt') !== false || strpos($iconName, 'sparkles') !== false ||
                    in_array($iconName, ['beaker'])) {
                $categories['Nature & Weather'][] = $iconName;
            }
            // Time & Calendar
            elseif (strpos($iconName, 'clock') !== false || strpos($iconName, 'calendar') !== false) {
                $categories['Time & Calendar'][] = $iconName;
            }
            // Security
            elseif (strpos($iconName, 'lock') !== false || strpos($iconName, 'shield') !== false || 
                    strpos($iconName, 'key') !== false || strpos($iconName, 'finger-print') !== false ||
                    strpos($iconName, 'eye') !== false) {
                $categories['Security'][] = $iconName;
            }
            // Status & Alerts
            elseif (strpos($iconName, 'check') !== false || strpos($iconName, 'exclamation') !== false || 
                    strpos($iconName, 'information') !== false || strpos($iconName, 'question') !== false ||
                    strpos($iconName, 'badge') !== false || strpos($iconName, 'flag') !== false ||
                    in_array($iconName, ['no-symbol'])) {
                $categories['Status & Alerts'][] = $iconName;
            }
            // Shapes & Symbols
            elseif (strpos($iconName, 'circle') !== false || strpos($iconName, 'square') !== false || 
                    strpos($iconName, 'cube') !== false || strpos($iconName, 'ellipsis') !== false ||
                    strpos($iconName, 'plus') !== false || strpos($iconName, 'minus') !== false ||
                    in_array($iconName, ['heart', 'star', 'puzzle-piece'])) {
                $categories['Shapes & Symbols'][] = $iconName;
            }
            // Actions (must be after other categories to avoid conflicts)
            elseif (in_array($iconName, ['trash', 'pencil', 'pencil-square', 'duplicate', 'save', 'download', 'upload', 'refresh', 'external-link', 'scissors', 'paint-brush', 'swatch', 'wrench', 'wrench-screwdriver', 'cog', 'cog-6-tooth', 'cog-8-tooth', 'adjustments', 'adjustments-horizontal', 'adjustments-vertical', 'cursor-arrow-rays', 'cursor-arrow-ripple', 'hand-raised', 'hand-thumb-down', 'hand-thumb-up', 'rocket-launch', 'power', 'lifebuoy', 'language', 'viewfinder-circle'])) {
                $categories['Actions'][] = $iconName;
            }
            // Interface elements
            elseif (in_array($iconName, ['template', 'view-columns', 'view-grid', 'collection', 'dots-horizontal', 'dots-vertical', 'switch-horizontal', 'switch-vertical', 'selector', 'bookmark', 'bookmark-slash', 'bookmark-square', 'face-frown', 'face-smile'])) {
                $categories['Interface'][] = $iconName;
            }
            // Everything else goes to Miscellaneous
            else {
                $categories['Miscellaneous'][] = $iconName;
            }
        }
        
        // Remove empty categories and sort icons within each category
        $finalCategories = [];
        foreach ($categories as $categoryName => $icons) {
            if (!empty($icons)) {
                sort($icons);
                $finalCategories[$categoryName] = $icons;
            }
        }
        
        return $finalCategories;
    }
    
    /**
     * Generate icon gallery HTML
     */
    private function generateIconGalleryHTML(string $iconClass, string $iconStyle, array $categories, string $fillType, string $strokeType): string
    {
        $html = '<h1>' . ucfirst($iconStyle) . ' Icons Preview</h1>';
        
        // Add appropriate description based on icon style
        if ($iconStyle === 'micro') {
            $html .= '<p>Gallery of micro icons (16x16) from the Isotone Icon Library. These compact icons are perfect for small UI elements and tight spaces.</p>';
        } elseif ($iconStyle === 'solid') {
            $html .= '<p>Gallery of solid style icons (24x24) from the Isotone Icon Library. These filled icons provide strong visual emphasis.</p>';
        } else {
            $html .= '<p>Gallery of outline style icons (24x24) from the Isotone Icon Library. These are the default icons with clean strokes.</p>';
        }
        
        // Add search box
        $html .= '
<div class="icon-search-container">
    <input type="text" id="icon-search" class="icon-search" placeholder="Search icons..." onkeyup="filterIcons()">
</div>';
        
        // Add icon grid
        $html .= '<div class="icon-gallery">';
        
        $totalIcons = 0;
        foreach ($categories as $category => $iconNames) {
            $categoryHtml = '<div class="icon-category" data-category="' . htmlspecialchars($category) . '">';
            $categoryHtml .= '<h2>' . htmlspecialchars($category) . '</h2>';
            $categoryHtml .= '<div class="icon-grid">';
            
            $hasIcons = false;
            foreach ($iconNames as $iconName) {
                if ($iconClass::hasIcon($iconName)) {
                    $iconPath = $iconClass::getIconPath($iconName);
                    $viewBox = $iconStyle === 'micro' ? '0 0 16 16' : '0 0 24 24';
                    
                    // Add icon-micro class for smaller icons
                    $svgClasses = $iconStyle === 'micro' ? 'icon-svg icon-micro' : 'icon-svg';
                    
                    $categoryHtml .= '
<div class="icon-card" data-icon="' . htmlspecialchars($iconName) . '" 
     onclick="copyIconName(\'' . htmlspecialchars($iconName) . '\', this)">
    <svg class="' . $svgClasses . '" fill="' . $fillType . '" stroke="' . $strokeType . '" viewBox="' . $viewBox . '">
        ' . $iconPath . '
    </svg>
    <div class="icon-name">' . htmlspecialchars($iconName) . '</div>
</div>';
                    $hasIcons = true;
                    $totalIcons++;
                }
            }
            
            $categoryHtml .= '</div></div>';
            
            if ($hasIcons) {
                $html .= $categoryHtml;
            }
        }
        
        $html .= '</div>';
        
        // Add stats
        $html .= '<p class="icon-stats">Total icons: ' . $totalIcons . '</p>';
        
        // Add JavaScript for interactivity
        $html .= '
<script>
function filterIcons() {
    const searchTerm = document.getElementById("icon-search").value.toLowerCase();
    const iconCards = document.querySelectorAll(".icon-card");
    const categories = document.querySelectorAll(".icon-category");
    
    categories.forEach(category => {
        let hasVisibleIcons = false;
        const categoryCards = category.querySelectorAll(".icon-card");
        
        categoryCards.forEach(card => {
            const iconName = card.dataset.icon;
            if (iconName.includes(searchTerm)) {
                card.style.display = "";
                hasVisibleIcons = true;
            } else {
                card.style.display = "none";
            }
        });
        
        category.style.display = hasVisibleIcons ? "" : "none";
    });
}

function copyIconName(iconName, element) {
    // Copy to clipboard
    const textArea = document.createElement("textarea");
    textArea.value = iconName;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand("copy");
    document.body.removeChild(textArea);
    
    // Visual feedback using CSS class
    element.classList.add("copied");
    
    setTimeout(() => {
        element.classList.remove("copied");
    }, 500);
    
    // Show toast notification
    showToast("Copied: " + iconName);
}

function showToast(message) {
    const existing = document.getElementById("copy-toast");
    if (existing) existing.remove();
    
    const toast = document.createElement("div");
    toast.id = "copy-toast";
    toast.textContent = message;
    toast.style.cssText = "animation: slideUp 0.3s ease;";
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = "slideDown 0.3s ease";
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}

// CSS styles are now in documentation.css
</script>';
        
        return $html;
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <svg class="moon-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                </button>
                <!-- Search -->
                <div class="search-box">
                    <input type="text" placeholder="Search docs..." id="search">
                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <svg class="moon-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                </button>
                <!-- Search -->
                <div class="search-box">
                    <input type="text" placeholder="Search docs..." id="search">
                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
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
            'Getting Started' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>',
            'Development' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>',
            'Icons' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path></svg>',
            'API Reference' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>',
            'Configuration' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>',
            'Toni AI' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>',
            'Troubleshooting' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
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
            'Icons' => [
                'icons/icons-overview',
                'icons/icon-preview-outline',
                'icons/icon-preview-solid',
                'icons/icon-preview-micro'
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
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
    
    <div class="card">
        <div class="card-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
            </svg>
        </div>
        <h2>Icon Library</h2>
        <p>Browse and search all available icons in the Isotone icon library with interactive galleries for all three styles.</p>
        <ul>
            <li><a href="./icons/icons-overview.html">Icon Library Overview</a></li>
            <li><a href="./icons/icon-preview-outline.html">Outline Icons Gallery</a></li>
            <li><a href="./icons/icon-preview-solid.html">Solid Icons Gallery</a></li>
            <li><a href="./icons/icon-preview-micro.html">Micro Icons Gallery</a></li>
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
     * Setup assets directory - copy from Documentation/assets
     */
    private function setupAssets(): void
    {
        // Source assets directory in iso-automation/src/Documentation/assets
        $sourceAssetsDir = dirname(__DIR__) . '/Documentation/assets';
        $targetAssetsDir = $this->outputPath . '/assets';
        
        // Check if source assets directory exists
        if (!is_dir($sourceAssetsDir)) {
            echo "  ⚠️  Assets directory not found at: $sourceAssetsDir\n";
            return;
        }
        
        // Copy entire assets directory
        if (is_dir($sourceAssetsDir)) {
            // Remove existing assets directory if it exists
            if (is_dir($targetAssetsDir)) {
                $this->removeDirectory($targetAssetsDir);
            }
            
            // Copy the entire assets folder
            $this->copyDirectory($sourceAssetsDir, $targetAssetsDir);
            echo "  ✓ Copied assets from Documentation/assets\n";
        }
        
        echo "  ✓ Assets setup complete\n";
    }
    
    /**
     * Remove directory recursively
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
    
    /**
     * Copy static assets
     */
    private function copyAssets(): void
    {
        // Copy any additional assets from user-docs if they exist
        $assetsSource = $this->docsPath . '/assets';
        $assetsTarget = $this->outputPath . '/assets';
        
        if (is_dir($assetsSource)) {
            // Only copy additional assets, don't overwrite our CSS/JS
            $this->copyDirectorySelective($assetsSource, $assetsTarget);
        }
    }
    
    /**
     * Copy directory selectively (don't overwrite existing)
     */
    private function copyDirectorySelective(string $source, string $target): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            $targetPath = $target . '/' . $iterator->getSubPathName();
            
            // Skip if target already exists (preserve our CSS/JS)
            if (file_exists($targetPath)) {
                continue;
            }
            
            if ($file->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                copy($file, $targetPath);
            }
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
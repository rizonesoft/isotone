<?php
/**
 * Icon Gallery Generator
 * 
 * Generates HTML documentation showing all available icons
 * 
 * @package Isotone\Automation
 */

class IconGalleryGenerator
{
    private $iconLibraryPath;
    private $outputPath;
    private $icons = [];
    
    public function __construct()
    {
        $this->iconLibraryPath = dirname(dirname(dirname(__DIR__))) . '/iso-core/Core/IconLibrary.php';
        $this->outputPath = dirname(dirname(dirname(__DIR__))) . '/docs/icon-gallery.html';
    }
    
    /**
     * Generate the icon gallery HTML
     */
    public function generate()
    {
        echo "📊 Generating Icon Gallery...\n";
        
        // Load the icon library
        require_once $this->iconLibraryPath;
        
        // Get all icons
        $this->collectIcons();
        
        // Generate HTML
        $html = $this->generateHTML();
        
        // Create docs directory if it doesn't exist
        $docsDir = dirname($this->outputPath);
        if (!is_dir($docsDir)) {
            mkdir($docsDir, 0755, true);
        }
        
        // Write HTML file
        file_put_contents($this->outputPath, $html);
        
        echo "✅ Icon gallery generated at: " . $this->outputPath . "\n";
        echo "📊 Total icons documented: " . count($this->icons) . "\n";
        
        return true;
    }
    
    /**
     * Collect all icons from the library
     */
    private function collectIcons()
    {
        // Get all icon names
        $iconNames = \IconLibrary::getIconNames();
        
        // Organize by category
        $categories = [
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
        
        $this->icons = $categories;
    }
    
    /**
     * Generate the HTML document
     */
    private function generateHTML()
    {
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Isotone Icon Gallery</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem;
            text-align: center;
        }
        
        .header h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-top: 2rem;
        }
        
        .stat {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .search-container {
            padding: 2rem 3rem;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        
        .search-input {
            width: 100%;
            padding: 1rem 1.5rem;
            font-size: 1.1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .content {
            padding: 2rem;
        }
        
        .category {
            margin-bottom: 3rem;
        }
        
        .category-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }
        
        .icon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 1rem;
        }
        
        .icon-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1.5rem 1rem;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            position: relative;
        }
        
        .icon-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            border-color: #667eea;
        }
        
        .icon-card.copied {
            background: #10b981;
            border-color: #10b981;
        }
        
        .icon-card.copied .icon-svg {
            color: white;
        }
        
        .icon-card.copied .icon-name {
            color: white;
        }
        
        .icon-svg {
            width: 40px;
            height: 40px;
            margin: 0 auto 1rem;
            color: #4a5568;
            transition: all 0.3s;
        }
        
        .icon-card:hover .icon-svg {
            color: #667eea;
            transform: scale(1.1);
        }
        
        .icon-name {
            font-size: 0.85rem;
            color: #718096;
            font-family: "SF Mono", Monaco, Consolas, monospace;
            word-break: break-all;
        }
        
        .copy-toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #10b981;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .copy-toast.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        .no-results {
            text-align: center;
            padding: 3rem;
            color: #718096;
            font-size: 1.2rem;
        }
        
        .footer {
            background: #f8f9fa;
            padding: 2rem;
            text-align: center;
            color: #718096;
            border-top: 1px solid #e9ecef;
        }
        
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎨 Isotone Icon Gallery</h1>
            <p>Comprehensive icon library based on Heroicons v2</p>
            <div class="stats">
                <div class="stat">
                    <div class="stat-number" id="total-icons">0</div>
                    <div class="stat-label">Total Icons</div>
                </div>
                <div class="stat">
                    <div class="stat-number" id="total-categories">0</div>
                    <div class="stat-label">Categories</div>
                </div>
            </div>
        </div>
        
        <div class="search-container">
            <input type="text" class="search-input" id="search-input" placeholder="Search icons... (e.g., user, arrow, settings)">
        </div>
        
        <div class="content" id="content">
';
        
        // Add categories and icons
        $totalIcons = 0;
        foreach ($this->icons as $category => $iconNames) {
            $html .= '            <div class="category" data-category="' . htmlspecialchars($category) . '">
                <h2 class="category-title">' . htmlspecialchars($category) . '</h2>
                <div class="icon-grid">
';
            
            foreach ($iconNames as $iconName) {
                if (\IconLibrary::hasIcon($iconName)) {
                    $iconPath = \IconLibrary::getIconPath($iconName);
                    $html .= '                    <div class="icon-card" data-icon-name="' . htmlspecialchars($iconName) . '" onclick="copyIconName(\'' . htmlspecialchars($iconName) . '\', this)">
                        <svg class="icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            ' . $iconPath . '
                        </svg>
                        <div class="icon-name">' . htmlspecialchars($iconName) . '</div>
                    </div>
';
                    $totalIcons++;
                }
            }
            
            $html .= '                </div>
            </div>
';
        }
        
        $html .= '        </div>
        
        <div class="footer">
            <p>Part of the <a href="https://github.com/rizonesoft/isotone">Isotone CMS</a> project</p>
            <p>Icons from <a href="https://heroicons.com/" target="_blank">Heroicons</a> by Steve Schoger</p>
        </div>
    </div>
    
    <div class="copy-toast" id="copy-toast">
        Icon name copied to clipboard!
    </div>
    
    <script>
        // Set stats
        document.getElementById("total-icons").textContent = "' . $totalIcons . '";
        document.getElementById("total-categories").textContent = "' . count($this->icons) . '";
        
        // Search functionality
        const searchInput = document.getElementById("search-input");
        const content = document.getElementById("content");
        
        searchInput.addEventListener("input", function() {
            const searchTerm = this.value.toLowerCase();
            const iconCards = document.querySelectorAll(".icon-card");
            const categories = document.querySelectorAll(".category");
            
            let hasResults = false;
            
            categories.forEach(category => {
                let categoryHasVisibleIcons = false;
                const categoryIcons = category.querySelectorAll(".icon-card");
                
                categoryIcons.forEach(card => {
                    const iconName = card.dataset.iconName;
                    if (iconName.includes(searchTerm)) {
                        card.style.display = "";
                        categoryHasVisibleIcons = true;
                        hasResults = true;
                    } else {
                        card.style.display = "none";
                    }
                });
                
                category.style.display = categoryHasVisibleIcons ? "" : "none";
            });
            
            // Show no results message
            const existingNoResults = document.querySelector(".no-results");
            if (existingNoResults) {
                existingNoResults.remove();
            }
            
            if (!hasResults && searchTerm) {
                const noResults = document.createElement("div");
                noResults.className = "no-results";
                noResults.textContent = `No icons found for "${searchTerm}"`;
                content.appendChild(noResults);
            }
        });
        
        // Copy to clipboard functionality
        function copyIconName(iconName, element) {
            // Copy to clipboard
            const textArea = document.createElement("textarea");
            textArea.value = iconName;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand("copy");
            document.body.removeChild(textArea);
            
            // Visual feedback
            element.classList.add("copied");
            setTimeout(() => {
                element.classList.remove("copied");
            }, 500);
            
            // Show toast
            const toast = document.getElementById("copy-toast");
            toast.textContent = `"${iconName}" copied to clipboard!`;
            toast.classList.add("show");
            setTimeout(() => {
                toast.classList.remove("show");
            }, 2000);
        }
        
        // Add keyboard navigation
        document.addEventListener("keydown", function(e) {
            if (e.key === "/" && document.activeElement !== searchInput) {
                e.preventDefault();
                searchInput.focus();
            }
            if (e.key === "Escape") {
                searchInput.value = "";
                searchInput.dispatchEvent(new Event("input"));
                searchInput.blur();
            }
        });
    </script>
</body>
</html>';
        
        return $html;
    }
}
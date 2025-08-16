<?php
/**
 * Isotone Documentation Viewer
 * 
 * Displays user documentation from markdown files with navigation
 */

// Check authentication
require_once 'auth.php';

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config.php';

// Enhanced markdown parser if Parsedown is not available
class SimpleMarkdownParser {
    public function text($markdown) {
        // Preserve code blocks first (to avoid processing their content)
        $codeBlocks = [];
        $blockId = 0;
        
        // Handle fenced code blocks with language specification
        $markdown = preg_replace_callback('/```(\w+)?\n(.*?)```/s', function($matches) use (&$codeBlocks, &$blockId) {
            $language = $matches[1] ?? '';
            $code = htmlspecialchars($matches[2], ENT_QUOTES, 'UTF-8');
            $id = "CODE_BLOCK_" . $blockId++;
            $codeBlocks[$id] = '<pre class="language-' . $language . '"><code>' . trim($code) . '</code></pre>';
            return $id;
        }, $markdown);
        
        // Convert headers (must be at line start)
        $html = preg_replace('/^#{6}\s+(.*?)$/m', '<h6>$1</h6>', $markdown);
        $html = preg_replace('/^#{5}\s+(.*?)$/m', '<h5>$1</h5>', $html);
        $html = preg_replace('/^#{4}\s+(.*?)$/m', '<h4>$1</h4>', $html);
        $html = preg_replace('/^#{3}\s+(.*?)$/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^#{2}\s+(.*?)$/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^#\s+(.*?)$/m', '<h1>$1</h1>', $html);
        
        // Convert horizontal rules
        $html = preg_replace('/^---+$/m', '<hr>', $html);
        $html = preg_replace('/^\*\*\*+$/m', '<hr>', $html);
        
        // Convert blockquotes
        $html = preg_replace('/^>\s+(.*?)$/m', '<blockquote>$1</blockquote>', $html);
        $html = preg_replace('/<\/blockquote>\n<blockquote>/', "\n", $html);
        
        // Convert bold and italic (order matters!)
        $html = preg_replace('/\*\*\*(.*?)\*\*\*/s', '<strong><em>$1</em></strong>', $html);
        $html = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $html);
        $html = preg_replace('/__(.*?)__/s', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $html);
        $html = preg_replace('/_(.*?)_/s', '<em>$1</em>', $html);
        
        // Convert inline code
        $html = preg_replace('/`([^`]+)`/', '<code class="inline-code">$1</code>', $html);
        
        // Convert links and images
        $html = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/', '<img src="$2" alt="$1" class="doc-image">', $html);
        $html = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $html);
        
        // Convert unordered lists
        $lines = explode("\n", $html);
        $inList = false;
        $listLevel = 0;
        $newLines = [];
        
        foreach ($lines as $line) {
            if (preg_match('/^(\s*)[-*+]\s+(.*)$/', $line, $matches)) {
                $indent = strlen($matches[1]);
                $content = $matches[2];
                
                if (!$inList) {
                    $newLines[] = '<ul>';
                    $inList = true;
                }
                
                $newLines[] = '<li>' . $content . '</li>';
            } else {
                if ($inList && trim($line) === '') {
                    // Empty line might mean end of list
                    continue;
                } elseif ($inList && !preg_match('/^(\s*)[-*+]\s+/', $line)) {
                    $newLines[] = '</ul>';
                    $inList = false;
                }
                $newLines[] = $line;
            }
        }
        
        if ($inList) {
            $newLines[] = '</ul>';
        }
        
        $html = implode("\n", $newLines);
        
        // Convert ordered lists
        $html = preg_replace('/^\d+\.\s+(.*)$/m', '<ol><li>$1</li></ol>', $html);
        $html = preg_replace('/<\/ol>\n<ol>/', '', $html);
        
        // Convert line breaks to paragraphs (but be smarter about it)
        $blocks = explode("\n\n", $html);
        $html = '';
        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block) {
                // Don't wrap if it's already a block element
                if (!preg_match('/^<(?:h[1-6]|ul|ol|li|pre|div|table|blockquote|hr)/i', $block)) {
                    $html .= '<p>' . $block . '</p>' . "\n";
                } else {
                    $html .= $block . "\n";
                }
            }
        }
        
        // Restore code blocks
        foreach ($codeBlocks as $id => $codeBlock) {
            $html = str_replace($id, $codeBlock, $html);
        }
        
        // Clean up any double line breaks
        $html = preg_replace('/\n{3,}/', "\n\n", $html);
        
        return $html;
    }
}

// Try to use Parsedown if available, otherwise use simple parser
if (class_exists('Parsedown')) {
    $parsedown = new Parsedown();
    $parsedown->setSafeMode(false);
} else {
    $parsedown = new SimpleMarkdownParser();
}

// Function to convert kebab-case to Title Case with abbreviation handling
function toTitleCase($string) {
    // Common abbreviations that should be all caps
    $abbreviations = [
        'api' => 'API',
        'cms' => 'CMS',
        'css' => 'CSS',
        'html' => 'HTML',
        'http' => 'HTTP',
        'https' => 'HTTPS',
        'id' => 'ID',
        'ide' => 'IDE',
        'js' => 'JS',
        'json' => 'JSON',
        'php' => 'PHP',
        'sql' => 'SQL',
        'ui' => 'UI',
        'url' => 'URL',
        'uri' => 'URI',
        'ux' => 'UX',
        'xml' => 'XML',
        'yaml' => 'YAML',
        'cli' => 'CLI',
        'faq' => 'FAQ',
        'seo' => 'SEO',
        'rss' => 'RSS',
        'db' => 'DB',
        'wp' => 'WP'
    ];
    
    // Replace hyphens and underscores with spaces
    $string = str_replace(['-', '_'], ' ', $string);
    
    // Split into words
    $words = explode(' ', $string);
    
    // Process each word
    $processed = array_map(function($word) use ($abbreviations) {
        $lower = strtolower($word);
        
        // Check if it's a known abbreviation
        if (isset($abbreviations[$lower])) {
            return $abbreviations[$lower];
        }
        
        // Otherwise, capitalize first letter
        return ucfirst($lower);
    }, $words);
    
    return implode(' ', $processed);
}

// Function to get icon for section based on name
function getSectionIcon($section) {
    $icons = [
        'getting-started' => 'rocket',
        'configuration' => 'adjustments',
        'api' => 'terminal',
        'developers' => 'code',
        'development' => 'code',
        'guide' => 'book-open',
        'troubleshooting' => 'exclamation-circle',
        'reference' => 'academic-cap'
    ];
    
    return $icons[$section] ?? 'folder';
}

// Dynamically build documentation structure from filesystem
$docs_path = dirname(__DIR__) . '/user-docs/';
$docs_structure = [];

// Scan for section directories
if (is_dir($docs_path)) {
    $sections = scandir($docs_path);
    
    // Files and folders to exclude from documentation
    $excludeItems = ['.', '..', '.vitepress', '.git', '.gitignore', 'node_modules', 'vendor'];
    $excludeFiles = ['README.md', 'package.json', 'package-lock.json', 'composer.json', 'composer.lock', 'index.md'];
    
    foreach ($sections as $section) {
        // Skip excluded items, hidden folders (starting with .), and non-directories
        if (in_array($section, $excludeItems) || 
            strpos($section, '.') === 0 || 
            !is_dir($docs_path . $section)) {
            continue;
        }
        
        // Initialize section
        $docs_structure[$section] = [
            'title' => toTitleCase($section),
            'icon' => getSectionIcon($section),
            'pages' => []
        ];
        
        // Scan for markdown files in section
        $files = scandir($docs_path . $section);
        
        foreach ($files as $file) {
            // Only process .md files
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'md') {
                continue;
            }
            
            $filename = pathinfo($file, PATHINFO_FILENAME);
            
            // Add page to section
            $docs_structure[$section]['pages'][$filename] = [
                'title' => toTitleCase($filename),
                'file' => $section . '/' . $file
            ];
        }
        
        // Sort pages alphabetically by title
        if (!empty($docs_structure[$section]['pages'])) {
            uasort($docs_structure[$section]['pages'], function($a, $b) {
                return strcasecmp($a['title'], $b['title']);
            });
        }
    }
    
    // Sort sections alphabetically, but keep getting-started first if it exists
    uksort($docs_structure, function($a, $b) {
        if ($a === 'getting-started') return -1;
        if ($b === 'getting-started') return 1;
        return strcasecmp($a, $b);
    });
}

// Get requested section and page
$section = $_GET['section'] ?? '';
$page = $_GET['page'] ?? '';

// If no section specified or invalid, use first available section
if (empty($section) || !isset($docs_structure[$section])) {
    $section = array_key_first($docs_structure);
}

// If no page specified or invalid, use first available page in section
if (empty($page) || !isset($docs_structure[$section]['pages'][$page])) {
    $page = array_key_first($docs_structure[$section]['pages'] ?? []);
}

// Handle case where section has no pages
if (empty($docs_structure[$section]['pages'])) {
    $error_message = "No documentation files found in section: " . $docs_structure[$section]['title'];
    $html_content = '<div class="text-red-500">Error: ' . htmlspecialchars($error_message) . '</div>';
} else if (empty($page)) {
    $error_message = "No pages available in this section";
    $html_content = '<div class="text-red-500">Error: ' . htmlspecialchars($error_message) . '</div>';
}

// Load the markdown file only if we have a valid page
if (!empty($page) && !empty($docs_structure[$section]['pages'][$page])) {
    $docs_path = dirname(__DIR__) . '/user-docs/';
    $file_path = $docs_path . $docs_structure[$section]['pages'][$page]['file'];
    $markdown_content = '';
    $error_message = '';

    if (file_exists($file_path)) {
        $markdown_content = file_get_contents($file_path);
        // Convert markdown to HTML
        $html_content = $parsedown->text($markdown_content);
    } else {
        $error_message = "Documentation file not found: " . $docs_structure[$section]['pages'][$page]['file'];
        $html_content = '<div class="text-red-500">Error: ' . htmlspecialchars($error_message) . '</div>';
    }
}

// Page configuration
$page_title = !empty($page) && !empty($docs_structure[$section]['pages'][$page]) 
    ? $docs_structure[$section]['pages'][$page]['title'] . ' - Documentation'
    : 'Documentation';
    
$breadcrumbs = [
    ['title' => 'Documentation', 'url' => '/isotone/iso-admin/documentation.php'],
    ['title' => $docs_structure[$section]['title'] ?? 'Unknown Section']
];

if (!empty($page) && !empty($docs_structure[$section]['pages'][$page])) {
    $breadcrumbs[] = ['title' => $docs_structure[$section]['pages'][$page]['title']];
}

// Start output buffering to capture page content
ob_start();
?>

<div class="flex gap-8">
    <!-- Main Content Area -->
    <div class="flex-1">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold dark:text-white text-gray-900 mb-2">
                <?php echo htmlspecialchars(!empty($page) && !empty($docs_structure[$section]['pages'][$page]) 
                    ? $docs_structure[$section]['pages'][$page]['title'] 
                    : 'Documentation'); ?>
            </h1>
            <p class="dark:text-gray-400 text-gray-600">
                <?php echo htmlspecialchars($docs_structure[$section]['title'] ?? ''); ?>
            </p>
        </div>

        <!-- Documentation Content -->
        <div class="dark:bg-gray-800 bg-white rounded-lg dark:border-gray-700 border-gray-200 border">
            <div class="p-8">
                <style>
                    /* Enhanced Markdown Typography */
                    .doc-content {
                        font-size: 16px;
                        line-height: 1.7;
                        color: #374151;
                    }
                    
                    .dark .doc-content {
                        color: #d1d5db;
                    }
                    
                    /* Headings */
                    .doc-content h1 {
                        font-size: 2.25rem;
                        font-weight: 700;
                        margin-top: 0;
                        margin-bottom: 1.5rem;
                        color: #111827;
                        border-bottom: 2px solid #e5e7eb;
                        padding-bottom: 0.5rem;
                    }
                    
                    .dark .doc-content h1 {
                        color: #f3f4f6;
                        border-bottom-color: #374151;
                    }
                    
                    .doc-content h2 {
                        font-size: 1.875rem;
                        font-weight: 600;
                        margin-top: 2.5rem;
                        margin-bottom: 1.25rem;
                        color: #1f2937;
                        border-bottom: 1px solid #e5e7eb;
                        padding-bottom: 0.375rem;
                    }
                    
                    .dark .doc-content h2 {
                        color: #e5e7eb;
                        border-bottom-color: #4b5563;
                    }
                    
                    .doc-content h3 {
                        font-size: 1.5rem;
                        font-weight: 600;
                        margin-top: 2rem;
                        margin-bottom: 1rem;
                        color: #374151;
                    }
                    
                    .dark .doc-content h3 {
                        color: #d1d5db;
                    }
                    
                    .doc-content h4 {
                        font-size: 1.25rem;
                        font-weight: 600;
                        margin-top: 1.5rem;
                        margin-bottom: 0.75rem;
                        color: #4b5563;
                    }
                    
                    .dark .doc-content h4 {
                        color: #9ca3af;
                    }
                    
                    /* Paragraphs */
                    .doc-content p {
                        margin-bottom: 1.25rem;
                        line-height: 1.75;
                    }
                    
                    /* Lists */
                    .doc-content ul {
                        list-style-type: disc;
                        margin-left: 1.5rem;
                        margin-bottom: 1.25rem;
                    }
                    
                    .doc-content ol {
                        list-style-type: decimal;
                        margin-left: 1.5rem;
                        margin-bottom: 1.25rem;
                    }
                    
                    .doc-content li {
                        margin-bottom: 0.5rem;
                        line-height: 1.75;
                    }
                    
                    .doc-content li > ul,
                    .doc-content li > ol {
                        margin-top: 0.5rem;
                        margin-bottom: 0.5rem;
                    }
                    
                    /* Code Blocks */
                    .doc-content pre {
                        background: #1f2937;
                        border: 1px solid #374151;
                        border-radius: 0.5rem;
                        padding: 1rem;
                        margin: 1.5rem 0;
                        overflow-x: auto;
                        position: relative;
                    }
                    
                    .doc-content pre code {
                        display: block;
                        background: transparent;
                        color: #e5e7eb;
                        font-family: 'Fira Code', 'Courier New', monospace;
                        font-size: 0.875rem;
                        line-height: 1.5;
                        padding: 0;
                        border: none;
                        white-space: pre;
                    }
                    
                    /* Inline Code */
                    .doc-content code.inline-code,
                    .doc-content p code,
                    .doc-content li code {
                        background: #f3f4f6;
                        color: #dc2626;
                        padding: 0.125rem 0.375rem;
                        border-radius: 0.25rem;
                        font-family: 'Fira Code', 'Courier New', monospace;
                        font-size: 0.875em;
                        font-weight: 500;
                        white-space: nowrap;
                    }
                    
                    .dark .doc-content code.inline-code,
                    .dark .doc-content p code,
                    .dark .doc-content li code {
                        background: #374151;
                        color: #67e8f9;
                    }
                    
                    /* Language-specific code highlighting */
                    .doc-content pre.language-bash code {
                        color: #86efac;
                    }
                    
                    .doc-content pre.language-php code,
                    .doc-content pre.language-javascript code,
                    .doc-content pre.language-js code {
                        color: #fbbf24;
                    }
                    
                    .doc-content pre.language-sql code {
                        color: #60a5fa;
                    }
                    
                    .doc-content pre.language-html code,
                    .doc-content pre.language-xml code {
                        color: #f87171;
                    }
                    
                    .doc-content pre.language-css code {
                        color: #c084fc;
                    }
                    
                    /* Links */
                    .doc-content a {
                        color: #0ea5e9;
                        text-decoration: underline;
                        text-underline-offset: 2px;
                        transition: color 0.2s;
                    }
                    
                    .doc-content a:hover {
                        color: #0284c7;
                    }
                    
                    .dark .doc-content a {
                        color: #38bdf8;
                    }
                    
                    .dark .doc-content a:hover {
                        color: #7dd3fc;
                    }
                    
                    /* Blockquotes */
                    .doc-content blockquote {
                        border-left: 4px solid #0ea5e9;
                        padding-left: 1rem;
                        margin: 1.5rem 0;
                        color: #6b7280;
                        font-style: italic;
                        background: #f9fafb;
                        padding: 1rem;
                        border-radius: 0 0.5rem 0.5rem 0;
                    }
                    
                    .dark .doc-content blockquote {
                        border-left-color: #0284c7;
                        color: #9ca3af;
                        background: #1f2937;
                    }
                    
                    /* Horizontal Rules */
                    .doc-content hr {
                        border: none;
                        border-top: 2px solid #e5e7eb;
                        margin: 2.5rem 0;
                    }
                    
                    .dark .doc-content hr {
                        border-top-color: #4b5563;
                    }
                    
                    /* Tables */
                    .doc-content table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 1.5rem 0;
                    }
                    
                    .doc-content th {
                        background: #f9fafb;
                        padding: 0.75rem;
                        text-align: left;
                        font-weight: 600;
                        border: 1px solid #e5e7eb;
                    }
                    
                    .dark .doc-content th {
                        background: #1f2937;
                        border-color: #4b5563;
                    }
                    
                    .doc-content td {
                        padding: 0.75rem;
                        border: 1px solid #e5e7eb;
                    }
                    
                    .dark .doc-content td {
                        border-color: #4b5563;
                    }
                    
                    /* Images */
                    .doc-content img,
                    .doc-content .doc-image {
                        max-width: 100%;
                        height: auto;
                        border-radius: 0.5rem;
                        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
                        margin: 1.5rem 0;
                    }
                    
                    /* Strong/Bold */
                    .doc-content strong {
                        font-weight: 600;
                        color: #111827;
                    }
                    
                    .dark .doc-content strong {
                        color: #f3f4f6;
                    }
                    
                    /* Em/Italic */
                    .doc-content em {
                        font-style: italic;
                    }
                    
                    /* Copy button for code blocks */
                    .doc-content pre {
                        position: relative;
                    }
                    
                    .doc-content pre .copy-button {
                        position: absolute;
                        top: 0.5rem;
                        right: 0.5rem;
                        padding: 0.25rem 0.75rem;
                        background: #4b5563;
                        color: #e5e7eb;
                        border: 1px solid #6b7280;
                        border-radius: 0.25rem;
                        font-size: 0.75rem;
                        cursor: pointer;
                        opacity: 0;
                        transition: opacity 0.2s, background 0.2s;
                    }
                    
                    .doc-content pre:hover .copy-button {
                        opacity: 1;
                    }
                    
                    .doc-content pre .copy-button:hover {
                        background: #6b7280;
                    }
                    
                    .doc-content pre .copy-button.copied {
                        background: #10b981;
                        border-color: #10b981;
                    }
                </style>
                <div class="doc-content">
                    <?php echo $html_content; ?>
                </div>
            </div>
        </div>

        <!-- Navigation buttons -->
        <div class="flex justify-between mt-8">
            <?php 
            // Find previous and next pages
            $all_pages = [];
            foreach ($docs_structure as $s_key => $s_data) {
                foreach ($s_data['pages'] as $p_key => $p_data) {
                    $all_pages[] = ['section' => $s_key, 'page' => $p_key, 'data' => $p_data];
                }
            }
            
            $current_index = -1;
            foreach ($all_pages as $index => $item) {
                if ($item['section'] === $section && $item['page'] === $page) {
                    $current_index = $index;
                    break;
                }
            }
            
            $prev_page = $current_index > 0 ? $all_pages[$current_index - 1] : null;
            $next_page = $current_index < count($all_pages) - 1 ? $all_pages[$current_index + 1] : null;
            ?>
            
            <?php if ($prev_page): ?>
            <a href="?section=<?php echo $prev_page['section']; ?>&page=<?php echo $prev_page['page']; ?>" 
               class="flex items-center space-x-2 px-4 py-2 dark:bg-gray-800 bg-white rounded-lg dark:border-gray-700 border-gray-200 border dark:hover:bg-gray-700 hover:bg-gray-50 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                <span>Previous: <?php echo htmlspecialchars($prev_page['data']['title']); ?></span>
            </a>
            <?php else: ?>
            <div></div>
            <?php endif; ?>
            
            <?php if ($next_page): ?>
            <a href="?section=<?php echo $next_page['section']; ?>&page=<?php echo $next_page['page']; ?>" 
               class="flex items-center space-x-2 px-4 py-2 dark:bg-gray-800 bg-white rounded-lg dark:border-gray-700 border-gray-200 border dark:hover:bg-gray-700 hover:bg-gray-50 transition">
                <span>Next: <?php echo htmlspecialchars($next_page['data']['title']); ?></span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
            <?php else: ?>
            <div></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Sidebar Navigation -->
    <aside class="w-80 flex-shrink-0 hidden lg:block">
        <div class="sticky top-20">
            <!-- Section Navigation -->
            <div class="dark:bg-gray-800 bg-white rounded-lg p-4 dark:border-gray-700 border-gray-200 border mb-4">
                <h3 class="text-sm font-semibold dark:text-gray-400 text-gray-600 mb-3 uppercase tracking-wider">Documentation</h3>
                <nav class="space-y-1">
                    <?php foreach ($docs_structure as $s_key => $s_data): ?>
                    <div class="mb-3">
                        <div class="flex items-center space-x-2 text-sm font-semibold dark:text-gray-300 text-gray-700 mb-2">
                            <?php if ($s_data['icon'] === 'rocket'): ?>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <?php elseif ($s_data['icon'] === 'adjustments'): ?>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                            <?php elseif ($s_data['icon'] === 'terminal'): ?>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <?php elseif ($s_data['icon'] === 'code'): ?>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                            </svg>
                            <?php elseif ($s_data['icon'] === 'book-open'): ?>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            <?php elseif ($s_data['icon'] === 'exclamation-circle'): ?>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <?php elseif ($s_data['icon'] === 'academic-cap'): ?>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path>
                            </svg>
                            <?php else: /* Default folder icon */ ?>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                            <?php endif; ?>
                            <span><?php echo htmlspecialchars($s_data['title']); ?></span>
                        </div>
                        <div class="ml-6 space-y-1">
                            <?php foreach ($s_data['pages'] as $p_key => $p_data): ?>
                            <a href="?section=<?php echo $s_key; ?>&page=<?php echo $p_key; ?>" 
                               class="block px-3 py-1.5 text-sm rounded-md transition-colors <?php echo ($section === $s_key && $page === $p_key) ? 'bg-cyan-500 text-white' : 'dark:text-gray-400 text-gray-600 dark:hover:text-white hover:text-gray-900 dark:hover:bg-gray-700 hover:bg-gray-100'; ?>">
                                <?php echo htmlspecialchars($p_data['title']); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </nav>
            </div>

            <!-- Quick Actions -->
            <div class="dark:bg-gray-800 bg-white rounded-lg p-4 dark:border-gray-700 border-gray-200 border">
                <h3 class="text-sm font-semibold dark:text-gray-400 text-gray-600 mb-3 uppercase tracking-wider">Quick Actions</h3>
                <div class="space-y-2">
                    <a href="/isotone/iso-admin/automation.php" 
                       class="flex items-center space-x-2 text-sm dark:text-gray-400 text-gray-600 dark:hover:text-cyan-400 hover:text-cyan-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>Automation Dashboard</span>
                    </a>
                    <a href="/isotone/iso-admin/hooks-explorer.php" 
                       class="flex items-center space-x-2 text-sm dark:text-gray-400 text-gray-600 dark:hover:text-cyan-400 hover:text-cyan-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                        <span>Hooks Explorer</span>
                    </a>
                    <a href="/isotone/iso-admin/api-test.php" 
                       class="flex items-center space-x-2 text-sm dark:text-gray-400 text-gray-600 dark:hover:text-cyan-400 hover:text-cyan-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>API Testing</span>
                    </a>
                </div>
            </div>
        </div>
    </aside>
</div>

<script>
// Add smooth scrolling for anchor links within documentation
document.addEventListener('DOMContentLoaded', function() {
    // Handle anchor links
    document.querySelectorAll('.prose a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add copy button to code blocks
    document.querySelectorAll('.doc-content pre').forEach(pre => {
        // Skip if button already exists
        if (pre.querySelector('.copy-button')) return;
        
        const button = document.createElement('button');
        button.className = 'copy-button';
        button.textContent = 'Copy';
        
        button.addEventListener('click', () => {
            const code = pre.querySelector('code');
            if (code) {
                // Get text content without the button text
                const textToCopy = code.textContent || code.innerText;
                
                navigator.clipboard.writeText(textToCopy).then(() => {
                    button.textContent = 'Copied!';
                    button.classList.add('copied');
                    setTimeout(() => {
                        button.textContent = 'Copy';
                        button.classList.remove('copied');
                    }, 2000);
                }).catch(err => {
                    // Fallback for older browsers
                    const textarea = document.createElement('textarea');
                    textarea.value = textToCopy;
                    textarea.style.position = 'fixed';
                    textarea.style.opacity = '0';
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    
                    button.textContent = 'Copied!';
                    button.classList.add('copied');
                    setTimeout(() => {
                        button.textContent = 'Copy';
                        button.classList.remove('copied');
                    }, 2000);
                });
            }
        });
        
        pre.appendChild(button);
    });
    
    // Add syntax highlighting classes based on code content
    document.querySelectorAll('.doc-content pre code').forEach(code => {
        const text = code.textContent;
        const pre = code.parentElement;
        
        // Try to detect language if not already specified
        if (!pre.className.includes('language-')) {
            if (text.includes('<?php') || text.includes('function ') || text.includes('class ')) {
                pre.classList.add('language-php');
            } else if (text.includes('CREATE TABLE') || text.includes('SELECT ') || text.includes('INSERT INTO')) {
                pre.classList.add('language-sql');
            } else if (text.includes('#!/bin/bash') || text.includes('npm ') || text.includes('composer ')) {
                pre.classList.add('language-bash');
            } else if (text.includes('const ') || text.includes('let ') || text.includes('var ') || text.includes('=>')) {
                pre.classList.add('language-javascript');
            } else if (text.includes('<html') || text.includes('<div') || text.includes('<!DOCTYPE')) {
                pre.classList.add('language-html');
            } else if (text.includes('{') && text.includes('}') && text.includes(':') && text.includes(';')) {
                pre.classList.add('language-css');
            }
        }
    });
});
</script>

<?php
// Get the buffered content
$page_content = ob_get_clean();

// Include the admin layout
require_once __DIR__ . '/includes/admin-layout.php';
?>
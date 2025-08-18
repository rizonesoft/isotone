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

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;

// Configure CommonMark with GitHub Flavored Markdown
$environment = new Environment([
    'html_input' => 'strip',
    'allow_unsafe_links' => false,
    'max_nesting_level' => 100
]);

// Add GitHub Flavored Markdown extensions (includes tables, strikethrough, autolinks, task lists)
$environment->addExtension(new GithubFlavoredMarkdownExtension());

// Create the markdown converter
$markdownConverter = new CommonMarkConverter([], $environment);

// Function to convert filename to title
function toTitleCase($string) {
    $string = str_replace(['-', '_'], ' ', $string);
    return ucwords($string);
}

// Function to get sort priority for documentation files
function getDocSortPriority($filename) {
    // Define priority order (lower number = higher priority)
    $priorities = [
        // Introductory content
        'introduction' => 1,
        'intro' => 1,
        'overview' => 2,
        'getting-started' => 3,
        'getting_started' => 3,
        'quick-start' => 4,
        'quickstart' => 4,
        'installation' => 5,
        'install' => 5,
        'setup' => 6,
        'requirements' => 7,
        'prerequisites' => 8,
        
        // Basic/Core concepts
        'basics' => 10,
        'fundamentals' => 11,
        'core-concepts' => 12,
        'concepts' => 13,
        'architecture' => 14,
        'structure' => 15,
        
        // Configuration
        'configuration' => 20,
        'config' => 20,
        'settings' => 21,
        'options' => 22,
        
        // Usage/Features
        'usage' => 30,
        'features' => 31,
        'functionality' => 32,
        'how-to' => 33,
        'howto' => 33,
        'guides' => 34,
        'tutorials' => 35,
        'examples' => 36,
        
        // API/Reference
        'api' => 40,
        'api-reference' => 41,
        'reference' => 42,
        'methods' => 43,
        'functions' => 44,
        'hooks' => 45,
        'filters' => 46,
        'actions' => 47,
        
        // Advanced topics
        'advanced' => 50,
        'customization' => 51,
        'extending' => 52,
        'plugins' => 53,
        'themes' => 54,
        'development' => 55,
        
        // Troubleshooting
        'troubleshooting' => 60,
        'debugging' => 61,
        'errors' => 62,
        'common-issues' => 63,
        'faq' => 64,
        'faqs' => 64,
        
        // Migration/Updates
        'migration' => 70,
        'upgrading' => 71,
        'updates' => 72,
        'changelog' => 73,
        'release-notes' => 74,
        
        // Additional resources
        'resources' => 80,
        'links' => 81,
        'credits' => 82,
        'acknowledgments' => 83,
        'license' => 84,
        'contributing' => 85,
        'glossary' => 90,
        'appendix' => 95,
        'notes' => 96
    ];
    
    // Convert filename to lowercase for comparison
    $lower = strtolower($filename);
    
    // Check for exact match
    if (isset($priorities[$lower])) {
        return $priorities[$lower];
    }
    
    // Check for partial matches (e.g., "01-introduction" or "introduction-guide")
    foreach ($priorities as $keyword => $priority) {
        if (strpos($lower, $keyword) !== false) {
            // Give slight penalty for partial match vs exact match
            return $priority + 0.5;
        }
    }
    
    // Default priority for unmatched files (alphabetical within this group)
    return 100;
}

// Function to sort documentation pages
function sortDocPages($pages) {
    uasort($pages, function($a, $b) {
        // Extract filename without extension from file path
        $filenameA = pathinfo($a['file'], PATHINFO_FILENAME);
        $filenameB = pathinfo($b['file'], PATHINFO_FILENAME);
        
        // Get priorities
        $priorityA = getDocSortPriority($filenameA);
        $priorityB = getDocSortPriority($filenameB);
        
        // Sort by priority first
        if ($priorityA != $priorityB) {
            return $priorityA <=> $priorityB;
        }
        
        // If same priority, sort alphabetically by title
        return strcasecmp($a['title'], $b['title']);
    });
    
    return $pages;
}

// Function to get icon for category
function getCategoryIcon($category) {
    $icons = [
        'getting-started' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>',
        'configuration' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>',
        'development' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>',
        'api-reference' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>',
        'automation' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>',
        'toni' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>',
        'troubleshooting' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
    ];
    
    // Default icon (folder)
    $defaultIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>';
    
    return $icons[$category] ?? $defaultIcon;
}

// Static documentation structure
$docs_path = dirname(__DIR__) . '/user-docs/';
$docs_structure = [
    'getting-started' => [
        'title' => 'Getting Started',
        'description' => 'Quick start guides',
        'pages' => []
    ],
    'configuration' => [
        'title' => 'Configuration', 
        'description' => 'System settings',
        'pages' => []
    ],
    'development' => [
        'title' => 'Development',
        'description' => 'Developer guides',
        'pages' => []
    ],
    'api-reference' => [
        'title' => 'API Reference',
        'description' => 'API documentation',
        'pages' => []
    ]
];

// Scan for markdown files
if (is_dir($docs_path)) {
    $folders = scandir($docs_path);
    foreach ($folders as $folder) {
        if ($folder === '.' || $folder === '..' || !is_dir($docs_path . $folder)) {
            continue;
        }
        
        // Map folder to category
        $category_key = $folder;
        if (!isset($docs_structure[$category_key])) {
            $docs_structure[$category_key] = [
                'title' => toTitleCase($folder),
                'description' => '',
                'pages' => []
            ];
        }
        
        // Scan for .md files
        $files = scandir($docs_path . $folder);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'md') {
                $filename = pathinfo($file, PATHINFO_FILENAME);
                $docs_structure[$category_key]['pages'][$filename] = [
                    'title' => toTitleCase($filename),
                    'file' => $folder . '/' . $file
                ];
            }
        }
        
        // Sort the pages in logical order
        if (!empty($docs_structure[$category_key]['pages'])) {
            $docs_structure[$category_key]['pages'] = sortDocPages($docs_structure[$category_key]['pages']);
        }
    }
}

// Get requested category and page
$category = $_GET['category'] ?? $_GET['section'] ?? array_key_first($docs_structure) ?? 'getting-started';
$page = $_GET['page'] ?? '';

// Validate category
if (!isset($docs_structure[$category])) {
    $category = array_key_first($docs_structure) ?? 'getting-started';
}

// Get first page if none specified
if (empty($page) && !empty($docs_structure[$category]['pages'])) {
    $page = array_key_first($docs_structure[$category]['pages']);
}

// Load markdown content
$html_content = '';
if (!empty($page) && isset($docs_structure[$category]['pages'][$page])) {
    $file_path = $docs_path . $docs_structure[$category]['pages'][$page]['file'];
    if (file_exists($file_path)) {
        $markdown = file_get_contents($file_path);
        try {
            $html_content = $markdownConverter->convert($markdown)->getContent();
        } catch (Exception $e) {
            // Fallback to displaying raw markdown with basic formatting
            $html_content = '<pre class="whitespace-pre-wrap">' . htmlspecialchars($markdown) . '</pre>';
        }
    } else {
        $html_content = '<div class="text-red-500">File not found</div>';
    }
} else {
    $html_content = '<div class="text-gray-500">Select a document from the sidebar</div>';
}

// Page configuration
$page_title = !empty($page) && isset($docs_structure[$category]['pages'][$page])
    ? $docs_structure[$category]['pages'][$page]['title'] . ' - Documentation'
    : 'Documentation';

$breadcrumbs = [
    ['title' => 'Documentation', 'url' => '/isotone/iso-admin/documentation.php'],
    ['title' => $docs_structure[$category]['title'] ?? 'Unknown']
];

if (!empty($page) && isset($docs_structure[$category]['pages'][$page])) {
    $breadcrumbs[] = ['title' => $docs_structure[$category]['pages'][$page]['title']];
}

// Start output buffering
ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold dark:text-white text-gray-900">
        <?php echo htmlspecialchars($page_title); ?>
    </h1>
    <p class="dark:text-gray-400 text-gray-600 mt-2">
        <?php echo htmlspecialchars($docs_structure[$category]['description'] ?? ''); ?>
    </p>
</div>

<!-- Custom Styles -->
<style>
    /* Custom scrollbar for the sidebar */
    .sidebar-scroll::-webkit-scrollbar {
        width: 6px;
    }
    
    .sidebar-scroll::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .sidebar-scroll::-webkit-scrollbar-thumb {
        background-color: #4b5563;
        border-radius: 3px;
    }
    
    .sidebar-scroll::-webkit-scrollbar-thumb:hover {
        background-color: #6b7280;
    }
    
    /* Firefox */
    .sidebar-scroll {
        scrollbar-width: thin;
        scrollbar-color: #4b5563 transparent;
    }
    
    /* Fallback typography styles if prose classes don't work */
    .prose h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-top: 1.5rem;
        margin-bottom: 1rem;
        line-height: 1.2;
    }
    
    .prose h2 {
        font-size: 1.5rem;
        font-weight: 600;
        margin-top: 1.25rem;
        margin-bottom: 0.75rem;
        line-height: 1.3;
    }
    
    .prose h3 {
        font-size: 1.25rem;
        font-weight: 600;
        margin-top: 1rem;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }
    
    .prose h4 {
        font-size: 1.125rem;
        font-weight: 600;
        margin-top: 0.75rem;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }
    
    .prose p {
        margin-bottom: 1rem;
        line-height: 1.75;
    }
    
    .prose ul, .prose ol {
        margin: 1rem 0;
        padding-left: 1.5rem;
    }
    
    .prose li {
        margin: 0.25rem 0;
        line-height: 1.75;
    }
    
    .prose ul {
        list-style-type: disc;
    }
    
    .prose ol {
        list-style-type: decimal;
    }
    
    .prose code {
        background-color: rgb(17 24 39);
        color: rgb(34 211 238);
        padding: 0.125rem 0.25rem;
        border-radius: 0.25rem;
        font-size: 0.875em;
    }
    
    .prose pre {
        background-color: rgb(17 24 39);
        color: rgb(229 231 235);
        padding: 1rem;
        border-radius: 0.375rem;
        overflow-x: auto;
        margin: 1rem 0;
    }
    
    .prose pre code {
        background-color: transparent;
        padding: 0;
        color: inherit;
    }
    
    .prose blockquote {
        border-left: 4px solid rgb(34 211 238);
        padding-left: 1rem;
        font-style: italic;
        margin: 1rem 0;
    }
    
    .prose a {
        color: rgb(34 211 238);
        text-decoration: none;
    }
    
    .prose a:hover {
        text-decoration: underline;
    }
    
    .prose table {
        width: 100%;
        border-collapse: collapse;
        margin: 1rem 0;
    }
    
    .prose th {
        background-color: rgb(55 65 81);
        padding: 0.5rem;
        text-align: left;
        font-weight: 600;
    }
    
    .prose td {
        padding: 0.5rem;
        border-top: 1px solid rgb(55 65 81);
    }
    
    .prose hr {
        margin: 2rem 0;
        border-color: rgb(55 65 81);
    }
    
    /* Dark mode text colors */
    .dark .prose h1,
    .dark .prose h2,
    .dark .prose h3,
    .dark .prose h4 {
        color: rgb(243 244 246);
    }
    
    .dark .prose p,
    .dark .prose li {
        color: rgb(209 213 219);
    }
    
    .dark .prose strong {
        color: rgb(243 244 246);
    }
</style>

<!-- Main Layout with Sidebar -->
<div class="flex gap-6">
    
    <!-- Main Content Area -->
    <div class="flex-1">
        <div class="dark:bg-gray-800 bg-white rounded-lg dark:border-gray-700 border-gray-200 border p-6">
            <div class="prose prose-lg dark:prose-invert max-w-none
                        prose-headings:font-bold
                        prose-h1:text-3xl prose-h1:mb-4 prose-h1:mt-6
                        prose-h2:text-2xl prose-h2:mb-3 prose-h2:mt-5
                        prose-h3:text-xl prose-h3:mb-2 prose-h3:mt-4
                        prose-h4:text-lg prose-h4:mb-2 prose-h4:mt-3
                        prose-p:mb-4 prose-p:leading-relaxed
                        prose-ul:my-4 prose-ol:my-4
                        prose-li:my-1
                        prose-pre:bg-gray-900 prose-pre:text-gray-100
                        prose-code:text-cyan-400 prose-code:bg-gray-900 prose-code:px-1 prose-code:py-0.5 prose-code:rounded
                        prose-blockquote:border-l-4 prose-blockquote:border-cyan-500 prose-blockquote:pl-4 prose-blockquote:italic
                        prose-a:text-cyan-400 prose-a:no-underline hover:prose-a:underline
                        prose-strong:text-white
                        prose-table:w-full
                        prose-th:text-left prose-th:p-2 prose-th:bg-gray-700
                        prose-td:p-2 prose-td:border-t prose-td:border-gray-700">
                <?php echo $html_content; ?>
            </div>
        </div>
    </div>
    
    <!-- Right Sidebar -->
    <aside class="w-80 flex-shrink-0 hidden lg:block">
        <div class="sticky top-20 max-h-[calc(100vh-6rem)] overflow-y-auto">
            <div class="dark:bg-gray-800 bg-white rounded-lg dark:border-gray-700 border-gray-200 border p-4">
                <h3 class="text-sm font-semibold dark:text-gray-400 text-gray-600 mb-4 uppercase tracking-wider">
                    Documentation Topics
                </h3>
            
                <div class="space-y-3 overflow-y-auto max-h-[calc(100vh-12rem)] pr-2 sidebar-scroll">
                    <?php foreach ($docs_structure as $cat_key => $cat_data): ?>
                    <div class="border-b dark:border-gray-700 border-gray-200 pb-3 last:border-0">
                        <!-- Category Header with Icon (Clickable) -->
                        <button onclick="toggleDocCategory('<?php echo $cat_key; ?>')" 
                                class="w-full flex items-center justify-between font-medium dark:text-gray-300 text-gray-700 mb-2 dark:hover:text-white hover:text-gray-900 transition-colors">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <?php echo getCategoryIcon($cat_key); ?>
                                </svg>
                                <span><?php echo htmlspecialchars($cat_data['title']); ?></span>
                                <?php if (!empty($cat_data['pages'])): ?>
                                <span class="text-xs dark:text-gray-500 text-gray-400">(<?php echo count($cat_data['pages']); ?>)</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($cat_data['pages'])): ?>
                            <svg id="chevron-<?php echo $cat_key; ?>" 
                                 class="w-4 h-4 transition-transform <?php echo ($category === $cat_key) ? 'rotate-90' : ''; ?>" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            <?php endif; ?>
                        </button>
                        
                        <?php if (!empty($cat_data['pages'])): ?>
                        <div id="category-<?php echo $cat_key; ?>" 
                             class="ml-6 space-y-1 overflow-hidden transition-all duration-300 <?php echo ($category !== $cat_key) ? 'hidden' : ''; ?>">
                            <?php foreach ($cat_data['pages'] as $page_key => $page_data): ?>
                            <a href="?category=<?php echo urlencode($cat_key); ?>&page=<?php echo urlencode($page_key); ?>"
                               class="block px-3 py-1.5 text-sm rounded-md transition-colors <?php echo ($category === $cat_key && $page === $page_key) 
                                   ? 'bg-cyan-500 text-white' 
                                   : 'dark:text-gray-400 text-gray-600 dark:hover:text-white hover:text-gray-900 dark:hover:bg-gray-700 hover:bg-gray-100'; ?>">
                                <?php echo htmlspecialchars($page_data['title']); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="ml-6 text-xs dark:text-gray-500 text-gray-500 italic">
                            No documents yet
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </aside>
    
</div>

<script>
// Toggle category visibility
function toggleDocCategory(categoryKey) {
    const categoryDiv = document.getElementById('category-' + categoryKey);
    const chevron = document.getElementById('chevron-' + categoryKey);
    
    if (categoryDiv) {
        categoryDiv.classList.toggle('hidden');
        if (chevron) {
            chevron.classList.toggle('rotate-90');
        }
    }
}
</script>

<?php
// Get the buffered content
$page_content = ob_get_clean();

// Include the admin layout
require_once __DIR__ . '/includes/admin-layout.php';
?>
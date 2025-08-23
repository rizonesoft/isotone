<?php
/**
 * Isotone Hooks Explorer
 * 
 * Interactive explorer for discovering and understanding Isotone hooks
 */

// Check authentication
require_once 'auth.php';

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config.php';

// Initialize database connection
try {
    \RedBeanPHP\R::setup(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
        DB_USER,
        DB_PASSWORD
    );
} catch (\Exception $e) {
    // Continue without database
}

// Load the hooks system
require_once dirname(__DIR__) . '/iso-core/hooks.php';

// Load theme hooks if active theme exists
$activeTheme = \RedBeanPHP\R::findOne('setting', 'setting_key = ?', ['active_theme']);
if ($activeTheme && $activeTheme->setting_value) {
    $themeFunctions = dirname(__DIR__) . '/iso-content/themes/' . $activeTheme->setting_value . '/functions.php';
    if (file_exists($themeFunctions)) {
        require_once $themeFunctions;
    }
}

// Load plugin hooks
$pluginsDir = dirname(__DIR__) . '/iso-content/plugins';
if (is_dir($pluginsDir)) {
    foreach (glob($pluginsDir . '/*/') as $pluginDir) {
        $pluginFile = basename($pluginDir) . '.php';
        $pluginPath = $pluginDir . $pluginFile;
        if (file_exists($pluginPath)) {
            require_once $pluginPath;
        }
    }
}

// Trigger initialization hooks to register everything
// Note: We skip admin_menu as it may cause issues without full admin context
if (function_exists('do_action')) {
    do_action('after_setup_theme');
    do_action('init');
    do_action('widgets_init');
    do_action('iso_enqueue_scripts');
}

use Isotone\Core\Hook;
use Isotone\Core\SystemHooks;

// Get system-defined hooks
$systemHooks = SystemHooks::getSystemHooks();

// Get hooks with registered callbacks
$registeredHooks = Hook::getAllHooks();

// Load implementation data BEFORE processing hooks
$implementationData = [];
$implementationFile = dirname(__DIR__) . '/iso-automation/storage/hooks-implementation.json';
if (file_exists($implementationFile)) {
    $data = json_decode(file_get_contents($implementationFile), true);
    if ($data && isset($data['implementations'])) {
        $implementationData = $data['implementations'];
    }
}

// We no longer parse HOOKS.md - all hook documentation comes from SystemHooks class
// HOOKS.md is only used for tracking implementation status, not for documentation

// Group hooks by category
$categories = [
    'initialization' => [
        'name' => 'Initialization',
        'description' => 'System initialization and setup hooks',
        'hooks' => []
    ],
    'content' => [
        'name' => 'Content',
        'description' => 'Content display and filtering hooks',
        'hooks' => []
    ],
    'admin' => [
        'name' => 'Admin',
        'description' => 'Admin interface and dashboard hooks',
        'hooks' => []
    ],
    'user' => [
        'name' => 'User & Auth',
        'description' => 'User management and authentication hooks',
        'hooks' => []
    ],
    'database' => [
        'name' => 'Database',
        'description' => 'Database operation hooks',
        'hooks' => []
    ],
    'theme_plugin' => [
        'name' => 'Themes & Plugins',
        'description' => 'Theme and plugin lifecycle hooks',
        'hooks' => []
    ],
    'api' => [
        'name' => 'API & AJAX',
        'description' => 'REST API and AJAX hooks',
        'hooks' => []
    ],
    'other' => [
        'name' => 'Other',
        'description' => 'Miscellaneous hooks',
        'hooks' => []
    ]
];

// Process system hooks
foreach ($systemHooks as $hookName => $hookData) {
    // Determine category
    $category = 'other';
    
    if (strpos($hookName, 'init') !== false || in_array($hookName, ['iso_loaded', 'after_setup_theme', 'shutdown'])) {
        $category = 'initialization';
    } elseif (strpos($hookName, 'content') !== false || in_array($hookName, ['iso_before_post', 'iso_after_post', 'the_content', 'the_title', 'the_excerpt', 'iso_head', 'iso_footer', 'iso_body_open'])) {
        $category = 'content';
    } elseif (strpos($hookName, 'admin') !== false) {
        $category = 'admin';
    } elseif (strpos($hookName, 'user') !== false || strpos($hookName, 'login') !== false || strpos($hookName, 'logout') !== false || strpos($hookName, 'register') !== false || strpos($hookName, 'auth') !== false || strpos($hookName, 'capabilit') !== false) {
        $category = 'user';
    } elseif (strpos($hookName, 'save') !== false || strpos($hookName, 'delete') !== false || strpos($hookName, 'database') !== false) {
        $category = 'database';
    } elseif (strpos($hookName, 'theme') !== false || strpos($hookName, 'plugin') !== false || strpos($hookName, 'widget') !== false) {
        $category = 'theme_plugin';
    } elseif (strpos($hookName, 'ajax') !== false || strpos($hookName, 'rest') !== false || strpos($hookName, 'api') !== false) {
        $category = 'api';
    } elseif (strpos($hookName, 'script') !== false || strpos($hookName, 'style') !== false || strpos($hookName, 'enqueue') !== false) {
        $category = 'content';
    } elseif (strpos($hookName, 'route') !== false || strpos($hookName, 'template') !== false || strpos($hookName, 'url') !== false) {
        $category = 'content';
    }
    
    // Get callback count from registered hooks
    $callbackCount = 0;
    if (isset($registeredHooks['actions'][$hookName])) {
        foreach ($registeredHooks['actions'][$hookName] as $priority => $callbacks) {
            $callbackCount += count($callbacks);
        }
    }
    if (isset($registeredHooks['filters'][$hookName])) {
        foreach ($registeredHooks['filters'][$hookName] as $priority => $callbacks) {
            $callbackCount += count($callbacks);
        }
    }
    
    // Check if hook is implemented (actually fired in code)
    $isImplemented = false;
    $implementationLocations = [];
    
    if ($hookData['type'] === 'action' && isset($implementationData['actions'][$hookName])) {
        $isImplemented = true;
        $implementationLocations = $implementationData['actions'][$hookName];
    } elseif ($hookData['type'] === 'filter' && isset($implementationData['filters'][$hookName])) {
        $isImplemented = true;
        $implementationLocations = $implementationData['filters'][$hookName];
    }
    
    $hookInfo = [
        'name' => $hookName,
        'type' => ucfirst($hookData['type']),
        'description' => $hookData['description'],
        'since' => $hookData['since'],
        'callbacks' => $callbackCount,
        'dynamic' => !empty($hookData['dynamic']),
        'system' => true,
        'implemented' => $isImplemented,
        'locations' => $implementationLocations
    ];
    
    $categories[$category]['hooks'][] = $hookInfo;
}

// Sort hooks in each category
foreach ($categories as &$category) {
    usort($category['hooks'], function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
}

// Get hook statistics from JSON file
$hookStats = null;
$statsFile = dirname(__DIR__) . '/iso-automation/storage/hook-stats.json';
if (file_exists($statsFile)) {
    $statsData = json_decode(file_get_contents($statsFile), true);
    if ($statsData) {
        // Convert array to object format for easy access
        $hookStats = (object) $statsData;
    }
}


// Page configuration
$page_title = 'Hooks Explorer';
$breadcrumbs = [
    ['title' => 'Development'],
    ['title' => 'Hooks Explorer']
];

// Start output buffering
ob_start();
?>

<!-- Main Layout with Sidebar -->
<div class="flex gap-6">
    <!-- Main Content Area -->
    <div class="flex-1">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold dark:text-white text-gray-900">Hooks Explorer</h1>
            <p class="dark:text-gray-400 text-gray-600 mt-2">Discover and explore Isotone hooks and filters</p>
        </div>

        <?php if ($hookStats): ?>
        <!-- Implementation Statistics -->
        <div class="dark:bg-gray-800 bg-white rounded-lg p-6 mb-6 dark:border-gray-700 border-gray-200 border">
            <h2 class="text-xl font-semibold dark:text-white text-gray-900 mb-4">Implementation Statistics</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="dark:bg-gray-900 bg-gray-50 rounded-lg p-4 dark:border-gray-700 border-gray-200 border">
                    <div class="text-3xl font-bold text-cyan-400"><?php echo $hookStats->system_hooks_defined; ?></div>
                    <div class="text-xs text-gray-500 mt-1 uppercase tracking-wider">System Hooks</div>
                </div>
                <div class="dark:bg-gray-900 bg-gray-50 rounded-lg p-4 dark:border-gray-700 border-gray-200 border">
                    <div class="text-3xl font-bold text-green-400">
                        <?php 
                        $actionCount = 0;
                        foreach ($systemHooks as $hook) {
                            if ($hook['type'] === 'action') $actionCount++;
                        }
                        echo $actionCount;
                        ?>
                    </div>
                    <div class="text-xs text-gray-500 mt-1 uppercase tracking-wider">Actions</div>
                </div>
                <div class="dark:bg-gray-900 bg-gray-50 rounded-lg p-4 dark:border-gray-700 border-gray-200 border">
                    <div class="text-3xl font-bold text-yellow-400">
                        <?php 
                        $filterCount = 0;
                        foreach ($systemHooks as $hook) {
                            if ($hook['type'] === 'filter') $filterCount++;
                        }
                        echo $filterCount;
                        ?>
                    </div>
                    <div class="text-xs text-gray-500 mt-1 uppercase tracking-wider">Filters</div>
                </div>
                <div class="dark:bg-gray-900 bg-gray-50 rounded-lg p-4 dark:border-gray-700 border-gray-200 border">
                    <div class="text-3xl font-bold text-purple-400">
                        <?php 
                        $totalCallbacks = 0;
                        foreach ($categories as $cat) {
                            foreach ($cat['hooks'] as $hook) {
                                $totalCallbacks += $hook['callbacks'];
                            }
                        }
                        echo $totalCallbacks;
                        ?>
                    </div>
                    <div class="text-xs text-gray-500 mt-1 uppercase tracking-wider">Active Callbacks</div>
                </div>
                <div class="dark:bg-gray-900 bg-gray-50 rounded-lg p-4 dark:border-gray-700 border-gray-200 border">
                    <div class="text-3xl font-bold text-emerald-400"><?php echo $hookStats->system_hooks_implemented; ?></div>
                    <div class="text-xs text-gray-500 mt-1 uppercase tracking-wider">Implemented</div>
                </div>
                <div class="dark:bg-gray-900 bg-gray-50 rounded-lg p-4 dark:border-gray-700 border-gray-200 border">
                    <div class="text-3xl font-bold <?php echo $hookStats->implementation_coverage >= 50 ? 'text-emerald-400' : ($hookStats->implementation_coverage >= 25 ? 'text-yellow-400' : 'text-orange-400'); ?>">
                        <?php echo $hookStats->implementation_coverage; ?>%
                    </div>
                    <div class="text-xs text-gray-500 mt-1 uppercase tracking-wider">Coverage</div>
                    <div class="mt-2 w-full bg-gray-800 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all duration-500 <?php echo $hookStats->implementation_coverage >= 50 ? 'bg-emerald-400' : ($hookStats->implementation_coverage >= 25 ? 'bg-yellow-400' : 'bg-orange-400'); ?>" 
                             style="width: <?php echo $hookStats->implementation_coverage; ?>%"></div>
                    </div>
                </div>
                <div class="dark:bg-gray-900 bg-gray-50 rounded-lg p-4 dark:border-gray-700 border-gray-200 border">
                    <div class="text-3xl font-bold text-orange-400"><?php echo $hookStats->orphan_hooks; ?></div>
                    <div class="text-xs text-gray-500 mt-1 uppercase tracking-wider">Orphan Hooks</div>
                </div>
                <div class="dark:bg-gray-900 bg-gray-50 rounded-lg p-4 dark:border-gray-700 border-gray-200 border">
                    <div class="text-3xl font-bold text-blue-400"><?php echo $hookStats->files_scanned; ?></div>
                    <div class="text-xs text-gray-500 mt-1 uppercase tracking-wider">Files Scanned</div>
                </div>
            </div>
            <div class="mt-4 text-xs dark:text-gray-500 text-gray-600">
                Last updated: <?php echo date('M j, Y g:i A', strtotime($hookStats->generated_at)); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Search Bar -->
        <div class="relative mb-8">
            <input type="text" 
                   id="hook-search" 
                   placeholder="Search hooks..." 
                   class="w-full dark:bg-gray-800 bg-white dark:border-gray-700 border-gray-200 border rounded-lg py-3 px-4 pl-12 dark:text-white text-gray-900 dark:placeholder-gray-500 placeholder-gray-400 focus:outline-none focus:border-cyan-500">
            <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>

        <!-- Hooks by Category -->
        <?php foreach ($categories as $catKey => $category): ?>
            <?php if (empty($category['hooks'])) continue; ?>
            
            <div class="mb-8" data-category="<?php echo htmlspecialchars($catKey); ?>">
                <div class="dark:bg-gray-800 bg-white rounded-lg dark:border-gray-700 border-gray-200 border">
                    <div class="p-6 dark:border-gray-700 border-gray-200 border-b">
                        <h2 class="text-xl font-semibold dark:text-white text-gray-900"><?php echo htmlspecialchars($category['name']); ?></h2>
                        <p class="dark:text-gray-400 text-gray-600 text-sm mt-1"><?php echo htmlspecialchars($category['description']); ?></p>
                    </div>
                    
                    <div class="divide-y dark:divide-gray-700 divide-gray-200">
                        <?php foreach ($category['hooks'] as $hook): ?>
                        <div class="hook-item p-4 dark:hover:bg-gray-750 hover:bg-gray-50 transition" data-hook="<?php echo htmlspecialchars($hook['name']); ?>">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-lg font-mono text-cyan-400 mr-2"><?php echo htmlspecialchars($hook['name']); ?></h3>
                                        <span class="hook-badge hook-badge-<?php echo strtolower($hook['type']); ?> px-2.5 py-1 text-xs font-medium rounded-md inline-flex items-center gap-1">
                                            <?php if ($hook['type'] === 'Action'): ?>
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                            </svg>
                                            <?php else: ?>
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"/>
                                            </svg>
                                            <?php endif; ?>
                                            <?php echo $hook['type']; ?>
                                        </span>
                                        <?php if (!empty($hook['system'])): ?>
                                        <span class="hook-badge hook-badge-system px-2.5 py-1 text-xs font-medium rounded-md inline-flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            System
                                        </span>
                                        <?php endif; ?>
                                        <?php if (!empty($hook['dynamic'])): ?>
                                        <span class="hook-badge hook-badge-dynamic px-2.5 py-1 text-xs font-medium rounded-md inline-flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/>
                                            </svg>
                                            Dynamic
                                        </span>
                                        <?php endif; ?>
                                        <?php if ($hook['callbacks'] > 0): ?>
                                        <span class="hook-badge hook-badge-callbacks px-2.5 py-1 text-xs font-medium rounded-md inline-flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                            </svg>
                                            <?php echo $hook['callbacks']; ?>
                                        </span>
                                        <?php endif; ?>
                                        <?php if (!empty($hook['implemented'])): ?>
                                        <span class="hook-badge hook-badge-implemented px-2.5 py-1 text-xs font-medium rounded-md inline-flex items-center gap-1" title="This hook is fired in the codebase">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Active
                                        </span>
                                        <?php else: ?>
                                        <span class="hook-badge hook-badge-pending px-2.5 py-1 text-xs font-medium rounded-md inline-flex items-center gap-1" title="This hook is defined but not yet fired in code">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                            </svg>
                                            Pending
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($hook['description'])): ?>
                                    <p class="dark:text-gray-400 text-gray-600 mt-2"><?php echo htmlspecialchars($hook['description']); ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="mt-3 flex flex-wrap gap-4 text-sm">
                                        <?php if (!empty($hook['since'])): ?>
                                        <span class="dark:text-gray-500 text-gray-600">
                                            <strong>Since:</strong> <?php echo htmlspecialchars($hook['since']); ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php 
                                    // Generate usage example
                                    $usage = '';
                                    if ($hook['type'] === 'Action') {
                                        $usage = "add_action('{$hook['name']}', 'your_callback_function', 10, 1);";
                                    } else {
                                        $usage = "add_filter('{$hook['name']}', 'your_filter_function', 10, 1);";
                                    }
                                    if (!empty($hook['dynamic'])) {
                                        $usage .= "\n// Note: Replace placeholder in hook name with actual value";
                                    }
                                    ?>
                                    <details class="mt-3">
                                        <summary class="cursor-pointer text-sm text-cyan-400 hover:text-cyan-300">View usage example</summary>
                                        <pre class="mt-2 dark:bg-gray-900 bg-gray-100 rounded p-3 text-xs overflow-x-auto"><code class="language-php dark:text-gray-300 text-gray-700"><?php echo htmlspecialchars($usage); ?></code></pre>
                                    </details>
                                    
                                    <?php if (!empty($hook['locations']) && count($hook['locations']) > 0): ?>
                                    <details class="mt-2">
                                        <summary class="cursor-pointer text-sm text-green-400 hover:text-green-300">
                                            Fired in <?php echo count($hook['locations']); ?> location<?php echo count($hook['locations']) > 1 ? 's' : ''; ?>
                                        </summary>
                                        <div class="mt-2 text-xs dark:text-gray-400 text-gray-600">
                                            <?php foreach ($hook['locations'] as $location): ?>
                                            <div class="font-mono">
                                                <?php echo htmlspecialchars($location['file']); ?>:<?php echo htmlspecialchars($location['line']); ?>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </details>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Right Sidebar -->
    <aside class="w-80 flex-shrink-0 hidden lg:block">
        <div class="sticky top-20">
            <!-- Badge Legend -->
            <div class="dark:bg-gray-800 bg-white rounded-lg p-4 dark:border-gray-700 border-gray-200 border">
                <h3 class="text-sm font-semibold dark:text-gray-400 text-gray-600 mb-3 uppercase tracking-wider">Badge Legend</h3>
                <div class="space-y-1 text-sm">
                    <div class="flex items-center justify-between py-2 dark:border-gray-700 border-gray-200 border-b">
                        <span class="dark:text-gray-400 text-gray-600 text-xs">Triggers custom code</span>
                        <span class="hook-badge hook-badge-action px-2 py-0.5 text-xs font-medium rounded-md inline-flex items-center gap-1 flex-shrink-0">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                            </svg>
                            Action
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2 dark:border-gray-700 border-gray-200 border-b">
                        <span class="dark:text-gray-400 text-gray-600 text-xs">Modifies data</span>
                        <span class="hook-badge hook-badge-filter px-2 py-0.5 text-xs font-medium rounded-md inline-flex items-center gap-1 flex-shrink-0">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"/>
                            </svg>
                            Filter
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2 dark:border-gray-700 border-gray-200 border-b">
                        <span class="dark:text-gray-400 text-gray-600 text-xs">Core Isotone hook</span>
                        <span class="hook-badge hook-badge-system px-2 py-0.5 text-xs font-medium rounded-md inline-flex items-center gap-1 flex-shrink-0">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            System
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2 dark:border-gray-700 border-gray-200 border-b">
                        <span class="dark:text-gray-400 text-gray-600 text-xs">Active listeners</span>
                        <span class="hook-badge hook-badge-callbacks px-2 py-0.5 text-xs font-medium rounded-md inline-flex items-center gap-1 flex-shrink-0">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                            </svg>
                            3
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2 dark:border-gray-700 border-gray-200 border-b">
                        <span class="dark:text-gray-400 text-gray-600 text-xs">Fired in code</span>
                        <span class="hook-badge hook-badge-implemented px-2 py-0.5 text-xs font-medium rounded-md inline-flex items-center gap-1 flex-shrink-0">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Active
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2 dark:border-gray-700 border-gray-200 border-b">
                        <span class="dark:text-gray-400 text-gray-600 text-xs">Not yet used</span>
                        <span class="hook-badge hook-badge-pending px-2 py-0.5 text-xs font-medium rounded-md inline-flex items-center gap-1 flex-shrink-0">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            Pending
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="dark:text-gray-400 text-gray-600 text-xs">Has placeholders</span>
                        <span class="hook-badge hook-badge-dynamic px-2 py-0.5 text-xs font-medium rounded-md inline-flex items-center gap-1 flex-shrink-0">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/>
                            </svg>
                            Dynamic
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</div>

<script>
// Search functionality
document.getElementById('hook-search').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const hookItems = document.querySelectorAll('.hook-item');
    const categories = document.querySelectorAll('[data-category]');
    
    hookItems.forEach(item => {
        const hookName = item.dataset.hook.toLowerCase();
        const content = item.textContent.toLowerCase();
        
        if (searchTerm === '' || hookName.includes(searchTerm) || content.includes(searchTerm)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
    
    // Hide empty categories
    categories.forEach(category => {
        const visibleHooks = category.querySelectorAll('.hook-item:not([style*="display: none"])');
        category.style.display = visibleHooks.length > 0 ? '' : 'none';
    });
});

// Copy hook name on click
document.querySelectorAll('.hook-item h3').forEach(el => {
    el.style.cursor = 'pointer';
    el.title = 'Click to copy hook name';
    
    el.addEventListener('click', function() {
        const hookName = this.textContent;
        navigator.clipboard.writeText(hookName).then(() => {
            showToast(`Copied "${hookName}" to clipboard`, 'success');
        });
    });
});
</script>


<?php
// Get the buffered content
$page_content = ob_get_clean();

// Include the admin layout
require_once __DIR__ . '/includes/admin-layout.php';
?>
<?php
/**
 * Admin Layout Template
 * Modern admin interface with collapsible sidebar and top bar
 * 
 * @package Isotone
 */

// Include the Icon API and helper functions
require_once dirname(dirname(__DIR__)) . '/iso-includes/icon-functions.php';

// Preload admin sidebar and header icons for better performance
iso_preload_icons([
    // Sidebar menu icons
    ['name' => 'home', 'style' => 'outline'],
    ['name' => 'document-text', 'style' => 'outline'],
    ['name' => 'document-duplicate', 'style' => 'outline'],
    ['name' => 'film', 'style' => 'outline'],
    ['name' => 'user-group', 'style' => 'outline'],
    ['name' => 'puzzle-piece', 'style' => 'outline'],
    ['name' => 'swatch', 'style' => 'outline'],
    ['name' => 'shield-check', 'style' => 'outline'],
    ['name' => 'cog-6-tooth', 'style' => 'outline'],
    ['name' => 'wrench', 'style' => 'outline'],
    ['name' => 'code', 'style' => 'outline'],
    // Header action icons
    ['name' => 'plus', 'style' => 'outline'],
    ['name' => 'eye', 'style' => 'outline'],
    ['name' => 'magnifying-glass', 'style' => 'outline'],
    ['name' => 'bell', 'style' => 'outline'],
    ['name' => 'sparkles', 'style' => 'outline'],
    ['name' => 'moon', 'style' => 'outline'],
    ['name' => 'sun', 'style' => 'outline'],
    ['name' => 'chevron-down', 'style' => 'outline'],
    ['name' => 'chevron-left', 'style' => 'outline'],
    ['name' => 'chevron-right', 'style' => 'outline'],
    ['name' => 'bars-3', 'style' => 'outline'],
    // Fallback icons
    ['name' => 'question-mark-circle', 'style' => 'outline']
]);

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$current_url = $_SERVER['REQUEST_URI'];

// Function to check if a menu item or its children are active
function is_menu_active($menu_item, $current_url, $current_page) {
    // Parse current URL to get the script name
    $current_parts = parse_url($current_url);
    $current_path = $current_parts['path'] ?? '';
    
    // Parse menu URL
    $menu_parts = parse_url($menu_item['url']);
    $menu_path = $menu_parts['path'] ?? '';
    
    // Special handling for dashboard (root admin URL)
    if ($menu_path === '/isotone/iso-admin/' || $menu_path === '/isotone/iso-admin/index.php') {
        // Only match if we're exactly on the dashboard
        if ($current_path === '/isotone/iso-admin/' || 
            $current_path === '/isotone/iso-admin/index.php' ||
            $current_path === '/isotone/iso-admin/dashboard.php') {
            return true;
        }
    } else {
        // For other menu items, check if the URL matches
        if ($current_path === $menu_path) {
            return true;
        }
    }
    
    // Check submenu items
    if (!empty($menu_item['submenu'])) {
        foreach ($menu_item['submenu'] as $subitem) {
            $sub_parts = parse_url($subitem['url']);
            $sub_path = $sub_parts['path'] ?? '';
            
            // Check if current path matches submenu path (considering query strings)
            if ($current_path === $sub_path) {
                return true;
            }
            
            // Also check with query string for special cases
            if ($current_url === $subitem['url']) {
                return true;
            }
        }
    }
    
    return false;
}

// Function to check if a specific URL is active
function is_url_active($url, $current_url) {
    // Parse URLs for more accurate comparison
    $current_parts = parse_url($current_url);
    $menu_parts = parse_url($url);
    
    $current_path = $current_parts['path'] ?? '';
    $menu_path = $menu_parts['path'] ?? '';
    
    // First check exact match including query strings
    if ($current_url === $url) {
        return true;
    }
    
    // Then check path match
    if ($current_path === $menu_path) {
        // If the menu URL has query params, check if they match
        if (isset($menu_parts['query'])) {
            return isset($current_parts['query']) && $current_parts['query'] === $menu_parts['query'];
        }
        return true;
    }
    
    return false;
}

// Menu structure with submenus
$admin_menu = [
    'dashboard' => [
        'title' => 'Dashboard',
        'icon' => 'home',
        'url' => '/isotone/iso-admin/',
        'submenu' => []
    ],
    'posts' => [
        'title' => 'Posts',
        'icon' => 'document-text',
        'url' => '/isotone/iso-admin/posts.php',
        'submenu' => [
            ['title' => 'All Posts', 'url' => '/isotone/iso-admin/posts.php'],
            ['title' => 'Add New', 'url' => '/isotone/iso-admin/post-edit.php?action=new'],
            ['title' => 'Categories', 'url' => '/isotone/iso-admin/categories.php'],
            ['title' => 'Tags', 'url' => '/isotone/iso-admin/tags.php']
        ]
    ],
    'pages' => [
        'title' => 'Pages',
        'icon' => 'document-duplicate',
        'url' => '/isotone/iso-admin/pages.php',
        'submenu' => [
            ['title' => 'All Pages', 'url' => '/isotone/iso-admin/pages.php'],
            ['title' => 'Add New', 'url' => '/isotone/iso-admin/page-edit.php?action=new'],
            ['title' => 'Templates', 'url' => '/isotone/iso-admin/templates.php']
        ]
    ],
    'media' => [
        'title' => 'Media',
        'icon' => 'film',
        'url' => '/isotone/iso-admin/media.php',
        'submenu' => [
            ['title' => 'Library', 'url' => '/isotone/iso-admin/media.php'],
            ['title' => 'Add New', 'url' => '/isotone/iso-admin/media-upload.php'],
            ['title' => 'Bulk Optimize', 'url' => '/isotone/iso-admin/media-optimize.php']
        ]
    ],
    'users' => [
        'title' => 'Users',
        'icon' => 'user-group',
        'url' => '/isotone/iso-admin/users.php',
        'submenu' => [
            ['title' => 'All Users', 'url' => '/isotone/iso-admin/users.php'],
            ['title' => 'Add New', 'url' => '/isotone/iso-admin/user-edit.php?action=new'],
            ['title' => 'Profile', 'url' => '/isotone/iso-admin/profile.php'],
            ['title' => 'Roles', 'url' => '/isotone/iso-admin/roles.php']
        ]
    ],
    'plugins' => [
        'title' => 'Plugins',
        'icon' => 'puzzle-piece',
        'url' => '/isotone/iso-admin/plugins.php',
        'submenu' => [
            ['title' => 'Installed', 'url' => '/isotone/iso-admin/plugins.php'],
            ['title' => 'Add New', 'url' => '/isotone/iso-admin/plugin-install.php'],
            ['title' => 'Plugin Editor', 'url' => '/isotone/iso-admin/plugin-editor.php']
        ]
    ],
    'appearance' => [
        'title' => 'Appearance',
        'icon' => 'swatch',
        'url' => '/isotone/iso-admin/themes.php',
        'submenu' => [
            ['title' => 'Themes', 'url' => '/isotone/iso-admin/themes.php'],
            ['title' => 'Customize', 'url' => '/isotone/iso-admin/customize.php'],
            ['title' => 'Widgets', 'url' => '/isotone/iso-admin/widgets.php'],
            ['title' => 'Menus', 'url' => '/isotone/iso-admin/menus.php'],
            ['title' => 'Theme Editor', 'url' => '/isotone/iso-admin/theme-editor.php']
        ]
    ],
    'security' => [
        'title' => 'Security',
        'icon' => 'shield-check',
        'url' => '/isotone/iso-admin/security-login.php',
        'submenu' => [
            ['title' => 'Login Security', 'url' => '/isotone/iso-admin/security-login.php']
        ]
    ],
    'settings' => [
        'title' => 'Settings',
        'icon' => 'cog-6-tooth',
        'url' => '/isotone/iso-admin/settings.php',
        'submenu' => [
            ['title' => 'General', 'url' => '/isotone/iso-admin/settings.php'],
            ['title' => 'Reading', 'url' => '/isotone/iso-admin/settings-reading.php'],
            ['title' => 'Writing', 'url' => '/isotone/iso-admin/settings-writing.php'],
            ['title' => 'Media', 'url' => '/isotone/iso-admin/settings-media.php'],
            ['title' => 'Permalinks', 'url' => '/isotone/iso-admin/settings-permalinks.php'],
            ['title' => 'Privacy', 'url' => '/isotone/iso-admin/settings-privacy.php']
        ]
    ],
    'tools' => [
        'title' => 'Tools',
        'icon' => 'wrench',
        'url' => '/isotone/iso-admin/tools.php',
        'submenu' => [
            ['title' => 'Import', 'url' => '/isotone/iso-admin/import.php'],
            ['title' => 'Export', 'url' => '/isotone/iso-admin/export.php'],
            ['title' => 'Site Health', 'url' => '/isotone/iso-admin/site-health.php'],
            ['title' => 'Backup', 'url' => '/isotone/iso-admin/backup.php']
        ]
    ],
    'development' => [
        'title' => 'Development',
        'icon' => 'code',
        'url' => '/isotone/iso-admin/development.php',
        'submenu' => [
            ['title' => 'Automation', 'url' => '/isotone/iso-admin/automation.php'],
            ['title' => 'Hooks Explorer', 'url' => '/isotone/iso-admin/hooks-explorer.php'],
            ['title' => 'API Testing', 'url' => '/isotone/iso-admin/api-test.php'],
            ['title' => 'Debug Console', 'url' => '/isotone/iso-admin/debug.php']
        ]
    ]
];

// Helper function to render icons using the Icon API
function render_icon($icon_name, $class = 'w-6 h-6') {
    // Use the new Icon API with inline SVG for immediate rendering
    $icon = iso_get_icon($icon_name, 'outline', ['class' => $class], false); // false = inline SVG, not lazy
    
    // Fallback to question mark if icon is empty
    if (empty($icon)) {
        $icon = iso_get_icon('question-mark-circle', 'outline', ['class' => $class], false);
    }
    
    return $icon;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin'; ?> - Isotone</title>
    
    <!-- Critical CSS (inline for FOUC prevention - minimal subset) -->
    <style>
        [x-cloak] { display: none !important; }
        .admin-loading { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: #111827; z-index: 9999; display: flex; align-items: center; justify-content: center; }
        .spinner { width: 40px; height: 40px; border: 3px solid #374151; border-top-color: #00d9ff; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
    
    <!-- Initialize theme before page loads -->
    <script>
        // Set dark mode class on HTML element before page renders
        if (localStorage.getItem('darkMode') === 'false') {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark');
        }
    </script>
    
    <!-- Preload admin CSS -->
    <link rel="preload" href="/isotone/iso-admin/css/admin.css" as="style">
    
    <!-- Admin CSS -->
    <link rel="stylesheet" href="/isotone/iso-admin/css/admin.css">
    
    <!-- Tailwind CSS -->
    <?php 
    // Prefer minified version if available, otherwise use regular version
    $tailwindMinPath = __DIR__ . '/../css/tailwind.min.css';
    $tailwindPath = __DIR__ . '/../css/tailwind.css';
    
    if (file_exists($tailwindMinPath)): ?>
        <!-- Using minified Tailwind CSS -->
        <link rel="stylesheet" href="/isotone/iso-admin/css/tailwind.min.css">
    <?php elseif (file_exists($tailwindPath)): ?>
        <!-- Using regular Tailwind CSS -->
        <link rel="stylesheet" href="/isotone/iso-admin/css/tailwind.css">
    <?php else: ?>
        <!-- ERROR: Tailwind CSS not found! Run: composer tailwind:build -->
        <style>
            body::before {
                content: "⚠️ Tailwind CSS not found! Run: composer tailwind:build";
                display: block;
                background: #ef4444;
                color: white;
                padding: 1rem;
                text-align: center;
                font-family: monospace;
            }
        </style>
    <?php endif; ?>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Failsafe loader removal -->
    <script>
        // Remove loader after max 2 seconds even if Alpine fails
        window.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const loader = document.getElementById('admin-loading');
                if (loader && !loader.classList.contains('fade-out')) {
                    loader.classList.add('fade-out');
                    setTimeout(() => loader.remove(), 300);
                }
            }, 2000);
        });
        
        // Also remove loader when Alpine is ready
        document.addEventListener('alpine:init', function() {
            const loader = document.getElementById('admin-loading');
            if (loader) {
                loader.classList.add('fade-out');
                setTimeout(() => loader.remove(), 300);
            }
        });
    </script>
    
    
    <!-- Favicon -->
    <link rel="icon" href="/isotone/favicon.ico">
</head>
<body class="dark:bg-gray-900 bg-gray-50 dark:text-gray-100 text-gray-900" 
      x-data="adminApp()" 
      x-init="init()"
      @keydown.cmd.k.prevent="showSearch = true"
      @keydown.ctrl.k.prevent="showSearch = true"
      @keydown.escape="showSearch = false">

    <!-- Loading Overlay -->
    <div id="admin-loading" class="admin-loading">
        <div class="spinner"></div>
    </div>

    <!-- Top Admin Bar -->
    <header class="fixed top-0 left-0 right-0 h-16 dark:bg-gray-800 bg-white border-b dark:border-gray-700 border-gray-200 shadow-md z-50">
        <div class="h-full px-4 flex items-center justify-between">
            <!-- Left side -->
            <div class="flex items-center space-x-4">
                <!-- Menu toggle -->
                <button @click="sidebarOpen = !sidebarOpen" class="p-2 hover:bg-gray-700 rounded-lg transition-colors lg:hidden">
                    <?php echo render_icon('bars-3', 'w-6 h-6'); ?>
                </button>
                
                <!-- Logo -->
                <div class="flex items-center space-x-2">
                    <!-- Isotone SVG Logo - changes based on theme -->
                    <img :src="darkMode ? '/isotone/iso-includes/assets/logo.svg' : '/isotone/iso-includes/assets/logo-light.svg'" 
                         alt="Isotone" 
                         class="w-8 h-8 isotone-logo-pulse">
                    <h2 class="text-xl font-bold isotone-text-shimmer">
                        Isotone
                    </h2>
                </div>
                
                <!-- Quick Actions -->
                <div class="hidden md:flex items-center space-x-2">
                    <button class="px-3 py-1.5 bg-cyan-600 hover:bg-cyan-700 text-white rounded text-sm transition-colors flex items-center">
                        <?php echo render_icon('plus', 'w-4 h-4 mr-1'); ?>
                        New Post
                    </button>
                    <a href="/isotone" target="_blank" 
                       class="w-8 h-8 dark:bg-gray-700 bg-gray-200 dark:hover:bg-gray-600 hover:bg-gray-300 dark:text-white text-gray-900 rounded text-sm transition-colors inline-flex items-center justify-center"
                       title="View Site">
                        <?php echo render_icon('eye', 'w-4 h-4'); ?>
                    </a>
                </div>
            </div>
            
            <!-- Right side -->
            <div class="flex items-center space-x-4">
                <!-- View Site (Mobile & Desktop visible) -->
                <a href="/isotone" target="_blank" 
                   class="w-9 h-9 dark:hover:bg-gray-700 hover:bg-gray-200 rounded-lg transition-colors md:hidden flex items-center justify-center"
                   title="View Site">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </a>
                
                <!-- Search -->
                <button @click="showSearch = true" class="p-2 dark:hover:bg-gray-700 hover:bg-gray-200 rounded-lg transition-colors">
                    <?php echo render_icon('magnifying-glass', 'w-5 h-5'); ?>
                </button>
                
                <!-- Notifications -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="relative p-2 dark:hover:bg-gray-700 hover:bg-gray-200 rounded-lg transition-colors">
                        <?php echo render_icon('bell', 'w-5 h-5'); ?>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                    
                    <!-- Dropdown -->
                    <div x-show="open" 
                         @click.away="open = false"
                         x-cloak
                         class="absolute right-0 mt-2 w-80 dark:bg-gray-800 bg-white rounded-lg shadow-xl dark:border-gray-700 border-gray-200">
                        <div class="p-4">
                            <h3 class="text-sm font-semibold dark:text-gray-400 text-gray-600 mb-2">Notifications</h3>
                            <div class="space-y-2">
                                <div class="p-2 dark:hover:bg-gray-700 hover:bg-gray-100 rounded">
                                    <p class="text-sm dark:text-white text-gray-900">New user registration</p>
                                    <p class="text-xs dark:text-gray-500 text-gray-500">5 minutes ago</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Toni AI Assistant -->
                <button @click="toniOpen = !toniOpen" 
                        class="relative p-2 dark:hover:bg-gray-700 hover:bg-gray-200 rounded-lg transition-all group"
                        title="Toni AI Assistant">
                    <?php echo render_icon('sparkles', 'w-5 h-5 transition-colors group-hover:text-cyan-400'); ?>
                    <span class="absolute top-1 right-1 flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-cyan-500"></span>
                    </span>
                </button>
                
                <!-- Dark/Light Mode Toggle -->
                <button @click="darkMode = !darkMode" class="p-2 dark:hover:bg-gray-700 hover:bg-gray-200 rounded-lg transition-colors relative w-9 h-9 flex items-center justify-center">
                    <span x-show="!darkMode" x-transition class="absolute">
                        <?php echo render_icon('moon', 'w-5 h-5'); ?>
                    </span>
                    <span x-show="darkMode" x-transition class="absolute">
                        <?php echo render_icon('sun', 'w-5 h-5'); ?>
                    </span>
                </button>
                
                <!-- User Menu -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center space-x-2 p-2 dark:hover:bg-gray-700 hover:bg-gray-200 rounded-lg transition-colors">
                        <div class="w-8 h-8 bg-gradient-to-r from-cyan-400 to-green-400 rounded-full flex items-center justify-center text-gray-900 font-semibold">
                            <?php echo strtoupper(substr($current_user ?? 'A', 0, 1)); ?>
                        </div>
                        <?php echo render_icon('chevron-down', 'w-4 h-4'); ?>
                    </button>
                    
                    <!-- Dropdown -->
                    <div x-show="open" 
                         @click.away="open = false"
                         x-cloak
                         class="absolute right-0 mt-2 w-48 dark:bg-gray-800 bg-white rounded-lg shadow-xl dark:border-gray-700 border-gray-200">
                        <div class="py-1">
                            <a href="/isotone/iso-admin/profile.php" class="block px-4 py-2 text-sm dark:hover:bg-gray-700 hover:bg-gray-100 dark:text-white text-gray-900">Profile</a>
                            <a href="/isotone/iso-admin/settings.php" class="block px-4 py-2 text-sm dark:hover:bg-gray-700 hover:bg-gray-100 dark:text-white text-gray-900">Settings</a>
                            <hr class="my-1 dark:border-gray-700 border-gray-200">
                            <a href="/isotone/iso-admin/logout.php" class="block px-4 py-2 text-sm dark:hover:bg-gray-700 hover:bg-gray-100 dark:text-white text-gray-900">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Layout Container -->
    <div class="flex" style="padding-top: 64px; min-height: 100vh;">
        <!-- Sidebar -->
        <aside :class="sidebarCollapsed ? 'w-16' : 'w-64'" 
               class="fixed left-0 top-16 bottom-0 dark:bg-gray-800 bg-gray-100 dark:border-gray-700 border-gray-200 border-r overflow-y-auto overflow-x-hidden sidebar-transition z-30"
               x-data="{ collapsed: false }">
            
            <!-- Collapse Toggle -->
            <div class="relative">
                <button @click="sidebarCollapsed = !sidebarCollapsed" 
                        class="absolute top-4 w-6 h-6 dark:bg-gray-700 bg-gray-400 dark:hover:bg-gray-600 hover:bg-gray-500 text-white rounded-full flex items-center justify-center transition-all z-40"
                        :class="sidebarCollapsed ? 'left-5' : 'right-4'">
                    <span class="transition-transform" :class="sidebarCollapsed && 'rotate-180'">
                        <?php echo render_icon('chevron-left', 'w-4 h-4'); ?>
                    </span>
                </button>
            </div>
            
            <!-- Menu -->
            <nav class="mt-16">
                <?php foreach ($admin_menu as $key => $item): 
                    $is_active = is_menu_active($item, $current_url, $current_page);
                    $has_submenu = !empty($item['submenu']);
                ?>
                <div x-data="{ open: <?php echo $is_active && $has_submenu ? 'true' : 'false'; ?> }">
                    <!-- Main Menu Item -->
                    <a href="<?php echo $item['url']; ?>" 
                       @click="<?php echo $has_submenu ? 'open = !open; $event.preventDefault()' : ''; ?>"
                       class="flex items-center py-2 dark:hover:bg-gray-700 hover:bg-gray-200 transition-colors relative border-l-4 pl-4 pr-4 <?php echo $is_active ? 'dark:bg-gray-700 bg-gray-200 text-cyan-400 border-cyan-400' : 'border-transparent'; ?>"
                       :class="sidebarCollapsed ? 'justify-center px-0' : ''">
                        <span class="flex-shrink-0 <?php echo $is_active ? 'text-cyan-400' : ''; ?>"><?php echo render_icon($item['icon']); ?></span>
                        <span x-show="!sidebarCollapsed" class="ml-3" x-cloak><?php echo $item['title']; ?></span>
                        <?php if ($has_submenu): ?>
                        <span x-show="!sidebarCollapsed" class="ml-auto transition-transform" :class="open && 'rotate-90'">
                            <?php echo render_icon('chevron-right', 'w-4 h-4'); ?>
                        </span>
                        <?php endif; ?>
                    </a>
                    
                    <!-- Submenu -->
                    <?php if ($has_submenu): ?>
                    <div x-show="open && !sidebarCollapsed" x-cloak class="dark:bg-gray-900 bg-gray-50">
                        <?php foreach ($item['submenu'] as $subitem): 
                            $sub_active = is_url_active($subitem['url'], $current_url);
                        ?>
                        <a href="<?php echo $subitem['url']; ?>" 
                           class="block pl-12 pr-4 py-1.5 text-sm dark:hover:bg-gray-700 hover:bg-gray-200 transition-colors relative <?php echo $sub_active ? 'dark:bg-gray-700 bg-gray-200 text-cyan-400' : 'dark:text-gray-300 text-gray-700'; ?>">
                            <?php if ($sub_active): ?>
                            <span class="absolute left-7 top-1/2 transform -translate-y-1/2 w-1.5 h-1.5 bg-cyan-400 rounded-full"></span>
                            <?php endif; ?>
                            <?php echo $subitem['title']; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main :class="sidebarCollapsed ? 'ml-16' : 'ml-64'" class="flex-1 sidebar-transition flex flex-col">
            <!-- Breadcrumbs -->
            <div class="px-8 py-4 border-b dark:border-gray-700 border-gray-200">
                <nav class="flex items-center space-x-2 text-sm">
                    <a href="/isotone/iso-admin/" class="dark:text-gray-400 text-gray-600 dark:hover:text-gray-200 hover:text-gray-900">Dashboard</a>
                    <?php if (isset($breadcrumbs)): ?>
                        <?php foreach ($breadcrumbs as $crumb): ?>
                        <span class="text-gray-600">/</span>
                        <?php if (isset($crumb['url'])): ?>
                        <a href="<?php echo $crumb['url']; ?>" class="text-gray-400 hover:text-gray-200"><?php echo $crumb['title']; ?></a>
                        <?php else: ?>
                        <span class="text-gray-200"><?php echo $crumb['title']; ?></span>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </nav>
            </div>
            
            <!-- Page Content (grows to fill space) -->
            <div class="flex-1 p-8">
                <?php echo $page_content ?? ''; ?>
            </div>
            
            <!-- Footer -->
            <div class="mt-auto border-t dark:border-gray-700 border-gray-200 py-2 px-8 dark:bg-gray-900 bg-gray-50">
                <div class="flex items-center justify-between">
                    <!-- Left: Copyright and Version -->
                    <div class="flex items-center space-x-3">
                        <span class="text-sm dark:text-gray-500 text-gray-600">
                            © <?php echo date('Y'); ?> Isotone
                        </span>
                        <?php 
                        // Get version from config.php comment
                        $version = '0.1.2-alpha'; // Default
                        $config_path = dirname(dirname(__DIR__)) . '/config.php';
                        if (file_exists($config_path)) {
                            $config_file = file_get_contents($config_path);
                            if (preg_match('/@version\s+(.+)/', $config_file, $matches)) {
                                $version = trim($matches[1]);
                            }
                        }
                        ?>
                        <span class="text-sm dark:text-gray-500 text-gray-600">
                            v<?php echo $version; ?>
                        </span>
                    </div>
                    
                    <!-- Right: Performance Metrics -->
                    <div class="flex items-center space-x-4 text-sm">
                        <?php
                        $memory_usage = round(memory_get_usage() / 1024 / 1024, 2);
                        $memory_peak = round(memory_get_peak_usage() / 1024 / 1024, 2);
                        $page_time = defined('ISOTONE_START') ? round((microtime(true) - ISOTONE_START) * 1000, 0) : 0;
                        $memory_percent = defined('MEMORY_LIMIT') ? 
                            round(($memory_usage / (intval(MEMORY_LIMIT) ?: 128)) * 100, 0) : 0;
                        
                        // Database queries count (if available)
                        $db_queries = 0; // This would need to be tracked by RedBeanPHP
                        
                        // Determine speed icon and color for page load time
                        // TEST VALUES: Under 50ms = fast, Over 50ms = slow
                        // PRODUCTION VALUES: Under 200ms = fast, Over 200ms = slow
                        $speed_threshold = 200; // Change to 200 for production
                        $is_fast = $page_time <= $speed_threshold;
                        ?>
                        
                        <!-- Page Load Time -->
                        <div class="flex items-center space-x-2">
                            <?php if ($is_fast): ?>
                            <!-- Lightning bolt for fast load -->
                            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <?php else: ?>
                            <!-- Snail icon for slow load -->
                            <svg class="w-5 h-5 text-orange-400" fill="currentColor" viewBox="0 0 32 32">
                                <path d="m28.875 10.1455a4.078 4.078 0 0 0 -2.4616-.9845 11.1233 11.1233 0 0 1 1.9528-3.6581.75.75 0 1 0 -1.1123-1.0058 13.417 13.417 0 0 0 -2.44 4.7158c-.0192.0043-.0387.0084-.0578.013a5.9737 5.9737 0 0 1 -.1117-2.682.75.75 0 1 0 -1.4482-.3886 7.743 7.743 0 0 0 .1874 3.7592 8.407 8.407 0 0 0 -1.84 2.6319c1.2172 3.3262.2151 6.7139-2.7575 9.32-1.1279 1.9942-3.6938 3.2276-6.6308 3.2276a8.3546 8.3546 0 0 1 -6.2015-2.345c-.5862.1851-1.1462.3583-1.6515.511a2.8251 2.8251 0 0 0 -2.0523 2.74 1.7522 1.7522 0 0 0 1.75 1.75h15.06c5.5063 0 7.8711-5.166 8.2475-10 .229-2.8623 1.0806-3.6406 1.6445-4.1553a1.8687 1.8687 0 0 0 .798-1.4347 2.746 2.746 0 0 0 -.875-2.0145z"></path>
                                <path d="m20.1191 13.0215c-.78-3.1232-4.3364-5.2783-7.3051-5.1914a8.2492 8.2492 0 0 0 -6.6778 3.7793 8.5592 8.5592 0 0 0 -.5468 8.042 6.4508 6.4508 0 0 0 4.5888 3.7786 5.3028 5.3028 0 0 0 4.4253-.9817 5.8793 5.8793 0 0 0 1.6765-2.3057 5.2523 5.2523 0 0 0 -.8452-4.73 2.7761 2.7761 0 0 0 -2.6529-1.1416 2.1829 2.1829 0 0 0 -2.0766 2.4228c.0737.9786.6533 1.8057 1.2646 1.8057a.75.75 0 0 1 0 1.5c-1.436 0-2.6225-1.373-2.7607-3.1924a3.65 3.65 0 0 1 3.3476-4.0185 4.2155 4.2155 0 0 1 4.0562 1.6953c2.3586 2.5813.96 6.4135.932 6.47a8.1556 8.1556 0 0 0 2.5741-7.9324z"></path>
                            </svg>
                            <?php endif; ?>
                            <span class="<?php echo $is_fast ? 'dark:text-green-400 text-green-600' : 'dark:text-orange-400 text-orange-600'; ?>">
                                <?php echo $page_time; ?>ms
                            </span>
                        </div>
                        
                        <!-- Memory Usage -->
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                            </svg>
                            <span class="dark:text-gray-400 text-gray-600"><?php echo $memory_usage; ?>MB</span>
                            <?php if ($memory_percent > 0): ?>
                            <span class="dark:text-gray-600 text-gray-500">(<?php echo $memory_percent; ?>%)</span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Peak Memory -->
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            <span class="dark:text-gray-400 text-gray-600">Peak: <?php echo $memory_peak; ?>MB</span>
                        </div>
                        
                        <!-- Page Size -->
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="dark:text-gray-400 text-gray-600" id="page-size-metric">Calculating...</span>
                        </div>
                        
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Search Modal -->
    <div x-show="showSearch" 
         x-cloak
         @click.away="showSearch = false"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-start justify-center pt-20">
        <div class="dark:bg-gray-800 bg-white rounded-lg shadow-xl w-full max-w-2xl">
            <div class="p-4">
                <input type="text" 
                       placeholder="Search everything... (Press ESC to close)"
                       class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500"
                       x-ref="searchInput"
                       @click.stop
                       x-init="$watch('showSearch', value => value && $nextTick(() => $refs.searchInput.focus()))">
            </div>
        </div>
    </div>

    <!-- Toni AI Assistant Sidebar -->
    <div x-show="toniOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed right-0 top-16 bottom-0 w-96 dark:bg-gray-800 bg-white dark:border-gray-700 border-gray-200 border-l shadow-xl z-40 flex flex-col">
        
        <!-- Toni Header -->
        <div class="p-4 dark:bg-gray-900 bg-gray-50 border-b dark:border-gray-700 border-gray-200 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-cyan-400 to-green-400 rounded-full flex items-center justify-center">
                    <span class="text-gray-900 font-bold text-lg">T</span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-semibold dark:text-white text-gray-900">Toni</h3>
                    </div>
                    <p class="text-xs dark:text-gray-400 text-gray-600">AI Assistant</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button @click="clearToniChat()" 
                        class="p-1.5 dark:hover:bg-gray-700 hover:bg-gray-200 rounded transition-colors"
                        title="Clear conversation">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
                <button @click="toniOpen = false" 
                        class="p-1.5 dark:hover:bg-gray-700 hover:bg-gray-200 rounded transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Chat Messages -->
        <div id="toni-chat" class="flex-1 overflow-y-auto p-4 space-y-4">
            <!-- Welcome message if no messages -->
            <template x-if="toniMessages.length === 0">
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gradient-to-r from-cyan-400 to-green-400 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-gray-900 font-bold text-2xl">T</span>
                    </div>
                    <h4 class="font-semibold dark:text-white text-gray-900 mb-2">Hi! I'm Toni</h4>
                    <p class="text-sm dark:text-gray-400 text-gray-600 mb-4">Your AI assistant for Isotone. I can help you with:</p>
                    <ul class="text-sm dark:text-gray-400 text-gray-600 space-y-1">
                        <li>• Content creation and management</li>
                        <li>• Site configuration</li>
                        <li>• SEO optimization</li>
                        <li>• Troubleshooting issues</li>
                        <li>• Best practices and tips</li>
                    </ul>
                </div>
            </template>
            
            <!-- Chat messages -->
            <template x-for="(message, index) in toniMessages" :key="index">
                <div :class="message.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="message.role === 'user' 
                         ? 'max-w-xs bg-cyan-600 text-white rounded-lg px-4 py-2' 
                         : 'max-w-xs dark:bg-gray-700 bg-gray-100 dark:text-white text-gray-900 rounded-lg px-4 py-2'">
                        <p class="text-sm whitespace-pre-wrap" x-text="message.content"></p>
                    </div>
                </div>
            </template>
            
            <!-- Loading indicator -->
            <template x-if="toniLoading">
                <div class="flex justify-start">
                    <div class="max-w-xs dark:bg-gray-700 bg-gray-100 rounded-lg px-4 py-2">
                        <div class="flex space-x-2">
                            <div class="w-2 h-2 bg-cyan-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                            <div class="w-2 h-2 bg-cyan-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                            <div class="w-2 h-2 bg-cyan-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        
        <!-- Input Area -->
        <div class="p-4 border-t dark:border-gray-700 border-gray-200">
            <form @submit.prevent="sendToToni()" class="flex space-x-2">
                <button type="button"
                        @click="captureScreenshot()"
                        :disabled="toniLoading"
                        class="p-2 dark:bg-gray-700 bg-gray-200 dark:hover:bg-gray-600 hover:bg-gray-300 rounded-lg transition-colors disabled:opacity-50"
                        title="Capture screenshot">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </button>
                <input type="text" 
                       x-model="toniMessage"
                       :disabled="toniLoading"
                       placeholder="Ask Toni anything..."
                       class="flex-1 px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500 disabled:opacity-50">
                <button type="submit" 
                        :disabled="toniLoading || !toniMessage.trim()"
                        class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="fixed bottom-4 right-4 z-50 space-y-2" id="toast-container"></div>

    <!-- Admin JavaScript -->
    <script>
        function adminApp() {
            return {
                sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
                darkMode: localStorage.getItem('darkMode') !== 'false',
                showSearch: false,
                sidebarOpen: false,
                toniOpen: false,
                toniMessage: '',
                toniMessages: [],
                toniLoading: false,
                toniScreenshotCaptured: false,
                capturedScreenshot: null,
                
                init() {
                    // Remove loading overlay once Alpine is initialized
                    const loader = document.getElementById('admin-loading');
                    if (loader) {
                        setTimeout(() => {
                            loader.classList.add('fade-out');
                            setTimeout(() => loader.remove(), 300);
                        }, 100);
                    }
                    
                    // Watch sidebar state
                    this.$watch('sidebarCollapsed', value => {
                        localStorage.setItem('sidebarCollapsed', value);
                    });
                    
                    // Watch dark mode - only toggle 'dark' class on html element
                    this.$watch('darkMode', value => {
                        localStorage.setItem('darkMode', value);
                        if (value) {
                            document.documentElement.classList.add('dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                        }
                    });
                    
                    // Set initial dark mode state (already done in head script, but ensure sync)
                    if (this.darkMode) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                    
                    // Calculate and display page size
                    this.calculatePageSize();
                    
                    // Load Toni conversation history when opened and send page context
                    this.$watch('toniOpen', async value => {
                        if (value) {
                            if (this.toniMessages.length === 0) {
                                this.loadToniHistory();
                            }
                            // Page context removed - using screenshot feature instead
                        }
                    });
                },
                
                // Toni AI methods
                async sendToToni() {
                    if (!this.toniMessage.trim()) return;
                    
                    const message = this.toniMessage;
                    this.toniMessage = '';
                    
                    // Add user message to chat
                    this.toniMessages.push({
                        role: 'user',
                        content: message,
                        created_at: new Date().toISOString()
                    });
                    
                    this.toniLoading = true;
                    
                    try {
                        const formData = new FormData();
                        formData.append('action', 'send');
                        formData.append('message', message);
                        
                        const response = await fetch('/isotone/iso-admin/api/toni.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        // Show debug info in console if available
                        if (data.debug) {
                            console.group('🤖 Toni AI Debug Info');
                            console.log('Provider:', data.debug.provider);
                            console.log('API Key Present:', data.debug.openai_api_key_present || data.debug.anthropic_api_key_present || false);
                            console.log('Model:', data.debug.model || data.debug.selected_model || data.debug.openai_model || data.debug.anthropic_model || 'N/A');
                            console.log('Max Tokens:', data.debug.max_tokens);
                            console.log('Context Messages:', data.debug.context_count);
                            
                            if (data.debug.error) {
                                console.error('❌ Error:', data.debug.error);
                            }
                            
                            if (data.debug.api_call_duration) {
                                console.log('API Call Duration:', data.debug.api_call_duration + 's');
                                console.log('HTTP Code:', data.debug.http_code);
                                console.log('Tokens Used:', data.debug.tokens_used);
                            }
                            
                            if (data.debug.curl_error) {
                                console.error('CURL Error:', data.debug.curl_error);
                            }
                            
                            if (data.debug.api_error) {
                                console.error('API Error:', data.debug.api_error);
                                console.error('API Response:', data.debug.api_response);
                            }
                            
                            if (data.debug.fallback_used) {
                                console.warn('⚠️ Fallback response used (no AI)');
                            } else if (data.debug.ai_response_received) {
                                console.log('✅ AI response received successfully');
                            }
                            
                            if (data.debug.api_response_keys) {
                                console.warn('🔑 API Response Keys:', data.debug.api_response_keys);
                            }
                            if (data.debug.raw_response_sample) {
                                console.warn('📝 Raw Response Sample:', data.debug.raw_response_sample);
                            }
                            if (data.debug.full_response) {
                                console.warn('📄 Full Response:', data.debug.full_response);
                            }
                            console.log('Full Debug Object:', data.debug);
                            console.groupEnd();
                        }
                        
                        if (data.success) {
                            this.toniMessages.push({
                                role: 'assistant',
                                content: data.response,
                                created_at: new Date().toISOString()
                            });
                            
                            // Scroll to bottom
                            this.$nextTick(() => {
                                const chatContainer = document.getElementById('toni-chat');
                                if (chatContainer) {
                                    chatContainer.scrollTop = chatContainer.scrollHeight;
                                }
                            });
                        } else {
                            showToast('Failed to send message to Toni', 'error');
                        }
                    } catch (error) {
                        console.error('Toni error:', error);
                        showToast('Error communicating with Toni', 'error');
                    } finally {
                        this.toniLoading = false;
                    }
                },
                
                async loadToniHistory() {
                    try {
                        const response = await fetch('/isotone/iso-admin/api/toni.php?action=history');
                        const data = await response.json();
                        
                        if (data.success) {
                            this.toniMessages = data.conversation || [];
                        }
                    } catch (error) {
                        console.error('Failed to load Toni history:', error);
                    }
                },
                
                async captureScreenshot() {
                    try {
                        // Check if html2canvas is loaded
                        if (typeof html2canvas === 'undefined') {
                            // Load html2canvas dynamically
                            await this.loadHtml2Canvas();
                        }
                        
                        // Show loading indicator
                        this.toniMessages.push({
                            id: 'screenshot-loading-' + Date.now(),
                            role: 'system',
                            content: '📸 Capturing screenshot...',
                            timestamp: new Date().toISOString()
                        });
                        
                        // Capture the main content area
                        const mainContent = document.querySelector('main') || document.body;
                        
                        const canvas = await html2canvas(mainContent, {
                            scale: 0.5, // Reduce size for faster processing
                            logging: false,
                            useCORS: true,
                            allowTaint: true,
                            backgroundColor: null,
                            width: mainContent.scrollWidth,
                            height: Math.min(mainContent.scrollHeight, 2000) // Limit height
                        });
                        
                        // Convert to base64
                        const base64Image = canvas.toDataURL('image/jpeg', 0.7);
                        this.capturedScreenshot = base64Image;
                        this.toniScreenshotCaptured = true;
                        
                        // Remove loading message
                        this.toniMessages = this.toniMessages.filter(msg => !msg.id.startsWith('screenshot-loading'));
                        
                        // Add success message
                        this.toniMessages.push({
                            id: 'screenshot-success-' + Date.now(),
                            role: 'system',
                            content: '✅ Screenshot captured! Sending to Toni...',
                            timestamp: new Date().toISOString()
                        });
                        
                        // Send screenshot to Toni
                        await this.sendScreenshotToToni(base64Image);
                        
                        return base64Image;
                    } catch (error) {
                        console.error('Failed to capture screenshot:', error);
                        this.toniMessages.push({
                            id: 'screenshot-error-' + Date.now(),
                            role: 'system',
                            content: '❌ Failed to capture screenshot',
                            timestamp: new Date().toISOString()
                        });
                        return null;
                    }
                },
                
                async loadHtml2Canvas() {
                    return new Promise((resolve, reject) => {
                        const script = document.createElement('script');
                        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
                        script.onload = resolve;
                        script.onerror = reject;
                        document.head.appendChild(script);
                    });
                },
                
                async sendScreenshotToToni(base64Image) {
                    try {
                        console.group('📸 Toni Screenshot Capture');
                        console.log('Image Data URL prefix:', base64Image.substring(0, 50));
                        console.log('Image size (bytes):', base64Image.length);
                        console.log('Image format:', base64Image.match(/data:image\/([^;]+)/)?.[1] || 'unknown');
                        
                        const formData = new FormData();
                        formData.append('action', 'send');
                        formData.append('message', '[Screenshot] Please analyze this screenshot');
                        formData.append('screenshot', base64Image);
                        formData.append('is_visual', 'true');
                        
                        console.log('📤 Sending to API:', {
                            action: 'send',
                            message: '[Screenshot] Please analyze this screenshot',
                            has_screenshot: true,
                            is_visual: true,
                            image_length: base64Image.length
                        });
                        
                        const response = await fetch('/isotone/iso-admin/api/toni.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        console.log('📡 Response status:', response.status, response.statusText);
                        
                        const data = await response.json();
                        console.log('📥 API Response:', data);
                        
                        if (data.debug) {
                            console.group('🔍 Debug Info from Server');
                            console.table(data.debug);
                            console.groupEnd();
                        }
                        
                        if (data.success) {
                            console.log('✅ Screenshot processed successfully');
                            console.log('AI Response:', data.response);
                            
                            // Remove success message
                            this.toniMessages = this.toniMessages.filter(msg => !msg.id.startsWith('screenshot-success'));
                            
                            // Add Toni's response about the screenshot
                            this.toniMessages.push({
                                id: data.message_id,
                                role: 'assistant',
                                content: data.response,
                                timestamp: new Date().toISOString()
                            });
                        } else {
                            console.error('❌ Processing failed:', data.error || 'Unknown error');
                            if (data.debug_error) {
                                console.error('Debug error:', data.debug_error);
                            }
                        }
                        
                        console.groupEnd();
                    } catch (error) {
                        console.error('❌ Failed to send screenshot:', error);
                        console.error('Error details:', error.message, error.stack);
                        console.groupEnd();
                        
                        this.toniMessages.push({
                            id: 'error-' + Date.now(),
                            role: 'system',
                            content: '❌ Failed to send screenshot: ' + error.message,
                            timestamp: new Date().toISOString()
                        });
                    }
                },
                
                // Page context feature removed - screenshot feature provides better visual context
                
                calculatePageSize() {
                    // Wait for DOM to be fully loaded
                    window.addEventListener('load', () => {
                        let totalSize = 0;
                        
                        // Calculate HTML size
                        const htmlSize = new Blob([document.documentElement.outerHTML]).size;
                        totalSize += htmlSize;
                        
                        // Calculate CSS size
                        const stylesheets = document.styleSheets;
                        let cssSize = 0;
                        for (let i = 0; i < stylesheets.length; i++) {
                            try {
                                const stylesheet = stylesheets[i];
                                if (stylesheet.href) {
                                    // External stylesheet - estimate size
                                    cssSize += 50000; // Average CSS file estimate
                                } else if (stylesheet.cssRules) {
                                    // Inline styles - calculate actual size
                                    let cssText = '';
                                    for (let j = 0; j < stylesheet.cssRules.length; j++) {
                                        cssText += stylesheet.cssRules[j].cssText;
                                    }
                                    cssSize += new Blob([cssText]).size;
                                }
                            } catch (e) {
                                // Cross-origin CSS - estimate
                                cssSize += 30000;
                            }
                        }
                        totalSize += cssSize;
                        
                        // Calculate JavaScript size
                        const scripts = document.getElementsByTagName('script');
                        let jsSize = 0;
                        for (let script of scripts) {
                            if (script.src) {
                                // External script - estimate based on common libraries
                                if (script.src.includes('alpine')) jsSize += 25000;
                                else if (script.src.includes('chart')) jsSize += 80000;
                                else if (script.src.includes('tailwind')) jsSize += 15000;
                                else jsSize += 20000; // Default estimate
                            } else {
                                // Inline script
                                jsSize += new Blob([script.textContent]).size;
                            }
                        }
                        totalSize += jsSize;
                        
                        // Calculate image sizes (approximation)
                        const images = document.getElementsByTagName('img');
                        let imageSize = 0;
                        for (let img of images) {
                            // Estimate based on image dimensions or use default
                            const width = img.naturalWidth || img.width || 100;
                            const height = img.naturalHeight || img.height || 100;
                            imageSize += Math.max(width * height * 0.5, 2000); // Rough estimate
                        }
                        totalSize += imageSize;
                        
                        // Format size
                        const formatSize = (bytes) => {
                            if (bytes < 1024) return bytes + ' B';
                            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
                            return (bytes / 1024 / 1024).toFixed(2) + ' MB';
                        };
                        
                        // Update the display
                        const element = document.getElementById('page-size-metric');
                        if (element) {
                            element.textContent = formatSize(totalSize);
                            
                            // Add color coding for size
                            element.classList.remove('text-green-400', 'text-yellow-400', 'text-red-400');
                            if (totalSize < 500000) { // < 500KB = good
                                element.classList.add('text-green-400');
                            } else if (totalSize < 1000000) { // < 1MB = ok
                                element.classList.add('text-yellow-400');
                            } else { // > 1MB = heavy
                                element.classList.add('text-red-400');
                            }
                        }
                    });
                },
                
                async clearToniChat() {
                    if (!confirm('Clear your conversation with Toni?')) return;
                    
                    try {
                        const response = await fetch('/isotone/iso-admin/api/toni.php?action=clear');
                        const data = await response.json();
                        
                        if (data.success) {
                            this.toniMessages = [];
                            showToast('Conversation cleared', 'success');
                        }
                    } catch (error) {
                        console.error('Failed to clear Toni chat:', error);
                        showToast('Failed to clear conversation', 'error');
                    }
                }
            }
        }
        
        // Toast notification system
        function showToast(message, type = 'info') {
            const colors = {
                success: 'bg-green-800 border border-green-600',
                error: 'bg-red-800 border border-red-600',
                warning: 'bg-yellow-800 border border-yellow-600',
                info: 'bg-blue-800 border border-blue-600'
            };
            
            const icons = {
                success: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                error: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                warning: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
                info: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
            };
            
            const toast = document.createElement('div');
            toast.className = `${colors[type]} text-white px-6 py-3 rounded-lg shadow-2xl toast-enter flex items-center`;
            toast.innerHTML = `
                <span class="mr-3 flex-shrink-0">${icons[type]}</span>
                <span>${message}</span>
            `;
            
            document.getElementById('toast-container').appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }, 5000); // Increased from 3 to 5 seconds
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Cmd/Ctrl + N for new post
            if ((e.metaKey || e.ctrlKey) && e.key === 'n') {
                e.preventDefault();
                window.location.href = '/isotone/iso-admin/post-edit.php?action=new';
            }
        });
    </script>
    
    <!-- Alpine.js - Load after adminApp is defined -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
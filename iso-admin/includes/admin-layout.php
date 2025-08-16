<?php
/**
 * Admin Layout Template
 * Modern admin interface with collapsible sidebar and top bar
 * 
 * @package Isotone
 */

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$current_url = $_SERVER['REQUEST_URI'];

// Function to check if a menu item or its children are active
function is_menu_active($menu_item, $current_url, $current_page) {
    // Check main URL
    if (strpos($current_url, $menu_item['url']) !== false) {
        return true;
    }
    
    // Check submenu items
    if (!empty($menu_item['submenu'])) {
        foreach ($menu_item['submenu'] as $subitem) {
            if (strpos($current_url, $subitem['url']) !== false) {
                return true;
            }
        }
    }
    
    return false;
}

// Function to check if a specific URL is active
function is_url_active($url, $current_url) {
    // Exact match or current URL contains the menu URL
    return $current_url === $url || strpos($current_url, $url) !== false;
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
        'icon' => 'collection',
        'url' => '/isotone/iso-admin/pages.php',
        'submenu' => [
            ['title' => 'All Pages', 'url' => '/isotone/iso-admin/pages.php'],
            ['title' => 'Add New', 'url' => '/isotone/iso-admin/page-edit.php?action=new'],
            ['title' => 'Templates', 'url' => '/isotone/iso-admin/templates.php']
        ]
    ],
    'media' => [
        'title' => 'Media',
        'icon' => 'photograph',
        'url' => '/isotone/iso-admin/media.php',
        'submenu' => [
            ['title' => 'Library', 'url' => '/isotone/iso-admin/media.php'],
            ['title' => 'Add New', 'url' => '/isotone/iso-admin/media-upload.php'],
            ['title' => 'Bulk Optimize', 'url' => '/isotone/iso-admin/media-optimize.php']
        ]
    ],
    'users' => [
        'title' => 'Users',
        'icon' => 'users',
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
        'icon' => 'puzzle',
        'url' => '/isotone/iso-admin/plugins.php',
        'submenu' => [
            ['title' => 'Installed', 'url' => '/isotone/iso-admin/plugins.php'],
            ['title' => 'Add New', 'url' => '/isotone/iso-admin/plugin-install.php'],
            ['title' => 'Plugin Editor', 'url' => '/isotone/iso-admin/plugin-editor.php']
        ]
    ],
    'appearance' => [
        'title' => 'Appearance',
        'icon' => 'color-swatch',
        'url' => '/isotone/iso-admin/themes.php',
        'submenu' => [
            ['title' => 'Themes', 'url' => '/isotone/iso-admin/themes.php'],
            ['title' => 'Customize', 'url' => '/isotone/iso-admin/customize.php'],
            ['title' => 'Widgets', 'url' => '/isotone/iso-admin/widgets.php'],
            ['title' => 'Menus', 'url' => '/isotone/iso-admin/menus.php'],
            ['title' => 'Theme Editor', 'url' => '/isotone/iso-admin/theme-editor.php']
        ]
    ],
    'settings' => [
        'title' => 'Settings',
        'icon' => 'cog',
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
    'documentation' => [
        'title' => 'Documentation',
        'icon' => 'book-open',
        'url' => '/isotone/iso-admin/documentation.php',
        'submenu' => [
            ['title' => 'Getting Started', 'url' => '/isotone/iso-admin/documentation.php?section=getting-started'],
            ['title' => 'Configuration', 'url' => '/isotone/iso-admin/documentation.php?section=configuration'],
            ['title' => 'API Reference', 'url' => '/isotone/iso-admin/documentation.php?section=api'],
            ['title' => 'Developer Guide', 'url' => '/isotone/iso-admin/documentation.php?section=developers']
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
            ['title' => 'Debug Console', 'url' => '/isotone/iso-admin/debug.php'],
            ['title' => 'Developer Docs', 'url' => '/isotone/iso-admin/documentation.php?section=developers']
        ]
    ]
];

// Icon mapping for Heroicons
$icon_map = [
    'home' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />',
    'document-text' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
    'collection' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />',
    'photograph' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />',
    'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />',
    'puzzle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />',
    'color-swatch' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />',
    'cog' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />',
    'wrench' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />',
    'book-open' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />',
    'code' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />',
    'link' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />'
];

function render_icon($icon_name, $class = 'w-6 h-6') {
    global $icon_map;
    $path = $icon_map[$icon_name] ?? '';
    return '<svg class="' . $class . '" fill="none" stroke="currentColor" viewBox="0 0 24 24">' . $path . '</svg>';
}
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
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
    
    <!-- Preload Tailwind to reduce FOUC -->
    <link rel="preload" href="https://cdn.tailwindcss.com" as="script">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/isotone/iso-admin/css/tailwind-config.js"></script>
    
    <!-- Alpine.js for interactivity -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Chart.js for dashboard graphs -->
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
<body class="min-h-full dark:bg-gray-900 bg-gray-50 dark:text-gray-100 text-gray-900" 
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
    <header class="fixed top-0 left-0 right-0 h-16 dark:bg-gray-800 bg-white dark:border-gray-700 border-gray-200 border-b z-40">
        <div class="h-full px-4 flex items-center justify-between">
            <!-- Left side -->
            <div class="flex items-center space-x-4">
                <!-- Menu toggle -->
                <button @click="sidebarOpen = !sidebarOpen" class="p-2 hover:bg-gray-700 rounded-lg transition-colors lg:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
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
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Post
                    </button>
                    <button class="px-3 py-1.5 dark:bg-gray-700 bg-gray-200 dark:hover:bg-gray-600 hover:bg-gray-300 dark:text-white text-gray-900 rounded text-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Right side -->
            <div class="flex items-center space-x-4">
                <!-- Search -->
                <button @click="showSearch = true" class="p-2 dark:hover:bg-gray-700 hover:bg-gray-200 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
                
                <!-- Notifications -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="relative p-2 dark:hover:bg-gray-700 hover:bg-gray-200 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
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
                
                <!-- Dark/Light Mode Toggle -->
                <button @click="darkMode = !darkMode" class="p-2 dark:hover:bg-gray-700 hover:bg-gray-200 rounded-lg transition-colors relative w-9 h-9 flex items-center justify-center">
                    <svg x-show="!darkMode" x-transition class="w-5 h-5 absolute" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <svg x-show="darkMode" x-transition class="w-5 h-5 absolute" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </button>
                
                <!-- User Menu -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center space-x-2 p-2 dark:hover:bg-gray-700 hover:bg-gray-200 rounded-lg transition-colors">
                        <div class="w-8 h-8 bg-gradient-to-r from-cyan-400 to-green-400 rounded-full flex items-center justify-center text-gray-900 font-semibold">
                            <?php echo strtoupper(substr($current_user ?? 'A', 0, 1)); ?>
                        </div>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
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
    <div class="flex h-full pt-16">
        <!-- Sidebar -->
        <aside :class="sidebarCollapsed ? 'w-16' : 'w-64'" 
               class="fixed left-0 top-16 bottom-0 dark:bg-gray-800 bg-gray-100 dark:border-gray-700 border-gray-200 border-r overflow-y-auto overflow-x-hidden sidebar-transition z-30"
               x-data="{ collapsed: false }">
            
            <!-- Collapse Toggle -->
            <div class="relative">
                <button @click="sidebarCollapsed = !sidebarCollapsed" 
                        class="absolute top-4 w-6 h-6 dark:bg-gray-700 bg-gray-400 dark:hover:bg-gray-600 hover:bg-gray-500 text-white rounded-full flex items-center justify-center transition-all z-40"
                        :class="sidebarCollapsed ? 'left-5' : 'right-4'">
                    <svg class="w-4 h-4 transition-transform" :class="sidebarCollapsed && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
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
                       class="flex items-center py-2 dark:hover:bg-gray-700 hover:bg-gray-200 transition-colors relative <?php echo $is_active ? 'dark:bg-gray-700 bg-gray-200 text-cyan-400 border-l-4 border-cyan-400' : ''; ?>"
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'px-4'">
                        <span class="flex-shrink-0 <?php echo $is_active ? 'text-cyan-400' : ''; ?>"><?php echo render_icon($item['icon']); ?></span>
                        <span x-show="!sidebarCollapsed" class="ml-3 <?php echo $is_active ? 'font-semibold' : ''; ?>" x-cloak><?php echo $item['title']; ?></span>
                        <?php if ($has_submenu): ?>
                        <svg x-show="!sidebarCollapsed" class="w-4 h-4 ml-auto transition-transform" :class="open && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <?php endif; ?>
                    </a>
                    
                    <!-- Submenu -->
                    <?php if ($has_submenu): ?>
                    <div x-show="open && !sidebarCollapsed" x-cloak class="dark:bg-gray-900 bg-gray-50">
                        <?php foreach ($item['submenu'] as $subitem): 
                            $sub_active = is_url_active($subitem['url'], $current_url);
                        ?>
                        <a href="<?php echo $subitem['url']; ?>" 
                           class="block pl-12 pr-4 py-1.5 text-sm dark:hover:bg-gray-700 hover:bg-gray-200 transition-colors relative <?php echo $sub_active ? 'dark:bg-gray-700 bg-gray-200 text-cyan-400 font-semibold' : 'dark:text-gray-300 text-gray-700'; ?>">
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
        <main :class="sidebarCollapsed ? 'ml-16' : 'ml-64'" class="flex-1 sidebar-transition">
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
            
            <!-- Page Content -->
            <div class="p-8">
                <?php echo $page_content ?? ''; ?>
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
</body>
</html>
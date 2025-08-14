<?php
/**
 * Admin Layout Template
 * Modern admin interface with collapsible sidebar and top bar
 * 
 * @package Isotone
 */

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF'], '.php');

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
    'themes' => [
        'title' => 'Themes',
        'icon' => 'color-swatch',
        'url' => '/isotone/iso-admin/themes.php',
        'submenu' => [
            ['title' => 'Installed', 'url' => '/isotone/iso-admin/themes.php'],
            ['title' => 'Add New', 'url' => '/isotone/iso-admin/theme-install.php'],
            ['title' => 'Customize', 'url' => '/isotone/iso-admin/customize.php'],
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
    'wrench' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />'
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
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/isotone/iso-admin/css/tailwind-config.js"></script>
    
    <!-- Alpine.js for interactivity -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Chart.js for dashboard graphs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom Admin Styles -->
    <style>
        [x-cloak] { display: none !important; }
        
        /* Sidebar transitions */
        .sidebar-collapsed {
            width: 4rem;
        }
        
        .sidebar-expanded {
            width: 16rem;
        }
        
        /* Smooth transitions */
        .sidebar-transition {
            transition: all 0.3s ease;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }
        
        ::-webkit-scrollbar-thumb {
            background: rgba(0, 217, 255, 0.3);
            border-radius: 3px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 217, 255, 0.5);
        }
        
        /* Toast animations */
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .toast-enter {
            animation: slideIn 0.3s ease-out;
        }
        
        /* Pulse Animation for Logo - matching frontend */
        @keyframes pulse {
            0%, 100% { 
                transform: scale(1);
                filter: drop-shadow(0 0 20px rgba(0, 217, 255, 0.5));
            }
            50% { 
                transform: scale(1.05);
                filter: drop-shadow(0 0 30px rgba(0, 255, 136, 0.6));
            }
        }
        
        .isotone-logo-pulse {
            animation: pulse 2s ease-in-out infinite;
        }
        
        /* Shimmer Effect for Text - matching frontend */
        @keyframes shimmer {
            0%, 100% { 
                background-position: 0% 50%; 
            }
            50% { 
                background-position: 100% 50%; 
            }
        }
        
        .isotone-text-shimmer {
            background: linear-gradient(135deg, #FFFFFF 0%, #00D9FF 50%, #00FF88 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: shimmer 4s ease-in-out infinite;
            background-size: 200% 200%;
        }
    </style>
    
    <!-- Favicon -->
    <link rel="icon" href="/isotone/favicon.ico">
</head>
<body class="h-full bg-gray-900 text-gray-100" 
      x-data="adminApp()" 
      x-init="init()"
      @keydown.cmd.k.prevent="showSearch = true"
      @keydown.ctrl.k.prevent="showSearch = true"
      @keydown.escape="showSearch = false">

    <!-- Top Admin Bar -->
    <header class="fixed top-0 left-0 right-0 h-16 bg-gray-800 border-b border-gray-700 z-40">
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
                    <!-- Isotone SVG Logo -->
                    <img src="/isotone/iso-includes/assets/logo.svg" alt="Isotone" class="w-8 h-8 isotone-logo-pulse">
                    <h2 class="text-xl font-bold isotone-text-shimmer">
                        Isotone
                    </h2>
                </div>
                
                <!-- Quick Actions -->
                <div class="hidden md:flex items-center space-x-2">
                    <button class="px-3 py-1.5 bg-cyan-600 hover:bg-cyan-700 rounded text-sm transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Post
                    </button>
                    <button class="px-3 py-1.5 bg-gray-700 hover:bg-gray-600 rounded text-sm transition-colors">
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
                <button @click="showSearch = true" class="p-2 hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
                
                <!-- Notifications -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="relative p-2 hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                    
                    <!-- Dropdown -->
                    <div x-show="open" 
                         @click.away="open = false"
                         x-cloak
                         class="absolute right-0 mt-2 w-80 bg-gray-800 rounded-lg shadow-xl border border-gray-700">
                        <div class="p-4">
                            <h3 class="text-sm font-semibold text-gray-400 mb-2">Notifications</h3>
                            <div class="space-y-2">
                                <div class="p-2 hover:bg-gray-700 rounded">
                                    <p class="text-sm">New user registration</p>
                                    <p class="text-xs text-gray-500">5 minutes ago</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Dark/Light Mode Toggle -->
                <button @click="darkMode = !darkMode" class="p-2 hover:bg-gray-700 rounded-lg transition-colors">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </button>
                
                <!-- User Menu -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center space-x-2 p-2 hover:bg-gray-700 rounded-lg transition-colors">
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
                         class="absolute right-0 mt-2 w-48 bg-gray-800 rounded-lg shadow-xl border border-gray-700">
                        <div class="py-1">
                            <a href="/isotone/iso-admin/profile.php" class="block px-4 py-2 text-sm hover:bg-gray-700">Profile</a>
                            <a href="/isotone/iso-admin/settings.php" class="block px-4 py-2 text-sm hover:bg-gray-700">Settings</a>
                            <hr class="my-1 border-gray-700">
                            <a href="/isotone/iso-admin/logout.php" class="block px-4 py-2 text-sm hover:bg-gray-700">Logout</a>
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
               class="fixed left-0 top-16 bottom-0 bg-gray-800 border-r border-gray-700 overflow-y-auto overflow-x-hidden sidebar-transition z-30"
               x-data="{ collapsed: false }">
            
            <!-- Collapse Toggle -->
            <div class="relative">
                <button @click="sidebarCollapsed = !sidebarCollapsed" 
                        class="absolute top-4 w-6 h-6 bg-gray-700 hover:bg-gray-600 rounded-full flex items-center justify-center transition-all z-40"
                        :class="sidebarCollapsed ? 'left-5' : 'right-4'">
                    <svg class="w-4 h-4 transition-transform" :class="sidebarCollapsed && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            </div>
            
            <!-- Menu -->
            <nav class="mt-16">
                <?php foreach ($admin_menu as $key => $item): ?>
                <div x-data="{ open: false }">
                    <!-- Main Menu Item -->
                    <a href="<?php echo $item['url']; ?>" 
                       @click="<?php echo !empty($item['submenu']) ? 'open = !open; $event.preventDefault()' : ''; ?>"
                       class="flex items-center py-2 hover:bg-gray-700 transition-colors <?php echo $current_page === $key ? 'bg-gray-700 text-cyan-400 border-l-4 border-cyan-400' : ''; ?>"
                       :class="sidebarCollapsed ? 'justify-center px-0' : 'px-4'">
                        <span class="flex-shrink-0"><?php echo render_icon($item['icon']); ?></span>
                        <span x-show="!sidebarCollapsed" class="ml-3" x-cloak><?php echo $item['title']; ?></span>
                        <?php if (!empty($item['submenu'])): ?>
                        <svg x-show="!sidebarCollapsed" class="w-4 h-4 ml-auto transition-transform" :class="open && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <?php endif; ?>
                    </a>
                    
                    <!-- Submenu -->
                    <?php if (!empty($item['submenu'])): ?>
                    <div x-show="open && !sidebarCollapsed" x-cloak class="bg-gray-900">
                        <?php foreach ($item['submenu'] as $subitem): ?>
                        <a href="<?php echo $subitem['url']; ?>" 
                           class="block pl-12 pr-4 py-1.5 text-sm hover:bg-gray-700 transition-colors">
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
            <div class="px-8 py-4 border-b border-gray-700">
                <nav class="flex items-center space-x-2 text-sm">
                    <a href="/isotone/iso-admin/" class="text-gray-400 hover:text-gray-200">Dashboard</a>
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
        <div class="bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl">
            <div class="p-4">
                <input type="text" 
                       placeholder="Search everything... (Press ESC to close)"
                       class="w-full px-4 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:outline-none focus:border-cyan-500"
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
                
                init() {
                    // Watch sidebar state
                    this.$watch('sidebarCollapsed', value => {
                        localStorage.setItem('sidebarCollapsed', value);
                    });
                    
                    // Watch dark mode
                    this.$watch('darkMode', value => {
                        localStorage.setItem('darkMode', value);
                        document.documentElement.classList.toggle('dark', value);
                    });
                    
                    // Set initial dark mode
                    document.documentElement.classList.toggle('dark', this.darkMode);
                }
            }
        }
        
        // Toast notification system
        function showToast(message, type = 'info') {
            const colors = {
                success: 'bg-green-600',
                error: 'bg-red-600',
                warning: 'bg-yellow-600',
                info: 'bg-blue-600'
            };
            
            const toast = document.createElement('div');
            toast.className = `${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg toast-enter`;
            toast.textContent = message;
            
            document.getElementById('toast-container').appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
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
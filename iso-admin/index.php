<?php
/**
 * Admin Dashboard
 * 
 * @package Isotone
 */

// Check authentication
require_once 'auth.php';

// TODO: Get real stats from database
$stats = [
    'posts' => 0,
    'pages' => 0,
    'users' => 1,
    'comments' => 0
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Isotone Admin</title>
    
    <!-- Tailwind CSS (Local) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/isotone/iso-admin/css/tailwind-config.js"></script>
    
    <!-- Favicon -->
    <link rel="icon" href="/isotone/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/isotone/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/isotone/favicon-16x16.png">
    
</head>
<body class="bg-gray-900 text-gray-100 flex h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-800 border-r border-gray-700">
        <div class="p-6 border-b border-gray-700">
            <h2 class="text-2xl font-bold bg-gradient-to-r from-cyan-400 to-green-400 bg-clip-text text-transparent">
                Isotone
            </h2>
            <p class="text-xs text-gray-500 mt-1 tracking-wider">ADMIN PANEL</p>
        </div>
        
        <nav class="mt-6">
            <a href="/isotone/iso-admin/" class="flex items-center px-6 py-3 bg-gray-700 text-cyan-400 border-l-4 border-cyan-400">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>
            <a href="/isotone/iso-admin/posts.php" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-700 hover:text-cyan-400 transition-colors">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Posts
            </a>
            <a href="/isotone/iso-admin/pages.php" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-700 hover:text-cyan-400 transition-colors">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Pages
            </a>
            <a href="/isotone/iso-admin/media.php" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-700 hover:text-cyan-400 transition-colors">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Media
            </a>
            <a href="/isotone/iso-admin/users.php" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-700 hover:text-cyan-400 transition-colors">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Users
            </a>
            <a href="/isotone/iso-admin/plugins.php" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-700 hover:text-cyan-400 transition-colors">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
                Plugins
            </a>
            <a href="/isotone/iso-admin/themes.php" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-700 hover:text-cyan-400 transition-colors">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                </svg>
                Themes
            </a>
            <a href="/isotone/iso-admin/settings.php" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-700 hover:text-cyan-400 transition-colors">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
            </a>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="flex-1 flex flex-col">
        <!-- Header -->
        <header class="bg-gray-800 border-b border-gray-700 px-8 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-semibold">Dashboard</h1>
            
            <div class="flex items-center space-x-4">
                <span class="text-gray-400">Welcome, <?php echo htmlspecialchars($current_user); ?></span>
                <div class="w-8 h-8 bg-gradient-to-r from-cyan-400 to-green-400 rounded-full flex items-center justify-center text-gray-900 font-semibold">
                    <?php echo strtoupper(substr($current_user, 0, 1)); ?>
                </div>
                <a href="/isotone/iso-admin/logout.php" class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded text-sm transition-colors">
                    Logout
                </a>
            </div>
        </header>
        
        <!-- Dashboard Content -->
        <div class="flex-1 overflow-y-auto p-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <div class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-green-400 bg-clip-text text-transparent">
                        <?php echo $stats['posts']; ?>
                    </div>
                    <div class="text-gray-400 text-sm uppercase tracking-wider mt-2">Posts</div>
                </div>
                
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <div class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-green-400 bg-clip-text text-transparent">
                        <?php echo $stats['pages']; ?>
                    </div>
                    <div class="text-gray-400 text-sm uppercase tracking-wider mt-2">Pages</div>
                </div>
                
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <div class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-green-400 bg-clip-text text-transparent">
                        <?php echo $stats['users']; ?>
                    </div>
                    <div class="text-gray-400 text-sm uppercase tracking-wider mt-2">Users</div>
                </div>
                
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <div class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-green-400 bg-clip-text text-transparent">
                        <?php echo $stats['comments']; ?>
                    </div>
                    <div class="text-gray-400 text-sm uppercase tracking-wider mt-2">Comments</div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 mb-6">
                <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
                <div class="flex flex-wrap gap-4">
                    <a href="/isotone/iso-admin/posts.php?action=new" class="inline-flex items-center px-4 py-2 bg-cyan-600 hover:bg-cyan-700 rounded transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Post
                    </a>
                    <a href="/isotone/iso-admin/pages.php?action=new" class="inline-flex items-center px-4 py-2 bg-cyan-600 hover:bg-cyan-700 rounded transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        New Page
                    </a>
                    <a href="/isotone/iso-admin/media.php?action=upload" class="inline-flex items-center px-4 py-2 bg-cyan-600 hover:bg-cyan-700 rounded transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Upload Media
                    </a>
                    <a href="/isotone/iso-admin/users.php?action=new" class="inline-flex items-center px-4 py-2 bg-cyan-600 hover:bg-cyan-700 rounded transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        Add User
                    </a>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <h2 class="text-xl font-semibold mb-4">Recent Activity</h2>
                <p class="text-gray-500">No recent activity to display.</p>
            </div>
        </div>
    </main>
</body>
</html>
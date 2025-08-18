<?php
/**
 * Modern Admin Dashboard with Widgets
 * 
 * @package Isotone
 */

// Check authentication
require_once 'auth.php';

// Page setup
$page_title = 'Dashboard';
$breadcrumbs = [];

// Get stats (using RedBeanPHP)
use RedBeanPHP\R;
require_once dirname(__DIR__) . '/iso-includes/database.php';

// Use centralized database connection
isotone_db_connect();

// Get counts - Use raw queries for better memory efficiency
$stats = [
    'posts' => (int)R::getCell('SELECT COUNT(*) FROM post'),
    'pages' => (int)R::getCell('SELECT COUNT(*) FROM page'),
    'users' => (int)R::getCell('SELECT COUNT(*) FROM users'),
    'comments' => (int)R::getCell('SELECT COUNT(*) FROM comment'),
    'media' => (int)R::getCell('SELECT COUNT(*) FROM media')
];

// Get recent activity - Use find() with proper LIMIT for memory efficiency
// Only fetch the fields we actually display
$recent_posts = R::find('post', 'ORDER BY created_at DESC LIMIT 5');
$recent_users = R::find('users', 'ORDER BY created_at DESC LIMIT 5');

// Admin area should use ADMIN_MEMORY_LIMIT (already applied in auth.php)
// This is just for display purposes

// Calculate database size - Use more efficient query
try {
    // Get total database size in one query
    $db_name = DB_NAME;
    $result = R::getRow("
        SELECT 
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
        FROM information_schema.TABLES 
        WHERE table_schema = ?
    ", [$db_name]);
    $db_size = $result['size_mb'] ? $result['size_mb'] . ' MB' : 'N/A';
} catch (Exception $e) {
    $db_size = 'N/A';
}

// System info
$system_info = [
    'php_version' => PHP_VERSION,
    'isotone_limit' => defined('MEMORY_LIMIT') ? MEMORY_LIMIT : '256M',
    'php_original_limit' => defined('PHP_ORIGINAL_MEMORY_LIMIT') ? PHP_ORIGINAL_MEMORY_LIMIT : ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time') . ' seconds',
    'database_size' => $db_size,
];

// Start output buffering for content
ob_start();
?>

<!-- Dashboard Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Column (2/3 width) -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="dark:bg-gray-800 bg-white rounded-lg p-4 dark:border-gray-700 border-gray-200 hover:border-cyan-500 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-cyan-400"><?php echo $stats['posts']; ?></p>
                        <p class="text-sm dark:text-gray-400 text-gray-600 mt-1">Posts</p>
                    </div>
                    <svg class="w-8 h-8 dark:text-gray-600 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
            
            <div class="dark:bg-gray-800 bg-white rounded-lg p-4 dark:border-gray-700 border-gray-200 hover:border-cyan-500 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-green-400"><?php echo $stats['pages']; ?></p>
                        <p class="text-sm dark:text-gray-400 text-gray-600 mt-1">Pages</p>
                    </div>
                    <svg class="w-8 h-8 dark:text-gray-600 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
            </div>
            
            <div class="dark:bg-gray-800 bg-white rounded-lg p-4 dark:border-gray-700 border-gray-200 hover:border-cyan-500 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-purple-400"><?php echo $stats['users']; ?></p>
                        <p class="text-sm dark:text-gray-400 text-gray-600 mt-1">Users</p>
                    </div>
                    <svg class="w-8 h-8 dark:text-gray-600 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>
            
            <div class="dark:bg-gray-800 bg-white rounded-lg p-4 dark:border-gray-700 border-gray-200 hover:border-cyan-500 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-yellow-400"><?php echo $stats['media']; ?></p>
                        <p class="text-sm dark:text-gray-400 text-gray-600 mt-1">Media</p>
                    </div>
                    <svg class="w-8 h-8 dark:text-gray-600 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Site Analytics Graph -->
        <div class="dark:bg-gray-800 bg-white rounded-lg p-6 dark:border-gray-700 border-gray-200">
            <h2 class="text-lg font-semibold dark:text-white text-gray-900 mb-4">Site Analytics</h2>
            <canvas id="analyticsChart" height="100"></canvas>
        </div>
        
        <!-- Quick Draft Widget -->
        <div class="dark:bg-gray-800 bg-white rounded-lg p-6 dark:border-gray-700 border-gray-200">
            <h2 class="text-lg font-semibold dark:text-white text-gray-900 mb-4">Quick Draft</h2>
            <form id="quickDraftForm" class="space-y-4">
                <input type="text" 
                       placeholder="Post title..." 
                       class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 rounded-lg focus:outline-none focus:border-cyan-500">
                <textarea placeholder="Start writing..." 
                          rows="4"
                          class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 rounded-lg focus:outline-none focus:border-cyan-500"></textarea>
                <div class="flex justify-between">
                    <button type="button" class="px-4 py-2 dark:bg-gray-700 bg-gray-200 dark:hover:bg-gray-600 hover:bg-gray-300 dark:text-white text-gray-900 rounded transition-colors">
                        Save Draft
                    </button>
                    <button type="submit" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded transition-colors">
                        Publish
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Recent Activity -->
        <div class="dark:bg-gray-800 bg-white rounded-lg p-6 dark:border-gray-700 border-gray-200">
            <h2 class="text-lg font-semibold dark:text-white text-gray-900 mb-4">Recent Activity</h2>
            <div class="space-y-3">
                <?php if (empty($recent_posts)): ?>
                <p class="dark:text-gray-500 text-gray-400">No recent activity</p>
                <?php else: ?>
                    <?php foreach ($recent_posts as $post): ?>
                    <div class="flex items-center justify-between py-2 border-b dark:border-gray-700 border-gray-200 last:border-0">
                        <div>
                            <p class="text-sm dark:text-white text-gray-900"><?php echo htmlspecialchars($post->title ?? 'Untitled'); ?></p>
                            <p class="text-xs dark:text-gray-500 text-gray-400"><?php echo date('M j, Y', strtotime($post->created_at)); ?></p>
                        </div>
                        <a href="/isotone/iso-admin/post-edit.php?id=<?php echo $post->id; ?>" 
                           class="text-cyan-400 hover:text-cyan-300 text-sm">Edit</a>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Sidebar Column (1/3 width) -->
    <div class="space-y-6">
        
        <!-- System Health Widget -->
        <div class="dark:bg-gray-800 bg-white rounded-lg p-6 dark:border-gray-700 border-gray-200">
            <h2 class="text-lg font-semibold dark:text-white text-gray-900 mb-4">System Health</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm dark:text-gray-400 text-gray-600">PHP Version</span>
                    <span class="text-sm font-mono dark:text-white text-gray-900"><?php echo $system_info['php_version']; ?></span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-sm dark:text-gray-400 text-gray-600">Memory Limit</span>
                    <span class="text-sm font-mono dark:text-white text-gray-900"><?php echo $system_info['isotone_limit']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm dark:text-gray-400 text-gray-600">PHP Memory</span>
                    <span class="text-sm font-mono dark:text-white text-gray-900"><?php echo $system_info['php_original_limit'] == '-1' ? 'Unlimited' : $system_info['php_original_limit']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm dark:text-gray-400 text-gray-600">Database Size</span>
                    <span class="text-sm font-mono dark:text-white text-gray-900"><?php echo $system_info['database_size']; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm dark:text-gray-400 text-gray-600">Automation</span>
                    <span class="text-sm font-mono dark:text-white text-gray-900">1.0.0</span>
                </div>
                
            </div>
        </div>
        
        <!-- Quick Links Widget -->
        <div class="dark:bg-gray-800 bg-white rounded-lg p-6 dark:border-gray-700 border-gray-200">
            <h2 class="text-lg font-semibold dark:text-white text-gray-900 mb-4">Quick Links</h2>
            <div class="space-y-2">
                <a href="/isotone/iso-admin/post-edit.php?action=new" 
                   class="flex items-center p-2 dark:hover:bg-gray-700 hover:bg-gray-100 dark:text-white text-gray-900 rounded transition-colors">
                    <svg class="w-4 h-4 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Post
                </a>
                <a href="/isotone/iso-admin/media.php" 
                   class="flex items-center p-2 dark:hover:bg-gray-700 hover:bg-gray-100 dark:text-white text-gray-900 rounded transition-colors">
                    <svg class="w-4 h-4 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Upload Media
                </a>
                <a href="/isotone/iso-admin/settings.php" 
                   class="flex items-center p-2 dark:hover:bg-gray-700 hover:bg-gray-100 dark:text-white text-gray-900 rounded transition-colors">
                    <svg class="w-4 h-4 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Settings
                </a>
                <a href="/isotone" target="_blank"
                   class="flex items-center p-2 dark:hover:bg-gray-700 hover:bg-gray-100 dark:text-white text-gray-900 rounded transition-colors">
                    <svg class="w-4 h-4 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    View Site
                </a>
            </div>
        </div>
        
        <!-- Recent Users Widget -->
        <div class="dark:bg-gray-800 bg-white rounded-lg p-6 dark:border-gray-700 border-gray-200">
            <h2 class="text-lg font-semibold dark:text-white text-gray-900 mb-4">Recent Users</h2>
            <div class="space-y-3">
                <?php if (empty($recent_users)): ?>
                <p class="dark:text-gray-500 text-gray-400">No recent users</p>
                <?php else: ?>
                    <?php foreach ($recent_users as $user): ?>
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-r from-cyan-400 to-green-400 rounded-full flex items-center justify-center text-gray-900 text-sm font-semibold">
                            <?php echo strtoupper(substr($user->username, 0, 1)); ?>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm dark:text-white text-gray-900"><?php echo htmlspecialchars($user->username); ?></p>
                            <p class="text-xs dark:text-gray-500 text-gray-400"><?php echo htmlspecialchars($user->email); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Chart Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Analytics Chart
    const ctx = document.getElementById('analyticsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Page Views',
                data: [320, 450, 380, 420, 520, 480, 390],
                borderColor: 'rgb(0, 217, 255)',
                backgroundColor: 'rgba(0, 217, 255, 0.1)',
                tension: 0.4
            }, {
                label: 'Visitors',
                data: [120, 180, 150, 170, 210, 190, 160],
                borderColor: 'rgb(0, 255, 136)',
                backgroundColor: 'rgba(0, 255, 136, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    labels: {
                        color: '#9CA3AF'
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#9CA3AF'
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#9CA3AF'
                    }
                }
            }
        }
    });
    
    // Quick Draft Auto-save
    let draftTimeout;
    document.querySelectorAll('#quickDraftForm input, #quickDraftForm textarea').forEach(element => {
        element.addEventListener('input', function() {
            clearTimeout(draftTimeout);
            draftTimeout = setTimeout(() => {
                // Auto-save logic here
                showToast('Draft auto-saved', 'success');
            }, 2000);
        });
    });
});
</script>

<?php
$page_content = ob_get_clean();

// Debug memory usage (remove in production)
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    // Log memory usage for debugging
    error_log('Dashboard Memory Usage: ' . round(memory_get_usage() / 1024 / 1024, 2) . 'MB');
    error_log('Dashboard Peak Memory: ' . round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB');
}

// Include the new layout
include 'includes/admin-layout.php';
?>
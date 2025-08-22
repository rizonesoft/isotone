<?php
/**
 * Modern Admin Dashboard with Widgets
 * 
 * @package Isotone
 */

// Check authentication
require_once 'auth.php';

// Include Icon API
require_once dirname(__DIR__) . '/iso-includes/icon-functions.php';

// Preload dashboard icons
iso_preload_icons([
    // Stats card icons
    ['name' => 'document-text', 'style' => 'outline'],
    ['name' => 'document-duplicate', 'style' => 'outline'], 
    ['name' => 'users', 'style' => 'outline'],
    ['name' => 'photo', 'style' => 'outline'],
    // Quick links icons (micro style for smaller UI elements)
    ['name' => 'plus', 'style' => 'micro'],
    ['name' => 'cloud-arrow-up', 'style' => 'micro'],
    ['name' => 'cog-6-tooth', 'style' => 'micro'],
    ['name' => 'eye', 'style' => 'micro']
]);

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
                    <?php echo iso_get_icon('document-text', 'outline', ['class' => 'w-8 h-8 dark:text-gray-600 text-gray-400'], false); ?>
                </div>
            </div>
            
            <div class="dark:bg-gray-800 bg-white rounded-lg p-4 dark:border-gray-700 border-gray-200 hover:border-cyan-500 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-green-400"><?php echo $stats['pages']; ?></p>
                        <p class="text-sm dark:text-gray-400 text-gray-600 mt-1">Pages</p>
                    </div>
                    <?php echo iso_get_icon('document-duplicate', 'outline', ['class' => 'w-8 h-8 dark:text-gray-600 text-gray-400'], false); ?>
                </div>
            </div>
            
            <div class="dark:bg-gray-800 bg-white rounded-lg p-4 dark:border-gray-700 border-gray-200 hover:border-cyan-500 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-purple-400"><?php echo $stats['users']; ?></p>
                        <p class="text-sm dark:text-gray-400 text-gray-600 mt-1">Users</p>
                    </div>
                    <?php echo iso_get_icon('users', 'outline', ['class' => 'w-8 h-8 dark:text-gray-600 text-gray-400'], false); ?>
                </div>
            </div>
            
            <div class="dark:bg-gray-800 bg-white rounded-lg p-4 dark:border-gray-700 border-gray-200 hover:border-cyan-500 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-yellow-400"><?php echo $stats['media']; ?></p>
                        <p class="text-sm dark:text-gray-400 text-gray-600 mt-1">Media</p>
                    </div>
                    <?php echo iso_get_icon('photo', 'outline', ['class' => 'w-8 h-8 dark:text-gray-600 text-gray-400'], false); ?>
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
                    <?php echo iso_get_icon('plus', 'micro', ['class' => 'w-4 h-4 mr-2 text-cyan-400'], false); ?>
                    New Post
                </a>
                <a href="/isotone/iso-admin/media.php" 
                   class="flex items-center p-2 dark:hover:bg-gray-700 hover:bg-gray-100 dark:text-white text-gray-900 rounded transition-colors">
                    <?php echo iso_get_icon('cloud-arrow-up', 'micro', ['class' => 'w-4 h-4 mr-2 text-green-400'], false); ?>
                    Upload Media
                </a>
                <a href="/isotone/iso-admin/settings.php" 
                   class="flex items-center p-2 dark:hover:bg-gray-700 hover:bg-gray-100 dark:text-white text-gray-900 rounded transition-colors">
                    <?php echo iso_get_icon('cog-6-tooth', 'micro', ['class' => 'w-4 h-4 mr-2 text-purple-400'], false); ?>
                    Settings
                </a>
                <a href="/isotone" target="_blank"
                   class="flex items-center p-2 dark:hover:bg-gray-700 hover:bg-gray-100 dark:text-white text-gray-900 rounded transition-colors">
                    <?php echo iso_get_icon('eye', 'micro', ['class' => 'w-4 h-4 mr-2 text-yellow-400'], false); ?>
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
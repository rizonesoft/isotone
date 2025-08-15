<?php
/**
 * Isotone Automation Dashboard
 * 
 * Web interface for monitoring and managing automation systems
 */

// Check authentication
require_once 'auth.php';

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config.php';

// Initialize RedBeanPHP (optional)
// Database is optional for automation to work
try {
    @\RedBeanPHP\R::setup(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
        DB_USER,
        DB_PASSWORD
    );
} catch (\Exception $e) {
    // Continue without database
}

use Isotone\Automation\Core\AutomationEngine;
use Exception;

$engine = new AutomationEngine();
$engine->initialize();

// Get current status
$status = $engine->getStateManager()->getStatus();
$cacheStats = $engine->getCacheManager()->getStatistics();
$rules = $engine->getRuleEngine()->getAllRules();

// Page title
$page_title = 'Automation Dashboard';

// Include admin layout
include 'includes/admin-layout.php';
?>

<div class="min-h-screen bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white">Automation Dashboard</h1>
            <p class="text-gray-400 mt-2">Monitor and manage Isotone automation systems</p>
        </div>

        <!-- Status Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total Executions</p>
                        <p class="text-2xl font-bold text-white mt-1">
                            <?php echo $status['stats']['total_executions']; ?>
                        </p>
                    </div>
                    <div class="text-cyan-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Success Rate</p>
                        <p class="text-2xl font-bold text-white mt-1">
                            <?php echo $status['stats']['success_rate']; ?>%
                        </p>
                    </div>
                    <div class="text-green-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Avg Execution Time</p>
                        <p class="text-2xl font-bold text-white mt-1">
                            <?php echo $status['stats']['avg_execution_time']; ?>s
                        </p>
                    </div>
                    <div class="text-yellow-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Health -->
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 mb-8">
            <h2 class="text-xl font-semibold text-white mb-4">System Health</h2>
            <div class="space-y-3">
                <?php foreach ($status['health'] as $component => $health): ?>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <?php if ($health['status'] === 'healthy'): ?>
                            <span class="w-3 h-3 bg-green-400 rounded-full mr-3"></span>
                        <?php else: ?>
                            <span class="w-3 h-3 bg-yellow-400 rounded-full mr-3"></span>
                        <?php endif; ?>
                        <span class="text-gray-300 capitalize"><?php echo str_replace('_', ' ', $component); ?></span>
                    </div>
                    <span class="text-gray-400 text-sm"><?php echo $health['message']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 mb-8">
            <h2 class="text-xl font-semibold text-white mb-4">Quick Actions</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <button onclick="executeTask('check:docs')" class="bg-gray-700 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition">
                    Check Docs
                </button>
                <button onclick="executeTask('update:docs')" class="bg-gray-700 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition">
                    Update Docs
                </button>
                <button onclick="executeTask('generate:hooks')" class="bg-gray-700 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition">
                    Generate Hooks
                </button>
                <button onclick="executeTask('sync:ide')" class="bg-gray-700 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition">
                    Sync IDE Rules
                </button>
                <button onclick="executeTask('validate:rules')" class="bg-gray-700 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition">
                    Validate Rules
                </button>
                <button onclick="clearCache()" class="bg-red-600 hover:bg-red-500 text-white py-2 px-4 rounded-lg transition">
                    Clear Cache
                </button>
                <button onclick="refreshStatus()" class="bg-cyan-600 hover:bg-cyan-500 text-white py-2 px-4 rounded-lg transition">
                    Refresh Status
                </button>
                <a href="/isotone/iso-automation/config/rules.yaml" target="_blank" class="bg-purple-600 hover:bg-purple-500 text-white py-2 px-4 rounded-lg transition text-center">
                    View Rules
                </a>
            </div>
        </div>

        <!-- Recent Executions -->
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 mb-8">
            <h2 class="text-xl font-semibold text-white mb-4">Recent Executions</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-gray-400 text-sm">
                            <th class="pb-3">Task</th>
                            <th class="pb-3">Status</th>
                            <th class="pb-3">Started</th>
                            <th class="pb-3">Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($status['recent_executions'] as $exec): ?>
                        <tr class="border-t border-gray-700">
                            <td class="py-3 text-gray-300"><?php echo htmlspecialchars($exec['task']); ?></td>
                            <td class="py-3">
                                <?php if ($exec['status'] === 'completed'): ?>
                                    <span class="text-green-400">✓ Completed</span>
                                <?php elseif ($exec['status'] === 'failed'): ?>
                                    <span class="text-red-400">✗ Failed</span>
                                <?php else: ?>
                                    <span class="text-yellow-400">⟳ Running</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 text-gray-400 text-sm"><?php echo $exec['started_at']; ?></td>
                            <td class="py-3 text-gray-400 text-sm"><?php echo round($exec['execution_time'], 2); ?>s</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cache Statistics -->
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <h2 class="text-xl font-semibold text-white mb-4">Cache Statistics</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-gray-400 text-sm">Files Cached</p>
                    <p class="text-lg font-semibold text-white"><?php echo $cacheStats['total_files_cached']; ?></p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Memory Cache</p>
                    <p class="text-lg font-semibold text-white"><?php echo $cacheStats['memory_cache_size']; ?> items</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Disk Usage</p>
                    <p class="text-lg font-semibold text-white">
                        <?php 
                        $bytes = $cacheStats['disk_cache_size'];
                        $units = ['B', 'KB', 'MB', 'GB'];
                        $i = 0;
                        while ($bytes >= 1024 && $i < count($units) - 1) {
                            $bytes /= 1024;
                            $i++;
                        }
                        echo round($bytes, 2) . ' ' . $units[$i];
                        ?>
                    </p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Last Updated</p>
                    <p class="text-lg font-semibold text-white">
                        <?php echo $cacheStats['newest_cache'] ? date('H:i:s', strtotime($cacheStats['newest_cache'])) : 'Never'; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Toast -->
<div id="toast" class="hidden fixed bottom-4 right-4 bg-gray-800 border border-gray-700 rounded-lg p-4 shadow-lg">
    <div class="flex items-center">
        <div id="toast-icon" class="mr-3"></div>
        <div>
            <p id="toast-title" class="text-white font-semibold"></p>
            <p id="toast-message" class="text-gray-400 text-sm"></p>
        </div>
    </div>
</div>

<script>
function executeTask(task) {
    showToast('info', 'Executing', `Running ${task}...`);
    
    fetch('automation-ajax.php?action=execute', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'task=' + encodeURIComponent(task)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast('success', 'Success', `Task ${task} completed successfully`);
            setTimeout(refreshStatus, 1000);
        } else {
            const errorMsg = data.error || `Task ${task} failed`;
            showToast('error', 'Failed', errorMsg);
            if (data.output) {
                console.error('Task output:', data.output);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Error', 'Failed to execute task. Check console for details.');
    });
}

function clearCache() {
    if (!confirm('Are you sure you want to clear all caches?')) {
        return;
    }
    
    fetch('automation-ajax.php?action=clear_cache', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        showToast('success', 'Cache Cleared', 'All caches have been cleared');
        setTimeout(refreshStatus, 1000);
    })
    .catch(error => {
        showToast('error', 'Error', error.message);
    });
}

function refreshStatus() {
    location.reload();
}

function showToast(type, title, message) {
    const toast = document.getElementById('toast');
    const icon = document.getElementById('toast-icon');
    const titleEl = document.getElementById('toast-title');
    const messageEl = document.getElementById('toast-message');
    
    // Set icon based on type
    if (type === 'success') {
        icon.innerHTML = '<svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
    } else if (type === 'error') {
        icon.innerHTML = '<svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
    } else {
        icon.innerHTML = '<svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
    }
    
    titleEl.textContent = title;
    messageEl.textContent = message;
    
    toast.classList.remove('hidden');
    
    setTimeout(() => {
        toast.classList.add('hidden');
    }, 5000);
}

// Auto-refresh every 30 seconds
setInterval(() => {
    fetch('automation-ajax.php?action=status')
        .then(response => response.json())
        .then(data => {
            // Update UI with new data
            console.log('Status refreshed', data);
        })
        .catch(error => {
            console.error('Status refresh failed:', error);
        });
}, 30000);
</script>
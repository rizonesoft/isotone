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

// Page configuration
$page_title = 'Automation';
$breadcrumbs = [
    ['title' => 'Development'],
    ['title' => 'Automation']
];

// Start output buffering to capture page content
ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold dark:text-white text-gray-900">Automation Dashboard</h1>
    <p class="dark:text-gray-400 text-gray-600 mt-2">Monitor and manage Isotone automation systems</p>
</div>

<!-- Status Overview -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="dark:bg-gray-800 bg-white rounded-lg p-6 dark:border-gray-700 border-gray-200 border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="dark:text-gray-400 text-gray-600 text-sm">Total Executions</p>
                        <p class="text-2xl font-bold dark:text-white text-gray-900 mt-1">
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

            <div class="dark:bg-gray-800 bg-white rounded-lg p-6 dark:border-gray-700 border-gray-200 border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="dark:text-gray-400 text-gray-600 text-sm">Success Rate</p>
                        <p class="text-2xl font-bold dark:text-white text-gray-900 mt-1">
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

            <div class="dark:bg-gray-800 bg-white rounded-lg p-6 dark:border-gray-700 border-gray-200 border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="dark:text-gray-400 text-gray-600 text-sm">Avg Execution Time</p>
                        <p class="text-2xl font-bold dark:text-white text-gray-900 mt-1">
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

        <!-- Quick Actions and System Controls -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Command Execution Card -->
            <div class="lg:col-span-2 bg-gray-800 rounded-lg p-6 border border-gray-700">
                <h2 class="text-xl font-semibold dark:text-white text-gray-900 mb-4">Command Execution</h2>
                
                <!-- Command Dropdown -->
                <div class="relative">
                    <label class="block text-gray-400 text-sm mb-2">Select Command</label>
                    <div class="flex space-x-2">
                        <select id="command-select" class="flex-1 dark:bg-gray-900 bg-white dark:border-gray-700 border-gray-200 border dark:text-white text-gray-900 py-2 px-4 rounded-lg focus:outline-none focus:border-cyan-400">
                            <option value="">-- Select a command --</option>
                            <optgroup label="Documentation">
                                <option value="check:docs">Check Documentation (docs:check)</option>
                                <option value="update:docs">Update Documentation (docs:update)</option>
                                <option value="sync:user-docs">Sync User Documentation (docs:sync)</option>
                            </optgroup>
                            <optgroup label="Hooks">
                                <option value="hooks:scan">Scan & Generate Hooks Documentation</option>
                            </optgroup>
                            <optgroup label="IDE Integration">
                                <option value="sync:ide">Sync IDE Rules (ide:sync)</option>
                            </optgroup>
                            <optgroup label="Validation">
                                <option value="validate:rules">Validate Automation Rules</option>
                            </optgroup>
                            <optgroup label="Cache Management">
                                <option value="cache:clear">Clear All Caches</option>
                                <option value="cache:stats">Show Cache Statistics</option>
                            </optgroup>
                            <optgroup label="System">
                                <option value="status">Show Automation Status</option>
                                <option value="rules:export">Export Rules (YAML format)</option>
                            </optgroup>
                        </select>
                        <button onclick="executeSelectedCommand()" class="bg-green-600 hover:bg-green-500 text-white py-2 px-6 rounded-lg transition flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Execute</span>
                        </button>
                    </div>
                    <p class="dark:text-gray-500 text-gray-600 text-xs mt-2">Select a command from the dropdown and click Execute to run it</p>
                </div>
            </div>
            
            <!-- Automation Stats Card -->
            <div class="dark:bg-gray-800 bg-white rounded-lg p-6 dark:border-gray-700 border-gray-200 border">
                <h2 class="text-xl font-semibold dark:text-white text-gray-900 mb-4">Automation Stats</h2>
                <div class="space-y-4">
                    <!-- Total Commands -->
                    <div class="flex items-center justify-between">
                        <span class="dark:text-gray-400 text-gray-600 text-sm">Total Commands</span>
                        <span class="dark:text-white text-gray-900 font-semibold">11</span>
                    </div>
                    
                    <!-- Success Rate -->
                    <div class="flex items-center justify-between">
                        <span class="dark:text-gray-400 text-gray-600 text-sm">Success Rate</span>
                        <span class="dark:text-white text-gray-900 font-semibold"><?php echo $status['stats']['success_rate']; ?>%</span>
                    </div>
                    
                    <!-- Last Execution -->
                    <div class="flex items-center justify-between">
                        <span class="dark:text-gray-400 text-gray-600 text-sm">Last Run</span>
                        <span class="dark:text-white text-gray-900 font-semibold">
                            <?php 
                            $lastRun = $status['last_execution'] ?? null;
                            if ($lastRun) {
                                $time = strtotime($lastRun);
                                $diff = time() - $time;
                                if ($diff < 60) {
                                    echo $diff . 's ago';
                                } elseif ($diff < 3600) {
                                    echo round($diff / 60) . 'm ago';
                                } elseif ($diff < 86400) {
                                    echo round($diff / 3600) . 'h ago';
                                } else {
                                    echo round($diff / 86400) . 'd ago';
                                }
                            } else {
                                echo 'Never';
                            }
                            ?>
                        </span>
                    </div>
                    
                    <!-- View Rules Link -->
                    <div class="pt-2 border-t border-gray-700">
                        <a href="/isotone/iso-automation/config/rules.yaml" target="_blank" class="text-purple-400 hover:text-purple-300 text-sm flex items-center space-x-1 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            <span>View automation rules →</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Terminal Output -->
        <div class="dark:bg-gray-900 bg-gray-50 rounded-lg p-6 dark:border-gray-700 border-gray-200 border mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-white">Terminal Output</h2>
                <div class="flex space-x-2">
                    <button onclick="clearTerminal()" class="text-gray-400 hover:text-white transition" title="Clear Terminal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                    <button onclick="toggleAutoScroll()" id="auto-scroll-btn" class="text-cyan-400 hover:text-cyan-300 transition" title="Auto-scroll">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div id="terminal" class="bg-black rounded p-4 font-mono text-sm text-green-400 h-96 overflow-y-auto">
                <div class="text-gray-500">
                    <span class="text-cyan-400">isotone@automation</span>:<span class="text-blue-400">~</span>$ <span class="text-white">Ready for commands...</span>
                </div>
                <div id="terminal-output"></div>
                <div id="terminal-cursor" class="inline-block w-2 h-4 bg-green-400 animate-pulse"></div>
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

<script>
// Terminal functions
let autoScroll = true;
let terminalOutput = [];

function addToTerminal(text, type = 'info') {
    const terminal = document.getElementById('terminal-output');
    const cursor = document.getElementById('terminal-cursor');
    
    // Remove cursor temporarily
    cursor.style.display = 'none';
    
    // Create new line
    const line = document.createElement('div');
    line.className = `terminal-${type}`;
    
    // Add timestamp for non-command lines
    if (type !== 'command') {
        const timestamp = new Date().toLocaleTimeString();
        line.innerHTML = `<span class="terminal-info">[${timestamp}]</span> ${text}`;
    } else {
        line.innerHTML = text;
    }
    
    terminal.appendChild(line);
    
    // Re-add cursor
    cursor.style.display = 'inline-block';
    
    // Auto-scroll if enabled
    if (autoScroll) {
        const terminalContainer = document.getElementById('terminal');
        terminalContainer.scrollTop = terminalContainer.scrollHeight;
    }
}

function clearTerminal() {
    const terminal = document.getElementById('terminal-output');
    terminal.innerHTML = '';
    addToTerminal('<span class="text-cyan-400">isotone@automation</span>:<span class="text-blue-400">~</span>$ <span class="text-white">Terminal cleared</span>', 'command');
}

function toggleAutoScroll() {
    autoScroll = !autoScroll;
    const btn = document.getElementById('auto-scroll-btn');
    btn.classList.toggle('text-cyan-400', autoScroll);
    btn.classList.toggle('text-gray-400', !autoScroll);
}

function executeSelectedCommand() {
    const select = document.getElementById('command-select');
    const task = select.value;
    
    if (!task) {
        showToast('Please select a command to execute', 'warning');
        return;
    }
    
    executeTask(task);
    
    // Reset the dropdown
    select.value = '';
}

function executeTask(task) {
    // Show command in terminal
    const commandMap = {
        'check:docs': 'composer docs:check',
        'update:docs': 'composer docs:update',
        'sync:user-docs': 'composer docs:sync',
        'hooks:scan': 'php iso-automation/cli.php hooks:scan',
        'sync:ide': 'composer ide:sync',
        'validate:rules': 'php iso-automation/cli.php validate:rules',
        'cache:clear': 'php iso-automation/cli.php cache:clear',
        'cache:stats': 'php iso-automation/cli.php cache:stats',
        'status': 'php iso-automation/cli.php status',
        'rules:export': 'php iso-automation/cli.php rules:export'
    };
    
    const command = commandMap[task] || `php iso-automation/cli.php ${task}`;
    addToTerminal(`<span class="text-cyan-400">isotone@automation</span>:<span class="text-blue-400">~</span>$ <span class="text-white">${command}</span>`, 'command');
    
    // Add loading indicator
    addToTerminal('⏳ Executing command...', 'info');
    
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
            // Show success in terminal with enhanced formatting
            if (data.output) {
                // Split output by lines and add each with proper formatting
                const lines = data.output.split('\n');
                lines.forEach(line => {
                    if (line.trim()) {
                        // Detect different types of output lines for better visualization
                        if (line.includes('━━━')) {
                            // Separator line - dim it
                            addToTerminal(`<span class="text-gray-600">${line}</span>`, 'output');
                        } else if (line.match(/^[🔍🔎📋📊📝💾📚🗑️🧹✨✅]/)) {
                            // Lines with emoji indicators - highlight them
                            if (line.includes('Step')) {
                                // Step indicators - make them cyan
                                addToTerminal(`<span class="text-cyan-400">${line}</span>`, 'output');
                            } else if (line.includes('✅')) {
                                // Success messages - make them green
                                addToTerminal(`<span class="text-green-400">${line}</span>`, 'output');
                            } else {
                                // Other emoji lines
                                addToTerminal(line, 'info');
                            }
                        } else if (line.includes('Error') || line.includes('failed')) {
                            // Error messages - make them red
                            addToTerminal(`<span class="text-red-400">${line}</span>`, 'output');
                        } else if (line.match(/^\s{3,}/)) {
                            // Indented detail lines - make them dimmer
                            addToTerminal(`<span class="text-gray-400">${line}</span>`, 'output');
                        } else {
                            // Regular output
                            addToTerminal(line, 'info');
                        }
                    }
                });
            }
            addToTerminal(`✓ Task completed successfully`, 'success');
            showToast(`Task ${task} completed successfully`, 'success');
        } else {
            // Show error in terminal
            const errorMsg = data.error || `Task ${task} failed`;
            if (data.output) {
                const lines = data.output.split('\n');
                lines.forEach(line => {
                    if (line.trim()) {
                        addToTerminal(line, 'warning');
                    }
                });
            }
            addToTerminal(`✗ ${errorMsg}`, 'error');
            showToast(errorMsg, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        addToTerminal(`✗ Failed to execute task: ${error.message}`, 'error');
        showToast('Failed to execute task. Check terminal for details.', 'error');
    });
}

function clearCache() {
    if (!confirm('Are you sure you want to clear all caches?')) {
        return;
    }
    
    // Show command in terminal
    addToTerminal(`<span class="text-cyan-400">isotone@automation</span>:<span class="text-blue-400">~</span>$ <span class="text-white">php isotone cache:clear</span>`, 'command');
    
    fetch('automation-ajax.php?action=clear_cache', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            addToTerminal('Clearing memory cache...', 'info');
            addToTerminal('Clearing file cache...', 'info');
            addToTerminal('✓ All caches have been cleared successfully', 'success');
            showToast('All caches have been cleared', 'success');
        } else {
            addToTerminal(`✗ Failed to clear cache: ${data.error || 'Unknown error'}`, 'error');
            showToast(data.error || 'Failed to clear cache', 'error');
        }
    })
    .catch(error => {
        addToTerminal(`✗ Failed to clear cache: ${error.message}`, 'error');
        showToast(error.message, 'error');
    });
}

function refreshStatus() {
    location.reload();
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

<?php
// Get the buffered content
$page_content = ob_get_clean();

// Include the admin layout
require_once __DIR__ . '/includes/admin-layout.php';
?>
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

$engine = new AutomationEngine();
$engine->initialize();

// Get current status
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
        </div>

        <!-- Quick Actions and System Controls -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Command Execution Card -->
            <div class="lg:col-span-2 bg-gray-800 rounded-lg p-6 border border-gray-700">
                <h2 class="text-xl font-semibold dark:text-white text-gray-900 mb-4">Command Execution</h2>
                
                <!-- Command Dropdown -->
                <div class="relative">
                    <label class="block text-gray-400 text-sm mb-2">Select Command</label>
                    <div id="selected-command-display" class="hidden mb-2 p-2 bg-gray-900 rounded border border-gray-700">
                        <span class="text-gray-400">Selected: </span>
                        <span id="selected-desc" class="text-white"></span>
                        <span id="selected-cmd" class="text-cyan-400 font-mono text-sm ml-2"></span>
                    </div>
                    <div class="flex space-x-2">
                        <select id="command-select" class="flex-1 dark:bg-gray-900 bg-white dark:border-gray-700 border-gray-200 border dark:text-white text-gray-900 py-2 px-4 rounded-lg focus:outline-none focus:border-cyan-400">
                            <option value="">-- Select a command --</option>
                            <optgroup label="Documentation">
                                <option value="check:docs" data-desc="Check Documentation" data-cmd="docs:check">Check Documentation (docs:check)</option>
                                <option value="update:docs" data-desc="Update Documentation" data-cmd="docs:update">Update Documentation (docs:update)</option>
                                <option value="generate:hooks" data-desc="Generate Hooks Documentation" data-cmd="generate:hooks">Generate Hooks Documentation (generate:hooks)</option>
                            </optgroup>
                            <optgroup label="Rules Management">
                                <option value="rules:list" data-desc="List All Rules" data-cmd="rules:list">List All Rules (rules:list)</option>
                                <option value="rules:validate" data-desc="Validate All Rules" data-cmd="validate:rules">Validate All Rules (validate:rules)</option>
                                <option value="rules:export" data-desc="Export Rules YAML" data-cmd="rules:export">Export Rules YAML (rules:export)</option>
                            </optgroup>
                            <optgroup label="Tailwind CSS">
                                <option value="tailwind:build" data-desc="Build CSS" data-cmd="tailwind:build">Build CSS (tailwind:build)</option>
                                <option value="tailwind:watch" data-desc="Watch & Rebuild CSS" data-cmd="tailwind:watch">Watch & Rebuild CSS (tailwind:watch)</option>
                                <option value="tailwind:minify" data-desc="Build Minified CSS" data-cmd="tailwind:minify">Build Minified CSS (tailwind:minify)</option>
                                <option value="tailwind:install" data-desc="Install Dependencies" data-cmd="tailwind:install">Install Dependencies (tailwind:install)</option>
                                <option value="tailwind:update" data-desc="Update Tailwind" data-cmd="tailwind:update">Update Tailwind (tailwind:update)</option>
                                <option value="tailwind:status" data-desc="Check Build Status" data-cmd="tailwind:status">Check Build Status (tailwind:status)</option>
                            </optgroup>
                            <optgroup label="IDE Integration">
                                <option value="sync:ide" data-desc="Sync IDE Rules" data-cmd="ide:sync">Sync IDE Rules (ide:sync)</option>
                            </optgroup>
                            <optgroup label="System">
                                <option value="status" data-desc="Show Automation Status" data-cmd="status">Show Automation Status (status)</option>
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
                        <span class="dark:text-white text-gray-900 font-semibold">
                            <?php 
                            // Count available automation commands
                            $commands = [
                                'check:docs', 'update:docs', 'generate:hooks',
                                'sync:ide', 'validate:rules',
                                'rules:list', 'rules:search', 'rules:check',
                                'rules:export', 'status'
                            ];
                            echo count($commands);
                            ?>
                        </span>
                    </div>
                    
                    <!-- System State -->
                    <div class="flex items-center justify-between">
                        <span class="dark:text-gray-400 text-gray-600 text-sm">System Status</span>
                        <span class="dark:text-white text-gray-900 font-semibold">
                            <?php 
                            // Check if automation engine initialized successfully
                            try {
                                $rulesCount = count($rules);
                                if ($rulesCount > 0) {
                                    echo '<span class="text-green-400">● </span>Operational';
                                } else {
                                    echo '<span class="text-yellow-400">● </span>No Rules';
                                }
                            } catch (Exception $e) {
                                echo '<span class="text-red-400">● </span>Error';
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


<style>
/* Custom styling for command dropdown */
#command-select {
    font-family: system-ui, -apple-system, sans-serif;
}

#command-select option {
    padding: 8px;
}

/* Style optgroup labels */
#command-select optgroup {
    font-weight: bold;
    color: #9ca3af;
}

/* Use attribute selector to style the command part differently */
#command-select option[data-cmd] {
    font-family: system-ui, -apple-system, sans-serif;
}
</style>

<script>
// Enhanced dropdown styling
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('command-select');
    const display = document.getElementById('selected-command-display');
    const descSpan = document.getElementById('selected-desc');
    const cmdSpan = document.getElementById('selected-cmd');
    
    // Update display when selection changes
    select.addEventListener('change', function() {
        if (this.selectedIndex > 0 && this.value) {
            const option = this.options[this.selectedIndex];
            const desc = option.getAttribute('data-desc');
            const cmd = option.getAttribute('data-cmd');
            
            // Show the styled display
            display.classList.remove('hidden');
            descSpan.textContent = desc;
            cmdSpan.textContent = `(${cmd})`;
        } else {
            // Hide display when no selection
            display.classList.add('hidden');
        }
    });
    
    // Enhance the dropdown options visually
    enhanceDropdownOptions();
});

function enhanceDropdownOptions() {
    const select = document.getElementById('command-select');
    const options = select.querySelectorAll('option[data-cmd]');
    
    // Create tooltip or format text for better visibility
    options.forEach(option => {
        const desc = option.getAttribute('data-desc');
        const cmd = option.getAttribute('data-cmd');
        
        // Format the option text to separate description and command
        // Using Unicode spaces and special characters for visual separation
        const formattedText = `${desc} ➜ ${cmd}`;
        option.textContent = formattedText;
        
        // Add title attribute for hover tooltip
        option.title = `Command: ${cmd}`;
    });
}

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
        'generate:hooks': 'php iso-automation/cli.php generate:hooks',
        'sync:ide': 'composer ide:sync',
        'rules:list': 'php iso-automation/cli.php rules:list',
        'rules:validate': 'composer validate:rules',
        'rules:export': 'php iso-automation/cli.php rules:export',
        'tailwind:build': 'composer tailwind:build',
        'tailwind:watch': 'composer tailwind:watch',
        'tailwind:minify': 'composer tailwind:minify',
        'tailwind:install': 'composer tailwind:install',
        'tailwind:update': 'composer tailwind:update',
        'tailwind:status': 'composer tailwind:status',
        'status': 'php iso-automation/cli.php status'
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
<?php
/**
 * Login Security Settings Page - Enhanced with Charts and Tabs
 * 
 * @package Isotone
 */

require_once 'auth.php';
requireRole('admin');

require_once dirname(__DIR__) . '/iso-includes/database.php';
require_once dirname(__DIR__) . '/iso-includes/class-login-security.php';
isotone_db_connect();

use RedBeanPHP\R;

// Handle AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    
    // Get security stats for charts
    if (isset($_GET['action']) && $_GET['action'] === 'get_stats') {
        $period = $_GET['period'] ?? 'week';
        
        // Calculate date range
        switch ($period) {
            case 'day':
                $start_date = date('Y-m-d 00:00:00');
                break;
            case 'week':
                $start_date = date('Y-m-d 00:00:00', strtotime('-7 days'));
                break;
            case 'month':
            default:
                $start_date = date('Y-m-d 00:00:00', strtotime('-30 days'));
                break;
        }
        
        // Get hourly/daily stats
        $stats = [];
        if ($period === 'day') {
            // Hourly stats for today
            $sql = "SELECT 
                        HOUR(attempt_time) as period,
                        COUNT(*) as total,
                        SUM(success = 0) as failed,
                        SUM(success = 1) as successful
                    FROM loginattempt 
                    WHERE attempt_time >= ?
                    GROUP BY HOUR(attempt_time)
                    ORDER BY period";
        } else {
            // Daily stats
            $sql = "SELECT 
                        DATE(attempt_time) as period,
                        COUNT(*) as total,
                        SUM(success = 0) as failed,
                        SUM(success = 1) as successful
                    FROM loginattempt 
                    WHERE attempt_time >= ?
                    GROUP BY DATE(attempt_time)
                    ORDER BY period";
        }
        
        $stats = R::getAll($sql, [$start_date]);
        
        // Get top blocked IPs
        $blocked_ips = R::getAll(
            "SELECT ip_address as ipAddress, COUNT(*) as attempts 
             FROM loginattempt 
             WHERE success = 0 AND attempt_time >= ?
             GROUP BY ip_address 
             ORDER BY attempts DESC 
             LIMIT 5",
            [$start_date]
        );
        
        // Get total blocked attempts
        $total_blocked = R::getCell(
            "SELECT COUNT(*) FROM loginattempt WHERE success = 0 AND attempt_time >= ?",
            [$start_date]
        );
        
        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'blocked_ips' => $blocked_ips,
            'total_blocked' => $total_blocked,
            'period' => $period
        ]);
        exit;
    }
    
    // Handle IP list operations
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_ip':
                $ip = filter_var($_POST['ip'], FILTER_VALIDATE_IP);
                $list_type = $_POST['list_type'];
                $reason = $_POST['reason'] ?? '';
                
                if ($ip && in_array($list_type, ['safelist', 'denylist'])) {
                    $success = LoginSecurity::addIPToList($ip, $list_type, $reason, $_SESSION['isotone_admin_username']);
                    echo json_encode(['success' => $success]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Invalid IP or list type']);
                }
                break;
                
            case 'remove_ip':
                $id = (int)$_POST['id'];
                $ip_entry = R::load('iplist', $id);
                if ($ip_entry->id) {
                    R::trash($ip_entry);
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'IP entry not found']);
                }
                break;
                
            case 'add_username':
                $username = $_POST['username'];
                $list_type = $_POST['list_type'];
                $reason = $_POST['reason'] ?? '';
                
                if ($username && in_array($list_type, ['safelist', 'denylist'])) {
                    $success = LoginSecurity::addUsernameToList($username, $list_type, $reason, $_SESSION['isotone_admin_username']);
                    echo json_encode(['success' => $success]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Invalid username or list type']);
                }
                break;
                
            case 'remove_username':
                $id = (int)$_POST['id'];
                $username_entry = R::load('usernamelist', $id);
                if ($username_entry->id) {
                    R::trash($username_entry);
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Username entry not found']);
                }
                break;
                
            case 'clear_lockout':
                $id = (int)$_POST['id'];
                $success = LoginSecurity::clearLockout(null, null, $id);
                echo json_encode(['success' => $success]);
                break;
        }
        exit;
    }
}

// Handle form submission for settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    if (!iso_verify_csrf()) {
        $error_message = 'Security token invalid. Please refresh and try again.';
    } else {
        // Update settings based on active tab
        $tab = $_POST['tab'] ?? 'general';
        
        switch ($tab) {
            case 'general':
                $settings = [
                    'max_login_attempts' => (int)($_POST['max_login_attempts'] ?? 5),
                    'lockout_duration' => (int)($_POST['lockout_duration'] ?? 900),
                    'reset_time' => (int)($_POST['reset_time'] ?? 900),
                    'show_remaining_attempts' => isset($_POST['show_remaining_attempts']) ? 1 : 0,
                    'log_failed_attempts' => isset($_POST['log_failed_attempts']) ? 1 : 0,
                    'lockout_message' => $_POST['lockout_message'] ?? 'Too many failed login attempts. Please try again later.',
                    'enable_captcha_after' => (int)($_POST['enable_captcha_after'] ?? 0),
                    'chart_refresh_interval' => (int)($_POST['chart_refresh_interval'] ?? 30)
                ];
                break;
                
            case 'notifications':
                $settings = [
                    'notify_admin_lockout' => isset($_POST['notify_admin_lockout']) ? 1 : 0,
                    'admin_email' => $_POST['admin_email'] ?? '',
                    'notify_on_safelist_bypass' => isset($_POST['notify_on_safelist_bypass']) ? 1 : 0,
                    'notify_on_denylist_block' => isset($_POST['notify_on_denylist_block']) ? 1 : 0
                ];
                break;
                
            case 'access_control':
                $settings = [
                    'enable_ip_safelist' => isset($_POST['enable_ip_safelist']) ? 1 : 0,
                    'enable_ip_denylist' => isset($_POST['enable_ip_denylist']) ? 1 : 0,
                    'enable_username_safelist' => isset($_POST['enable_username_safelist']) ? 1 : 0,
                    'enable_username_denylist' => isset($_POST['enable_username_denylist']) ? 1 : 0
                ];
                break;
        }
        
        $success = true;
        foreach ($settings as $name => $value) {
            $type = in_array($name, ['max_login_attempts', 'lockout_duration', 'reset_time', 'enable_captcha_after', 'chart_refresh_interval']) ? 'integer' :
                   (in_array($name, ['show_remaining_attempts', 'log_failed_attempts', 'enable_ip_safelist', 'enable_ip_denylist', 
                                     'enable_username_safelist', 'enable_username_denylist', 'notify_admin_lockout',
                                     'notify_on_safelist_bypass', 'notify_on_denylist_block']) ? 'boolean' : 'string');
            
            if (!LoginSecurity::updateSetting($name, $value, $type)) {
                $success = false;
            }
        }
        
        if ($success) {
            $success_message = 'Settings updated successfully!';
        } else {
            $error_message = 'Some settings could not be updated.';
        }
    }
}

// Get current settings
$max_attempts = LoginSecurity::getSetting('max_login_attempts', 5);
$lockout_duration = LoginSecurity::getSetting('lockout_duration', 900);
$reset_time = LoginSecurity::getSetting('reset_time', 900);
$show_remaining = LoginSecurity::getSetting('show_remaining_attempts', true);
$log_failed = LoginSecurity::getSetting('log_failed_attempts', true);
$enable_ip_safelist = LoginSecurity::getSetting('enable_ip_safelist', false);
$enable_ip_denylist = LoginSecurity::getSetting('enable_ip_denylist', true);
$enable_username_safelist = LoginSecurity::getSetting('enable_username_safelist', false);
$enable_username_denylist = LoginSecurity::getSetting('enable_username_denylist', true);
$notify_admin = LoginSecurity::getSetting('notify_admin_lockout', false);
$admin_email = LoginSecurity::getSetting('admin_email', '');
$lockout_message = LoginSecurity::getSetting('lockout_message', 'Too many failed login attempts. Please try again later.');
$captcha_after = LoginSecurity::getSetting('enable_captcha_after', 3);
$chart_refresh_interval = LoginSecurity::getSetting('chart_refresh_interval', 30);

// Get statistics
$active_lockouts = LoginSecurity::getActiveLockouts();
$lockout_count = count($active_lockouts);

// Get blocked attempts in last 24 hours
$blocked_24h = R::count('loginattempt', 
    'success = 0 AND attempt_time > ?', 
    [date('Y-m-d H:i:s', strtotime('-24 hours'))]
);

// Get blocked attempts in last 7 days
$blocked_7d = R::count('loginattempt', 
    'success = 0 AND attempt_time > ?', 
    [date('Y-m-d H:i:s', strtotime('-7 days'))]
);

// Get IP lists
$ip_safelist = R::find('iplist', 'listType = ? AND active = 1 ORDER BY addedDate DESC', ['safelist']);
$ip_denylist = R::find('iplist', 'listType = ? AND active = 1 ORDER BY addedDate DESC', ['denylist']);

// Get username lists
$username_safelist = R::find('usernamelist', 'listType = ? AND active = 1 ORDER BY addedDate DESC', ['safelist']);
$username_denylist = R::find('usernamelist', 'listType = ? AND active = 1 ORDER BY addedDate DESC', ['denylist']);

// Fun lockout messages
$fun_messages = [
    "🚫 Whoa there, speedy! Your fingers need a timeout. Come back in %d minutes!",
    "🎮 Achievement Unlocked: Too Many Attempts! Cooldown: %d minutes",
    "☕ Perfect time for a coffee break! See you in %d minutes",
    "🔒 The fortress has spoken: Take a %d minute meditation break",
    "🎪 The security circus is closed! Reopening in %d minutes",
    "🚀 Houston, we have a problem! Mission resuming in T-%d minutes",
    "🎨 While you wait %d minutes, why not draw a picture of the correct password?",
    "🏖️ Mandatory vacation time: %d minutes. No passwords on the beach!",
    "🎭 Plot twist: The door is locked for %d minutes. Dramatic pause...",
    "🌮 Taco break enforced! Duration: %d minutes. Password not included."
];

// Start output buffering to capture content
ob_start();
?>

<style>
/* Chart container styles */
.chart-container {
    position: relative;
    height: 250px;
    width: 100%;
}

.chart-container canvas {
    max-width: 100%;
    height: 100% !important;
}

/* Loading state for charts */
.chart-loading {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.9);
    z-index: 10;
}

.dark .chart-loading {
    background: rgba(31, 41, 55, 0.9);
}

.chart-loading-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #e5e7eb;
    border-top-color: #06b6d4;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Slide down animation for messages */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-slideDown {
    animation: slideDown 0.4s ease-out;
}
</style>

<!-- Alpine.js component for the entire page -->
<div x-data="loginSecurityPage()" x-init="init()" class="max-w-7xl mx-auto p-6">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center">
            <svg class="w-8 h-8 mr-3 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            Login Security
        </h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
            Protect your site from brute force attacks and manage access control
        </p>
    </div>

    <?php if (isset($success_message)): ?>
    <div class="mb-6 relative overflow-hidden backdrop-blur-sm rounded-xl shadow-lg border border-cyan-200 dark:border-cyan-800 animate-slideDown" style="background-color: rgba(0, 211, 242, 0.1);">
        <div class="absolute top-0 left-0 w-1 h-full" style="background-color: #00D3F2;"></div>
        <div class="flex items-center p-4 pl-6">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                    <?php echo htmlspecialchars($success_message); ?>
                </p>
            </div>
            <button onclick="this.parentElement.parentElement.style.display='none'" class="ml-4 flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
    <div class="mb-6 relative overflow-hidden backdrop-blur-sm rounded-xl shadow-lg border border-red-200 dark:border-red-800 animate-slideDown" style="background-color: rgba(239, 68, 68, 0.1);">
        <div class="absolute top-0 left-0 w-1 h-full" style="background-color: #EF4444;"></div>
        <div class="flex items-center p-4 pl-6">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                    <?php echo htmlspecialchars($error_message); ?>
                </p>
            </div>
            <button onclick="this.parentElement.parentElement.style.display='none'" class="ml-4 flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Active Lockouts -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-red-500 opacity-10 rounded-full"></div>
            <div class="relative">
                <div class="flex items-center">
                    <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Active Lockouts</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo $lockout_count; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Blocked Today -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-yellow-500 opacity-10 rounded-full"></div>
            <div class="relative">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Blocked (24h)</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo $blocked_24h; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Blocked This Week -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-blue-500 opacity-10 rounded-full"></div>
            <div class="relative">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Blocked (7d)</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo $blocked_7d; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Max Attempts Setting -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-green-500 opacity-10 rounded-full"></div>
            <div class="relative">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Max Attempts</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo $max_attempts; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Login Attempts Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Login Attempts</h3>
                    <div class="flex gap-2">
                        <button @click="chartPeriod = 'day'; loadChartData()" 
                                :class="chartPeriod === 'day' ? 'bg-cyan-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                class="text-xs px-3 py-1 rounded transition-colors">Today</button>
                        <button @click="chartPeriod = 'week'; loadChartData()" 
                                :class="chartPeriod === 'week' ? 'bg-cyan-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                class="text-xs px-3 py-1 rounded transition-colors">Week</button>
                        <button @click="chartPeriod = 'month'; loadChartData()" 
                                :class="chartPeriod === 'month' ? 'bg-cyan-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                class="text-xs px-3 py-1 rounded transition-colors">Month</button>
                    </div>
                </div>
                <div class="chart-container" style="position: relative; height: 250px; width: 100%;">
                    <canvas id="attemptsChart"></canvas>
                    <div x-show="!chartsInitialized" class="chart-loading">
                        <div class="chart-loading-spinner"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Blocked IPs -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Blocked IPs</h3>
                <div class="chart-container" style="position: relative; height: 250px; width: 100%;">
                    <canvas id="blockedIPsChart"></canvas>
                    <div x-show="!chartsInitialized" class="chart-loading">
                        <div class="chart-loading-spinner"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
        <div class="border-b dark:border-gray-700">
            <nav class="flex -mb-px">
                <button @click="activeTab = 'general'" 
                        :class="activeTab === 'general' ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    General Settings
                </button>
                <button @click="activeTab = 'access_control'" 
                        :class="activeTab === 'access_control' ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    Access Control
                </button>
                <button @click="activeTab = 'ip_lists'" 
                        :class="activeTab === 'ip_lists' ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    IP Lists
                </button>
                <button @click="activeTab = 'username_lists'" 
                        :class="activeTab === 'username_lists' ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    Username Lists
                </button>
                <button @click="activeTab = 'active_lockouts'" 
                        :class="activeTab === 'active_lockouts' ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors relative">
                    Active Lockouts
                    <?php if ($lockout_count > 0): ?>
                    <span class="ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full"><?php echo $lockout_count; ?></span>
                    <?php endif; ?>
                </button>
                <button @click="activeTab = 'notifications'" 
                        :class="activeTab === 'notifications' ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    Notifications
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <form method="POST" action="" @submit.prevent="saveSettings">
            <?php echo iso_csrf_field(); ?>
            <input type="hidden" name="tab" :value="activeTab">
            
            <!-- General Settings Tab -->
            <div x-show="activeTab === 'general'" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="max_login_attempts" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Maximum Login Attempts
                        </label>
                        <input type="number" 
                               id="max_login_attempts" 
                               name="max_login_attempts" 
                               value="<?php echo $max_attempts; ?>"
                               min="1" 
                               max="50"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Number of failed attempts before lockout</p>
                    </div>

                    <div>
                        <label for="lockout_duration" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Lockout Duration (seconds)
                        </label>
                        <input type="number" 
                               id="lockout_duration" 
                               name="lockout_duration" 
                               value="<?php echo $lockout_duration; ?>"
                               min="60" 
                               max="86400"
                               step="60"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">How long users are locked out (900 = 15 minutes)</p>
                    </div>

                    <div>
                        <label for="reset_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Reset Time (seconds)
                        </label>
                        <input type="number" 
                               id="reset_time" 
                               name="reset_time" 
                               value="<?php echo $reset_time; ?>"
                               min="60" 
                               max="86400"
                               step="60"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Time before attempt counter resets</p>
                    </div>

                    <div>
                        <label for="enable_captcha_after" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Show CAPTCHA After (attempts)
                        </label>
                        <input type="number" 
                               id="enable_captcha_after" 
                               name="enable_captcha_after" 
                               value="<?php echo $captcha_after; ?>"
                               min="0" 
                               max="50"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">0 to disable CAPTCHA</p>
                    </div>

                    <div>
                        <label for="chart_refresh_interval" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Chart Refresh Interval (seconds)
                        </label>
                        <input type="number" 
                               id="chart_refresh_interval" 
                               name="chart_refresh_interval" 
                               value="<?php echo $chart_refresh_interval; ?>"
                               min="0" 
                               max="300"
                               step="5"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">0 to disable auto-refresh, recommended: 30-60 seconds</p>
                    </div>
                </div>

                <div class="mt-6">
                    <label for="lockout_message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Lockout Message
                    </label>
                    <select id="lockout_message" name="lockout_message" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="<?php echo htmlspecialchars($lockout_message); ?>" selected>Current: <?php echo htmlspecialchars($lockout_message); ?></option>
                        <?php foreach ($fun_messages as $msg): ?>
                        <option value="<?php echo htmlspecialchars(str_replace('%d', '{minutes}', $msg)); ?>">
                            <?php echo htmlspecialchars(sprintf($msg, 15)); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Message shown to locked out users. Use {minutes} as placeholder.</p>
                </div>

                <div class="mt-6 space-y-4">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="show_remaining_attempts" 
                               value="1"
                               <?php echo $show_remaining ? 'checked' : ''; ?>
                               class="w-4 h-4 text-cyan-600 bg-gray-100 border-gray-300 rounded focus:ring-cyan-500 dark:focus:ring-cyan-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Show remaining login attempts to users
                        </span>
                    </label>

                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="log_failed_attempts" 
                               value="1"
                               <?php echo $log_failed ? 'checked' : ''; ?>
                               class="w-4 h-4 text-cyan-600 bg-gray-100 border-gray-300 rounded focus:ring-cyan-500 dark:focus:ring-cyan-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Log all failed login attempts
                        </span>
                    </label>
                </div>
            </div>

            <!-- Access Control Tab -->
            <div x-show="activeTab === 'access_control'" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">IP Address Lists</h3>
                        <label class="flex items-center mb-3">
                            <input type="checkbox" 
                                   name="enable_ip_safelist" 
                                   value="1"
                                   <?php echo $enable_ip_safelist ? 'checked' : ''; ?>
                                   class="w-4 h-4 text-cyan-600 bg-gray-100 border-gray-300 rounded focus:ring-cyan-500 dark:focus:ring-cyan-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Enable IP safelist (bypass rate limiting)
                            </span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="enable_ip_denylist" 
                                   value="1"
                                   <?php echo $enable_ip_denylist ? 'checked' : ''; ?>
                                   class="w-4 h-4 text-cyan-600 bg-gray-100 border-gray-300 rounded focus:ring-cyan-500 dark:focus:ring-cyan-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Enable IP denylist (block access)
                            </span>
                        </label>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Username Lists</h3>
                        <label class="flex items-center mb-3">
                            <input type="checkbox" 
                                   name="enable_username_safelist" 
                                   value="1"
                                   <?php echo $enable_username_safelist ? 'checked' : ''; ?>
                                   class="w-4 h-4 text-cyan-600 bg-gray-100 border-gray-300 rounded focus:ring-cyan-500 dark:focus:ring-cyan-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Enable username safelist
                            </span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="enable_username_denylist" 
                                   value="1"
                                   <?php echo $enable_username_denylist ? 'checked' : ''; ?>
                                   class="w-4 h-4 text-cyan-600 bg-gray-100 border-gray-300 rounded focus:ring-cyan-500 dark:focus:ring-cyan-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Enable username denylist
                            </span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-cyan-50 dark:bg-cyan-900/20 border border-cyan-200 dark:border-cyan-800 rounded-lg">
                    <h4 class="text-sm font-semibold text-cyan-900 dark:text-cyan-300 mb-2">How Access Control Works</h4>
                    <ul class="text-sm text-cyan-700 dark:text-cyan-400 space-y-1">
                        <li>• <strong>Safelist:</strong> IPs/usernames that bypass rate limiting (trusted users)</li>
                        <li>• <strong>Denylist:</strong> IPs/usernames that are always blocked (known threats)</li>
                        <li>• Denylists are checked first, then safelists, then rate limiting</li>
                        <li>• Use the IP Lists and Username Lists tabs to manage entries</li>
                    </ul>
                </div>
            </div>

            <!-- IP Lists Tab -->
            <div x-show="activeTab === 'ip_lists'" class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- IP Safelist -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            IP Safelist 
                            <span class="text-sm font-normal text-gray-500">(<?php echo count($ip_safelist); ?> entries)</span>
                        </h3>
                        
                        <!-- Add IP to Safelist -->
                        <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" 
                                       x-model="newSafeIP" 
                                       placeholder="IP Address"
                                       class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white text-sm">
                                <input type="text" 
                                       x-model="newSafeIPReason" 
                                       placeholder="Reason (optional)"
                                       class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white text-sm">
                            </div>
                            <button @click="addToIPList('safelist')" 
                                    type="button"
                                    class="mt-2 w-full px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm">
                                Add to Safelist
                            </button>
                        </div>
                        
                        <!-- Safelist Entries -->
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            <?php foreach ($ip_safelist as $entry): ?>
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div>
                                    <div class="font-mono text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($entry->ip_address); ?></div>
                                    <?php if ($entry->reason): ?>
                                    <div class="text-xs text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($entry->reason); ?></div>
                                    <?php endif; ?>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">Added <?php echo date('M j, Y', strtotime($entry->addedDate)); ?></div>
                                </div>
                                <button @click="removeFromIPList(<?php echo $entry->id; ?>)" 
                                        type="button"
                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                            <?php endforeach; ?>
                            <?php if (count($ip_safelist) === 0): ?>
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No IPs in safelist</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- IP Denylist -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            IP Denylist 
                            <span class="text-sm font-normal text-gray-500">(<?php echo count($ip_denylist); ?> entries)</span>
                        </h3>
                        
                        <!-- Add IP to Denylist -->
                        <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" 
                                       x-model="newDenyIP" 
                                       placeholder="IP Address"
                                       class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white text-sm">
                                <input type="text" 
                                       x-model="newDenyIPReason" 
                                       placeholder="Reason (optional)"
                                       class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white text-sm">
                            </div>
                            <button @click="addToIPList('denylist')" 
                                    type="button"
                                    class="mt-2 w-full px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm">
                                Add to Denylist
                            </button>
                        </div>
                        
                        <!-- Denylist Entries -->
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            <?php foreach ($ip_denylist as $entry): ?>
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div>
                                    <div class="font-mono text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($entry->ip_address); ?></div>
                                    <?php if ($entry->reason): ?>
                                    <div class="text-xs text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($entry->reason); ?></div>
                                    <?php endif; ?>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">Added <?php echo date('M j, Y', strtotime($entry->addedDate)); ?></div>
                                </div>
                                <button @click="removeFromIPList(<?php echo $entry->id; ?>)" 
                                        type="button"
                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                            <?php endforeach; ?>
                            <?php if (count($ip_denylist) === 0): ?>
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No IPs in denylist</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Username Lists Tab -->
            <div x-show="activeTab === 'username_lists'" class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Username Safelist -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Username Safelist 
                            <span class="text-sm font-normal text-gray-500">(<?php echo count($username_safelist); ?> entries)</span>
                        </h3>
                        
                        <!-- Add Username to Safelist -->
                        <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" 
                                       x-model="newSafeUsername" 
                                       placeholder="Username"
                                       class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white text-sm">
                                <input type="text" 
                                       x-model="newSafeUsernameReason" 
                                       placeholder="Reason (optional)"
                                       class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white text-sm">
                            </div>
                            <button @click="addToUsernameList('safelist')" 
                                    type="button"
                                    class="mt-2 w-full px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm">
                                Add to Safelist
                            </button>
                        </div>
                        
                        <!-- Safelist Entries -->
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            <?php foreach ($username_safelist as $entry): ?>
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div>
                                    <div class="font-medium text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($entry->username); ?></div>
                                    <?php if ($entry->reason): ?>
                                    <div class="text-xs text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($entry->reason); ?></div>
                                    <?php endif; ?>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">Added <?php echo date('M j, Y', strtotime($entry->addedDate)); ?></div>
                                </div>
                                <button @click="removeFromUsernameList(<?php echo $entry->id; ?>)" 
                                        type="button"
                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                            <?php endforeach; ?>
                            <?php if (count($username_safelist) === 0): ?>
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No usernames in safelist</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Username Denylist -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Username Denylist 
                            <span class="text-sm font-normal text-gray-500">(<?php echo count($username_denylist); ?> entries)</span>
                        </h3>
                        
                        <!-- Add Username to Denylist -->
                        <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" 
                                       x-model="newDenyUsername" 
                                       placeholder="Username"
                                       class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white text-sm">
                                <input type="text" 
                                       x-model="newDenyUsernameReason" 
                                       placeholder="Reason (optional)"
                                       class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white text-sm">
                            </div>
                            <button @click="addToUsernameList('denylist')" 
                                    type="button"
                                    class="mt-2 w-full px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm">
                                Add to Denylist
                            </button>
                        </div>
                        
                        <!-- Denylist Entries -->
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            <?php foreach ($username_denylist as $entry): ?>
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div>
                                    <div class="font-medium text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($entry->username); ?></div>
                                    <?php if ($entry->reason): ?>
                                    <div class="text-xs text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($entry->reason); ?></div>
                                    <?php endif; ?>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">Added <?php echo date('M j, Y', strtotime($entry->addedDate)); ?></div>
                                </div>
                                <button @click="removeFromUsernameList(<?php echo $entry->id; ?>)" 
                                        type="button"
                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                            <?php endforeach; ?>
                            <?php if (count($username_denylist) === 0): ?>
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No usernames in denylist</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Lockouts Tab -->
            <div x-show="activeTab === 'active_lockouts'" class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Currently Locked Out 
                    <span class="text-sm font-normal text-gray-500">(<?php echo $lockout_count; ?> active)</span>
                </h3>
                
                <?php if ($lockout_count > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="text-left py-2 px-3 text-gray-700 dark:text-gray-300">IP Address</th>
                                <th class="text-left py-2 px-3 text-gray-700 dark:text-gray-300">Username</th>
                                <th class="text-left py-2 px-3 text-gray-700 dark:text-gray-300">Locked At</th>
                                <th class="text-left py-2 px-3 text-gray-700 dark:text-gray-300">Unlocks At</th>
                                <th class="text-left py-2 px-3 text-gray-700 dark:text-gray-300">Reason</th>
                                <th class="text-center py-2 px-3 text-gray-700 dark:text-gray-300">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($active_lockouts as $lockout): ?>
                            <tr class="border-b dark:border-gray-700/50">
                                <td class="py-2 px-3 font-mono text-gray-900 dark:text-white"><?php echo htmlspecialchars($lockout['ipAddress']); ?></td>
                                <td class="py-2 px-3 text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($lockout['username'] ?: '-'); ?></td>
                                <td class="py-2 px-3 text-gray-600 dark:text-gray-400"><?php echo date('M j, g:i a', strtotime($lockout['lockoutTime'])); ?></td>
                                <td class="py-2 px-3 text-gray-600 dark:text-gray-400"><?php echo date('M j, g:i a', strtotime($lockout['unlockTime'])); ?></td>
                                <td class="py-2 px-3 text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($lockout['reason']); ?></td>
                                <td class="py-2 px-3 text-center">
                                    <button @click="clearLockout(<?php echo $lockout['id']; ?>)" 
                                            type="button"
                                            class="text-cyan-600 hover:text-cyan-800 dark:text-cyan-400 dark:hover:text-cyan-300">
                                        Unlock
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No active lockouts</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Notifications Tab -->
            <div x-show="activeTab === 'notifications'" class="p-6">
                <div class="space-y-6">
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="notify_admin_lockout" 
                                   value="1"
                                   <?php echo $notify_admin ? 'checked' : ''; ?>
                                   class="w-4 h-4 text-cyan-600 bg-gray-100 border-gray-300 rounded focus:ring-cyan-500 dark:focus:ring-cyan-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Send email notification on lockout
                            </span>
                        </label>
                    </div>

                    <div>
                        <label for="admin_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Admin Email for Notifications
                        </label>
                        <input type="email" 
                               id="admin_email" 
                               name="admin_email" 
                               value="<?php echo htmlspecialchars($admin_email); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="notify_on_safelist_bypass" 
                                   value="1"
                                   <?php echo LoginSecurity::getSetting('notify_on_safelist_bypass', false) ? 'checked' : ''; ?>
                                   class="w-4 h-4 text-cyan-600 bg-gray-100 border-gray-300 rounded focus:ring-cyan-500 dark:focus:ring-cyan-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Notify when safelist bypasses rate limiting
                            </span>
                        </label>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="notify_on_denylist_block" 
                                   value="1"
                                   <?php echo LoginSecurity::getSetting('notify_on_denylist_block', false) ? 'checked' : ''; ?>
                                   class="w-4 h-4 text-cyan-600 bg-gray-100 border-gray-300 rounded focus:ring-cyan-500 dark:focus:ring-cyan-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Notify when denylist blocks access
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t dark:border-gray-600 flex justify-between items-center">
                <div x-show="saveMessage" x-text="saveMessage" 
                     :class="saveSuccess ? 'text-green-600' : 'text-red-600'"
                     class="text-sm font-medium"></div>
                <button type="submit" 
                        :disabled="saving"
                        class="px-6 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!saving">Save Settings</span>
                    <span x-show="saving">Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Dynamic Chart.js Loading -->
<script>
// Load Chart.js dynamically if not already loaded
if (typeof Chart === 'undefined') {
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
    script.onload = function() {
        // Small delay to ensure Chart.js is fully initialized
        setTimeout(() => {
            window.dispatchEvent(new Event('chartjs-loaded'));
        }, 50);
    };
    script.onerror = function() {
        console.error('Failed to load Chart.js');
    };
    document.head.appendChild(script);
} else {
    // Chart.js already loaded, trigger event
    setTimeout(() => {
        window.dispatchEvent(new Event('chartjs-loaded'));
    }, 10);
}
</script>

<!-- Alpine.js Component -->
<script>
function loginSecurityPage() {
    return {
        activeTab: localStorage.getItem('securityTab') || 'general',
        chartPeriod: 'week',
        attemptsChart: null,
        blockedIPsChart: null,
        refreshInterval: null,
        chartsInitialized: false,
        chartRefreshIntervalSeconds: <?php echo $chart_refresh_interval; ?>,
        saving: false,
        saveMessage: '',
        saveSuccess: false,
        
        // IP List Management
        newSafeIP: '',
        newSafeIPReason: '',
        newDenyIP: '',
        newDenyIPReason: '',
        
        // Username List Management
        newSafeUsername: '',
        newSafeUsernameReason: '',
        newDenyUsername: '',
        newDenyUsernameReason: '',
        
        init() {
            // Watch for tab changes
            this.$watch('activeTab', value => {
                localStorage.setItem('securityTab', value);
            });
            
            // Wait for Chart.js to load before initializing charts
            if (typeof Chart === 'undefined') {
                // Listen for Chart.js to load
                window.addEventListener('chartjs-loaded', () => {
                    this.initializeCharts();
                }, { once: true });
                
                // Also try polling in case event was missed
                const waitForChart = () => {
                    if (typeof Chart !== 'undefined') {
                        this.initializeCharts();
                    } else {
                        setTimeout(waitForChart, 100);
                    }
                };
                waitForChart();
            } else {
                this.initializeCharts();
            }
            
            // Cleanup on destroy
            window.addEventListener('beforeunload', () => {
                if (this.refreshInterval) {
                    clearInterval(this.refreshInterval);
                }
                if (this.attemptsChart) {
                    this.attemptsChart.destroy();
                }
                if (this.blockedIPsChart) {
                    this.blockedIPsChart.destroy();
                }
            });
        },
        
        async loadChartData() {
            try {
                const response = await fetch(`?action=get_stats&period=${this.chartPeriod}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.updateCharts(data);
                }
            } catch (error) {
                console.error('Error loading chart data:', error);
            }
        },
        
        updateCharts(data) {
            // Check if Chart.js is loaded
            if (typeof Chart === 'undefined') {
                setTimeout(() => this.updateCharts(data), 100);
                return;
            }
            
            // Wait for next tick to ensure DOM is ready
            this.$nextTick(() => {
                // Verify both canvas elements exist
                const attemptsCanvas = document.getElementById('attemptsChart');
                const blockedCanvas = document.getElementById('blockedIPsChart');
                
                if (!attemptsCanvas || !blockedCanvas) {
                    console.warn('Chart canvas elements not found, retrying...');
                    setTimeout(() => this.updateCharts(data), 100);
                    return;
                }
                const isDarkMode = document.documentElement.classList.contains('dark');
                const textColor = isDarkMode ? '#9CA3AF' : '#4B5563';
                const gridColor = isDarkMode ? '#374151' : '#E5E7EB';
                
                // Update Attempts Chart
                const attemptsCtx = document.getElementById('attemptsChart');
                if (attemptsCtx && attemptsCtx.getContext && attemptsCtx.offsetParent !== null) {
                    // Destroy existing chart if it exists
                    if (this.attemptsChart) {
                        console.log('Destroying existing attempts chart');
                        try {
                            this.attemptsChart.destroy();
                        } catch (e) {
                            console.warn('Error destroying attempts chart:', e);
                        }
                        this.attemptsChart = null;
                    }
                    
                    const labels = [];
                    const failedData = [];
                    const successfulData = [];
                    
                    // Ensure data.stats exists and is an array
                    if (data.stats && Array.isArray(data.stats)) {
                        data.stats.forEach(stat => {
                            if (this.chartPeriod === 'day') {
                                labels.push(stat.period + ':00');
                            } else {
                                const date = new Date(stat.period);
                                labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
                            }
                            failedData.push(parseInt(stat.failed) || 0);
                            successfulData.push(parseInt(stat.successful) || 0);
                        });
                    }
                    
                    // Create default data if empty
                    if (labels.length === 0) {
                        labels.push('No Data');
                        failedData.push(0);
                        successfulData.push(0);
                    }
                    
                    try {
                        console.log('Creating attempts chart...', attemptsCtx);
                        
                        // Small delay to ensure DOM is stable
                        setTimeout(() => {
                            this.attemptsChart = new Chart(attemptsCtx.getContext('2d'), {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [
                                    {
                                        label: 'Failed Attempts',
                                        data: failedData,
                                        borderColor: '#EF4444',
                                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                        tension: 0.3,
                                        fill: true
                                    },
                                    {
                                        label: 'Successful Logins',
                                        data: successfulData,
                                        borderColor: '#10B981',
                                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                        tension: 0.3,
                                        fill: true
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: false,
                                interaction: {
                                    mode: 'index',
                                    intersect: false
                                },
                                plugins: {
                                    legend: {
                                        labels: { color: textColor }
                                    },
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false
                                    }
                                },
                                scales: {
                                    x: {
                                        ticks: { color: textColor },
                                        grid: { color: gridColor, display: true }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        ticks: { 
                                            color: textColor,
                                            stepSize: 1,
                                            precision: 0
                                        },
                                        grid: { color: gridColor, display: true }
                                    }
                                }
                            }
                            });
                        }, 100);
                    } catch (error) {
                        console.error('Error creating attempts chart:', error);
                    }
                }
                
                // Update Blocked IPs Chart
                const blockedCtx = document.getElementById('blockedIPsChart');
                if (blockedCtx && blockedCtx.getContext && blockedCtx.offsetParent !== null) {
                    // Destroy existing chart if it exists
                    if (this.blockedIPsChart) {
                        console.log('Destroying existing blocked IPs chart');
                        try {
                            this.blockedIPsChart.destroy();
                        } catch (e) {
                            console.warn('Error destroying blocked IPs chart:', e);
                        }
                        this.blockedIPsChart = null;
                    }
                    
                    let ips = ['No blocked IPs'];
                    let attempts = [0];
                    
                    // Ensure data.blocked_ips exists and is an array
                    if (data.blocked_ips && Array.isArray(data.blocked_ips) && data.blocked_ips.length > 0) {
                        ips = data.blocked_ips.map(item => item.ipAddress || 'Unknown');
                        attempts = data.blocked_ips.map(item => parseInt(item.attempts) || 0);
                    }
                    
                    try {
                        console.log('Creating blocked IPs chart...', blockedCtx);
                        
                        // Small delay to ensure DOM is stable
                        setTimeout(() => {
                            this.blockedIPsChart = new Chart(blockedCtx.getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: ips,
                                datasets: [{
                                    label: 'Failed Attempts',
                                    data: attempts,
                                    backgroundColor: '#EF4444',
                                    borderColor: '#DC2626',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: false,
                                indexAxis: 'y',
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return 'Attempts: ' + context.parsed.x;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        beginAtZero: true,
                                        ticks: { 
                                            color: textColor,
                                            stepSize: 1,
                                            precision: 0
                                        },
                                        grid: { color: gridColor, display: true }
                                    },
                                    y: {
                                        ticks: { color: textColor },
                                        grid: { color: gridColor, display: false }
                                    }
                                }
                            }
                            });
                        }, 150);
                    } catch (error) {
                        console.error('Error creating blocked IPs chart:', error);
                    }
                }
            });
        },
        
        initializeCharts() {
            if (this.chartsInitialized) {
                return;
            }
            
            // Wait for DOM elements to be available
            const waitForElements = () => {
                const attemptsCanvas = document.getElementById('attemptsChart');
                const blockedCanvas = document.getElementById('blockedIPsChart');
                
                if (!attemptsCanvas || !blockedCanvas) {
                    setTimeout(waitForElements, 50);
                    return;
                }
                
                // Mark as initialized BEFORE starting to prevent duplicates
                this.chartsInitialized = true;
                
                this.$nextTick(() => {
                    // Register Chart.js defaults
                    Chart.defaults.responsive = true;
                    Chart.defaults.maintainAspectRatio = false;
                    
                    // Load chart data
                    this.loadChartData();
                    
                    // Set up auto-refresh if enabled (0 = disabled)
                    if (!this.refreshInterval && this.chartRefreshIntervalSeconds > 0) {
                        this.refreshInterval = setInterval(() => {
                            this.loadChartData();
                        }, this.chartRefreshIntervalSeconds * 1000);
                    }
                });
            };
            
            waitForElements();
        },
        
        async saveSettings(event) {
            event.preventDefault();
            this.saving = true;
            this.saveMessage = '';
            
            const formData = new FormData(event.target);
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                // Since we're not using AJAX for the form, let it submit normally
                event.target.submit();
                
            } catch (error) {
                this.saving = false;
                this.saveSuccess = false;
                this.saveMessage = 'Error saving settings';
            }
        },
        
        async addToIPList(listType) {
            const ip = listType === 'safelist' ? this.newSafeIP : this.newDenyIP;
            const reason = listType === 'safelist' ? this.newSafeIPReason : this.newDenyIPReason;
            
            if (!ip) {
                alert('Please enter an IP address');
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'add_ip');
                formData.append('ip', ip);
                formData.append('list_type', listType);
                formData.append('reason', reason);
                
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Clear inputs and reload page
                    if (listType === 'safelist') {
                        this.newSafeIP = '';
                        this.newSafeIPReason = '';
                    } else {
                        this.newDenyIP = '';
                        this.newDenyIPReason = '';
                    }
                    window.location.reload();
                } else {
                    alert(result.error || 'Failed to add IP');
                }
            } catch (error) {
                console.error('Error adding IP:', error);
                alert('Error adding IP');
            }
        },
        
        async removeFromIPList(id) {
            if (!confirm('Are you sure you want to remove this IP?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'remove_ip');
                formData.append('id', id);
                
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.error || 'Failed to remove IP');
                }
            } catch (error) {
                console.error('Error removing IP:', error);
                alert('Error removing IP');
            }
        },
        
        async addToUsernameList(listType) {
            const username = listType === 'safelist' ? this.newSafeUsername : this.newDenyUsername;
            const reason = listType === 'safelist' ? this.newSafeUsernameReason : this.newDenyUsernameReason;
            
            if (!username) {
                alert('Please enter a username');
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'add_username');
                formData.append('username', username);
                formData.append('list_type', listType);
                formData.append('reason', reason);
                
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Clear inputs and reload page
                    if (listType === 'safelist') {
                        this.newSafeUsername = '';
                        this.newSafeUsernameReason = '';
                    } else {
                        this.newDenyUsername = '';
                        this.newDenyUsernameReason = '';
                    }
                    window.location.reload();
                } else {
                    alert(result.error || 'Failed to add username');
                }
            } catch (error) {
                console.error('Error adding username:', error);
                alert('Error adding username');
            }
        },
        
        async removeFromUsernameList(id) {
            if (!confirm('Are you sure you want to remove this username?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'remove_username');
                formData.append('id', id);
                
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.error || 'Failed to remove username');
                }
            } catch (error) {
                console.error('Error removing username:', error);
                alert('Error removing username');
            }
        },
        
        async clearLockout(id) {
            if (!confirm('Are you sure you want to unlock this user?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'clear_lockout');
                formData.append('id', id);
                
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.location.reload();
                } else {
                    alert('Failed to clear lockout');
                }
            } catch (error) {
                console.error('Error clearing lockout:', error);
                alert('Error clearing lockout');
            }
        }
    }
}
</script>

<?php
// Get the buffered content
$page_content = ob_get_clean();

// Now include the layout with the content
$page_title = 'Login Security Settings';
require_once 'includes/admin-layout.php';
?>
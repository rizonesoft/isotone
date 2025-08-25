<?php
/**
 * Admin Page Template
 * 
 * This is the standard template for all Isotone admin pages.
 * Based on the security-login.php design pattern.
 * 
 * Usage:
 * 1. Copy this template to create a new admin page
 * 2. Update the $page_title and $breadcrumbs
 * 3. Replace the content sections with your functionality
 * 4. Keep the structure and styling consistent
 * 
 * @package Isotone
 */

// Required authentication
require_once dirname(dirname(__DIR__)) . '/iso-admin/auth.php';
requireRole('admin'); // Change to appropriate role if needed

// Required includes
require_once dirname(dirname(__DIR__)) . '/config.php';
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
require_once dirname(dirname(__DIR__)) . '/iso-includes/database.php';
require_once dirname(dirname(__DIR__)) . '/iso-includes/icon-functions.php';

// Initialize database connection
isotone_db_connect();

use RedBeanPHP\R;

// Preload icons for this page
iso_preload_icons([
    // Page header icon - change to match your page
    ['name' => 'cog-6-tooth', 'style' => 'outline'], // Example icon
    // Info card icons (Security/Status style)
    ['name' => 'lock-closed', 'style' => 'outline'],
    ['name' => 'exclamation-circle', 'style' => 'outline'],
    ['name' => 'shield-exclamation', 'style' => 'outline'],
    ['name' => 'clock', 'style' => 'outline'],
    // Action icons
    ['name' => 'plus', 'style' => 'outline'],
    ['name' => 'trash', 'style' => 'outline'],
    ['name' => 'pencil', 'style' => 'outline'],
    // Info icon for components link
    ['name' => 'information-circle', 'style' => 'micro'],
    // Message icons
    ['name' => 'x-circle', 'style' => 'outline'],
    ['name' => 'x-mark', 'style' => 'outline'],
    // Card header icons
    ['name' => 'chart-bar', 'style' => 'micro'],
    ['name' => 'users', 'style' => 'micro'],
    ['name' => 'cog', 'style' => 'micro'],
    ['name' => 'arrow-path', 'style' => 'micro'],
    ['name' => 'plus-circle', 'style' => 'micro'],
]);

// Handle AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    
    // CSRF verification for AJAX
    if (!iso_verify_csrf()) {
        echo json_encode(['success' => false, 'error' => 'CSRF token validation failed']);
        exit;
    }
    
    // Handle your AJAX actions here
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'example_action':
                // Your AJAX handler code
                echo json_encode(['success' => true, 'message' => 'Action completed']);
                break;
                
            default:
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
    }
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!iso_verify_csrf()) {
        $_SESSION['error_message'] = 'Security validation failed. Please try again.';
    } else {
        // Handle your form submissions here
        $_SESSION['success_message'] = 'Settings saved successfully!';
    }
    
    // Redirect to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch data for the page - Examples only
// Replace these with your actual data queries

// Start output buffering for content
ob_start();
?>

<!-- Alpine.js component for the page -->
<div x-data="pageComponent()" x-init="init()">
    
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-4">
            <span class="shield-pulse flex-shrink-0">
                <?php echo iso_get_icon('cog-6-tooth', 'outline', ['class' => 'w-10 h-10 text-cyan-500'], false); ?>
            </span>
            <span>Page Title</span>
        </h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
            Brief description of what this page does
        </p>
        <!-- Components Reference Link -->
        <div class="mt-4 alert alert-info">
            <div class="alert-icon">
                <?php echo iso_get_icon('information-circle', 'micro', ['class' => 'w-5 h-5'], false); ?>
            </div>
            <div class="alert-content">
                <p>
                    This is a template page. View the 
                    <a href="/isotone/iso-admin/lumina/admin-components-showcase.php">Components Showcase</a> 
                    for all available UI components and their proper styling.
                </p>
            </div>
        </div>
    </div>
    
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6 animate-slideDown">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <?php echo iso_get_icon('check-circle', 'outline', ['class' => 'w-6 h-6 text-green-600 dark:text-green-400'], false); ?>
            </div>
            <div class="ml-3">
                <p class="text-green-800 dark:text-green-200"><?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
            </div>
            <button onclick="this.parentElement.parentElement.style.display='none'" class="ml-4 flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <?php echo iso_get_icon('x-mark', 'outline', ['class' => 'w-5 h-5'], false); ?>
            </button>
        </div>
    </div>
    <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6 animate-slideDown">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <?php echo iso_get_icon('x-circle', 'outline', ['class' => 'w-6 h-6 text-red-600 dark:text-red-400'], false); ?>
            </div>
            <div class="ml-3">
                <p class="text-red-800 dark:text-red-200"><?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
            </div>
            <button onclick="this.parentElement.parentElement.style.display='none'" class="ml-4 flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <?php echo iso_get_icon('x-mark', 'outline', ['class' => 'w-5 h-5'], false); ?>
            </button>
        </div>
    </div>
    <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    
    <!-- Info Cards Grid (Security/Status Style) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        
        <!-- Info Card 1 -->
        <div class="info-card info-red">
            <div class="info-card-content">
                <div class="info-card-icon">
                    <?php echo iso_get_icon('lock-closed', 'outline', [], false); ?>
                </div>
                <div class="info-card-body">
                    <p class="info-card-label">Active Lockouts</p>
                    <p class="info-card-value">0</p>
                </div>
            </div>
        </div>
        
        <!-- Info Card 2 -->
        <div class="info-card info-yellow">
            <div class="info-card-content">
                <div class="info-card-icon">
                    <?php echo iso_get_icon('exclamation-circle', 'outline', [], false); ?>
                </div>
                <div class="info-card-body">
                    <p class="info-card-label">Blocked (24h)</p>
                    <p class="info-card-value">1</p>
                </div>
            </div>
        </div>
        
        <!-- Info Card 3 -->
        <div class="info-card info-blue">
            <div class="info-card-content">
                <div class="info-card-icon">
                    <?php echo iso_get_icon('shield-exclamation', 'outline', [], false); ?>
                </div>
                <div class="info-card-body">
                    <p class="info-card-label">Blocked (7d)</p>
                    <p class="info-card-value">10</p>
                </div>
            </div>
        </div>
        
        <!-- Info Card 4 -->
        <div class="info-card info-green">
            <div class="info-card-content">
                <div class="info-card-icon">
                    <?php echo iso_get_icon('clock', 'outline', [], false); ?>
                </div>
                <div class="info-card-body">
                    <p class="info-card-label">Max Attempts</p>
                    <p class="info-card-value">5</p>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <!-- Left Column -->
        <div class="content-card">
            <div class="content-card-header">
                <h2>
                    <span class="content-card-header-icon">
                        <?php echo iso_get_icon('chart-bar', 'micro', [], false); ?>
                    </span>
                    Section Title
                </h2>
                <div class="content-card-header-actions">
                    <span class="content-card-header-badge">12</span>
                    <button class="content-card-header-action">
                        <?php echo iso_get_icon('arrow-path', 'micro', [], false); ?>
                    </button>
                </div>
            </div>
            <div class="content-card-body">
                <!-- Your content here -->
                <p class="text-gray-600 dark:text-gray-400">Content goes here...</p>
            </div>
        </div>
        
        <!-- Right Column -->
        <div class="content-card">
            <div class="content-card-header">
                <h2>
                    <span class="content-card-header-icon">
                        <?php echo iso_get_icon('users', 'micro', [], false); ?>
                    </span>
                    Another Section
                </h2>
                <div class="content-card-header-actions">
                    <a href="#" class="content-card-header-action">
                        <?php echo iso_get_icon('plus-circle', 'micro', [], false); ?>
                        Add New
                    </a>
                </div>
            </div>
            <div class="content-card-body">
                <!-- Your content here -->
                <p class="text-gray-600 dark:text-gray-400">More content...</p>
            </div>
        </div>
        
    </div>
    
    <!-- Tabbed Content (Optional) -->
    <div class="tab-card mb-8">
        <div class="tab-nav">
            <nav class="flex -mb-px">
                <button @click="activeTab = 'tab1'" 
                        :class="{'border-cyan-500 text-cyan-600 dark:text-cyan-400': activeTab === 'tab1', 
                                'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300': activeTab !== 'tab1'}"
                        class="px-6 py-3 border-b-2 font-medium text-sm transition-colors">
                    Tab 1
                </button>
                <button @click="activeTab = 'tab2'"
                        :class="{'border-cyan-500 text-cyan-600 dark:text-cyan-400': activeTab === 'tab2',
                                'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300': activeTab !== 'tab2'}"
                        class="px-6 py-3 border-b-2 font-medium text-sm transition-colors">
                    Tab 2
                </button>
            </nav>
        </div>
        
        <!-- Tab Content -->
        <div class="content-card-body">
            <div x-show="activeTab === 'tab1'" x-transition>
                <!-- Tab 1 Content -->
                <p class="text-gray-600 dark:text-gray-400">Tab 1 content...</p>
            </div>
            
            <div x-show="activeTab === 'tab2'" x-transition>
                <!-- Tab 2 Content -->
                <p class="text-gray-600 dark:text-gray-400">Tab 2 content...</p>
            </div>
        </div>
    </div>
    
    <!-- Form Example -->
    <div class="form-card">
        <div class="content-card-header">
            <h2>
                <span class="content-card-header-icon">
                    <?php echo iso_get_icon('cog', 'micro', [], false); ?>
                </span>
                Settings Form
            </h2>
        </div>
        <form method="POST" action="">
            <?php echo iso_csrf_field(); ?>
            
            <div class="content-card-body space-y-6">
                <!-- Form Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Field Label
                    </label>
                    <input type="text" name="field_name" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                  focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Help text for this field.
                    </p>
                </div>
            </div>
            
            <!-- Form Footer with Actions -->
            <div class="form-card-footer flex justify-between items-center">
                <div x-show="saveMessage" x-text="saveMessage" 
                     :class="saveSuccess ? 'text-green-600' : 'text-red-600'"
                     class="text-sm font-medium"></div>
                <button type="submit" 
                        :disabled="loading"
                        class="px-6 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 
                               transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!loading">Save Settings</span>
                    <span x-show="loading">Saving...</span>
                </button>
            </div>
        </form>
    </div>
    
    <!-- Chart Components Section -->
    <div class="mt-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Chart Components</h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Chart Card 1 -->
            <div class="content-card">
                <div class="content-card-header">
                    <h3>
                        <span class="content-card-header-icon">
                            <?php echo iso_get_icon('chart-bar', 'micro', [], false); ?>
                        </span>
                        Revenue Chart
                    </h3>
                    <div class="content-card-header-actions">
                        <span class="content-card-header-badge">Live</span>
                    </div>
                </div>
                <div class="content-card-body">
                    <div class="chart-container">
                        <!-- Loading state demo using Orbital spinner -->
                        <div class="loading-overlay" id="chart1-loading">
                            <div class="loading-container">
                                <!-- Orbital spinner for Revenue Chart -->
                                <div class="spinner-orbit">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                                <div class="loading-text">Loading revenue data...</div>
                            </div>
                        </div>
                        <canvas id="chart1"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Chart Card 2 -->
            <div class="content-card">
                <div class="content-card-header">
                    <h3>
                        <span class="content-card-header-icon">
                            <?php echo iso_get_icon('chart-pie', 'micro', [], false); ?>
                        </span>
                        Distribution Chart
                    </h3>
                    <div class="content-card-header-actions">
                        <button class="content-card-header-action" onclick="refreshDistributionChart()">
                            <?php echo iso_get_icon('arrow-path', 'micro', [], false); ?>
                            Refresh
                        </button>
                    </div>
                </div>
                <div class="content-card-body">
                    <div class="chart-container">
                        <!-- Loading state demo using Ripple spinner -->
                        <div class="loading-overlay" id="chart2-loading" style="display: none;">
                            <div class="loading-container">
                                <!-- Ripple spinner for Distribution Chart -->
                                <div class="spinner-ripple">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                                <div class="loading-text">Loading distribution data...</div>
                            </div>
                        </div>
                        <canvas id="chart2"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Alert Components Section -->
    <div class="mt-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Alert Components</h2>
        
        <!-- Alert demo container -->
        <div class="content-card">
            <div class="content-card-header">
                <h3>
                    <span class="content-card-header-icon">
                        <?php echo iso_get_icon('bell-alert', 'micro', [], false); ?>
                    </span>
                    Interactive Alert Demonstrations
                </h3>
            </div>
            <div class="content-card-body">
                <!-- Buttons to trigger different alerts -->
                <div class="flex flex-wrap gap-3 mb-6">
                    <button onclick="showAlert('success')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        <?php echo iso_get_icon('check-circle', 'micro', ['class' => 'inline h-4 w-4 mr-1'], false); ?>
                        Show Success
                    </button>
                    <button onclick="showAlert('error')" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        <?php echo iso_get_icon('x-circle', 'micro', ['class' => 'inline h-4 w-4 mr-1'], false); ?>
                        Show Error
                    </button>
                    <button onclick="showAlert('info')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <?php echo iso_get_icon('information-circle', 'micro', ['class' => 'inline h-4 w-4 mr-1'], false); ?>
                        Show Info
                    </button>
                    <button onclick="showAlert('warning')" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition">
                        <?php echo iso_get_icon('exclamation-triangle', 'micro', ['class' => 'inline h-4 w-4 mr-1'], false); ?>
                        Show Warning
                    </button>
                </div>
                
                <!-- Alert display area -->
                <div id="alert-container" class="space-y-3">
                    <!-- Alerts will be shown here dynamically -->
                    <p class="text-gray-500 dark:text-gray-400 text-sm italic">Click a button above to display an alert message</p>
                </div>
            </div>
        </div>
        
        <!-- Script for alert demonstrations -->
        <script>
        function showAlert(type) {
            const container = document.getElementById('alert-container');
            let alertHTML = '';
            
            // Generate random ID for dismissible alerts
            const alertId = 'alert-' + Date.now();
            
            switch(type) {
                case 'success':
                    alertHTML = `
                        <div id="${alertId}" class="alert alert-success">
                            <div class="alert-icon">
                                <?php echo iso_get_icon('check-circle', 'micro', [], false); ?>
                            </div>
                            <div class="alert-content">
                                <p><strong>Success!</strong> Your changes have been saved successfully. <a href="#" onclick="event.preventDefault(); alert('View changes clicked')">View changes</a> or <a href="#" onclick="event.preventDefault(); alert('Continue editing clicked')">continue editing</a>.</p>
                            </div>
                            <button onclick="dismissAlert('${alertId}')" class="alert-close ml-auto flex-shrink-0">
                                <?php echo iso_get_icon('x-mark', 'micro', ['class' => 'h-4 w-4'], false); ?>
                            </button>
                        </div>
                    `;
                    break;
                    
                case 'error':
                    alertHTML = `
                        <div id="${alertId}" class="alert alert-error">
                            <div class="alert-icon">
                                <?php echo iso_get_icon('x-circle', 'micro', [], false); ?>
                            </div>
                            <div class="alert-content">
                                <p><strong>Error occurred!</strong> Unable to process your request. <a href="#" onclick="event.preventDefault(); alert('View error log clicked')">View error log</a> or <a href="#" onclick="event.preventDefault(); alert('Contact support clicked')">contact support</a>.</p>
                            </div>
                            <button onclick="dismissAlert('${alertId}')" class="alert-close ml-auto flex-shrink-0">
                                <?php echo iso_get_icon('x-mark', 'micro', ['class' => 'h-4 w-4'], false); ?>
                            </button>
                        </div>
                    `;
                    break;
                    
                case 'info':
                    alertHTML = `
                        <div id="${alertId}" class="alert alert-info">
                            <div class="alert-icon">
                                <?php echo iso_get_icon('information-circle', 'micro', [], false); ?>
                            </div>
                            <div class="alert-content">
                                <p><strong>Information:</strong> System maintenance scheduled for tonight. <a href="#" onclick="event.preventDefault(); alert('Learn more clicked')">Learn more</a> about the updates.</p>
                            </div>
                            <button onclick="dismissAlert('${alertId}')" class="alert-close ml-auto flex-shrink-0">
                                <?php echo iso_get_icon('x-mark', 'micro', ['class' => 'h-4 w-4'], false); ?>
                            </button>
                        </div>
                    `;
                    break;
                    
                case 'warning':
                    alertHTML = `
                        <div id="${alertId}" class="alert alert-warning">
                            <div class="alert-icon">
                                <?php echo iso_get_icon('exclamation-triangle', 'micro', [], false); ?>
                            </div>
                            <div class="alert-content">
                                <p><strong>Warning:</strong> Your session will expire in 5 minutes. <a href="#" onclick="event.preventDefault(); alert('Save work clicked')">Save your work</a> or <a href="#" onclick="event.preventDefault(); alert('Extend session clicked')">extend session</a>.</p>
                            </div>
                            <button onclick="dismissAlert('${alertId}')" class="alert-close ml-auto flex-shrink-0">
                                <?php echo iso_get_icon('x-mark', 'micro', ['class' => 'h-4 w-4'], false); ?>
                            </button>
                        </div>
                    `;
                    break;
            }
            
            // Clear placeholder text if it exists
            if (container.querySelector('.text-gray-500')) {
                container.innerHTML = '';
            }
            
            // Add the new alert with fade-in animation
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = alertHTML;
            const alertElement = tempDiv.firstElementChild;
            alertElement.style.opacity = '0';
            alertElement.style.transform = 'translateY(-10px)';
            alertElement.style.transition = 'all 0.3s ease';
            
            container.insertBefore(alertElement, container.firstChild);
            
            // Trigger animation
            setTimeout(() => {
                alertElement.style.opacity = '1';
                alertElement.style.transform = 'translateY(0)';
            }, 10);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                dismissAlert(alertId);
            }, 5000);
        }
        
        function dismissAlert(alertId) {
            const alert = document.getElementById(alertId);
            if (alert) {
                // Fade out animation
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                
                // Remove after animation
                setTimeout(() => {
                    alert.remove();
                    
                    // Show placeholder if no alerts remain
                    const container = document.getElementById('alert-container');
                    if (container.children.length === 0) {
                        container.innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-sm italic">Click a button above to display an alert message</p>';
                    }
                }, 300);
            }
        }
        </script>
    </div>
    
    <!-- Skeleton Loaders Section -->
    <div class="mt-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Skeleton Loaders</h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Skeleton Text Demo -->
            <div class="content-card">
                <div class="content-card-header">
                    <h3>
                        <span class="content-card-header-icon">
                            <?php echo iso_get_icon('document-text', 'micro', [], false); ?>
                        </span>
                        Text Content Loading
                    </h3>
                    <div class="content-card-header-actions">
                        <button onclick="toggleSkeleton('skeleton-text-demo')" class="content-card-header-action">
                            <?php echo iso_get_icon('arrow-path', 'micro', [], false); ?>
                            Toggle
                        </button>
                    </div>
                </div>
                <div class="content-card-body" id="skeleton-text-demo">
                    <!-- Skeleton state -->
                    <div class="skeleton-state">
                        <div class="skeleton skeleton-title"></div>
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text" style="width: 80%;"></div>
                    </div>
                    <!-- Loaded state (hidden by default) -->
                    <div class="loaded-state" style="display: none;">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Article Title</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            This is the actual content that appears after loading. 
                            It replaces the skeleton loader to provide a smooth transition.
                            The skeleton loader gives users a preview of the content structure.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Skeleton Card Demo -->
            <div class="content-card">
                <div class="content-card-header">
                    <h3>
                        <span class="content-card-header-icon">
                            <?php echo iso_get_icon('user-circle', 'micro', [], false); ?>
                        </span>
                        User Profile Loading
                    </h3>
                    <div class="content-card-header-actions">
                        <button onclick="toggleSkeleton('skeleton-profile-demo')" class="content-card-header-action">
                            <?php echo iso_get_icon('arrow-path', 'micro', [], false); ?>
                            Toggle
                        </button>
                    </div>
                </div>
                <div class="content-card-body" id="skeleton-profile-demo">
                    <!-- Skeleton state -->
                    <div class="skeleton-state">
                        <div class="flex items-center space-x-4">
                            <div class="skeleton skeleton-avatar"></div>
                            <div class="flex-1">
                                <div class="skeleton skeleton-text" style="width: 150px; margin-bottom: 8px;"></div>
                                <div class="skeleton skeleton-text" style="width: 200px;"></div>
                            </div>
                        </div>
                        <div class="mt-4 flex gap-2">
                            <div class="skeleton skeleton-button"></div>
                            <div class="skeleton skeleton-button"></div>
                        </div>
                    </div>
                    <!-- Loaded state (hidden by default) -->
                    <div class="loaded-state" style="display: none;">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                                JD
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 dark:text-white">John Doe</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">john.doe@example.com</p>
                            </div>
                        </div>
                        <div class="mt-4 flex gap-2">
                            <button class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition">
                                View Profile
                            </button>
                            <button class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Message
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading States for Components Section -->
    <div class="mt-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Component Loading States</h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Metric Card Loading Demo -->
            <div class="content-card">
                <div class="content-card-header">
                    <h3>
                        <span class="content-card-header-icon">
                            <?php echo iso_get_icon('chart-bar-square', 'micro', [], false); ?>
                        </span>
                        Metric Card Loading
                    </h3>
                    <div class="content-card-header-actions">
                        <button onclick="toggleMetricLoading()" class="content-card-header-action">
                            <?php echo iso_get_icon('arrow-path', 'micro', [], false); ?>
                            Toggle Loading
                        </button>
                    </div>
                </div>
                <div class="content-card-body">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="metric-card" id="metric-card-1">
                            <div class="metric-card-label">Total Revenue</div>
                            <div class="metric-card-value">$45,231</div>
                            <div class="metric-card-change text-green-600">+12.5%</div>
                        </div>
                        <div class="metric-card" id="metric-card-2">
                            <div class="metric-card-label">Active Users</div>
                            <div class="metric-card-value">1,234</div>
                            <div class="metric-card-change text-red-600">-3.2%</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Progress Striped Animation Demo -->
            <div class="content-card">
                <div class="content-card-header">
                    <h3>
                        <span class="content-card-header-icon">
                            <?php echo iso_get_icon('arrow-trending-up', 'micro', [], false); ?>
                        </span>
                        Animated Progress Bars
                    </h3>
                </div>
                <div class="content-card-body space-y-4">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Upload Progress</span>
                            <span class="text-sm text-gray-500">65%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-cyan-600 h-2.5 rounded-full progress-striped" style="width: 65%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Processing</span>
                            <span class="text-sm text-gray-500">40%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-blue-600 h-2.5 rounded-full progress-striped" style="width: 40%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Optimization</span>
                            <span class="text-sm text-gray-500">85%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-green-600 h-2.5 rounded-full progress-striped" style="width: 85%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Status Indicators Section -->
    <div class="mt-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Status Indicators</h2>
        
        <div class="content-card">
            <div class="content-card-header">
                <h3>
                    <span class="content-card-header-icon">
                        <?php echo iso_get_icon('signal', 'micro', [], false); ?>
                    </span>
                    Live Status Indicators
                </h3>
            </div>
            <div class="content-card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Active Status -->
                    <div class="flex items-center space-x-3 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <span class="status-indicator active"></span>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Server Active</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">All systems operational</p>
                        </div>
                    </div>
                    
                    <!-- Warning Status -->
                    <div class="flex items-center space-x-3 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <span class="status-indicator warning"></span>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">High Load</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Response time increased</p>
                        </div>
                    </div>
                    
                    <!-- Error Status -->
                    <div class="flex items-center space-x-3 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <span class="status-indicator error"></span>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Database Error</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Connection failed</p>
                        </div>
                    </div>
                    
                    <!-- Inactive Status -->
                    <div class="flex items-center space-x-3 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <span class="status-indicator inactive"></span>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Maintenance</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Service offline</p>
                        </div>
                    </div>
                </div>
                
                <!-- Live Connection Status Demo -->
                <div class="mt-6 p-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 rounded-lg">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Live Connection Status</h4>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">API Gateway</span>
                            <span class="status-indicator active"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Database Cluster</span>
                            <span class="status-indicator active"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Cache Server</span>
                            <span class="status-indicator warning"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">CDN</span>
                            <span class="status-indicator active"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Responsive Animations Section -->
    <div class="mt-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Responsive Animations</h2>
        
        <div class="content-card">
            <div class="content-card-header">
                <h3>
                    <span class="content-card-header-icon">
                        <?php echo iso_get_icon('adjustments-horizontal', 'micro', [], false); ?>
                    </span>
                    Animation Preferences
                </h3>
            </div>
            <div class="content-card-body">
                <!-- Reduced Motion Info -->
                <div class="alert alert-info mb-6">
                    <div class="alert-icon">
                        <?php echo iso_get_icon('information-circle', 'micro', [], false); ?>
                    </div>
                    <div class="alert-content">
                        <p><strong>Accessibility Feature:</strong> All animations respect the user's <code>prefers-reduced-motion</code> setting. 
                        If reduced motion is enabled in your OS/browser, animations will be minimized or disabled.</p>
                    </div>
                </div>
                
                <!-- Animation Speed Demo -->
                <div class="space-y-4">
                    <h4 class="font-semibold text-gray-900 dark:text-white">Test Animation Responsiveness</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Normal Animation -->
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="flex justify-center mb-2">
                                <div class="spinner spinner-sm"></div>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Normal Speed</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">0.8s rotation</p>
                        </div>
                        
                        <!-- Slow Animation for Demonstration -->
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="flex justify-center mb-2">
                                <div class="spinner spinner-sm" style="animation-duration: 2s;"></div>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Slow Animation</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">2s rotation</p>
                        </div>
                        
                        <!-- Fast Animation for Demonstration -->
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="flex justify-center mb-2">
                                <div class="spinner spinner-sm" style="animation-duration: 0.3s;"></div>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Fast Animation</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">0.3s rotation</p>
                        </div>
                    </div>
                    
                    <!-- Motion Preference Detection -->
                    <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <h4 class="font-semibold text-blue-900 dark:text-blue-200 mb-2">Your Current Motion Preference</h4>
                        <p class="text-sm text-blue-700 dark:text-blue-300" id="motion-preference">
                            Checking motion preference...
                        </p>
                    </div>
                    
                    <!-- Browser/OS Settings Guide -->
                    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">How to Test Reduced Motion</h4>
                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• <strong>Windows:</strong> Settings → Accessibility → Visual effects → Animation effects</li>
                            <li>• <strong>macOS:</strong> System Preferences → Accessibility → Display → Reduce motion</li>
                            <li>• <strong>iOS:</strong> Settings → Accessibility → Motion → Reduce Motion</li>
                            <li>• <strong>Android:</strong> Settings → Accessibility → Remove animations</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Spinner Showcase Section -->
    <div class="mt-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Advanced Spinners & Loaders</h2>
        
        <div class="content-card">
            <div class="content-card-header">
                <h3>
                    <span class="content-card-header-icon">
                        <?php echo iso_get_icon('sparkles', 'micro', [], false); ?>
                    </span>
                    Spinner Collection
                </h3>
            </div>
            <div class="content-card-body">
                <!-- Custom responsive grid -->
                <style>
                    .spinner-showcase-grid {
                        display: grid;
                        grid-template-columns: repeat(2, 1fr);
                        gap: 2rem;
                    }
                    @media (min-width: 640px) {
                        .spinner-showcase-grid {
                            grid-template-columns: repeat(3, 1fr);
                        }
                    }
                    @media (min-width: 1024px) {
                        .spinner-showcase-grid {
                            grid-template-columns: repeat(6, 1fr);
                        }
                    }
                </style>
                <div class="spinner-showcase-grid">
                    <!-- Basic Spinner -->
                    <div class="text-center">
                            <div class="flex justify-center items-center h-16 mb-2">
                                <div class="spinner"></div>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Basic</p>
                        </div>
                        
                        <!-- Dual Ring Spinner -->
                        <div class="text-center">
                            <div class="flex justify-center items-center h-16 mb-2">
                                <div class="spinner-dual"></div>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Dual Ring</p>
                        </div>
                        
                        <!-- Orbital Spinner -->
                        <div class="text-center">
                            <div class="flex justify-center items-center h-16 mb-2">
                                <div class="spinner-orbit">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Orbital</p>
                        </div>
                        
                        <!-- Ripple Spinner -->
                        <div class="text-center">
                            <div class="flex justify-center items-center h-16 mb-2">
                                <div class="spinner-ripple">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Ripple</p>
                        </div>
                        
                        <!-- Pulse Loader -->
                        <div class="text-center">
                            <div class="flex justify-center items-center h-16 mb-2">
                                <div class="loader-pulse"></div>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Pulse</p>
                        </div>
                        
                        <!-- Dots Loader -->
                        <div class="text-center">
                            <div class="flex justify-center items-center h-16 mb-2">
                                <div class="loader-dots">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Dots</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Chart Initialization Script -->
    <script>
    // Toggle skeleton loading states
    function toggleSkeleton(containerId) {
        const container = document.getElementById(containerId);
        const skeletonState = container.querySelector('.skeleton-state');
        const loadedState = container.querySelector('.loaded-state');
        
        if (skeletonState.style.display === 'none') {
            // Show skeleton, hide content
            skeletonState.style.display = 'block';
            loadedState.style.display = 'none';
            
            // Simulate loading and auto-switch after 2 seconds
            setTimeout(() => {
                skeletonState.style.display = 'none';
                loadedState.style.display = 'block';
            }, 2000);
        } else {
            // Show content immediately
            skeletonState.style.display = 'none';
            loadedState.style.display = 'block';
        }
    }
    
    // Toggle metric card loading states
    function toggleMetricLoading() {
        const card1 = document.getElementById('metric-card-1');
        const card2 = document.getElementById('metric-card-2');
        
        // Toggle loading class
        card1.classList.toggle('loading');
        card2.classList.toggle('loading');
        
        // If loading was added, remove it after 2 seconds
        if (card1.classList.contains('loading')) {
            setTimeout(() => {
                card1.classList.remove('loading');
                card2.classList.remove('loading');
            }, 2000);
        }
    }
    
    // Check motion preference
    function checkMotionPreference() {
        const motionPrefElement = document.getElementById('motion-preference');
        if (motionPrefElement) {
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            
            if (prefersReducedMotion) {
                motionPrefElement.innerHTML = `
                    <strong class="text-green-700 dark:text-green-300">✓ Reduced Motion is ENABLED</strong><br>
                    Animations are minimized to respect your accessibility preferences.
                `;
            } else {
                motionPrefElement.innerHTML = `
                    <strong>Standard Motion is ACTIVE</strong><br>
                    All animations are running at normal speed. You can enable reduced motion in your OS settings to minimize animations.
                `;
            }
        }
    }
    
    // Run motion preference check when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            checkMotionPreference();
        });
    } else {
        // DOM already loaded, run immediately
        checkMotionPreference();
    }
    
    // Listen for changes to motion preference
    const motionMediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    motionMediaQuery.addEventListener('change', checkMotionPreference);
    
    document.addEventListener('DOMContentLoaded', function() {
        
        // Simulate loading state for first chart
        setTimeout(() => {
            const loadingEl = document.getElementById('chart1-loading');
            if (loadingEl) {
                loadingEl.style.display = 'none';
            }
            
            // Initialize Chart 1
            const ctx1 = document.getElementById('chart1');
            if (ctx1 && typeof Chart !== 'undefined') {
                // Create gradient fill
                const chartCtx = ctx1.getContext('2d');
                const gradient = chartCtx.createLinearGradient(0, 0, 0, 250);
                gradient.addColorStop(0, 'rgba(6, 182, 212, 0.3)');
                gradient.addColorStop(0.5, 'rgba(6, 182, 212, 0.15)');
                gradient.addColorStop(1, 'rgba(6, 182, 212, 0)');
                
                new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Revenue',
                            data: [12000, 19000, 15000, 25000, 22000, 30000],
                            borderColor: '#06b6d4',
                            backgroundColor: gradient,
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#06b6d4',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
        }, 1500);
        
        // Initialize Chart 2
        const ctx2 = document.getElementById('chart2');
        if (ctx2 && typeof Chart !== 'undefined') {
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: ['Desktop', 'Mobile', 'Tablet'],
                    datasets: [{
                        data: [65, 25, 10],
                        backgroundColor: [
                            '#06b6d4',
                            '#3b82f6',
                            '#8b5cf6'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    });
    
    // Function to refresh Distribution Chart with loading animation
    function refreshDistributionChart() {
        // Show the loading overlay with Ripple spinner
        const loadingEl = document.getElementById('chart2-loading');
        if (loadingEl) {
            loadingEl.style.display = 'flex';
        }
        
        // Simulate data refresh (remove old chart and recreate)
        setTimeout(() => {
            // Hide loading overlay
            if (loadingEl) {
                loadingEl.style.display = 'none';
            }
            
            // Get the canvas and destroy existing chart if it exists
            const ctx2 = document.getElementById('chart2');
            if (ctx2 && typeof Chart !== 'undefined') {
                // Get existing chart instance and destroy it
                const existingChart = Chart.getChart(ctx2);
                if (existingChart) {
                    existingChart.destroy();
                }
                
                // Create new chart with updated data (simulated)
                new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: ['Desktop', 'Mobile', 'Tablet'],
                        datasets: [{
                            data: [Math.floor(Math.random() * 40) + 40, Math.floor(Math.random() * 30) + 20, Math.floor(Math.random() * 20) + 10],
                            backgroundColor: [
                                '#06b6d4',
                                '#3b82f6', 
                                '#8b5cf6'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        }, 1500); // Simulate loading time
    }
    </script>
    
</div>

<!-- Alpine.js Component Script -->
<script>
function pageComponent() {
    return {
        activeTab: 'tab1',
        loading: false,
        saveMessage: '',
        saveSuccess: false,
        
        init() {
            console.log('Page initialized');
            // Initialize any charts, fetch data, etc.
        },
        
        // Add your methods here
        async performAction() {
            this.loading = true;
            
            try {
                const formData = new FormData();
                formData.append('action', 'example_action');
                
                // Add CSRF token for AJAX requests
                const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
                if (csrfToken) {
                    formData.append('csrf_token', csrfToken);
                }
                
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Handle success
                    window.location.reload();
                } else {
                    alert(result.error || 'An error occurred');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>

<?php
// Get the buffered content
$page_content = ob_get_clean();

// Add CSS for admin components
// Lumina CSS is already loaded by admin-layout.php
$page_styles = '';

// Set page configuration
$page_title = 'Lumina UI Template';
$breadcrumbs = [
    ['title' => 'Development', 'url' => '#'],
    ['title' => 'Lumina', 'url' => '']
];

// Include the admin layout
require_once dirname(dirname(__DIR__)) . '/iso-admin/includes/admin-layout.php';
?>
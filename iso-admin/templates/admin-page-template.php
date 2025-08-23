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
require_once dirname(__DIR__) . '/auth.php';
requireRole('admin'); // Change to appropriate role if needed

// Required includes
require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once dirname(__DIR__, 2) . '/iso-includes/database.php';
require_once dirname(__DIR__, 2) . '/iso-includes/icon-functions.php';

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
                    <a href="admin-components-showcase.php">Components Showcase</a> 
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
$page_styles = '<link rel="stylesheet" href="../css/admin-components.css">';

// Set page configuration
$page_title = 'Page Title'; // Change this
$breadcrumbs = [
    ['title' => 'Section', 'url' => '/isotone/iso-admin/section.php'],
    ['title' => 'Current Page', 'url' => '']
];

// Include the admin layout
require_once dirname(__DIR__) . '/includes/admin-layout.php';
?>
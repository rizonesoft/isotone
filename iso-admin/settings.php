<?php
/**
 * General Settings Page
 */

// Check authentication
require_once 'auth.php';

// Load configuration and database
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

use RedBeanPHP\R;

// Initialize database connection
if (!R::testConnection()) {
    $host = defined('DB_HOST') ? DB_HOST : 'localhost';
    $dbname = defined('DB_NAME') ? DB_NAME : 'isotone_db';
    $user = defined('DB_USER') ? DB_USER : 'root';
    $pass = defined('DB_PASSWORD') ? DB_PASSWORD : '';
    
    try {
        R::setup("mysql:host=$host;dbname=$dbname", $user, $pass);
    } catch (Exception $e) {
        die('Database connection failed: ' . $e->getMessage());
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tab = $_POST['tab'] ?? 'general';
    $response = ['success' => false, 'message' => ''];
    
    try {
        switch ($tab) {
            case 'general':
                $settings = [
                    'site_title' => $_POST['site_title'] ?? '',
                    'site_tagline' => $_POST['site_tagline'] ?? '',
                    'site_url' => $_POST['site_url'] ?? '',
                    'admin_email' => $_POST['admin_email'] ?? '',
                    'timezone' => $_POST['timezone'] ?? 'UTC',
                    'date_format' => $_POST['date_format'] ?? 'Y-m-d',
                    'time_format' => $_POST['time_format'] ?? 'H:i:s',
                    'week_starts_on' => $_POST['week_starts_on'] ?? '1',
                    'language' => $_POST['language'] ?? 'en_US'
                ];
                break;
                
            case 'api':
                $settings = [
                    // AI API Credentials
                    'openai_api_key' => $_POST['openai_api_key'] ?? '',
                    'openai_org_id' => $_POST['openai_org_id'] ?? '',
                    
                    // Function-based Model Selection
                    'toni_chat_model' => $_POST['toni_chat_model'] ?? 'gpt-5-nano',
                    'toni_chat_reasoning_effort' => $_POST['toni_chat_reasoning_effort'] ?? 'minimal',
                    'toni_chat_verbosity' => $_POST['toni_chat_verbosity'] ?? 'medium',
                    
                    // Advanced AI Settings
                    'toni_timeout' => $_POST['toni_timeout'] ?? '30',
                    
                    // Analytics Settings
                    'analytics_provider' => $_POST['analytics_provider'] ?? 'none',
                    'google_analytics_id' => $_POST['google_analytics_id'] ?? '',
                    'matomo_url' => $_POST['matomo_url'] ?? '',
                    'matomo_site_id' => $_POST['matomo_site_id'] ?? ''
                ];
                break;
                
            case 'smtp':
                $settings = [
                    'mail_method' => $_POST['mail_method'] ?? 'mail',
                    'smtp_host' => $_POST['smtp_host'] ?? '',
                    'smtp_port' => $_POST['smtp_port'] ?? '587',
                    'smtp_username' => $_POST['smtp_username'] ?? '',
                    'smtp_password' => $_POST['smtp_password'] ?? '',
                    'smtp_encryption' => $_POST['smtp_encryption'] ?? 'tls',
                    'smtp_from_email' => $_POST['smtp_from_email'] ?? '',
                    'smtp_from_name' => $_POST['smtp_from_name'] ?? '',
                    'email_footer' => $_POST['email_footer'] ?? ''
                ];
                break;
                
            case 'advanced':
                $settings = [
                    'debug_mode' => isset($_POST['debug_mode']) ? '1' : '0',
                    'maintenance_mode' => isset($_POST['maintenance_mode']) ? '1' : '0',
                    'cache_enabled' => isset($_POST['cache_enabled']) ? '1' : '0',
                    'cache_ttl' => $_POST['cache_ttl'] ?? '3600',
                    'max_upload_size' => $_POST['max_upload_size'] ?? '10',
                    'allowed_file_types' => $_POST['allowed_file_types'] ?? 'jpg,jpeg,png,gif,pdf,doc,docx',
                    'posts_per_page' => $_POST['posts_per_page'] ?? '10',
                    'comments_enabled' => isset($_POST['comments_enabled']) ? '1' : '0',
                    'comments_moderation' => isset($_POST['comments_moderation']) ? '1' : '0',
                    'user_registration' => isset($_POST['user_registration']) ? '1' : '0',
                    'default_user_role' => $_POST['default_user_role'] ?? 'subscriber'
                ];
                break;
        }
        
        // Save settings to database
        foreach ($settings as $key => $value) {
            $setting = R::findOne('settings', 'setting_key = ?', [$key]);
            if (!$setting) {
                $setting = R::dispense('settings');
                $setting->setting_key = $key;
            }
            $setting->setting_value = $value;
            $setting->updated_at = date('Y-m-d H:i:s');
            R::store($setting);
        }
        
        $response['success'] = true;
        $response['message'] = 'Settings saved successfully!';
        
    } catch (Exception $e) {
        $response['message'] = 'Error saving settings: ' . $e->getMessage();
    }
    
    // Return JSON response for AJAX requests
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

// Load existing settings
$allSettings = R::findAll('settings');
$settings = [];
foreach ($allSettings as $setting) {
    $settings[$setting->setting_key] = $setting->setting_value;
}

// Get default values
function getSetting($key, $default = '') {
    global $settings;
    return $settings[$key] ?? $default;
}

// Start output buffering for content
ob_start();
?>

<div x-data="settingsPage()" x-init="init()">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold dark:text-white text-gray-900">Settings</h1>
        <p class="mt-2 text-sm dark:text-gray-400 text-gray-600">Configure your site settings and preferences</p>
    </div>

    <!-- Tabs Navigation -->
    <div class="border-b dark:border-gray-700 border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
            <button @click="activeTab = 'general'"
                    :class="activeTab === 'general' 
                        ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400' 
                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300'"
                    class="py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                General
            </button>
            <button @click="activeTab = 'api'"
                    :class="activeTab === 'api' 
                        ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400' 
                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300'"
                    class="py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                API
            </button>
            <button @click="activeTab = 'smtp'"
                    :class="activeTab === 'smtp' 
                        ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400' 
                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300'"
                    class="py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                SMTP
            </button>
            <button @click="activeTab = 'advanced'"
                    :class="activeTab === 'advanced' 
                        ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400' 
                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300'"
                    class="py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                Advanced
            </button>
        </nav>
    </div>

    <!-- Settings Forms -->
    <div class="dark:bg-gray-800 bg-white rounded-lg shadow-sm">
        <form @submit.prevent="saveSettings" class="p-6">
            <!-- General Tab -->
            <?php include __DIR__ . '/includes/settings-tab-general.php'; ?>

            <!-- API Tab -->
            <?php include __DIR__ . '/includes/settings-tab-api.php'; ?>

            <!-- SMTP Tab -->
            <?php include __DIR__ . '/includes/settings-tab-smtp.php'; ?>

            <!-- Advanced Tab -->
            <?php include __DIR__ . '/includes/settings-tab-advanced.php'; ?>

            <!-- Form Actions -->
            <div class="mt-8 pt-6 border-t dark:border-gray-700 border-gray-200 flex justify-between items-center">
                <div x-show="saveMessage" x-text="saveMessage" 
                     :class="saveSuccess ? 'text-green-600' : 'text-red-600'"
                     class="text-sm font-medium"></div>
                <button type="submit" 
                        :disabled="saving"
                        class="px-6 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!saving">Save Settings</span>
                    <span x-show="saving">Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function settingsPage() {
    return {
        activeTab: localStorage.getItem('settingsTab') || '<?php echo isset($_GET['tab']) ? htmlspecialchars($_GET['tab']) : 'general'; ?>',
        aiProvider: '<?php echo getSetting('ai_provider', 'none'); ?>',
        analyticsProvider: '<?php echo getSetting('analytics_provider', 'none'); ?>',
        mailMethod: '<?php echo getSetting('mail_method', 'mail'); ?>',
        saving: false,
        saveMessage: '',
        saveSuccess: false,
        
        init() {
            // Watch for tab changes and save to localStorage
            this.$watch('activeTab', value => {
                localStorage.setItem('settingsTab', value);
                // Update URL without reload
                const url = new URL(window.location);
                url.searchParams.set('tab', value);
                window.history.replaceState({}, '', url);
            });
            
            // Set initial values
            const providerSelect = document.querySelector('select[name="ai_provider"]');
            if (providerSelect) {
                this.aiProvider = providerSelect.value;
            }
            const analyticsSelect = document.querySelector('select[name="analytics_provider"]');
            if (analyticsSelect) {
                this.analyticsProvider = analyticsSelect.value;
            }
            const mailSelect = document.querySelector('select[name="mail_method"]');
            if (mailSelect) {
                this.mailMethod = mailSelect.value;
            }
        },
        
        async saveSettings() {
            this.saving = true;
            this.saveMessage = '';
            
            const form = event.target;
            const formData = new FormData(form);
            formData.append('tab', this.activeTab);
            
            try {
                const response = await fetch('/isotone/iso-admin/settings.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                this.saveSuccess = result.success;
                this.saveMessage = result.message;
                
                if (result.success) {
                    showToast('Settings saved successfully!', 'success');
                } else {
                    showToast(result.message || 'Failed to save settings', 'error');
                }
                
                // Clear message after 3 seconds
                setTimeout(() => {
                    this.saveMessage = '';
                }, 3000);
                
            } catch (error) {
                console.error('Error saving settings:', error);
                this.saveSuccess = false;
                this.saveMessage = 'Error saving settings';
                showToast('Error saving settings', 'error');
            } finally {
                this.saving = false;
            }
        },
        
        async sendTestEmail() {
            // TODO: Implement test email functionality
            showToast('Test email functionality coming soon!', 'info');
        }
    }
}
</script>

<?php
// Get the buffered content
$page_content = ob_get_clean();

// Set page variables
$page_title = 'Settings';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/isotone/iso-admin/'],
    ['title' => 'Settings', 'url' => '']
];

// Include the layout
require_once __DIR__ . '/includes/admin-layout.php';
?>
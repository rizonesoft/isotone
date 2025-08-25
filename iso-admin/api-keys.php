<?php
/**
 * API Key Management Page
 * Manage API keys for external integrations
 * 
 * @package Isotone
 * @since 0.3.3
 */

// Required authentication (this starts the session)
require_once 'auth.php';
requireRole('admin');

// Required includes
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/iso-includes/database.php';
require_once dirname(__DIR__) . '/iso-includes/icon-functions.php';
require_once dirname(__DIR__) . '/iso-includes/class-security.php';
require_once dirname(__DIR__) . '/iso-includes/class-apiauth.php';

// Initialize database connection
isotone_db_connect();

use RedBeanPHP\R;

// Preload icons for this page
iso_preload_icons([
    ['name' => 'key', 'style' => 'outline'],
    ['name' => 'plus', 'style' => 'outline'],
    ['name' => 'trash', 'style' => 'outline'],
    ['name' => 'eye', 'style' => 'outline'],
    ['name' => 'eye-slash', 'style' => 'outline'],
    ['name' => 'clipboard-document', 'style' => 'outline'],
    ['name' => 'check-circle', 'style' => 'outline'],
    ['name' => 'x-circle', 'style' => 'outline'],
    ['name' => 'clock', 'style' => 'outline'],
    ['name' => 'shield-check', 'style' => 'outline'],
    ['name' => 'plus', 'style' => 'micro'],
    ['name' => 'x-mark', 'style' => 'micro'],
]);

// Get current user
$current_user_id = $_SESSION['isotone_admin_user_id'] ?? 1;
$current_user = $_SESSION['isotone_admin_user'] ?? 'admin';

// Capture any new API key from session before processing forms
$new_api_key = null;
if (isset($_SESSION['new_api_key'])) {
    $new_api_key = $_SESSION['new_api_key'];
    // Don't unset yet - we'll do it after displaying
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!iso_verify_csrf()) {
        $_SESSION['error_message'] = 'CSRF token validation failed. Please refresh and try again.';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            // Generate new API key
            $prefix = $_POST['key_type'] ?? 'live';
            $api_key = IsotoneAPIAuth::generateApiKey($prefix);
            
            // Create database record
            $key = R::dispense('apikey');
            $key->user_id = $current_user_id;
            $key->name = $_POST['name'] ?? 'Unnamed Key';
            $key->key_hash = password_hash($api_key, PASSWORD_BCRYPT);
            $key->permissions = json_encode($_POST['permissions'] ?? ['todos.read']);
            $key->is_active = 1;
            $key->created_at = date('Y-m-d H:i:s');
            
            // Optional expiration
            if (!empty($_POST['expires_at'])) {
                $key->expires_at = $_POST['expires_at'];
            }
            
            // Optional IP whitelist
            if (!empty($_POST['ip_whitelist'])) {
                $ips = array_map('trim', explode("\n", $_POST['ip_whitelist']));
                $key->ip_whitelist = json_encode(array_filter($ips));
            }
            
            try {
                R::store($key);
                $_SESSION['new_api_key'] = $api_key;
                $_SESSION['success_message'] = 'API key created successfully! Copy it now as it won\'t be shown again.';
            } catch (Exception $e) {
                $_SESSION['error_message'] = 'Error creating API key: ' . $e->getMessage();
            }
            break;
            
        case 'revoke':
            $key_id = $_POST['key_id'] ?? 0;
            $key = R::load('apikey', $key_id);
            
            if ($key && $key->user_id == $current_user_id) {
                $key->is_active = 0;
                R::store($key);
                $_SESSION['success_message'] = 'API key revoked successfully.';
            }
            break;
            
        case 'delete':
            $key_id = $_POST['key_id'] ?? 0;
            $key = R::load('apikey', $key_id);
            
            if ($key && $key->user_id == $current_user_id) {
                R::trash($key);
                $_SESSION['success_message'] = 'API key deleted successfully.';
            }
            break;
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Get all API keys for current user
$api_keys = R::find('apikey', 'user_id = ? ORDER BY created_at DESC', [$current_user_id]);

// Available permissions
$available_permissions = [
    'todos.*' => 'Full To-Do access',
    'todos.read' => 'Read To-Dos',
    'todos.write' => 'Create/Update To-Dos',
    'todos.delete' => 'Delete To-Dos',
    'stats.read' => 'Read Statistics',
    'ai.access' => 'Access AI Features',
    'docs.search' => 'Search Documentation',
    '*' => 'Full API Access'
];

// Debug: Check session before output
$debug_has_key = $new_api_key !== null;
$debug_key_value = $new_api_key ?? 'NOT SET';

// Start output buffering
ob_start();
?>

<div x-data="apiKeyManager()" x-init="init()">
    
    <!-- Debug Info (Temporary) -->
    <div class="bg-yellow-100 dark:bg-yellow-900/20 p-4 mb-4 rounded text-sm">
        <p><strong>Debug Info:</strong></p>
        <p>Session has new_api_key? <?php echo $debug_has_key ? 'YES' : 'NO'; ?></p>
        <p>Key value: <?php echo htmlspecialchars($debug_key_value); ?></p>
        <p>Session ID: <?php echo session_id(); ?></p>
        <p>Request Method: <?php echo $_SERVER['REQUEST_METHOD']; ?></p>
    </div>
    
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span class="shield-pulse flex-shrink-0">
                    <?php echo iso_get_icon('key', 'outline', ['class' => 'w-10 h-10 text-cyan-500'], false); ?>
                </span>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        API Keys
                    </h1>
                    <p class="mt-1 text-gray-600 dark:text-gray-400">
                        Manage API keys for external integrations
                    </p>
                </div>
            </div>
            <button @click="showCreateModal = true" class="btn-primary">
                <span class="btn-icon-box">
                    <?php echo iso_get_icon('plus', 'micro', [], false); ?>
                </span>
                <span class="btn-primary-text">New API Key</span>
            </button>
        </div>
    </div>
    
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <?php echo iso_get_icon('check-circle', 'outline', ['class' => 'w-6 h-6 text-green-600 dark:text-green-400'], false); ?>
            </div>
            <div class="ml-3">
                <p class="text-green-800 dark:text-green-200"><?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <?php echo iso_get_icon('x-circle', 'outline', ['class' => 'w-6 h-6 text-red-600 dark:text-red-400'], false); ?>
            </div>
            <div class="ml-3">
                <p class="text-red-800 dark:text-red-200"><?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    
    <!-- New API Key Display -->
    <?php if ($new_api_key): ?>
    <div class="content-card bg-gradient-to-r from-cyan-50 to-blue-50 dark:from-cyan-900/20 dark:to-blue-900/20 border-cyan-500 border-2 mb-6">
        <div class="content-card-header">
            <div class="content-card-header-icon">
                <?php echo iso_get_icon('shield-check', 'outline', ['class' => 'w-6 h-6 text-cyan-600'], false); ?>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">New API Key Created</h3>
        </div>
        <div class="content-card-body">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                Copy this key now. For security reasons, it won't be shown again.
            </p>
            <div class="flex items-center gap-2">
                <input type="text" 
                       value="<?php echo htmlspecialchars($new_api_key); ?>" 
                       readonly
                       id="new-api-key"
                       class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono text-sm">
                <button onclick="copyApiKey()" class="btn-secondary">
                    <?php echo iso_get_icon('clipboard-document', 'outline', ['class' => 'w-4 h-4'], false); ?>
                    Copy
                </button>
            </div>
        </div>
    </div>
    <script>
    function copyApiKey() {
        const input = document.getElementById('new-api-key');
        input.select();
        document.execCommand('copy');
        alert('API key copied to clipboard!');
    }
    </script>
    <?php 
    // Now that we've displayed it, remove from session
    unset($_SESSION['new_api_key']); 
    ?>
    <?php endif; ?>
    
    <!-- API Keys List -->
    <div class="space-y-4">
        <?php if (empty($api_keys)): ?>
            <div class="content-card">
                <div class="content-card-body text-center py-12">
                    <?php echo iso_get_icon('key', 'outline', ['class' => 'w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4'], false); ?>
                    <p class="text-gray-500 dark:text-gray-400">No API keys yet. Create your first key to get started.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($api_keys as $key): 
                $permissions = json_decode($key->permissions, true) ?? [];
                $is_active = $key->is_active;
                $has_expired = $key->expires_at && strtotime($key->expires_at) < time();
            ?>
                <div class="content-card <?php echo !$is_active ? 'opacity-60' : ''; ?> <?php echo $has_expired ? 'border-red-500' : ''; ?>">
                    <div class="content-card-body">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($key->name); ?>
                                    </h3>
                                    <?php if ($is_active && !$has_expired): ?>
                                        <span class="badge badge-green">Active</span>
                                    <?php elseif ($has_expired): ?>
                                        <span class="badge badge-red">Expired</span>
                                    <?php else: ?>
                                        <span class="badge badge-gray">Revoked</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                    <p class="font-mono">
                                        <?php 
                                        $prefix = strpos($key->key_hash, 'iso_test') !== false ? 'iso_test_sk_' : 'iso_live_sk_';
                                        echo $prefix . '****' . substr(md5($key->key_hash), 0, 8);
                                        ?>
                                    </p>
                                    <p>Created: <?php echo date('M d, Y', strtotime($key->created_at)); ?></p>
                                    <?php if ($key->last_used): ?>
                                        <p>Last used: <?php echo date('M d, Y H:i', strtotime($key->last_used)); ?></p>
                                    <?php else: ?>
                                        <p>Never used</p>
                                    <?php endif; ?>
                                    <?php if ($key->expires_at): ?>
                                        <p>Expires: <?php echo date('M d, Y', strtotime($key->expires_at)); ?></p>
                                    <?php endif; ?>
                                    <?php if ($key->usage_count): ?>
                                        <p>Used <?php echo number_format($key->usage_count); ?> times</p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mt-3">
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Permissions:</p>
                                    <div class="flex flex-wrap gap-1">
                                        <?php foreach ($permissions as $perm): ?>
                                            <span class="badge badge-blue text-xs"><?php echo htmlspecialchars($perm); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <?php if ($is_active && !$has_expired): ?>
                                    <form method="POST" class="inline">
                                        <?php echo iso_csrf_field(); ?>
                                        <input type="hidden" name="action" value="revoke">
                                        <input type="hidden" name="key_id" value="<?php echo $key->id; ?>">
                                        <button type="submit" 
                                                class="p-2 text-yellow-500 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
                                                title="Revoke key">
                                            <?php echo iso_get_icon('eye-slash', 'outline', ['class' => 'w-4 h-4'], false); ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <form method="POST" class="inline" onsubmit="return confirm('Delete this API key?');">
                                    <?php echo iso_csrf_field(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="key_id" value="<?php echo $key->id; ?>">
                                    <button type="submit" 
                                            class="p-2 text-red-500 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
                                            title="Delete key">
                                        <?php echo iso_get_icon('trash', 'outline', ['class' => 'w-4 h-4'], false); ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Create API Key Modal -->
    <div x-show="showCreateModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        
        <div class="flex items-center justify-center min-h-screen px-4">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showCreateModal = false"></div>
            
            <!-- Modal -->
            <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-xl max-w-2xl w-full p-6 border border-gray-200 dark:border-gray-800"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100">
                
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Create New API Key</h2>
                
                <form method="POST">
                    <?php echo iso_csrf_field(); ?>
                    <input type="hidden" name="action" value="create">
                    
                    <div class="space-y-4">
                        <!-- Key Name -->
                        <div class="form-group">
                            <label class="form-label">
                                Key Name
                                <span class="form-label-required">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   required 
                                   placeholder="e.g., Claude Code Integration"
                                   class="form-input">
                        </div>
                        
                        <!-- Key Type -->
                        <div class="form-group">
                            <label class="form-label">Key Type</label>
                            <select name="key_type" class="form-input">
                                <option value="live">Production (iso_live_sk_)</option>
                                <option value="test">Testing (iso_test_sk_)</option>
                            </select>
                        </div>
                        
                        <!-- Permissions -->
                        <div class="form-group">
                            <label class="form-label">Permissions</label>
                            <div class="space-y-2">
                                <?php foreach ($available_permissions as $perm => $label): ?>
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                               name="permissions[]" 
                                               value="<?php echo htmlspecialchars($perm); ?>"
                                               <?php echo $perm === 'todos.read' ? 'checked' : ''; ?>
                                               class="mr-2">
                                        <span class="text-sm"><?php echo htmlspecialchars($label); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Expiration (Optional) -->
                        <div class="form-group">
                            <label class="form-label">Expiration Date (Optional)</label>
                            <input type="date" 
                                   name="expires_at" 
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                   class="form-input">
                        </div>
                        
                        <!-- IP Whitelist (Optional) -->
                        <div class="form-group">
                            <label class="form-label">IP Whitelist (Optional)</label>
                            <textarea name="ip_whitelist" 
                                      rows="3" 
                                      placeholder="One IP address per line"
                                      class="form-input"></textarea>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Leave empty to allow all IPs
                            </p>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" @click="showCreateModal = false" class="btn-secondary">
                            Cancel
                        </button>
                        <button type="submit" class="btn-primary">
                            Create API Key
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js Component -->
<script>
function apiKeyManager() {
    return {
        showCreateModal: false,
        
        init() {
            // Any initialization logic
        }
    };
}
</script>

<?php
// Get the buffered content
$page_content = ob_get_clean();

// Set page configuration
$page_title = 'API Keys';
$breadcrumbs = [
    ['title' => 'Settings', 'url' => '/isotone/iso-admin/settings.php'],
    ['title' => 'API Keys', 'url' => '']
];

// Include the admin layout
require_once 'includes/admin-layout.php';
?>
<?php
/**
 * Themes Management Page
 * Handles theme listing, activation, upload, and deletion
 * 
 * @package Isotone
 */

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/app/Services/ThemeService.php';

use RedBeanPHP\R;
use Isotone\Services\ThemeService;

// Initialize database connection
if (!R::testConnection()) {
    R::setup('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
}

// Get current user (temporary - will be replaced with auth system)
$current_user = 'Admin';

// Page configuration
$page_title = 'Themes';
$breadcrumbs = [
    ['title' => 'Appearance'],
    ['title' => 'Themes']
];

// Initialize theme service
$themeService = new ThemeService();

// Handle theme actions
$action_message = '';
$action_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'activate':
            $theme_slug = $_POST['theme'] ?? '';
            if ($themeService->activateTheme($theme_slug)) {
                $action_message = 'Theme activated successfully!';
                $action_type = 'success';
            } else {
                $action_message = 'Failed to activate theme.';
                $action_type = 'error';
            }
            break;
            
        case 'delete':
            $theme_slug = $_POST['theme'] ?? '';
            if ($themeService->deleteTheme($theme_slug)) {
                $action_message = 'Theme deleted successfully!';
                $action_type = 'success';
            } else {
                $action_message = 'Failed to delete theme.';
                $action_type = 'error';
            }
            break;
            
        case 'upload':
            if (isset($_FILES['theme_zip']) && $_FILES['theme_zip']['error'] === UPLOAD_ERR_OK) {
                $result = $themeService->uploadTheme($_FILES['theme_zip']);
                if ($result['success']) {
                    $action_message = 'Theme uploaded successfully!';
                    $action_type = 'success';
                } else {
                    $action_message = 'Upload failed: ' . $result['message'];
                    $action_type = 'error';
                }
            }
            break;
    }
}

// Get all themes
$themes = $themeService->getAllThemes();
$active_theme = $themeService->getActiveTheme();

// Start output buffering for page content
ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-white">Themes</h1>
            <p class="text-gray-400 mt-2">Manage your site's appearance with themes</p>
        </div>
        <button @click="showUploadModal = true" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg transition flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add New Theme
        </button>
    </div>
</div>

<?php if ($action_message): ?>
<div class="mb-6 p-4 rounded-lg <?php echo $action_type === 'success' ? 'bg-green-900 text-green-200' : 'bg-red-900 text-red-200'; ?>">
    <?php echo htmlspecialchars($action_message); ?>
</div>
<?php endif; ?>

<!-- Active Theme -->
<div class="mb-8">
    <h2 class="text-xl font-semibold text-gray-300 mb-4">Active Theme</h2>
    <?php if ($active_theme): ?>
    <div class="bg-gray-800 rounded-lg p-6 border-2 border-cyan-500">
        <div class="flex items-start space-x-6">
            <!-- Theme Screenshot -->
            <div class="flex-shrink-0">
                <?php if (file_exists($active_theme['path'] . '/screenshot.png')): ?>
                    <img src="/isotone/iso-content/themes/<?php echo $active_theme['slug']; ?>/screenshot.png" 
                         alt="<?php echo htmlspecialchars($active_theme['name']); ?>" 
                         class="w-64 h-48 object-cover rounded-lg">
                <?php elseif (file_exists($active_theme['path'] . '/screenshot.svg')): ?>
                    <img src="/isotone/iso-content/themes/<?php echo $active_theme['slug']; ?>/screenshot.svg" 
                         alt="<?php echo htmlspecialchars($active_theme['name']); ?>" 
                         class="w-64 h-48 object-cover rounded-lg">
                <?php else: ?>
                    <div class="w-64 h-48 bg-gradient-to-br from-cyan-500 to-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Theme Info -->
            <div class="flex-1">
                <h3 class="text-2xl font-bold text-white mb-2"><?php echo htmlspecialchars($active_theme['name']); ?></h3>
                <p class="text-gray-400 mb-4"><?php echo htmlspecialchars($active_theme['description']); ?></p>
                
                <div class="flex items-center space-x-6 text-sm text-gray-500">
                    <span>Version <?php echo htmlspecialchars($active_theme['version']); ?></span>
                    <?php if ($active_theme['author']): ?>
                        <span>By <?php echo htmlspecialchars($active_theme['author']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="mt-4 flex space-x-3">
                    <a href="/isotone/iso-admin/customize.php" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded transition">
                        Customize
                    </a>
                    <a href="/isotone/iso-admin/widgets.php" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded transition">
                        Widgets
                    </a>
                    <a href="/isotone/iso-admin/menus.php" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded transition">
                        Menus
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Available Themes -->
<div>
    <h2 class="text-xl font-semibold text-gray-300 mb-4">Available Themes</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($themes as $theme): ?>
            <?php if ($theme['slug'] === $active_theme['slug']) continue; ?>
            
            <div class="bg-gray-800 rounded-lg overflow-hidden hover:shadow-xl transition-shadow" x-data="{ showActions: false }">
                <!-- Theme Screenshot -->
                <div class="relative group">
                    <?php if (file_exists($theme['path'] . '/screenshot.png')): ?>
                        <img src="/isotone/iso-content/themes/<?php echo $theme['slug']; ?>/screenshot.png" 
                             alt="<?php echo htmlspecialchars($theme['name']); ?>" 
                             class="w-full h-48 object-cover">
                    <?php elseif (file_exists($theme['path'] . '/screenshot.svg')): ?>
                        <img src="/isotone/iso-content/themes/<?php echo $theme['slug']; ?>/screenshot.svg" 
                             alt="<?php echo htmlspecialchars($theme['name']); ?>" 
                             class="w-full h-48 object-cover">
                    <?php else: ?>
                        <div class="w-full h-48 bg-gradient-to-br from-gray-700 to-gray-800 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Hover Actions -->
                    <div class="absolute inset-0 bg-black bg-opacity-75 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center space-x-2">
                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="activate">
                            <input type="hidden" name="theme" value="<?php echo htmlspecialchars($theme['slug']); ?>">
                            <button type="submit" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded transition">
                                Activate
                            </button>
                        </form>
                        
                        <button @click="window.open('/isotone/?theme_preview=<?php echo $theme['slug']; ?>', '_blank')" 
                                class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded transition">
                            Preview
                        </button>
                    </div>
                </div>
                
                <!-- Theme Info -->
                <div class="p-4">
                    <h3 class="font-semibold text-white mb-1"><?php echo htmlspecialchars($theme['name']); ?></h3>
                    <p class="text-sm text-gray-400 mb-3 line-clamp-2"><?php echo htmlspecialchars($theme['description']); ?></p>
                    
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>v<?php echo htmlspecialchars($theme['version']); ?></span>
                        <button @click="showActions = !showActions" class="hover:text-gray-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Dropdown Actions -->
                    <div x-show="showActions" @click.away="showActions = false" x-cloak
                         class="absolute right-4 bottom-12 bg-gray-900 rounded-lg shadow-xl border border-gray-700 py-1 z-10">
                        <button onclick="if(confirm('Are you sure you want to delete this theme?')) { 
                                    document.getElementById('delete-<?php echo $theme['slug']; ?>').submit(); 
                                }"
                                class="block w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-gray-800">
                            Delete Theme
                        </button>
                    </div>
                    
                    <!-- Hidden delete form -->
                    <form id="delete-<?php echo $theme['slug']; ?>" method="POST" class="hidden">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="theme" value="<?php echo htmlspecialchars($theme['slug']); ?>">
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        
        <!-- Add New Theme Card -->
        <div class="bg-gray-800 rounded-lg overflow-hidden border-2 border-dashed border-gray-700 hover:border-gray-600 transition-colors">
            <button @click="showUploadModal = true" class="w-full h-full p-8 flex flex-col items-center justify-center text-gray-500 hover:text-gray-400">
                <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="text-lg font-medium">Add New Theme</span>
                <span class="text-sm mt-2">Upload a theme ZIP file</span>
            </button>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div x-data="{ showUploadModal: false }">
    <div x-show="showUploadModal" 
         x-cloak
         @click.away="showUploadModal = false"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6" @click.stop>
            <h3 class="text-xl font-semibold text-white mb-4">Upload Theme</h3>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="action" value="upload">
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Choose theme ZIP file
                    </label>
                    <input type="file" 
                           name="theme_zip" 
                           accept=".zip"
                           required
                           class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-gray-300 focus:outline-none focus:border-cyan-500">
                    <p class="mt-1 text-xs text-gray-500">Maximum file size: 10MB</p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            @click="showUploadModal = false"
                            class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded transition">
                        Upload Theme
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Style for line clamp -->
<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<script>
    // Show toast if there's an action message
    <?php if ($action_message): ?>
    showToast('<?php echo addslashes($action_message); ?>', '<?php echo $action_type; ?>');
    <?php endif; ?>
</script>

<?php
// Get the buffered content
$page_content = ob_get_clean();

// Include the admin layout
require_once __DIR__ . '/includes/admin-layout.php';
?>
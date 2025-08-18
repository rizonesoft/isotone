<!-- Advanced Settings Tab -->
<div x-show="activeTab === 'advanced'" x-cloak>
    <div class="space-y-8">
        <!-- System Settings -->
        <div>
            <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-4 pb-2 border-b dark:border-gray-700 border-gray-200">
                System Settings
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="debug_mode" 
                               <?php echo getSetting('debug_mode', '0') === '1' ? 'checked' : ''; ?>
                               class="rounded dark:bg-gray-900 bg-gray-50 border-gray-300 text-cyan-600 focus:ring-cyan-500 mr-2">
                        <span class="text-sm dark:text-gray-300 text-gray-700">Enable Debug Mode</span>
                    </label>
                </div>
                
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="maintenance_mode" 
                               <?php echo getSetting('maintenance_mode', '0') === '1' ? 'checked' : ''; ?>
                               class="rounded dark:bg-gray-900 bg-gray-50 border-gray-300 text-cyan-600 focus:ring-cyan-500 mr-2">
                        <span class="text-sm dark:text-gray-300 text-gray-700">Enable Maintenance Mode</span>
                    </label>
                </div>
                
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="cache_enabled" 
                               <?php echo getSetting('cache_enabled', '1') === '1' ? 'checked' : ''; ?>
                               class="rounded dark:bg-gray-900 bg-gray-50 border-gray-300 text-cyan-600 focus:ring-cyan-500 mr-2">
                        <span class="text-sm dark:text-gray-300 text-gray-700">Enable Caching</span>
                    </label>
                </div>
                
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                        Cache TTL (seconds)
                    </label>
                    <input type="number" 
                           name="cache_ttl" 
                           value="<?php echo htmlspecialchars(getSetting('cache_ttl', '3600')); ?>"
                           class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                </div>
            </div>
        </div>

        <!-- Media Settings -->
        <div>
            <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-4 pb-2 border-b dark:border-gray-700 border-gray-200">
                Media Settings
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                        Max Upload Size (MB)
                    </label>
                    <input type="number" 
                           name="max_upload_size" 
                           value="<?php echo htmlspecialchars(getSetting('max_upload_size', '10')); ?>"
                           class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                </div>
                
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                        Allowed File Types
                    </label>
                    <input type="text" 
                           name="allowed_file_types" 
                           value="<?php echo htmlspecialchars(getSetting('allowed_file_types', 'jpg,jpeg,png,gif,pdf,doc,docx')); ?>"
                           placeholder="jpg,jpeg,png,gif,pdf"
                           class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                    <p class="mt-1 text-xs dark:text-gray-500 text-gray-500">Comma-separated list of file extensions</p>
                </div>
            </div>
        </div>

        <!-- Content Settings -->
        <div>
            <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-4 pb-2 border-b dark:border-gray-700 border-gray-200">
                Content Settings
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                        Posts Per Page
                    </label>
                    <input type="number" 
                           name="posts_per_page" 
                           value="<?php echo htmlspecialchars(getSetting('posts_per_page', '10')); ?>"
                           min="1"
                           max="100"
                           class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                </div>
                
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="comments_enabled" 
                               <?php echo getSetting('comments_enabled', '1') === '1' ? 'checked' : ''; ?>
                               class="rounded dark:bg-gray-900 bg-gray-50 border-gray-300 text-cyan-600 focus:ring-cyan-500 mr-2">
                        <span class="text-sm dark:text-gray-300 text-gray-700">Enable Comments</span>
                    </label>
                </div>
                
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="comments_moderation" 
                               <?php echo getSetting('comments_moderation', '1') === '1' ? 'checked' : ''; ?>
                               class="rounded dark:bg-gray-900 bg-gray-50 border-gray-300 text-cyan-600 focus:ring-cyan-500 mr-2">
                        <span class="text-sm dark:text-gray-300 text-gray-700">Moderate Comments</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
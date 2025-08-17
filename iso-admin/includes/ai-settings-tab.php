<?php
/**
 * AI Settings Tab Component
 * Dynamic AI model configuration based on API keys
 */

// Load AI Model Service
require_once dirname(dirname(__DIR__)) . '/iso-core/Services/AIModelService.php';
$aiModelService = new AIModelService();
$modelConfig = $aiModelService->getModelConfig();
$modelConfigJson = $aiModelService->getModelConfigJson();

// Get current settings for active provider detection
$currentSettings = $settings ?? [];
?>

<!-- Load AI Model Selector JavaScript -->
<script src="/isotone/iso-admin/js/ai-model-selector.js"></script>

<!-- API Tab Content -->
<div x-show="activeTab === 'api'" x-cloak>
    <div class="space-y-8">
        
        <!-- Provider Status Overview -->
        <div class="dark:bg-gray-800/50 bg-gray-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold dark:text-white text-gray-900">AI Provider Status</h3>
                <span id="active-provider-count" class="text-sm dark:text-gray-400 text-gray-600">0 Providers Active</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-2 gap-4">
                <?php 
                // Only show OpenAI and Anthropic for now
                $allowedProviders = ['openai', 'anthropic'];
                foreach ($modelConfig['providers'] as $providerId => $provider): 
                    if (!in_array($providerId, $allowedProviders)) continue;
                ?>
                <div class="flex items-center space-x-2">
                    <div id="provider-status-<?php echo $providerId; ?>" class="flex items-center space-x-1 text-sm">
                        <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                        <span class="dark:text-gray-300 text-gray-700"><?php echo htmlspecialchars($provider['name']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Toni AI Configuration Notice -->
        <div class="dark:bg-cyan-900/20 bg-cyan-50 border dark:border-cyan-800 border-cyan-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-cyan-600 dark:text-cyan-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h4 class="text-sm font-semibold dark:text-cyan-300 text-cyan-900 mb-1">Smart Model Selection</h4>
                    <p class="text-sm dark:text-cyan-400 text-cyan-700">Configure your API keys below. Toni will only show models from providers with valid API keys, automatically filtering by task capabilities and optimizing for cost.</p>
                </div>
            </div>
        </div>

        <!-- AI API Credentials Section -->
        <div>
            <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-4 pb-2 border-b dark:border-gray-700 border-gray-200">
                AI API Credentials
            </h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <?php 
                // Only show OpenAI and Anthropic for now
                $allowedProviders = ['openai', 'anthropic'];
                foreach ($modelConfig['providers'] as $providerId => $provider): 
                    if (!in_array($providerId, $allowedProviders)) continue;
                ?>
                <div class="dark:bg-gray-800/50 bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium dark:text-white text-gray-900 mb-3 flex items-center">
                        <span class="w-2 h-2 bg-<?php echo $provider['color']; ?>-500 rounded-full mr-2"></span>
                        <?php echo htmlspecialchars($provider['name']); ?>
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">
                                API Key
                            </label>
                            <input type="password" 
                                   name="<?php echo $provider['api_key_field']; ?>" 
                                   value="<?php echo htmlspecialchars(getSetting($provider['api_key_field'], '')); ?>"
                                   placeholder="Enter your <?php echo $provider['name']; ?> API key"
                                   class="w-full px-3 py-2 text-sm dark:bg-gray-900 bg-white dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500 api-key-input"
                                   data-provider="<?php echo $providerId; ?>">
                        </div>
                        
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Model Selection by Task Type -->
        <div>
            <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-4 pb-2 border-b dark:border-gray-700 border-gray-200">
                Model Selection by Task Type
            </h3>
            
            <div class="space-y-4">
                <?php 
                $taskCategories = [
                    'simple' => ['color' => 'cyan', 'icon' => '⚡'],
                    'standard' => ['color' => 'green', 'icon' => '🎯'],
                    'complex' => ['color' => 'purple', 'icon' => '🧠'],
                    'vision' => ['color' => 'pink', 'icon' => '👁️']
                ];
                
                foreach ($modelConfig['task_categories'] as $categoryId => $category): 
                    $taskStyle = $taskCategories[$categoryId] ?? ['color' => 'gray', 'icon' => '📝'];
                ?>
                <div class="dark:bg-gray-800/30 bg-gray-50 rounded-lg p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h4 class="font-medium dark:text-<?php echo $taskStyle['color']; ?>-400 text-<?php echo $taskStyle['color']; ?>-700 flex items-center">
                                <span class="mr-2"><?php echo $taskStyle['icon']; ?></span>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </h4>
                            <p class="text-xs dark:text-gray-500 text-gray-500 mt-1">
                                <?php echo htmlspecialchars($category['description']); ?>
                            </p>
                        </div>
                        <div id="cost-estimate-<?php echo $categoryId; ?>" class="text-xs dark:text-gray-400 text-gray-600 text-right">
                            <!-- Cost estimation will be inserted here -->
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Primary Model -->
                        <div>
                            <label class="block text-xs font-medium dark:text-gray-400 text-gray-600 mb-1">
                                Primary Model
                            </label>
                            <select name="toni_<?php echo $categoryId; ?>_primary" 
                                    class="w-full px-3 py-2 text-sm dark:bg-gray-900 bg-white dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500 model-selector"
                                    data-category="<?php echo $categoryId; ?>"
                                    data-type="primary">
                                <!-- Options will be populated dynamically -->
                                <option value="">Loading models...</option>
                            </select>
                        </div>
                        
                        <!-- Fallback Model -->
                        <div>
                            <label class="block text-xs font-medium dark:text-gray-400 text-gray-600 mb-1">
                                Fallback Model
                            </label>
                            <select name="toni_<?php echo $categoryId; ?>_fallback" 
                                    class="w-full px-3 py-2 text-sm dark:bg-gray-900 bg-white dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500 model-selector"
                                    data-category="<?php echo $categoryId; ?>"
                                    data-type="fallback">
                                <option value="none">None</option>
                                <!-- Options will be populated dynamically -->
                            </select>
                        </div>
                        
                        <!-- Max Tokens/Size -->
                        <div>
                            <label class="block text-xs font-medium dark:text-gray-400 text-gray-600 mb-1">
                                <?php echo $categoryId === 'vision' ? 'Max Image Size (MB)' : 'Max Tokens'; ?>
                            </label>
                            <input type="number" 
                                   name="toni_<?php echo $categoryId; ?>_<?php echo $categoryId === 'vision' ? 'max_size' : 'max_tokens'; ?>" 
                                   value="<?php echo htmlspecialchars(getSetting('toni_' . $categoryId . '_' . ($categoryId === 'vision' ? 'max_size' : 'max_tokens'), $categoryId === 'vision' ? '20' : '4000')); ?>"
                                   min="<?php echo $categoryId === 'vision' ? '1' : '100'; ?>"
                                   max="<?php echo $categoryId === 'vision' ? '100' : '128000'; ?>"
                                   class="w-full px-3 py-2 text-sm dark:bg-gray-900 bg-white dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500 max-tokens-input"
                                   data-category="<?php echo $categoryId; ?>">
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Advanced AI Settings -->
        <div>
            <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-4 pb-2 border-b dark:border-gray-700 border-gray-200">
                Advanced AI Settings
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                        Temperature (Creativity)
                    </label>
                    <input type="number" 
                           name="toni_temperature" 
                           value="<?php echo htmlspecialchars(getSetting('toni_temperature', '0.7')); ?>"
                           min="0" max="2" step="0.1"
                           class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                    <p class="mt-1 text-xs dark:text-gray-500 text-gray-500">0 = Deterministic, 1 = Balanced, 2 = Creative</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                        Request Timeout (seconds)
                    </label>
                    <input type="number" 
                           name="toni_timeout" 
                           value="<?php echo htmlspecialchars(getSetting('toni_timeout', '30')); ?>"
                           min="10" max="300"
                           class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                        Rate Limit (req/min)
                    </label>
                    <input type="number" 
                           name="toni_rate_limit" 
                           value="<?php echo htmlspecialchars(getSetting('toni_rate_limit', '60')); ?>"
                           min="1" max="1000"
                           class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                </div>
                
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="toni_cache_enabled" 
                               <?php echo getSetting('toni_cache_enabled', '1') === '1' ? 'checked' : ''; ?>
                               class="rounded dark:bg-gray-900 bg-gray-50 border-gray-300 text-cyan-600 focus:ring-cyan-500 mr-2">
                        <span class="text-sm dark:text-gray-300 text-gray-700">Enable Response Caching</span>
                    </label>
                </div>
                
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="toni_batch_enabled" 
                               <?php echo getSetting('toni_batch_enabled', '0') === '1' ? 'checked' : ''; ?>
                               class="rounded dark:bg-gray-900 bg-gray-50 border-gray-300 text-cyan-600 focus:ring-cyan-500 mr-2">
                        <span class="text-sm dark:text-gray-300 text-gray-700">Enable Batch Processing</span>
                    </label>
                </div>
                
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="toni_auto_fallback" 
                               <?php echo getSetting('toni_auto_fallback', '1') === '1' ? 'checked' : ''; ?>
                               class="rounded dark:bg-gray-900 bg-gray-50 border-gray-300 text-cyan-600 focus:ring-cyan-500 mr-2">
                        <span class="text-sm dark:text-gray-300 text-gray-700">Auto-Fallback on Error</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Analytics API Section (keeping existing) -->
        <div>
            <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-4 pb-2 border-b dark:border-gray-700 border-gray-200">
                Analytics Configuration
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                        Analytics Provider
                    </label>
                    <select name="analytics_provider" 
                            @change="analyticsProvider = $event.target.value"
                            class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                        <option value="none" <?php echo getSetting('analytics_provider', 'none') === 'none' ? 'selected' : ''; ?>>None</option>
                        <option value="google" <?php echo getSetting('analytics_provider') === 'google' ? 'selected' : ''; ?>>Google Analytics</option>
                        <option value="matomo" <?php echo getSetting('analytics_provider') === 'matomo' ? 'selected' : ''; ?>>Matomo</option>
                    </select>
                </div>

                <!-- Google Analytics -->
                <template x-if="analyticsProvider === 'google'">
                    <div>
                        <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                            Measurement ID
                        </label>
                        <input type="text" 
                               name="google_analytics_id" 
                               value="<?php echo htmlspecialchars(getSetting('google_analytics_id', '')); ?>"
                               placeholder="G-XXXXXXXXXX"
                               class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                    </div>
                </template>

                <!-- Matomo -->
                <template x-if="analyticsProvider === 'matomo'">
                    <div class="sm:col-span-2 lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                                Matomo URL
                            </label>
                            <input type="url" 
                                   name="matomo_url" 
                                   value="<?php echo htmlspecialchars(getSetting('matomo_url', '')); ?>"
                                   placeholder="https://your-matomo.com"
                                   class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                                Site ID
                            </label>
                            <input type="number" 
                                   name="matomo_site_id" 
                                   value="<?php echo htmlspecialchars(getSetting('matomo_site_id', '')); ?>"
                                   placeholder="1"
                                   class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize AI Model Selector when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Pass the model configuration from PHP to JavaScript
    const modelConfig = <?php echo $modelConfigJson; ?>;
    
    // Create and initialize the AI Model Selector
    const aiSelector = new AIModelSelector(modelConfig);
    aiSelector.init();
    
    // Store current selections
    <?php foreach (['simple', 'standard', 'complex', 'vision'] as $category): ?>
    <?php 
        $primaryValue = getSetting('toni_' . $category . '_primary', '');
        $fallbackValue = getSetting('toni_' . $category . '_fallback', 'none');
    ?>
    <?php if ($primaryValue): ?>
    const <?php echo $category; ?>Primary = document.querySelector('select[name="toni_<?php echo $category; ?>_primary"]');
    if (<?php echo $category; ?>Primary) {
        <?php echo $category; ?>Primary.dataset.currentValue = '<?php echo $primaryValue; ?>';
    }
    <?php endif; ?>
    <?php if ($fallbackValue): ?>
    const <?php echo $category; ?>Fallback = document.querySelector('select[name="toni_<?php echo $category; ?>_fallback"]');
    if (<?php echo $category; ?>Fallback) {
        <?php echo $category; ?>Fallback.dataset.currentValue = '<?php echo $fallbackValue; ?>';
    }
    <?php endif; ?>
    <?php endforeach; ?>
    
    // Restore selections after dynamic loading
    setTimeout(() => {
        document.querySelectorAll('.model-selector').forEach(selector => {
            if (selector.dataset.currentValue) {
                selector.value = selector.dataset.currentValue;
            }
        });
    }, 100);
});
</script>
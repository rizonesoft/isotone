<?php
/**
 * Simplified AI Settings Tab
 * Clean layout for API key configuration
 */

// Get current settings
$settings = $settings ?? [];
?>

<!-- API Tab Content -->
<div x-show="activeTab === 'api'" x-cloak>
    <div class="space-y-8">
        
        <!-- Toni AI Configuration Notice -->
        <div class="dark:bg-cyan-900/20 bg-cyan-50 border dark:border-cyan-800 border-cyan-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-cyan-600 dark:text-cyan-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h4 class="text-sm font-semibold dark:text-cyan-300 text-cyan-900 mb-1">Toni AI Assistant</h4>
                    <p class="text-sm dark:text-cyan-400 text-cyan-700">Configure your AI provider API keys. Toni will automatically select the most cost-effective model for each task.</p>
                </div>
            </div>
        </div>

        <!-- AI Providers Configuration -->
        <div>
            <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-6 pb-2 border-b dark:border-gray-700 border-gray-200">
                AI API Credentials
            </h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- OpenAI Configuration -->
                <div class="dark:bg-gray-800/50 bg-gray-50 rounded-lg p-6">
                    <h4 class="text-lg font-semibold dark:text-white text-gray-900 mb-4 flex items-center">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                        OpenAI
                    </h4>
                    
                    <div class="space-y-4">
                        <!-- API Key -->
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                                API Key
                            </label>
                            <input type="password" 
                                   name="openai_api_key" 
                                   value="<?php echo htmlspecialchars(getSetting('openai_api_key', '')); ?>"
                                   placeholder="sk-..."
                                   class="w-full px-4 py-2 dark:bg-gray-900 bg-white dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500"
                                   x-data
                                   @input="$dispatch('provider-key-changed', { provider: 'openai', hasKey: $el.value.length > 0 })">
                        </div>
                        
                        <!-- Organization ID -->
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                                Organization ID (Optional)
                            </label>
                            <input type="text" 
                                   name="openai_org_id" 
                                   value="<?php echo htmlspecialchars(getSetting('openai_org_id', '')); ?>"
                                   placeholder="org-..."
                                   class="w-full px-4 py-2 dark:bg-gray-900 bg-white dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                        </div>
                    </div>
                </div>

                <!-- Anthropic Configuration -->
                <div class="dark:bg-gray-800/50 bg-gray-50 rounded-lg p-6">
                    <h4 class="text-lg font-semibold dark:text-white text-gray-900 mb-4 flex items-center">
                        <span class="w-2 h-2 bg-purple-500 rounded-full mr-2"></span>
                        Anthropic
                    </h4>
                    
                    <div class="space-y-4">
                        <!-- API Key -->
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                                API Key
                            </label>
                            <input type="password" 
                                   name="anthropic_api_key" 
                                   value="<?php echo htmlspecialchars(getSetting('anthropic_api_key', '')); ?>"
                                   placeholder="sk-ant-api..."
                                   class="w-full px-4 py-2 dark:bg-gray-900 bg-white dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500"
                                   x-data
                                   @input="$dispatch('provider-key-changed', { provider: 'anthropic', hasKey: $el.value.length > 0 })">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Settings -->
        <div class="dark:bg-gray-800/50 bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-4">
                Advanced Settings
            </h3>
            
            <div>
                <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                    Request Timeout (seconds)
                </label>
                <input type="number" 
                       name="toni_timeout" 
                       value="<?php echo htmlspecialchars(getSetting('toni_timeout', '30')); ?>"
                       min="10" max="300"
                       class="w-full max-w-md px-4 py-2 dark:bg-gray-900 bg-white dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                <p class="mt-1 text-xs dark:text-gray-500 text-gray-500">How long to wait for AI responses before timing out</p>
            </div>
        </div>

        <!-- Analytics Configuration (keeping existing) -->
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
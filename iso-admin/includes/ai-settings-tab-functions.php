<?php
/**
 * AI Settings Tab - Function-based Model Selection
 * Select specific AI models for different functions
 */

// Get current settings
$settings = $settings ?? [];

// Define GPT-5 models with accurate pricing from Context7 docs
$gpt5_models = [
    'gpt-5-nano' => [
        'name' => 'GPT-5 Nano',
        'description' => 'Fastest, most cost-efficient',
        'context_window' => '400K tokens',
        'max_output' => '128K tokens',
        'input_price' => 0.05,  // Per 1M tokens
        'output_price' => 0.40,  // Per 1M tokens
        'best_for' => 'Chat, help, classification'
    ],
    'gpt-5-mini' => [
        'name' => 'GPT-5 Mini',
        'description' => 'Balanced performance',
        'context_window' => '400K tokens',
        'max_output' => '128K tokens',
        'input_price' => 0.25,  // Per 1M tokens
        'output_price' => 2.00,  // Per 1M tokens (Standard tier)
        'best_for' => 'Content creation, analysis'
    ],
    'gpt-5' => [
        'name' => 'GPT-5',
        'description' => 'Most capable model',
        'context_window' => '400K tokens',
        'max_output' => '128K tokens',
        'input_price' => 1.25,  // Per 1M tokens
        'output_price' => 10.00, // Per 1M tokens
        'best_for' => 'Complex reasoning, coding'
    ]
];

// Define functions that can use AI
$ai_functions = [
    'toni_chat' => [
        'name' => 'Toni Chat Assistant',
        'description' => 'General chat and help for users',
        'setting_key' => 'toni_chat_model',
        'default' => 'gpt-5-nano'
    ],
    // Future functions can be added here
    // 'content_generation' => [...],
    // 'seo_optimization' => [...],
    // 'code_assistance' => [...],
];
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
                    <h4 class="text-sm font-semibold dark:text-cyan-300 text-cyan-900 mb-1">AI Model Configuration</h4>
                    <p class="text-sm dark:text-cyan-400 text-cyan-700">Select which GPT-5 model to use for each function. Pricing shown is per 1M tokens.</p>
                </div>
            </div>
        </div>

        <!-- API Credentials -->
        <div>
            <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-6 pb-2 border-b dark:border-gray-700 border-gray-200">
                OpenAI API Credentials
            </h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                        API Key <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           name="openai_api_key" 
                           value="<?php echo htmlspecialchars(getSetting('openai_api_key', '')); ?>"
                           placeholder="sk-..."
                           class="w-full px-4 py-2 dark:bg-gray-900 bg-white dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                    <p class="mt-1 text-xs dark:text-gray-500 text-gray-500">Required for all AI features</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                        Organization ID (Optional)
                    </label>
                    <input type="text" 
                           name="openai_org_id" 
                           value="<?php echo htmlspecialchars(getSetting('openai_org_id', '')); ?>"
                           placeholder="org-..."
                           class="w-full px-4 py-2 dark:bg-gray-900 bg-white dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                    <p class="mt-1 text-xs dark:text-gray-500 text-gray-500">Only needed for organization accounts</p>
                </div>
            </div>
        </div>

        <!-- Function-based Model Selection -->
        <div>
            <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-6 pb-2 border-b dark:border-gray-700 border-gray-200">
                Model Selection by Function
            </h3>
            
            <div class="space-y-6">
                <?php foreach ($ai_functions as $function_id => $function): ?>
                <div class="dark:bg-gray-800/50 bg-gray-50 rounded-lg p-6">
                    <div class="mb-4">
                        <h4 class="text-md font-semibold dark:text-white text-gray-900"><?php echo $function['name']; ?></h4>
                        <p class="text-sm dark:text-gray-400 text-gray-600 mt-1"><?php echo $function['description']; ?></p>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                                Select Model
                            </label>
                            <select name="<?php echo $function['setting_key']; ?>" 
                                    id="<?php echo $function_id; ?>_model_select"
                                    onchange="updateModelInfo('<?php echo $function_id; ?>')"
                                    class="w-full px-4 py-2 dark:bg-gray-900 bg-white dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                                <?php 
                                $current_model = getSetting($function['setting_key'], $function['default']);
                                foreach ($gpt5_models as $model_id => $model): 
                                ?>
                                <option value="<?php echo $model_id; ?>" 
                                        data-input="<?php echo $model['input_price']; ?>"
                                        data-output="<?php echo $model['output_price']; ?>"
                                        data-context="<?php echo $model['context_window']; ?>"
                                        data-max-output="<?php echo $model['max_output']; ?>"
                                        data-description="<?php echo htmlspecialchars($model['description']); ?>"
                                        data-best-for="<?php echo htmlspecialchars($model['best_for']); ?>"
                                        <?php echo $current_model === $model_id ? 'selected' : ''; ?>>
                                    <?php echo $model['name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div id="<?php echo $function_id; ?>_model_info" class="dark:bg-gray-900/50 bg-gray-100 rounded-lg p-4">
                            <!-- Model info will be populated by JavaScript -->
                        </div>
                    </div>
                    
                    <!-- Reasoning Effort and Verbosity Settings -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                                Reasoning Effort
                            </label>
                            <select name="<?php echo $function_id; ?>_reasoning_effort" 
                                    class="w-full px-4 py-2 dark:bg-gray-900 bg-white dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                                <option value="minimal" <?php echo getSetting($function_id . '_reasoning_effort', 'minimal') === 'minimal' ? 'selected' : ''; ?>>Minimal (Fastest)</option>
                                <option value="low" <?php echo getSetting($function_id . '_reasoning_effort', 'minimal') === 'low' ? 'selected' : ''; ?>>Low</option>
                                <option value="medium" <?php echo getSetting($function_id . '_reasoning_effort', 'minimal') === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                <option value="high" <?php echo getSetting($function_id . '_reasoning_effort', 'minimal') === 'high' ? 'selected' : ''; ?>>High (Most thorough)</option>
                            </select>
                            <p class="mt-1 text-xs dark:text-gray-500 text-gray-500">Controls thinking depth. Use minimal for chat, high for complex tasks.</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                                Response Verbosity
                            </label>
                            <select name="<?php echo $function_id; ?>_verbosity" 
                                    class="w-full px-4 py-2 dark:bg-gray-900 bg-white dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 border rounded-lg focus:outline-none focus:border-cyan-500">
                                <option value="low" <?php echo getSetting($function_id . '_verbosity', 'medium') === 'low' ? 'selected' : ''; ?>>Low (Concise)</option>
                                <option value="medium" <?php echo getSetting($function_id . '_verbosity', 'medium') === 'medium' ? 'selected' : ''; ?>>Medium (Balanced)</option>
                                <option value="high" <?php echo getSetting($function_id . '_verbosity', 'medium') === 'high' ? 'selected' : ''; ?>>High (Detailed)</option>
                            </select>
                            <p class="mt-1 text-xs dark:text-gray-500 text-gray-500">Controls response length. Low for quick answers, high for detailed explanations.</p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Cost Estimation -->
        <div class="dark:bg-blue-900/20 bg-blue-50 border dark:border-blue-800 border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold dark:text-blue-300 text-blue-900 mb-4">
                Cost Estimation Guide
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="dark:bg-blue-900/30 bg-white rounded-lg p-4">
                    <h4 class="font-semibold dark:text-blue-200 text-blue-800 mb-2">GPT-5 Nano</h4>
                    <p class="dark:text-blue-300 text-blue-700">
                        <span class="font-mono">$0.05/$0.40</span> per 1M tokens<br>
                        ~<span class="font-mono">$0.0004</span> per chat message<br>
                        <span class="text-xs">Best for: General chat, help</span>
                    </p>
                </div>
                
                <div class="dark:bg-blue-900/30 bg-white rounded-lg p-4">
                    <h4 class="font-semibold dark:text-blue-200 text-blue-800 mb-2">GPT-5 Mini</h4>
                    <p class="dark:text-blue-300 text-blue-700">
                        <span class="font-mono">$0.25/$2.00</span> per 1M tokens<br>
                        ~<span class="font-mono">$0.0023</span> per chat message<br>
                        <span class="text-xs">Best for: Content creation</span>
                    </p>
                </div>
                
                <div class="dark:bg-blue-900/30 bg-white rounded-lg p-4">
                    <h4 class="font-semibold dark:text-blue-200 text-blue-800 mb-2">GPT-5</h4>
                    <p class="dark:text-blue-300 text-blue-700">
                        <span class="font-mono">$1.25/$10.00</span> per 1M tokens<br>
                        ~<span class="font-mono">$0.0113</span> per chat message<br>
                        <span class="text-xs">Best for: Complex tasks</span>
                    </p>
                </div>
            </div>
            
            <p class="mt-4 text-xs dark:text-blue-400 text-blue-600">
                * Estimated costs assume ~1000 tokens per message (input + output combined)
            </p>
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

<script>
function updateModelInfo(functionId) {
    const select = document.getElementById(functionId + '_model_select');
    const infoDiv = document.getElementById(functionId + '_model_info');
    const selected = select.options[select.selectedIndex];
    
    const inputPrice = parseFloat(selected.dataset.input);
    const outputPrice = parseFloat(selected.dataset.output);
    const contextWindow = selected.dataset.context;
    const maxOutput = selected.dataset.maxOutput;
    const description = selected.dataset.description;
    const bestFor = selected.dataset.bestFor;
    
    // Calculate estimated cost per message (assuming 1000 tokens total)
    const estimatedCost = ((500 * inputPrice) + (500 * outputPrice)) / 1000000;
    
    infoDiv.innerHTML = `
        <h5 class="text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">Model Information</h5>
        <div class="space-y-1 text-xs">
            <div><span class="font-medium">Description:</span> ${description}</div>
            <div><span class="font-medium">Context Window:</span> ${contextWindow}</div>
            <div><span class="font-medium">Max Output:</span> ${maxOutput}</div>
            <div><span class="font-medium">Pricing:</span> $${inputPrice}/$${outputPrice} per 1M tokens</div>
            <div><span class="font-medium">Est. Cost:</span> $${estimatedCost.toFixed(6)} per message</div>
            <div><span class="font-medium">Best For:</span> ${bestFor}</div>
        </div>
    `;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach ($ai_functions as $function_id => $function): ?>
    updateModelInfo('<?php echo $function_id; ?>');
    <?php endforeach; ?>
});
</script>
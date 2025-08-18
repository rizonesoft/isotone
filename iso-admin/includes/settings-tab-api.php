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
        
        <!-- Token Usage Dashboard -->
        <div x-data="tokenUsageMonitor()" x-init="init()">
            <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-6 pb-2 border-b dark:border-gray-700 border-gray-200">
                Token Usage Analytics
                <div class="inline-flex ml-4 gap-2">
                    <button @click="setPeriod('day')" 
                            :class="period === 'day' ? 'bg-cyan-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                            class="text-sm px-3 py-1 rounded-lg transition-colors">
                        Today
                    </button>
                    <button @click="setPeriod('week')" 
                            :class="period === 'week' ? 'bg-cyan-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                            class="text-sm px-3 py-1 rounded-lg transition-colors">
                        Week
                    </button>
                    <button @click="setPeriod('month')" 
                            :class="period === 'month' ? 'bg-cyan-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                            class="text-sm px-3 py-1 rounded-lg transition-colors">
                        Month
                    </button>
                </div>
                <a href="https://platform.openai.com/usage" 
                   target="_blank" 
                   class="ml-2 text-sm px-3 py-1 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors inline-flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    OpenAI
                </a>
            </h3>
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="dark:bg-gray-800/50 bg-gray-50 rounded-lg p-4">
                    <div class="text-sm dark:text-gray-400 text-gray-600 mb-1">Total Cost</div>
                    <div class="text-2xl font-bold dark:text-cyan-400 text-cyan-600" x-text="'$' + totals.cost.toFixed(4)"></div>
                </div>
                <div class="dark:bg-gray-800/50 bg-gray-50 rounded-lg p-4">
                    <div class="text-sm dark:text-gray-400 text-gray-600 mb-1">API Calls</div>
                    <div class="text-2xl font-bold dark:text-green-400 text-green-600" x-text="totals.api_calls.toLocaleString()"></div>
                </div>
                <div class="dark:bg-gray-800/50 bg-gray-50 rounded-lg p-4">
                    <div class="text-sm dark:text-gray-400 text-gray-600 mb-1">Input Tokens</div>
                    <div class="text-2xl font-bold dark:text-blue-400 text-blue-600" x-text="formatTokens(totals.input_tokens)"></div>
                </div>
                <div class="dark:bg-gray-800/50 bg-gray-50 rounded-lg p-4">
                    <div class="text-sm dark:text-gray-400 text-gray-600 mb-1">Output Tokens</div>
                    <div class="text-2xl font-bold dark:text-purple-400 text-purple-600" x-text="formatTokens(totals.output_tokens)"></div>
                </div>
            </div>
            
            <!-- Charts Container -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Token Usage Chart -->
                <div class="dark:bg-gray-800/50 bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-semibold dark:text-gray-300 text-gray-700 mb-4">Token Usage Over Time</h4>
                    <div style="height: 250px; position: relative;">
                        <canvas id="tokenUsageChart"></canvas>
                    </div>
                </div>
                
                <!-- Cost by Model Chart -->
                <div class="dark:bg-gray-800/50 bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-semibold dark:text-gray-300 text-gray-700 mb-4">Cost by Model</h4>
                    <div style="height: 250px; position: relative;">
                        <canvas id="costByModelChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Model Breakdown Table -->
            <div class="mt-6 dark:bg-gray-800/50 bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-semibold dark:text-gray-300 text-gray-700 mb-4">Model Usage Breakdown</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-gray-700 border-gray-200">
                                <th class="text-left py-2 dark:text-gray-300 text-gray-700">Model</th>
                                <th class="text-right py-2 dark:text-gray-300 text-gray-700">Calls</th>
                                <th class="text-right py-2 dark:text-gray-300 text-gray-700">Input Tokens</th>
                                <th class="text-right py-2 dark:text-gray-300 text-gray-700">Output Tokens</th>
                                <th class="text-right py-2 dark:text-gray-300 text-gray-700">Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="model in modelSummary" :key="model.model">
                                <tr class="border-b dark:border-gray-700/50 border-gray-200/50">
                                    <td class="py-2 dark:text-gray-400 text-gray-600" x-text="model.model"></td>
                                    <td class="text-right py-2 dark:text-gray-400 text-gray-600" x-text="model.api_calls"></td>
                                    <td class="text-right py-2 dark:text-gray-400 text-gray-600" x-text="formatTokens(model.total_input_tokens)"></td>
                                    <td class="text-right py-2 dark:text-gray-400 text-gray-600" x-text="formatTokens(model.total_output_tokens)"></td>
                                    <td class="text-right py-2 dark:text-green-400 text-green-600 font-mono" x-text="'$' + parseFloat(model.total_cost).toFixed(4)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Error Message -->
            <div x-show="error" x-cloak class="mt-4 p-4 dark:bg-red-900/20 bg-red-50 border dark:border-red-800 border-red-200 rounded-lg">
                <p class="text-sm dark:text-red-400 text-red-700" x-text="error"></p>
            </div>
        </div>


        <!-- Function-based Model Selection -->
        <div>
            <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-4 pb-2 border-b dark:border-gray-700 border-gray-200">
                Model Selection by Function
            </h3>
            
            <!-- Model Configuration Info Box -->
            <div class="dark:bg-cyan-900/20 bg-cyan-50 border dark:border-cyan-800 border-cyan-200 rounded-lg p-4 mb-6">
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

// Token Usage Monitor Alpine component
function tokenUsageMonitor() {
    return {
        period: 'day',
        loading: false,
        error: null,
        initialized: false,
        refreshInterval: null,
        totals: {
            input_tokens: 0,
            output_tokens: 0,
            total_tokens: 0,
            cost: 0,
            api_calls: 0
        },
        modelSummary: [],
        chartData: [],
        tokenChart: null,
        costChart: null,
        
        async init() {
            // Prevent duplicate initialization
            if (this.initialized) return;
            this.initialized = true;
            
            await this.fetchUsageData();
            
            // Cleanup on tab switch
            this.$watch('$root.activeTab', (newTab) => {
                if (newTab !== 'api' && this.refreshInterval) {
                    clearInterval(this.refreshInterval);
                    this.refreshInterval = null;
                }
            });
        },
        
        async setPeriod(newPeriod) {
            this.period = newPeriod;
            await this.fetchUsageData();
        },
        
        formatTokens(num) {
            if (num >= 1000000) {
                return (num / 1000000).toFixed(2) + 'M';
            } else if (num >= 1000) {
                return (num / 1000).toFixed(1) + 'K';
            }
            return num.toString();
        },
        
        async fetchUsageData() {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await fetch(`/isotone/iso-admin/api/token-usage.php?period=${this.period}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Failed to fetch usage data');
                }
                
                const data = await response.json();
                
                if (data.success) {
                    this.totals = data.totals || this.totals;
                    this.modelSummary = data.summary_by_model || [];
                    this.chartData = data.chart_data || [];
                    
                    // Update charts
                    this.$nextTick(() => {
                        this.updateCharts(data);
                    });
                } else {
                    this.error = data.error || 'Failed to load usage data';
                }
            } catch (error) {
                console.error('Error fetching token usage:', error);
                this.error = 'Unable to fetch usage data. Start using the API to see statistics.';
            } finally {
                this.loading = false;
            }
        },
        
        updateCharts(data) {
            // Check if Chart.js is loaded
            if (typeof Chart === 'undefined') {
                console.error('Chart.js not loaded');
                return;
            }
            
            // Get dark mode status
            const isDarkMode = document.documentElement.classList.contains('dark');
            const textColor = isDarkMode ? '#9CA3AF' : '#4B5563';
            const gridColor = isDarkMode ? '#374151' : '#E5E7EB';
            
            // Prepare token usage chart data
            const labels = [];
            const inputTokens = [];
            const outputTokens = [];
            
            data.chart_data.forEach(item => {
                let label = item.time;
                if (this.period === 'day') {
                    label = label + ':00';
                } else {
                    // Format date
                    const date = new Date(item.time);
                    label = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }
                labels.push(label);
                
                let inputSum = 0;
                let outputSum = 0;
                
                if (item.models) {
                    Object.values(item.models).forEach(model => {
                        inputSum += model.input_tokens || 0;
                        outputSum += model.output_tokens || 0;
                    });
                }
                
                inputTokens.push(inputSum);
                outputTokens.push(outputSum);
            });
            
            // Update or create token usage chart
            const tokenCanvas = document.getElementById('tokenUsageChart');
            if (!tokenCanvas) return;
            
            const tokenCtx = tokenCanvas.getContext('2d');
            
            if (this.tokenChart) {
                this.tokenChart.destroy();
            }
            
            this.tokenChart = new Chart(tokenCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Input Tokens',
                            data: inputTokens,
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Output Tokens',
                            data: outputTokens,
                            borderColor: '#A855F7',
                            backgroundColor: 'rgba(168, 85, 247, 0.1)',
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 750
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: textColor
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: textColor
                            },
                            grid: {
                                color: gridColor
                            }
                        },
                        y: {
                            ticks: {
                                color: textColor,
                                callback: function(value) {
                                    if (value >= 1000000) {
                                        return (value / 1000000).toFixed(1) + 'M';
                                    } else if (value >= 1000) {
                                        return (value / 1000).toFixed(0) + 'K';
                                    }
                                    return value;
                                }
                            },
                            grid: {
                                color: gridColor
                            }
                        }
                    }
                }
            });
            
            // Prepare cost by model chart data
            const modelNames = [];
            const modelCosts = [];
            const modelColors = {
                'gpt-5-nano': '#06B6D4',  // Cyan
                'gpt-5-mini': '#8B5CF6',  // Purple
                'gpt-5': '#F59E0B'        // Orange
            };
            
            data.summary_by_model.forEach(model => {
                modelNames.push(model.model);
                modelCosts.push(parseFloat(model.total_cost));
            });
            
            // Update or create cost chart
            const costCanvas = document.getElementById('costByModelChart');
            if (!costCanvas) return;
            
            const costCtx = costCanvas.getContext('2d');
            
            if (this.costChart) {
                this.costChart.destroy();
            }
            
            this.costChart = new Chart(costCtx, {
                type: 'doughnut',
                data: {
                    labels: modelNames,
                    datasets: [{
                        label: 'Cost by Model',
                        data: modelCosts,
                        backgroundColor: modelNames.map(name => modelColors[name] || '#6B7280'),
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 750
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: textColor,
                                padding: 15
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = '$' + context.parsed.toFixed(4);
                                    return label + ': ' + value;
                                }
                            }
                        }
                    }
                }
            });
        }
    }
}
</script>

<!-- Add Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
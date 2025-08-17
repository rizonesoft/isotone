/**
 * AI Model Selector
 * Dynamic model selection based on configured API keys
 */

class AIModelSelector {
    constructor(modelConfig) {
        this.modelConfig = modelConfig;
        this.activeProviders = new Set();
        this.selectedModels = {
            simple: { primary: null, fallback: null },
            standard: { primary: null, fallback: null },
            complex: { primary: null, fallback: null },
            vision: { primary: null, fallback: null }
        };
    }

    /**
     * Initialize the model selector
     */
    init() {
        this.detectActiveProviders();
        this.bindEventListeners();
        this.updateAllSelectors();
    }

    /**
     * Detect which providers have API keys configured
     */
    detectActiveProviders() {
        this.activeProviders.clear();
        
        Object.keys(this.modelConfig.providers).forEach(provider => {
            const keyField = this.modelConfig.providers[provider].api_key_field;
            const input = document.querySelector(`input[name="${keyField}"]`);
            
            if (input && input.value && input.value.trim().length > 0) {
                this.activeProviders.add(provider);
            }
        });

        this.updateProviderIndicators();
    }

    /**
     * Bind event listeners to API key inputs
     */
    bindEventListeners() {
        Object.keys(this.modelConfig.providers).forEach(provider => {
            const keyField = this.modelConfig.providers[provider].api_key_field;
            const input = document.querySelector(`input[name="${keyField}"]`);
            
            if (input) {
                // Debounce the input to avoid too many updates
                let timeout;
                input.addEventListener('input', (e) => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        this.handleApiKeyChange(provider, e.target.value);
                    }, 500);
                });
            }
        });

        // Bind to model selectors
        ['simple', 'standard', 'complex', 'vision'].forEach(category => {
            ['primary', 'fallback'].forEach(type => {
                const selector = document.querySelector(`select[name="toni_${category}_${type}"]`);
                if (selector) {
                    selector.addEventListener('change', (e) => {
                        this.handleModelSelection(category, type, e.target.value);
                    });
                }
            });
        });
    }

    /**
     * Handle API key changes
     */
    handleApiKeyChange(provider, value) {
        if (value && value.trim().length > 0) {
            this.activeProviders.add(provider);
        } else {
            this.activeProviders.delete(provider);
        }
        
        this.updateProviderIndicators();
        this.updateAllSelectors();
    }

    /**
     * Update provider status indicators
     */
    updateProviderIndicators() {
        Object.keys(this.modelConfig.providers).forEach(provider => {
            const indicator = document.querySelector(`#provider-status-${provider}`);
            if (indicator) {
                const isActive = this.activeProviders.has(provider);
                indicator.classList.toggle('active', isActive);
                indicator.classList.toggle('inactive', !isActive);
                
                // Update visual style
                if (isActive) {
                    indicator.innerHTML = `<span class="w-2 h-2 bg-green-500 rounded-full"></span> Active`;
                } else {
                    indicator.innerHTML = `<span class="w-2 h-2 bg-gray-400 rounded-full"></span> Not Configured`;
                }
            }
        });

        // Update active provider count
        const countElement = document.querySelector('#active-provider-count');
        if (countElement) {
            countElement.textContent = `${this.activeProviders.size} Provider${this.activeProviders.size !== 1 ? 's' : ''} Active`;
        }
    }

    /**
     * Update all model selectors
     */
    updateAllSelectors() {
        ['simple', 'standard', 'complex', 'vision'].forEach(category => {
            this.updateCategorySelectors(category);
        });
    }

    /**
     * Update selectors for a specific category
     */
    updateCategorySelectors(category) {
        const primarySelector = document.querySelector(`select[name="toni_${category}_primary"]`);
        const fallbackSelector = document.querySelector(`select[name="toni_${category}_fallback"]`);
        
        if (!primarySelector) return;

        // Get available models for this category
        const availableModels = this.getAvailableModels(category);
        
        // Store current selections
        const currentPrimary = primarySelector.value;
        const currentFallback = fallbackSelector ? fallbackSelector.value : 'none';
        
        // Rebuild primary selector
        this.rebuildSelector(primarySelector, availableModels, currentPrimary, category);
        
        // Rebuild fallback selector
        if (fallbackSelector) {
            this.rebuildSelector(fallbackSelector, availableModels, currentFallback, category, true);
        }

        // Update cost estimation
        this.updateCostEstimation(category);
    }

    /**
     * Get available models for a category
     */
    getAvailableModels(category) {
        const models = [];
        const taskConfig = this.modelConfig.task_categories[category];
        
        this.activeProviders.forEach(provider => {
            const providerConfig = this.modelConfig.providers[provider];
            
            Object.entries(providerConfig.models).forEach(([modelId, modelData]) => {
                // Check if model is suitable for this category
                if (!modelData.suitable_for.includes(category)) return;
                
                // Check required capabilities
                if (taskConfig.required_capabilities) {
                    const hasRequired = taskConfig.required_capabilities.every(cap => 
                        modelData.capabilities.includes(cap)
                    );
                    if (!hasRequired) return;
                }
                
                // Model is available
                models.push({
                    provider: provider,
                    providerName: providerConfig.name,
                    providerColor: providerConfig.color,
                    modelId: modelId,
                    ...modelData
                });
            });
        });
        
        // Sort by price (cheapest first)
        models.sort((a, b) => a.pricing.input - b.pricing.input);
        
        return models;
    }

    /**
     * Rebuild a selector with new options
     */
    rebuildSelector(selector, models, currentValue, category, isFallback = false) {
        // Clear current options
        selector.innerHTML = '';
        
        // Add "None" option for fallback
        if (isFallback) {
            const noneOption = document.createElement('option');
            noneOption.value = 'none';
            noneOption.textContent = 'None';
            selector.appendChild(noneOption);
        }
        
        // Group models by provider
        const modelsByProvider = {};
        models.forEach(model => {
            if (!modelsByProvider[model.provider]) {
                modelsByProvider[model.provider] = [];
            }
            modelsByProvider[model.provider].push(model);
        });
        
        // Add models grouped by provider
        Object.entries(modelsByProvider).forEach(([provider, providerModels]) => {
            const optgroup = document.createElement('optgroup');
            optgroup.label = this.modelConfig.providers[provider].name;
            
            providerModels.forEach(model => {
                const option = document.createElement('option');
                option.value = model.modelId;
                
                // Format pricing
                const inputPrice = this.formatPrice(model.pricing.input);
                const outputPrice = this.formatPrice(model.pricing.output);
                
                // Add status indicator
                let statusIcon = '';
                if (model.status === 'experimental' || model.status === 'preview') {
                    statusIcon = ' 🧪';
                } else if (model.status === 'coming_soon') {
                    statusIcon = ' 🔜';
                }
                
                // Format option text with pricing
                if (model.pricing.input === 0) {
                    option.textContent = `${model.name} (Free)${statusIcon}`;
                } else {
                    option.textContent = `${model.name} ($${inputPrice}/$${outputPrice})${statusIcon}`;
                }
                
                // Add data attributes for additional info
                option.dataset.provider = provider;
                option.dataset.inputPrice = model.pricing.input;
                option.dataset.outputPrice = model.pricing.output;
                option.dataset.contextWindow = model.context_window;
                option.dataset.capabilities = model.capabilities.join(',');
                
                // Disable if coming soon
                if (model.status === 'coming_soon') {
                    option.disabled = true;
                }
                
                optgroup.appendChild(option);
            });
            
            selector.appendChild(optgroup);
        });
        
        // Restore selection if possible
        if (currentValue && selector.querySelector(`option[value="${currentValue}"]`)) {
            selector.value = currentValue;
        } else if (!isFallback && selector.options.length > 0) {
            // Select first available option for primary
            selector.value = selector.options[0].value;
        }
        
        // Show/hide no models message
        const noModelsMsg = selector.parentElement.querySelector('.no-models-message');
        if (models.length === 0) {
            if (!noModelsMsg) {
                const msg = document.createElement('div');
                msg.className = 'no-models-message text-xs text-red-500 mt-1';
                msg.textContent = 'No models available. Please configure API keys.';
                selector.parentElement.appendChild(msg);
            }
            selector.disabled = true;
        } else {
            if (noModelsMsg) {
                noModelsMsg.remove();
            }
            selector.disabled = false;
        }
    }

    /**
     * Format price for display
     */
    formatPrice(price) {
        if (price === 0) return '0';
        if (price < 1) return price.toFixed(3).replace(/\.?0+$/, '');
        if (price < 10) return price.toFixed(2).replace(/\.?0+$/, '');
        return price.toFixed(0);
    }

    /**
     * Handle model selection
     */
    handleModelSelection(category, type, modelId) {
        this.selectedModels[category][type] = modelId;
        this.updateCostEstimation(category);
    }

    /**
     * Update cost estimation for a category
     */
    updateCostEstimation(category) {
        const estimationElement = document.querySelector(`#cost-estimate-${category}`);
        if (!estimationElement) return;
        
        const primarySelector = document.querySelector(`select[name="toni_${category}_primary"]`);
        const maxTokensInput = document.querySelector(`input[name="toni_${category}_max_tokens"]`);
        
        if (!primarySelector || !primarySelector.value || primarySelector.value === 'none') {
            estimationElement.textContent = '';
            return;
        }
        
        const selectedOption = primarySelector.selectedOptions[0];
        if (!selectedOption) return;
        
        const inputPrice = parseFloat(selectedOption.dataset.inputPrice);
        const outputPrice = parseFloat(selectedOption.dataset.outputPrice);
        const maxTokens = parseInt(maxTokensInput?.value || 1000);
        
        // Estimate costs (assuming 1:1 input/output ratio for simplicity)
        const estimatedInputCost = (maxTokens / 1000000) * inputPrice;
        const estimatedOutputCost = (maxTokens / 1000000) * outputPrice;
        const totalCost = estimatedInputCost + estimatedOutputCost;
        
        // Format display
        if (inputPrice === 0) {
            estimationElement.innerHTML = `
                <span class="text-green-600 dark:text-green-400">Free to use</span>
            `;
        } else {
            estimationElement.innerHTML = `
                Est. cost per request: <span class="font-medium">$${totalCost.toFixed(6)}</span>
                <br><span class="text-xs opacity-75">Context: ${this.formatNumber(selectedOption.dataset.contextWindow)} tokens</span>
            `;
        }
    }

    /**
     * Format large numbers
     */
    formatNumber(num) {
        const n = parseInt(num);
        if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M';
        if (n >= 1000) return (n / 1000).toFixed(0) + 'K';
        return n.toString();
    }

    /**
     * Get recommended models for each category
     */
    getRecommendations() {
        const recommendations = {};
        
        ['simple', 'standard', 'complex', 'vision'].forEach(category => {
            const models = this.getAvailableModels(category);
            if (models.length > 0) {
                recommendations[category] = {
                    cheapest: models[0], // Already sorted by price
                    best: this.findBestModel(models, category)
                };
            }
        });
        
        return recommendations;
    }

    /**
     * Find the best model based on price/performance
     */
    findBestModel(models, category) {
        // Simple heuristic: find model with best price/context ratio
        let bestModel = models[0];
        let bestScore = 0;
        
        models.forEach(model => {
            // Skip experimental/preview for "best" recommendation
            if (model.status === 'experimental' || model.status === 'preview') return;
            
            // Calculate score (context window / price)
            const price = model.pricing.input || 0.01; // Avoid division by zero
            const score = model.context_window / price;
            
            if (score > bestScore) {
                bestScore = score;
                bestModel = model;
            }
        });
        
        return bestModel;
    }
}

// Export for use in settings page
window.AIModelSelector = AIModelSelector;
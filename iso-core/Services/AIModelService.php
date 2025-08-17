<?php
/**
 * AI Model Service
 * Handles AI model configuration and selection
 */

class AIModelService
{
    private array $modelConfig;
    private array $activeProviders = [];
    
    public function __construct()
    {
        // Load model configuration
        $this->modelConfig = require dirname(__DIR__) . '/Config/ai-models.php';
    }
    
    /**
     * Get the complete model configuration
     */
    public function getModelConfig(): array
    {
        return $this->modelConfig;
    }
    
    /**
     * Get model configuration as JSON for JavaScript
     */
    public function getModelConfigJson(): string
    {
        return json_encode($this->modelConfig);
    }
    
    /**
     * Set active providers based on configured API keys
     */
    public function setActiveProviders(array $settings): void
    {
        $this->activeProviders = [];
        
        foreach ($this->modelConfig['providers'] as $provider => $config) {
            $keyField = $config['api_key_field'];
            if (!empty($settings[$keyField])) {
                $this->activeProviders[] = $provider;
            }
        }
    }
    
    /**
     * Get available models for a specific task category
     */
    public function getAvailableModels(string $category, array $settings): array
    {
        $this->setActiveProviders($settings);
        $availableModels = [];
        
        $taskConfig = $this->modelConfig['task_categories'][$category] ?? null;
        if (!$taskConfig) {
            return [];
        }
        
        foreach ($this->activeProviders as $provider) {
            $providerConfig = $this->modelConfig['providers'][$provider];
            
            foreach ($providerConfig['models'] as $modelId => $modelData) {
                // Check if model is suitable for this category
                if (!in_array($category, $modelData['suitable_for'])) {
                    continue;
                }
                
                // Check required capabilities
                if (isset($taskConfig['required_capabilities'])) {
                    $hasRequired = !array_diff(
                        $taskConfig['required_capabilities'],
                        $modelData['capabilities']
                    );
                    if (!$hasRequired) {
                        continue;
                    }
                }
                
                // Add model to available list
                $availableModels[$modelId] = array_merge($modelData, [
                    'provider' => $provider,
                    'provider_name' => $providerConfig['name'],
                    'model_id' => $modelId
                ]);
            }
        }
        
        // Sort by price (cheapest first)
        uasort($availableModels, function($a, $b) {
            return $a['pricing']['input'] <=> $b['pricing']['input'];
        });
        
        return $availableModels;
    }
    
    /**
     * Validate if a model is available for use
     */
    public function isModelAvailable(string $modelId, array $settings): bool
    {
        $this->setActiveProviders($settings);
        
        foreach ($this->activeProviders as $provider) {
            if (isset($this->modelConfig['providers'][$provider]['models'][$modelId])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get model details
     */
    public function getModelDetails(string $modelId): ?array
    {
        foreach ($this->modelConfig['providers'] as $provider => $providerConfig) {
            if (isset($providerConfig['models'][$modelId])) {
                return array_merge($providerConfig['models'][$modelId], [
                    'provider' => $provider,
                    'provider_name' => $providerConfig['name']
                ]);
            }
        }
        
        return null;
    }
    
    /**
     * Calculate estimated cost for a request
     */
    public function estimateCost(string $modelId, int $inputTokens, int $outputTokens): array
    {
        $model = $this->getModelDetails($modelId);
        if (!$model) {
            return ['error' => 'Model not found'];
        }
        
        $inputCost = ($inputTokens / 1000000) * $model['pricing']['input'];
        $outputCost = ($outputTokens / 1000000) * $model['pricing']['output'];
        
        return [
            'input_cost' => $inputCost,
            'output_cost' => $outputCost,
            'total_cost' => $inputCost + $outputCost,
            'currency' => 'USD'
        ];
    }
    
    /**
     * Get recommended models for each category
     */
    public function getRecommendations(array $settings): array
    {
        $recommendations = [];
        
        foreach ($this->modelConfig['task_categories'] as $category => $config) {
            $models = $this->getAvailableModels($category, $settings);
            
            if (!empty($models)) {
                // Get cheapest model
                $cheapest = reset($models);
                
                // Get best price/performance model
                $best = $this->findBestModel($models, $category);
                
                $recommendations[$category] = [
                    'cheapest' => $cheapest,
                    'best' => $best,
                    'total_available' => count($models)
                ];
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Find the best model based on price/performance ratio
     */
    private function findBestModel(array $models, string $category): array
    {
        $bestModel = reset($models);
        $bestScore = 0;
        
        foreach ($models as $model) {
            // Skip experimental/preview models for "best" recommendation
            if (in_array($model['status'], ['experimental', 'preview', 'coming_soon'])) {
                continue;
            }
            
            // Calculate score based on context window and price
            $price = $model['pricing']['input'] ?: 0.01;
            $score = $model['context_window'] / $price;
            
            // Bonus for additional capabilities
            if (in_array('function_calling', $model['capabilities'])) {
                $score *= 1.2;
            }
            if (in_array('vision', $model['capabilities'])) {
                $score *= 1.3;
            }
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestModel = $model;
            }
        }
        
        return $bestModel;
    }
    
    /**
     * Validate API key format (basic validation)
     */
    public function validateApiKey(string $provider, string $apiKey): array
    {
        $patterns = [
            'openai' => '/^sk-[a-zA-Z0-9]{32,}$/',
            'anthropic' => '/^sk-ant-api[a-zA-Z0-9-]{36,}$/',
            'google' => '/^AIza[a-zA-Z0-9-_]{35}$/',
            'groq' => '/^gsk_[a-zA-Z0-9]{32,}$/'
        ];
        
        if (!isset($patterns[$provider])) {
            return ['valid' => false, 'message' => 'Unknown provider'];
        }
        
        // For development, accept any non-empty key
        if (!empty($apiKey)) {
            return ['valid' => true, 'message' => 'Key format accepted'];
        }
        
        // Strict validation (disabled for now)
        /*
        if (preg_match($patterns[$provider], $apiKey)) {
            return ['valid' => true, 'message' => 'Valid key format'];
        }
        */
        
        return ['valid' => false, 'message' => 'Invalid key format'];
    }
}
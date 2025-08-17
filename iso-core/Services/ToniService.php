<?php
/**
 * Isotone - Toni AI Assistant Service
 * 
 * @copyright  2025 Rizonetech (Pty) Ltd
 * @license    MIT License
 * @author     Rizonetech Development Team
 */

use RedBeanPHP\R;

class ToniService
{
    /**
     * System prompts for Toni
     */
    private const SYSTEM_PROMPT = "You are Toni, a helpful AI assistant for Isotone. You help users with:
- Content creation and management
- System configuration
- Troubleshooting issues
- Writing and optimizing content
- Understanding Isotone features
- Best practices and tips

Be friendly, concise, and helpful. Focus on practical solutions.";

    /**
     * Conversation context window size
     */
    private const CONTEXT_WINDOW = 10;

    /**
     * Default model configuration
     */
    private const DEFAULT_MODEL = 'gpt-5-nano';
    
    /**
     * Model-specific configuration
     * GPT-5 models use the new Responses API with different parameters
     */
    private const MODEL_CONFIG = [
        'gpt-5-nano' => [
            'reasoning_effort' => 'minimal',  // Fast responses for chat
            'verbosity' => 'medium',         // Balanced output
            'api_type' => 'responses'        // New API
        ],
        'gpt-5-mini' => [
            'reasoning_effort' => 'low',     // Good balance
            'verbosity' => 'medium',         // Balanced output
            'api_type' => 'responses'        // New API
        ],
        'gpt-5' => [
            'reasoning_effort' => 'medium',  // Better reasoning
            'verbosity' => 'medium',         // Balanced output
            'api_type' => 'responses'        // New API
        ]
    ];

    /**
     * Get or create a conversation
     */
    public function getConversation(int $userId): array
    {
        try {
            // Get recent messages for context
            $messages = R::find('tonimessages', 
                'user_id = ? ORDER BY created_at DESC LIMIT ?', 
                [$userId, self::CONTEXT_WINDOW]
            );
            
            // Convert to array and reverse for chronological order
            $conversation = [];
            foreach (array_reverse($messages) as $message) {
                $conversation[] = [
                    'id' => $message->id,
                    'role' => $message->role,
                    'content' => $message->content,
                    'created_at' => $message->created_at
                ];
            }
            
            return $conversation;
        } catch (Exception $e) {
            error_log('Toni conversation error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Send a message to Toni
     */
    public function sendMessage(int $userId, string $message, bool $debug = false): array
    {
        try {
            // Store user message
            $userMessage = R::dispense('tonimessages');
            $userMessage->user_id = $userId;
            $userMessage->role = 'user';
            $userMessage->content = $message;
            $userMessage->created_at = date('Y-m-d H:i:s');
            R::store($userMessage);

            // Get conversation context
            $context = $this->getConversation($userId);

            // Generate AI response with debug info if requested
            if ($debug) {
                $debugInfo = [];
                $response = $this->generateResponseWithDebug($message, $context, $debugInfo);
                $result = [
                    'success' => true,
                    'response' => $response,
                    'debug' => $debugInfo
                ];
            } else {
                $response = $this->generateResponse($message, $context);
                $result = [
                    'success' => true,
                    'response' => $response
                ];
            }

            // Store AI response
            $aiMessage = R::dispense('tonimessages');
            $aiMessage->user_id = $userId;
            $aiMessage->role = 'assistant';
            $aiMessage->content = $response;
            $aiMessage->created_at = date('Y-m-d H:i:s');
            R::store($aiMessage);

            $result['message_id'] = $aiMessage->id;
            return $result;
        } catch (Exception $e) {
            error_log('Toni message error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to process message'
            ];
        }
    }

    /**
     * Generate response with debug information
     */
    private function generateResponseWithDebug(string $message, array $context, array &$debugInfo): string
    {
        $debugInfo['timestamp'] = date('Y-m-d H:i:s');
        $debugInfo['message'] = $message;
        $debugInfo['context_count'] = count($context);
        
        // Try to get AI response with debug tracking
        $aiResponse = $this->getAIResponseWithDebug($message, $context, $debugInfo);
        
        if ($aiResponse) {
            $debugInfo['ai_response_received'] = true;
            return $aiResponse;
        }
        
        $debugInfo['ai_response_received'] = false;
        $debugInfo['fallback_used'] = true;
        
        // Fallback to contextual responses
        return $this->getFallbackResponse($message);
    }


    /**
     * Get AI response from configured provider with debug info
     */
    private function getAIResponseWithDebug(string $message, array $context, array &$debugInfo): ?string
    {
        try {
            // Use the model selected for Toni chat function
            $provider = 'openai';
            $model = $this->getSetting('toni_chat_model', self::DEFAULT_MODEL);
            
            // Get default config and override with user settings
            $defaultConfig = self::MODEL_CONFIG[$model] ?? self::MODEL_CONFIG[self::DEFAULT_MODEL];
            $modelConfig = [
                'reasoning_effort' => $this->getSetting('toni_chat_reasoning_effort', $defaultConfig['reasoning_effort']),
                'verbosity' => $this->getSetting('toni_chat_verbosity', $defaultConfig['verbosity']),
                'api_type' => $defaultConfig['api_type']
            ];
            
            $timeout = intval($this->getSetting('toni_timeout', '30'));

            // Model pricing information
            $modelPricing = [
                'gpt-5-nano' => ['input' => 0.05, 'output' => 0.40],
                'gpt-5-mini' => ['input' => 0.25, 'output' => 2.00],
                'gpt-5' => ['input' => 1.25, 'output' => 10.00]
            ];
            
            $pricing = $modelPricing[$model] ?? $modelPricing['gpt-5-nano'];
            
            $debugInfo['provider'] = $provider;
            $debugInfo['model'] = $model;
            $debugInfo['api_type'] = $modelConfig['api_type'];
            $debugInfo['reasoning_effort'] = $modelConfig['reasoning_effort'];
            $debugInfo['verbosity'] = $modelConfig['verbosity'];
            $debugInfo['timeout'] = $timeout;
            // Estimate based on average message size (1000 tokens)
            $avgTokens = 1000;
            $debugInfo['estimated_cost'] = sprintf(
                '$%.6f per request (approx)',
                ($avgTokens / 1000000) * ($pricing['input'] + $pricing['output'])
            );

            $apiKey = $this->getSetting('openai_api_key');
            
            $debugInfo['openai_api_key_present'] = !empty($apiKey);
            
            if (empty($apiKey)) {
                $debugInfo['error'] = 'OpenAI API key is empty';
                return null;
            }
            
            return $this->callGPT5WithDebug($message, $context, $modelConfig, $timeout, $debugInfo, $model);
        } catch (Exception $e) {
            $debugInfo['exception'] = $e->getMessage();
        }
        
        return null;
    }

    /**
     * Call GPT-5 API with debug tracking using new Responses API
     */
    private function callGPT5WithDebug(string $message, array $context, array $modelConfig, int $timeout, array &$debugInfo, string $model = null): ?string
    {
        $apiKey = $this->getSetting('openai_api_key');
        if (!$model) {
            $model = self::DEFAULT_MODEL;
        }
        $orgId = $this->getSetting('openai_org_id');

        $debugInfo['api_call_started'] = microtime(true);

        // Build conversation input for Responses API
        $conversationHistory = "";
        
        // Add system prompt
        $conversationHistory .= "System: " . self::SYSTEM_PROMPT . "\n\n";
        
        // Add context messages
        foreach ($context as $contextMessage) {
            if ($contextMessage['role'] === 'user') {
                $conversationHistory .= "User: " . $contextMessage['content'] . "\n\n";
            } else if ($contextMessage['role'] === 'assistant') {
                $conversationHistory .= "Assistant: " . $contextMessage['content'] . "\n\n";
            }
        }
        
        // Add current message
        $conversationHistory .= "User: " . $message;

        // Use GPT-5 Responses API format
        $payload = [
            'model' => $model,
            'input' => $conversationHistory,
            'reasoning' => [
                'effort' => $modelConfig['reasoning_effort']
            ],
            'text' => [
                'verbosity' => $modelConfig['verbosity']
            ]
        ];

        $debugInfo['payload_size'] = strlen(json_encode($payload));
        $debugInfo['conversation_length'] = strlen($conversationHistory);

        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ];
        
        if (!empty($orgId)) {
            $headers[] = 'OpenAI-Organization: ' . $orgId;
            $debugInfo['org_id_used'] = true;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/responses');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for local development
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $debugInfo['api_call_completed'] = microtime(true);
        $debugInfo['api_call_duration'] = round($debugInfo['api_call_completed'] - $debugInfo['api_call_started'], 3);
        $debugInfo['http_code'] = $httpCode;

        if ($curlError) {
            $debugInfo['curl_error'] = $curlError;
            return null;
        }

        if ($response === false || $httpCode !== 200) {
            $debugInfo['api_error'] = 'HTTP ' . $httpCode;
            $debugInfo['api_response'] = substr($response, 0, 500); // First 500 chars of error
            return null;
        }

        // Log the raw response for debugging
        error_log("GPT-5 Raw API Response: " . substr($response, 0, 1000));
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $debugInfo['json_error'] = json_last_error_msg();
            $debugInfo['raw_response'] = substr($response, 0, 500);
            error_log("JSON decode error: " . json_last_error_msg());
            return null;
        }
        
        // Log the actual response structure for debugging
        error_log("GPT-5 API Response Structure: " . json_encode($data));
        
        // Try different response formats
        // 1. GPT-5 Responses API format - output is an array with message objects
        if (isset($data['output']) && is_array($data['output'])) {
            // Find the message type in the output array
            foreach ($data['output'] as $outputItem) {
                if ($outputItem['type'] === 'message' && isset($outputItem['content'][0]['text'])) {
                    $text = $outputItem['content'][0]['text'];
                    $debugInfo['reasoning_tokens'] = $data['usage']['output_tokens_details']['reasoning_tokens'] ?? 'unknown';
                    $debugInfo['total_tokens'] = $data['usage']['total_tokens'] ?? 'unknown';
                    $debugInfo['response_length'] = strlen($text);
                    return $text;
                }
            }
        }
        
        // 2. Responses API format with 'output_text' (alternative format)
        if (isset($data['output_text'])) {
            $debugInfo['reasoning_tokens'] = $data['usage']['reasoning_tokens'] ?? 'unknown';
            $debugInfo['total_tokens'] = $data['usage']['total_tokens'] ?? 'unknown';
            $debugInfo['response_length'] = strlen($data['output_text']);
            return $data['output_text'];
        }
        
        // 3. Chat Completions format (fallback)
        if (isset($data['choices'][0]['message']['content'])) {
            $debugInfo['tokens_used'] = $data['usage']['total_tokens'] ?? 'unknown';
            $debugInfo['response_length'] = strlen($data['choices'][0]['message']['content']);
            return $data['choices'][0]['message']['content'];
        }
        
        $debugInfo['api_parse_error'] = 'Unexpected response format';
        $debugInfo['api_response_keys'] = array_keys($data ?? []);
        $debugInfo['raw_response_sample'] = substr($response, 0, 200);
        
        // Add full response to debug for troubleshooting
        if (strlen($response) < 1000) {
            $debugInfo['full_response'] = $response;
        }
        
        return null;
    }

    /**
     * Call Anthropic API with debug tracking
     */
    private function callAnthropicWithDebug(string $message, array $context, int $maxTokens, int $timeout, array &$debugInfo, string $model = null): ?string
    {
        // Similar implementation for Anthropic with debug tracking
        // For brevity, using the existing callAnthropic logic with added debug
        $result = $this->callAnthropic($message, $context, $maxTokens, $timeout, $model);
        $debugInfo['anthropic_called'] = true;
        return $result;
    }

    /**
     * Get fallback response for common queries
     */
    private function getFallbackResponse(string $message): string
    {
        $message_lower = strtolower($message);
        
        // Help queries
        if (strpos($message_lower, 'help') !== false) {
            return "I'm here to help! You can ask me about:
• Creating and managing content
• Configuring your site settings
• Using plugins and themes
• Optimizing your content for SEO
• Troubleshooting common issues

What would you like to know more about?";
        }
        
        // Default response
        return "I understand you're asking about: \"" . $message . "\". 

While I'm still learning about this specific topic, I can help you with:
• Content management (posts, pages, media)
• Site configuration and settings
• Plugin and theme management
• SEO optimization
• General troubleshooting

Could you provide more details about what you're trying to accomplish?";
    }

    /**
     * Get AI response from OpenAI
     */
    private function getAIResponse(string $message, array $context): ?string
    {
        try {
            // Use the model selected for Toni chat function
            $model = $this->getSetting('toni_chat_model', self::DEFAULT_MODEL);
            
            // Get default config and override with user settings
            $defaultConfig = self::MODEL_CONFIG[$model] ?? self::MODEL_CONFIG[self::DEFAULT_MODEL];
            $modelConfig = [
                'reasoning_effort' => $this->getSetting('toni_chat_reasoning_effort', $defaultConfig['reasoning_effort']),
                'verbosity' => $this->getSetting('toni_chat_verbosity', $defaultConfig['verbosity']),
                'api_type' => $defaultConfig['api_type']
            ];
            
            $timeout = intval($this->getSetting('toni_timeout', '30'));

            // Debug logging
            error_log("Toni Debug - Using model: $model, Reasoning: {$modelConfig['reasoning_effort']}, Verbosity: {$modelConfig['verbosity']}");

            $apiKey = $this->getSetting('openai_api_key');
            error_log("Toni Debug - OpenAI API Key present: " . (!empty($apiKey) ? 'YES' : 'NO'));
            
            if (empty($apiKey)) {
                error_log("Toni Debug - OpenAI API key is empty, falling back to default responses");
                return null;
            }
            
            return $this->callGPT5($message, $context, $modelConfig, $timeout, $model);
        } catch (Exception $e) {
            error_log('Toni AI API error: ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Call GPT-5 API using new Responses API
     */
    private function callGPT5(string $message, array $context, array $modelConfig, int $timeout, string $model = null): ?string
    {
        $apiKey = $this->getSetting('openai_api_key');
        if (!$model) {
            $model = self::DEFAULT_MODEL;
        }
        $orgId = $this->getSetting('openai_org_id');
        
        if (empty($apiKey)) {
            error_log("Toni Debug - OpenAI API key is empty, returning null");
            return null;
        }

        error_log("Toni Debug - Making GPT-5 API call with model: $model, reasoning: {$modelConfig['reasoning_effort']}");

        // Build conversation input for Responses API
        $conversationHistory = "";
        
        // Add system prompt
        $conversationHistory .= "System: " . self::SYSTEM_PROMPT . "\n\n";
        
        // Add context messages
        foreach ($context as $contextMessage) {
            if ($contextMessage['role'] === 'user') {
                $conversationHistory .= "User: " . $contextMessage['content'] . "\n\n";
            } else if ($contextMessage['role'] === 'assistant') {
                $conversationHistory .= "Assistant: " . $contextMessage['content'] . "\n\n";
            }
        }
        
        // Add current message
        $conversationHistory .= "User: " . $message;

        // Use GPT-5 Responses API format
        $payload = [
            'model' => $model,
            'input' => $conversationHistory,
            'reasoning' => [
                'effort' => $modelConfig['reasoning_effort']
            ],
            'text' => [
                'verbosity' => $modelConfig['verbosity']
            ]
        ];

        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ];
        
        if (!empty($orgId)) {
            $headers[] = 'OpenAI-Organization: ' . $orgId;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/responses');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for local development
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            error_log('GPT-5 API error: HTTP ' . $httpCode . ' - ' . $response);
            return null;
        }

        // Log raw response for debugging
        error_log("GPT-5 Raw Response (first 500 chars): " . substr($response, 0, 500));
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error in callGPT5: " . json_last_error_msg());
            return null;
        }
        
        // Log the actual response structure for debugging
        error_log("GPT-5 API Response Keys: " . implode(', ', array_keys($data ?? [])));
        
        // Try different response formats
        // 1. GPT-5 Responses API format - output is an array with message objects
        if (isset($data['output']) && is_array($data['output'])) {
            // Find the message type in the output array
            foreach ($data['output'] as $outputItem) {
                if ($outputItem['type'] === 'message' && isset($outputItem['content'][0]['text'])) {
                    return $outputItem['content'][0]['text'];
                }
            }
        }
        
        // 2. Responses API format with 'output_text' (alternative format)
        if (isset($data['output_text'])) {
            return $data['output_text'];
        }
        
        // 3. Responses API format with 'text'
        if (isset($data['text'])) {
            return $data['text'];
        }
        
        // 4. Chat Completions format (fallback)
        if (isset($data['choices'][0]['message']['content'])) {
            return $data['choices'][0]['message']['content'];
        }
        
        error_log("GPT-5 API Error: Could not find response text in any expected field");
        error_log("Full response structure: " . json_encode($data));
        return null;
    }

    /**
     * Call Anthropic API
     */
    private function callAnthropic(string $message, array $context, int $maxTokens, int $timeout, string $model = null): ?string
    {
        $apiKey = $this->getSetting('anthropic_api_key');
        if (!$model) {
            $model = 'claude-3-haiku-20240307'; // Default to cheapest Claude model
        }
        
        if (empty($apiKey)) {
            return null;
        }

        // Build messages array
        $messages = [];
        
        // Add context messages (skip system messages for Anthropic)
        foreach ($context as $contextMessage) {
            if ($contextMessage['role'] !== 'system') {
                $messages[] = [
                    'role' => $contextMessage['role'],
                    'content' => $contextMessage['content']
                ];
            }
        }
        
        // Add current message
        $messages[] = ['role' => 'user', 'content' => $message];

        $payload = [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'system' => self::SYSTEM_PROMPT,
            'messages' => $messages
        ];

        $headers = [
            'x-api-key: ' . $apiKey,
            'Content-Type: application/json',
            'anthropic-version: 2023-06-01'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.anthropic.com/v1/messages');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for local development
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            error_log('Anthropic API error: HTTP ' . $httpCode . ' - ' . $response);
            return null;
        }

        $data = json_decode($response, true);
        return $data['content'][0]['text'] ?? null;
    }

    /**
     * Get setting value from database
     */
    private function getSetting(string $key, string $default = ''): string
    {
        try {
            $setting = R::findOne('settings', 'setting_key = ?', [$key]);
            return $setting ? $setting->setting_value : $default;
        } catch (Exception $e) {
            error_log('Error getting setting ' . $key . ': ' . $e->getMessage());
            return $default;
        }
    }

    /**
     * Generate AI response using configured AI provider
     */
    private function generateResponse(string $message, array $context): string
    {
        // Try to get AI response from configured provider
        $aiResponse = $this->getAIResponse($message, $context);
        if ($aiResponse) {
            return $aiResponse;
        }
        
        // Fallback to contextual responses based on keywords
        $message_lower = strtolower($message);
        
        // Help queries
        if (strpos($message_lower, 'help') !== false) {
            return "I'm here to help! You can ask me about:
• Creating and managing content
• Configuring your site settings
• Using plugins and themes
• Optimizing your content for SEO
• Troubleshooting common issues

What would you like to know more about?";
        }
        
        // Content creation
        if (strpos($message_lower, 'post') !== false || strpos($message_lower, 'page') !== false) {
            return "To create a new post or page:
1. Go to Posts → Add New (or Pages → Add New)
2. Enter your title and content
3. Set your categories and tags
4. Configure SEO settings if needed
5. Click Publish when ready

Would you like tips on writing engaging content?";
        }
        
        // Plugin queries
        if (strpos($message_lower, 'plugin') !== false) {
            return "Isotone supports WordPress-compatible plugins. You can:
• Install plugins from Plugins → Add New
• Activate/deactivate from Plugins → Installed
• Configure plugin settings from their menu items

Need help with a specific plugin?";
        }
        
        // Theme queries
        if (strpos($message_lower, 'theme') !== false) {
            return "To manage themes:
• Go to Appearance → Themes to see installed themes
• Click 'Activate' to switch themes
• Use Appearance → Customize for theme options
• Edit theme files from Appearance → Theme Editor (be careful!)

Looking for theme recommendations?";
        }
        
        // SEO queries
        if (strpos($message_lower, 'seo') !== false) {
            return "Here are some SEO best practices for Isotone:
• Use descriptive, keyword-rich titles (60 chars max)
• Write unique meta descriptions (155 chars max)
• Use header tags (H1, H2, H3) properly
• Optimize images with alt text
• Create SEO-friendly URLs
• Build internal links between related content

Want specific SEO advice for your content?";
        }
        
        // Settings queries
        if (strpos($message_lower, 'setting') !== false || strpos($message_lower, 'config') !== false) {
            return "You can configure Isotone from the Settings menu:
• General: Site title, tagline, timezone
• Reading: Homepage display, posts per page
• Writing: Default categories, post format
• Media: Image sizes, upload settings
• Permalinks: URL structure
• Privacy: Privacy policy page

Which settings would you like to adjust?";
        }
        
        // Greeting
        if (strpos($message_lower, 'hello') !== false || strpos($message_lower, 'hi') !== false) {
            return "Hello! I'm Toni, your AI assistant for Isotone. I'm here to help you manage your content, configure your site, and make the most of Isotone's features. What can I help you with today?";
        }
        
        // Thank you
        if (strpos($message_lower, 'thank') !== false) {
            return "You're welcome! I'm always here to help. Feel free to ask if you need anything else!";
        }
        
        // Default response
        return "I understand you're asking about: \"" . $message . "\". 

While I'm still learning about this specific topic, I can help you with:
• Content management (posts, pages, media)
• Site configuration and settings
• Plugin and theme management
• SEO optimization
• General troubleshooting

Could you provide more details about what you're trying to accomplish?";
    }

    /**
     * Clear conversation history
     */
    public function clearConversation(int $userId): bool
    {
        try {
            R::exec('DELETE FROM tonimessages WHERE user_id = ?', [$userId]);
            return true;
        } catch (Exception $e) {
            error_log('Toni clear conversation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get suggested actions based on user activity
     */
    public function getSuggestions(int $userId): array
    {
        $suggestions = [];
        
        // Check if user has posts
        $postCount = R::count('content', 'type = ? AND author_id = ?', ['post', $userId]);
        if ($postCount == 0) {
            $suggestions[] = [
                'icon' => 'document-text',
                'title' => 'Create your first post',
                'description' => 'Start sharing your content with the world',
                'action' => '/isotone/iso-admin/post-edit.php?action=new'
            ];
        }
        
        // Check if site has pages
        $pageCount = R::count('content', 'type = ?', ['page']);
        if ($pageCount < 2) {
            $suggestions[] = [
                'icon' => 'collection',
                'title' => 'Add essential pages',
                'description' => 'Create About, Contact, and Privacy pages',
                'action' => '/isotone/iso-admin/page-edit.php?action=new'
            ];
        }
        
        // Check for plugins
        $suggestions[] = [
            'icon' => 'puzzle',
            'title' => 'Explore plugins',
            'description' => 'Enhance your site with powerful plugins',
            'action' => '/isotone/iso-admin/plugins.php'
        ];
        
        // SEO reminder
        $suggestions[] = [
            'icon' => 'search',
            'title' => 'Optimize for search engines',
            'description' => 'Improve your content visibility',
            'action' => '#',
            'message' => 'How can I improve my SEO?'
        ];
        
        return $suggestions;
    }
}
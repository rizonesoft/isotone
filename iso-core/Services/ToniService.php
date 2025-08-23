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

Be friendly, concise, and helpful. Focus on practical solutions.

When you receive [Page Context] messages, understand the user is on that specific page and tailor your responses accordingly. Offer relevant help for that page.";

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
            $messages = R::find('toni', 
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
     * Send a message with an image to Toni (using GPT-5 vision)
     */
    public function sendMessageWithImage(int $userId, string $message, string $imageData, bool $debug = false): array
    {
        try {
            // Store user message
            $userMessage = R::dispense('toni');
            $userMessage->user_id = $userId;
            $userMessage->role = 'user';
            $userMessage->content = $message;
            $userMessage->has_image = true;
            $userMessage->created_at = date('Y-m-d H:i:s');
            R::store($userMessage);

            // Get conversation context
            $context = $this->getConversation($userId);

            // Generate AI response with image
            if ($debug) {
                $debugInfo = [];
                $response = $this->generateResponseWithImageDebug($message, $imageData, $context, $debugInfo);
                $result = [
                    'success' => true,
                    'response' => $response,
                    'debug' => $debugInfo
                ];
            } else {
                $response = $this->generateResponseWithImage($message, $imageData, $context);
                $result = [
                    'success' => true,
                    'response' => $response
                ];
            }

            // Store AI response
            $aiMessage = R::dispense('toni');
            $aiMessage->user_id = $userId;
            $aiMessage->role = 'assistant';
            $aiMessage->content = $response;
            $aiMessage->created_at = date('Y-m-d H:i:s');
            R::store($aiMessage);

            $result['message_id'] = $aiMessage->id;
            return $result;
        } catch (Exception $e) {
            error_log('Toni message with image error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to process message with image'
            ];
        }
    }
    
    /**
     * Send a message to Toni
     */
    public function sendMessage(int $userId, string $message, bool $debug = false): array
    {
        try {
            // Store user message
            $userMessage = R::dispense('toni');
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
            $aiMessage = R::dispense('toni');
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
     * Generate response with image and debug information
     */
    private function generateResponseWithImageDebug(string $message, string $imageData, array $context, array &$debugInfo): string
    {
        $debugInfo['timestamp'] = date('Y-m-d H:i:s');
        $debugInfo['message'] = $message;
        $debugInfo['has_image'] = true;
        $debugInfo['context_count'] = count($context);
        
        // Try to get AI response with image
        $aiResponse = $this->getAIResponseWithImageDebug($message, $imageData, $context, $debugInfo);
        
        if ($aiResponse) {
            $debugInfo['ai_response_received'] = true;
            return $aiResponse;
        }
        
        $debugInfo['ai_response_received'] = false;
        $debugInfo['fallback_used'] = true;
        
        // Fallback response for image
        return "I can see you've shared a screenshot. While I'm having trouble analyzing it right now, I can help you with:\n\n" . 
               "• Understanding what's on your screen\n" .
               "• Troubleshooting any issues you see\n" .
               "• Explaining UI elements or error messages\n\n" .
               "What specifically would you like help with regarding this screenshot?";
    }
    
    /**
     * Generate response with image
     */
    private function generateResponseWithImage(string $message, string $imageData, array $context): string
    {
        // Try to get AI response with image
        $aiResponse = $this->getAIResponseWithImage($message, $imageData, $context);
        if ($aiResponse) {
            return $aiResponse;
        }
        
        // Fallback response
        return "I can see you've shared a screenshot. What would you like me to help you with?";
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
     * Get AI response with image from configured provider with debug info
     */
    private function getAIResponseWithImageDebug(string $message, string $imageData, array $context, array &$debugInfo): ?string
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
            $debugInfo['has_image'] = true;
            
            $apiKey = $this->getSetting('openai_api_key');
            
            $debugInfo['openai_api_key_present'] = !empty($apiKey);
            
            if (empty($apiKey)) {
                $debugInfo['error'] = 'OpenAI API key is empty';
                return null;
            }
            
            return $this->callGPT5WithImageDebug($message, $imageData, $context, $modelConfig, $timeout, $debugInfo, $model);
        } catch (Exception $e) {
            $debugInfo['exception'] = $e->getMessage();
        }
        
        return null;
    }
    
    /**
     * Get AI response with image
     */
    private function getAIResponseWithImage(string $message, string $imageData, array $context): ?string
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

            $apiKey = $this->getSetting('openai_api_key');
            
            if (empty($apiKey)) {
                error_log("Toni Debug - OpenAI API key is empty, returning null");
                return null;
            }
            
            return $this->callGPT5WithImage($message, $imageData, $context, $modelConfig, $timeout, $model);
        } catch (Exception $e) {
            error_log('Toni AI API error with image: ' . $e->getMessage());
        }
        
        return null;
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
     * Call GPT-5 API with image and debug tracking
     */
    private function callGPT5WithImageDebug(string $message, string $imageData, array $context, array $modelConfig, int $timeout, array &$debugInfo, string $model = null): ?string
    {
        $apiKey = $this->getSetting('openai_api_key');
        if (!$model) {
            $model = self::DEFAULT_MODEL;
        }
        $orgId = $this->getSetting('openai_org_id');

        $debugInfo['api_call_started'] = microtime(true);

        // Build conversation input for Responses API with image
        $conversationHistory = "";
        
        // Add system prompt
        $conversationHistory .= "System: " . self::SYSTEM_PROMPT . "\n\n";
        
        // Add context messages (limited for image requests)
        $recentContext = array_slice($context, -3); // Only last 3 messages with images
        foreach ($recentContext as $contextMessage) {
            if ($contextMessage['role'] === 'user') {
                $conversationHistory .= "User: " . $contextMessage['content'] . "\n\n";
            } else if ($contextMessage['role'] === 'assistant') {
                $conversationHistory .= "Assistant: " . $contextMessage['content'] . "\n\n";
            }
        }
        
        // Add current message with image description
        $conversationHistory .= "User: " . $message . "\n[User has shared a screenshot - please analyze the visual content]";

        // Ensure image data has proper format
        if (strpos($imageData, 'data:image') !== 0) {
            error_log("GPT-5 Image: Invalid image data format");
            return null;
        }
        
        // Log image data info
        error_log("GPT-5 Image: Data URL prefix: " . substr($imageData, 0, 50));
        error_log("GPT-5 Image: Data length: " . strlen($imageData));
        
        // Use GPT-5 Responses API format with image
        // According to OpenAI docs, use input_text and input_image types
        $payload = [
            'model' => $model,
            'input' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => $conversationHistory
                        ],
                        [
                            'type' => 'input_image',
                            'image_url' => $imageData  // Direct data URL
                        ]
                    ]
                ]
            ],
            'reasoning' => [
                'effort' => $modelConfig['reasoning_effort']
            ],
            'text' => [
                'verbosity' => $modelConfig['verbosity']
            ]
        ];

        $debugInfo['payload_size'] = strlen(json_encode($payload));
        $debugInfo['has_image'] = true;
        $debugInfo['image_size'] = strlen($imageData);

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
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout + 10); // Add extra time for image processing
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
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
            $debugInfo['api_response'] = substr($response, 0, 500);
            return null;
        }

        // Log the raw response for debugging
        error_log("GPT-5 Image API Response: " . substr($response, 0, 1000));
        
        $data = json_decode($response, true);
        
        // Store full response structure for debugging
        if ($data) {
            $debugInfo['api_response_keys'] = array_keys($data);
            if (isset($data['output'])) {
                $debugInfo['output_structure'] = array_map(function($item) {
                    return ['type' => $item['type'] ?? 'unknown'];
                }, $data['output']);
            }
        }
        
        // Track token usage if available
        if (isset($data['usage'])) {
            $this->trackTokenUsage($model, $data['usage']);
        }
        
        // Parse GPT-5 Responses API format
        // 1. Primary format - output array with message objects
        if (isset($data['output']) && is_array($data['output'])) {
            foreach ($data['output'] as $outputItem) {
                if (isset($outputItem['type']) && $outputItem['type'] === 'message') {
                    // Check for content array structure
                    if (isset($outputItem['content']) && is_array($outputItem['content'])) {
                        foreach ($outputItem['content'] as $content) {
                            // Look for text type content
                            if (isset($content['type']) && $content['type'] === 'text' && isset($content['text'])) {
                                $text = $content['text'];
                                $debugInfo['content_type'] = 'text';
                                $debugInfo['reasoning_tokens'] = $data['usage']['output_tokens_details']['reasoning_tokens'] ?? 'unknown';
                                $debugInfo['total_tokens'] = $data['usage']['total_tokens'] ?? 'unknown';
                                $debugInfo['response_length'] = strlen($text);
                                return $text;
                            }
                            // Fallback for simpler structure
                            if (isset($content['text'])) {
                                $text = $content['text'];
                                $debugInfo['content_type'] = 'text_direct';
                                $debugInfo['response_length'] = strlen($text);
                                return $text;
                            }
                        }
                    }
                    // Handle direct string content
                    if (isset($outputItem['content']) && is_string($outputItem['content'])) {
                        $text = $outputItem['content'];
                        $debugInfo['content_type'] = 'string';
                        $debugInfo['response_length'] = strlen($text);
                        return $text;
                    }
                }
            }
        }
        
        // 2. Alternative format with direct output_text
        if (isset($data['output_text'])) {
            $debugInfo['content_type'] = 'output_text';
            $debugInfo['reasoning_tokens'] = $data['usage']['reasoning_tokens'] ?? 'unknown';
            $debugInfo['total_tokens'] = $data['usage']['total_tokens'] ?? 'unknown';
            $debugInfo['response_length'] = strlen($data['output_text']);
            return $data['output_text'];
        }
        
        // 3. Direct output string
        if (isset($data['output']) && is_string($data['output'])) {
            $debugInfo['content_type'] = 'output_string';
            $debugInfo['response_length'] = strlen($data['output']);
            return $data['output'];
        }
        
        $debugInfo['api_parse_error'] = 'Unexpected response format';
        $debugInfo['full_response_sample'] = substr(json_encode($data), 0, 500);
        if (isset($data['output']) && is_array($data['output']) && !empty($data['output'])) {
            $debugInfo['first_output_item'] = $data['output'][0];
        }
        return null;
    }
    
    /**
     * Call GPT-5 API with image
     */
    private function callGPT5WithImage(string $message, string $imageData, array $context, array $modelConfig, int $timeout, string $model = null): ?string
    {
        $apiKey = $this->getSetting('openai_api_key');
        if (!$model) {
            $model = self::DEFAULT_MODEL;
        }
        $orgId = $this->getSetting('openai_org_id');
        
        if (empty($apiKey)) {
            error_log("Toni Debug - OpenAI API key is empty");
            return null;
        }

        error_log("Toni Debug - Making GPT-5 API call with image, model: $model");

        // Build conversation input with limited context for image requests
        $conversationHistory = "System: " . self::SYSTEM_PROMPT . "\n\n";
        
        // Only include last few messages with images
        $recentContext = array_slice($context, -3);
        foreach ($recentContext as $contextMessage) {
            if ($contextMessage['role'] === 'user') {
                $conversationHistory .= "User: " . $contextMessage['content'] . "\n\n";
            } else if ($contextMessage['role'] === 'assistant') {
                $conversationHistory .= "Assistant: " . $contextMessage['content'] . "\n\n";
            }
        }
        
        $conversationHistory .= "User: " . $message;

        // Ensure image data has proper format
        if (strpos($imageData, 'data:image') !== 0) {
            error_log("GPT-5 Image: Invalid image data format");
            return null;
        }
        
        // Log image data info
        error_log("GPT-5 Image: Data URL prefix: " . substr($imageData, 0, 50));
        error_log("GPT-5 Image: Data length: " . strlen($imageData));
        
        // Use GPT-5 Responses API format with image
        // According to OpenAI docs, use input_text and input_image types
        $payload = [
            'model' => $model,
            'input' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => $conversationHistory
                        ],
                        [
                            'type' => 'input_image',
                            'image_url' => $imageData  // Direct data URL
                        ]
                    ]
                ]
            ],
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
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout + 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            error_log('GPT-5 Image API error: HTTP ' . $httpCode . ' - ' . $response);
            return null;
        }

        $data = json_decode($response, true);
        
        // Log response for debugging
        error_log("GPT-5 Image Response (first 500 chars): " . substr($response, 0, 500));
        
        // Track token usage if available
        if (isset($data['usage'])) {
            $this->trackTokenUsage($model, $data['usage']);
        }
        
        // Parse response - GPT-5 Responses API format
        // Primary format: output array with message objects
        if (isset($data['output']) && is_array($data['output'])) {
            foreach ($data['output'] as $outputItem) {
                // Look for message type with content
                if (isset($outputItem['type']) && $outputItem['type'] === 'message') {
                    // Check for content array structure
                    if (isset($outputItem['content']) && is_array($outputItem['content'])) {
                        foreach ($outputItem['content'] as $content) {
                            if (isset($content['type']) && $content['type'] === 'text' && isset($content['text'])) {
                                error_log("GPT-5 Image: Found text in content array (type=text)");
                                return $content['text'];
                            }
                            // Fallback for simpler structure
                            if (isset($content['text'])) {
                                error_log("GPT-5 Image: Found text in content array (direct)");
                                return $content['text'];
                            }
                        }
                    }
                    // Handle direct string content
                    if (isset($outputItem['content']) && is_string($outputItem['content'])) {
                        error_log("GPT-5 Image: Found direct content string");
                        return $outputItem['content'];
                    }
                }
            }
        }
        
        // Alternative: direct output_text field
        if (isset($data['output_text'])) {
            error_log("GPT-5 Image: Found output_text");
            return $data['output_text'];
        }
        
        // Alternative: output string
        if (isset($data['output']) && is_string($data['output'])) {
            error_log("GPT-5 Image: Found direct output string");
            return $data['output'];
        }
        
        // Log full structure for debugging
        error_log("GPT-5 Image API Error: Unexpected response format.");
        error_log("Response keys: " . json_encode(array_keys($data ?? [])));
        if (isset($data['output']) && is_array($data['output']) && !empty($data['output'])) {
            error_log("First output item structure: " . json_encode($data['output'][0]));
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
        
        // Track token usage if available
        if (isset($data['usage'])) {
            $this->trackTokenUsage($model, $data['usage']);
        }
        
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
        
        // Track token usage if available
        if (isset($data['usage'])) {
            $this->trackTokenUsage($model, $data['usage']);
        }
        
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
     * Track token usage to database
     */
    private function trackTokenUsage(string $model, array $usage): void
    {
        try {
            $record = R::dispense('tokenusage');
            $record->date = date('Y-m-d H:i:s');
            $record->provider = 'openai';
            $record->model = $model;
            $record->input_tokens = $usage['input_tokens'] ?? 0;
            $record->output_tokens = $usage['output_tokens'] ?? 0;
            $record->total_tokens = $usage['total_tokens'] ?? 0;
            
            // Calculate reasoning tokens if available
            if (isset($usage['output_tokens_details']['reasoning_tokens'])) {
                $record->reasoning_tokens = $usage['output_tokens_details']['reasoning_tokens'];
            }
            
            // Calculate cost based on model pricing (per 1M tokens)
            $modelPricing = [
                'gpt-5-nano' => ['input' => 0.05, 'output' => 0.40],
                'gpt-5-mini' => ['input' => 0.25, 'output' => 2.00],
                'gpt-5' => ['input' => 1.25, 'output' => 10.00]
            ];
            
            $pricing = $modelPricing[$model] ?? $modelPricing['gpt-5-nano'];
            $inputCost = ($record->input_tokens / 1000000) * $pricing['input'];
            $outputCost = ($record->output_tokens / 1000000) * $pricing['output'];
            $record->cost = round($inputCost + $outputCost, 6);
            
            R::store($record);
            
            error_log("Token usage tracked: Model=$model, Input={$record->input_tokens}, Output={$record->output_tokens}, Cost=\${$record->cost}");
        } catch (Exception $e) {
            error_log("Error tracking token usage: " . $e->getMessage());
        }
    }
    
    /**
     * Get setting value from database
     */
    private function getSetting(string $key, string $default = ''): string
    {
        try {
            $setting = R::findOne('setting', 'setting_key = ?', [$key]);
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
            R::exec('DELETE FROM toni WHERE user_id = ?', [$userId]);
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
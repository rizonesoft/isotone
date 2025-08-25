<?php
/**
 * OpenAI Usage API Endpoint
 * Fetches billing and usage information from OpenAI API
 */

// Check authentication
require_once dirname(__DIR__, 2) . '/iso-admin/auth.php';

// Load configuration
require_once dirname(dirname(__DIR__)) . '/config.php';
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use RedBeanPHP\R;

// Initialize database connection
if (!R::testConnection()) {
    $host = defined('DB_HOST') ? DB_HOST : 'localhost';
    $dbname = defined('DB_NAME') ? DB_NAME : 'isotone_db';
    $user = defined('DB_USER') ? DB_USER : 'root';
    $pass = defined('DB_PASSWORD') ? DB_PASSWORD : '';
    
    try {
        R::setup("mysql:host=$host;dbname=$dbname", $user, $pass);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }
}

// Helper function to get settings from database
function getSetting($key, $default = '') {
    $setting = R::findOne('setting', 'setting_key = ?', [$key]);
    return $setting ? $setting->setting_value : $default;
}

// Set JSON response header
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['isotone_admin_user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get OpenAI API key
$apiKey = getSetting('openai_api_key');

if (empty($apiKey)) {
    echo json_encode([
        'success' => false,
        'error' => 'OpenAI API key not configured'
    ]);
    exit;
}

// Fetch LOCAL usage data (OpenAI billing API requires browser session authentication)
try {
    // Get current date for billing cycle
    $currentDate = date('Y-m-d');
    $startDate = date('Y-m-01'); // First day of current month
    $endDate = date('Y-m-t'); // Last day of current month
    
    // Initialize response data
    $responseData = [
        'success' => true,
        'usage' => [
            'current_spend' => 0,
            'total_tokens' => 0,
            'api_calls' => 0,
            'by_model' => []
        ],
        'billing' => [
            'credits' => 0,
            'limit' => floatval(getSetting('openai_spend_limit', '120'))
        ],
        'notice' => 'Usage data is tracked locally. OpenAI billing API requires browser authentication.'
    ];
    
    // Get local usage data from database for current month
    $usageRecords = R::findAll('apiusage', 
        'provider = ? AND DATE(date) >= ? AND DATE(date) <= ? ORDER BY date DESC', 
        ['openai', $startDate, $endDate]
    );
    
    // Calculate totals from local records
    $modelUsage = [];
    $totalCost = 0;
    $totalTokens = 0;
    $totalCalls = 0;
    
    foreach ($usageRecords as $record) {
        // Sum up the calls
        $totalCalls++;
        
        // Get token and cost data if available
        if ($record->tokens_used) {
            $totalTokens += $record->tokens_used;
        }
        
        if ($record->cost) {
            $totalCost += $record->cost;
        }
        
        // Group by model if available
        if ($record->model) {
            if (!isset($modelUsage[$record->model])) {
                $modelUsage[$record->model] = [
                    'name' => $record->model,
                    'cost' => 0,
                    'tokens' => 0,
                    'calls' => 0
                ];
            }
            $modelUsage[$record->model]['cost'] += $record->cost ?: 0;
            $modelUsage[$record->model]['tokens'] += $record->tokens_used ?: 0;
            $modelUsage[$record->model]['calls']++;
        }
    }
    
    // Get estimated costs based on model pricing
    $gpt5Pricing = [
        'gpt-5-nano' => ['input' => 0.05, 'output' => 0.40],     // per 1M tokens
        'gpt-5-mini' => ['input' => 0.25, 'output' => 2.00],     // per 1M tokens
        'gpt-5' => ['input' => 1.25, 'output' => 10.00],         // per 1M tokens
    ];
    
    // If we don't have actual cost data, estimate based on tokens
    if ($totalCost == 0 && $totalTokens > 0) {
        // Use the default model for estimation
        $defaultModel = getSetting('toni_chat_model', 'gpt-5-nano');
        if (isset($gpt5Pricing[$defaultModel])) {
            // Estimate: 60% input, 40% output tokens
            $inputTokens = $totalTokens * 0.6;
            $outputTokens = $totalTokens * 0.4;
            $totalCost = ($inputTokens * $gpt5Pricing[$defaultModel]['input'] / 1000000) + 
                        ($outputTokens * $gpt5Pricing[$defaultModel]['output'] / 1000000);
        }
    }
    
    // Format model usage for response
    $responseData['usage']['by_model'] = array_values($modelUsage);
    $responseData['usage']['current_spend'] = round($totalCost, 4);
    $responseData['usage']['total_tokens'] = $totalTokens;
    $responseData['usage']['api_calls'] = $totalCalls;
    
    // Calculate remaining credits
    $responseData['billing']['credits'] = max(0, $responseData['billing']['limit'] - $responseData['usage']['current_spend']);
    
    // Add manual tracking notice
    $responseData['manual_entry'] = [
        'enabled' => true,
        'message' => 'Enter your actual OpenAI usage from platform.openai.com/usage',
        'current_manual_spend' => floatval(getSetting('openai_manual_spend', '0'))
    ];
    
    // If manual spend is set, use that instead
    if ($responseData['manual_entry']['current_manual_spend'] > 0) {
        $responseData['usage']['current_spend'] = $responseData['manual_entry']['current_manual_spend'];
        $responseData['billing']['credits'] = max(0, $responseData['billing']['limit'] - $responseData['usage']['current_spend']);
    }
    
    echo json_encode($responseData);
    
} catch (Exception $e) {
    error_log('OpenAI Usage API Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch usage data: ' . $e->getMessage()
    ]);
}
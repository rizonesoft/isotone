<?php
/**
 * Token Usage API Endpoint
 * Fetches token usage statistics from the database
 */

// Check authentication
require_once dirname(__DIR__) . '/auth.php';

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

// Set JSON response header
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['isotone_admin_user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get query parameters
$period = $_GET['period'] ?? 'day'; // day, week, month
$model = $_GET['model'] ?? 'all';

try {
    // Calculate date range based on period
    $endDate = date('Y-m-d H:i:s');
    switch ($period) {
        case 'day':
            $startDate = date('Y-m-d 00:00:00');
            $groupBy = 'HOUR(date)';
            $labelFormat = 'H:00';
            break;
        case 'week':
            $startDate = date('Y-m-d 00:00:00', strtotime('-7 days'));
            $groupBy = 'DATE(date)';
            $labelFormat = 'M d';
            break;
        case 'month':
        default:
            $startDate = date('Y-m-01 00:00:00');
            $groupBy = 'DATE(date)';
            $labelFormat = 'M d';
            break;
    }
    
    // Build query
    $whereClause = 'date >= ? AND date <= ?';
    $params = [$startDate, $endDate];
    
    if ($model !== 'all') {
        $whereClause .= ' AND model = ?';
        $params[] = $model;
    }
    
    // Get aggregated data
    $sql = "SELECT 
                $groupBy as time_group,
                model,
                SUM(input_tokens) as total_input_tokens,
                SUM(output_tokens) as total_output_tokens,
                SUM(total_tokens) as total_tokens,
                SUM(reasoning_tokens) as total_reasoning_tokens,
                SUM(cost) as total_cost,
                COUNT(*) as api_calls
            FROM tokenusage 
            WHERE $whereClause
            GROUP BY time_group, model
            ORDER BY time_group ASC, model ASC";
    
    $records = R::getAll($sql, $params);
    
    // Get summary statistics
    $summarySQL = "SELECT 
                    model,
                    SUM(input_tokens) as total_input_tokens,
                    SUM(output_tokens) as total_output_tokens,
                    SUM(total_tokens) as total_tokens,
                    SUM(reasoning_tokens) as total_reasoning_tokens,
                    SUM(cost) as total_cost,
                    COUNT(*) as api_calls
                FROM tokenusage 
                WHERE $whereClause
                GROUP BY model";
    
    $summary = R::getAll($summarySQL, $params);
    
    // Calculate totals
    $totals = [
        'input_tokens' => 0,
        'output_tokens' => 0,
        'total_tokens' => 0,
        'reasoning_tokens' => 0,
        'cost' => 0,
        'api_calls' => 0
    ];
    
    foreach ($summary as $row) {
        $totals['input_tokens'] += $row['total_input_tokens'];
        $totals['output_tokens'] += $row['total_output_tokens'];
        $totals['total_tokens'] += $row['total_tokens'];
        $totals['reasoning_tokens'] += $row['total_reasoning_tokens'] ?? 0;
        $totals['cost'] += $row['total_cost'];
        $totals['api_calls'] += $row['api_calls'];
    }
    
    // Format data for charts
    $chartData = [];
    $models = [];
    
    foreach ($records as $row) {
        $timeKey = $row['time_group'];
        $model = $row['model'];
        
        if (!isset($chartData[$timeKey])) {
            $chartData[$timeKey] = [
                'time' => $timeKey,
                'models' => []
            ];
        }
        
        $chartData[$timeKey]['models'][$model] = [
            'input_tokens' => intval($row['total_input_tokens']),
            'output_tokens' => intval($row['total_output_tokens']),
            'total_tokens' => intval($row['total_tokens']),
            'cost' => floatval($row['total_cost']),
            'calls' => intval($row['api_calls'])
        ];
        
        if (!in_array($model, $models)) {
            $models[] = $model;
        }
    }
    
    // Convert to array format for easier charting
    $chartDataArray = array_values($chartData);
    
    // Prepare response
    $response = [
        'success' => true,
        'period' => $period,
        'date_range' => [
            'start' => $startDate,
            'end' => $endDate
        ],
        'totals' => $totals,
        'summary_by_model' => $summary,
        'chart_data' => $chartDataArray,
        'models' => $models
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log('Token Usage API Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch usage data: ' . $e->getMessage()
    ]);
}
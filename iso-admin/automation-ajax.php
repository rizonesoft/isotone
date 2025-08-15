<?php
/**
 * Isotone Automation AJAX Handler
 * 
 * Separate handler for AJAX requests to ensure clean JSON responses
 */

// Start output buffering immediately
ob_start();

// Suppress all errors
error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Set JSON header
header('Content-Type: application/json');

// Check authentication
session_start();

// Check for admin session
$isAuthenticated = isset($_SESSION['isotone_admin_logged_in']) && $_SESSION['isotone_admin_logged_in'] === true;

// Also check for admin user session variables
if (!$isAuthenticated) {
    $isAuthenticated = isset($_SESSION['isotone_admin_user_id']) && $_SESSION['isotone_admin_user_id'] > 0;
}

if (!$isAuthenticated) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// Load required files
$vendorPath = dirname(__DIR__) . '/vendor/autoload.php';
$configPath = dirname(__DIR__) . '/config.php';

if (!file_exists($vendorPath)) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Vendor autoload not found']);
    exit;
}

require_once $vendorPath;

if (file_exists($configPath)) {
    require_once $configPath;
}

// Initialize automation engine
try {
    $engine = new \Isotone\Automation\Core\AutomationEngine();
    $engine->initialize();
    $engine->setQuietMode(true);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Failed to initialize automation engine']);
    exit;
}

// Get action
$action = $_GET['action'] ?? '';

// Handle actions
try {
    switch ($action) {
        case 'status':
            ob_clean();
            $status = $engine->getStateManager()->getStatus();
            echo json_encode($status);
            break;
            
        case 'execute':
            ob_clean();
            $task = $_POST['task'] ?? '';
            
            if (empty($task)) {
                echo json_encode(['success' => false, 'error' => 'No task specified']);
                break;
            }
            
            // Map task names to ensure compatibility
            $taskMap = [
                'check:docs' => 'check:docs',
                'update:docs' => 'update:docs',
                'generate:hooks' => 'generate:hooks',
                'sync:ide' => 'sync:ide',
                'sync:user-docs' => 'sync:user-docs',
                'validate:rules' => 'validate:rules'
            ];
            
            if (!isset($taskMap[$task])) {
                echo json_encode(['success' => false, 'error' => 'Unknown task: ' . $task]);
                break;
            }
            
            // Execute task using the CLI directly for better isolation
            $cliPath = dirname(__DIR__) . '/iso-automation/cli.php';
            $command = sprintf('php %s %s --quiet 2>&1', escapeshellarg($cliPath), escapeshellarg($task));
            
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);
            
            echo json_encode([
                'success' => $returnCode === 0,
                'output' => implode("\n", $output),
                'task' => $task
            ]);
            break;
            
        case 'cache_stats':
            ob_clean();
            $stats = $engine->getCacheManager()->getStatistics();
            echo json_encode($stats);
            break;
            
        case 'clear_cache':
            ob_clean();
            $engine->getCacheManager()->clearCache();
            echo json_encode(['success' => true, 'message' => 'Cache cleared successfully']);
            break;
            
        default:
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Server error occurred']);
}

// End output buffering and exit
ob_end_flush();
exit;
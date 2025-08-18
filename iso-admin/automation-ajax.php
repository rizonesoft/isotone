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
            // Return simplified status
            $status = [
                'success' => true,
                'engine' => 'operational',
                'rules' => count($engine->getRuleEngine()->getAllRules())
            ];
            echo json_encode($status);
            break;
            
        case 'execute':
            ob_clean();
            $task = $_POST['task'] ?? '';
            
            if (empty($task)) {
                echo json_encode(['success' => false, 'error' => 'No task specified']);
                break;
            }
            
            // Map task names to composer scripts where applicable
            $composerMap = [
                'check:docs' => 'docs:check',
                'update:docs' => 'docs:update',
                'sync:user-docs' => 'docs:sync',
                'sync:ide' => 'ide:sync',
                'tailwind:build' => 'tailwind:build',
                'tailwind:watch' => 'tailwind:watch',
                'tailwind:minify' => 'tailwind:minify',
                'tailwind:install' => 'tailwind:install',
                'tailwind:update' => 'tailwind:update',
                'tailwind:status' => 'tailwind:status',
            ];
            
            // Tasks that use composer
            $composerTasks = array_keys($composerMap);
            
            // All other tasks use automation CLI directly
            $automationTasks = [
                'generate:hooks',
                'hooks:scan',
                'validate:rules',
                'rules:validate',
                'rules:list',
                'rules:export',
                'status'
            ];
            
            $projectDir = dirname(__DIR__);
            
            if (in_array($task, $composerTasks)) {
                // Execute via composer
                $composerTask = $composerMap[$task];
                $command = sprintf('cd %s && composer run-script %s 2>&1', escapeshellarg($projectDir), escapeshellarg($composerTask));
            } elseif (in_array($task, $automationTasks)) {
                // Execute via automation CLI
                $cliPath = $projectDir . '/iso-automation/cli.php';
                
                // Handle command aliases
                if ($task === 'rules:validate') {
                    $task = 'validate:rules';
                }
                
                // Don't use quiet flag - we want to see the progress
                $command = sprintf('php %s %s 2>&1', escapeshellarg($cliPath), escapeshellarg($task));
            } else {
                echo json_encode(['success' => false, 'error' => 'Unknown task: ' . $task]);
                break;
            }
            
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);
            
            echo json_encode([
                'success' => $returnCode === 0,
                'output' => implode("\n", $output),
                'task' => $task
            ]);
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
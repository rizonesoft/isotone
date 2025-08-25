<?php
/**
 * Toni AI Assistant API Endpoint
 */

// Disable error output to prevent JSON corruption
error_reporting(0);
ini_set('display_errors', 0);

// Check authentication
require_once dirname(__DIR__, 2) . '/iso-admin/auth.php';

// Load configuration and database
require_once dirname(dirname(__DIR__)) . '/config.php';
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
require_once dirname(dirname(__DIR__)) . '/iso-core/Services/ToniService.php';

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

$userId = $_SESSION['isotone_admin_user_id'];
$toni = new ToniService();

// Handle different actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'send':
            // Send message to Toni
            if (!isset($_POST['message']) || empty(trim($_POST['message']))) {
                echo json_encode(['success' => false, 'error' => 'Message is required']);
                exit;
            }
            
            $message = trim($_POST['message']);
            $screenshot = $_POST['screenshot'] ?? null;
            $isVisual = $_POST['is_visual'] ?? false;
            
            // Enable debug mode for development
            $debugMode = true;
            
            // If screenshot is provided, send it with the message
            if ($screenshot && $isVisual) {
                // Log image info for debugging
                error_log("Toni API: Received screenshot, size: " . strlen($screenshot) . " bytes");
                error_log("Toni API: Image format: " . substr($screenshot, 0, 50));
                
                $result = $toni->sendMessageWithImage($userId, $message, $screenshot, $debugMode);
            } else {
                $result = $toni->sendMessage($userId, $message, $debugMode);
            }
            
            // Ensure we have a valid result
            if (!is_array($result)) {
                $result = ['success' => false, 'error' => 'Invalid response from service'];
            }
            
            echo json_encode($result);
            break;
        
    case 'history':
        // Get conversation history
        $conversation = $toni->getConversation($userId);
        echo json_encode([
            'success' => true,
            'conversation' => $conversation
        ]);
        break;
        
    case 'clear':
        // Clear conversation
        $result = $toni->clearConversation($userId);
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Conversation cleared' : 'Failed to clear conversation'
        ]);
        break;
        
    case 'suggestions':
        // Get suggestions
        $suggestions = $toni->getSuggestions($userId);
        echo json_encode([
            'success' => true,
            'suggestions' => $suggestions
        ]);
        break;
        
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    // Log error internally but return clean JSON
    error_log('Toni API Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred processing your request',
        'debug_error' => $e->getMessage()
    ]);
}
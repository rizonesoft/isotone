<?php
/**
 * To-Do API Endpoints
 * RESTful API for managing development to-dos
 * 
 * @package Isotone
 * @since 0.3.2
 */

// Use unified authentication handler (supports both session and API key)
require_once 'auth-handler.php';

// Load configuration and database
require_once dirname(dirname(__DIR__)) . '/config.php';
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
require_once dirname(dirname(__DIR__)) . '/iso-includes/class-security.php';

use RedBeanPHP\R;

// Set JSON response header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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

// Get current user ID (works with both session and API key auth)
$current_user_id = API_USER_ID;

// Parse request
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$todo_id = $_GET['id'] ?? null;

// Response helper
function sendResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// Verify CSRF token for non-GET requests (only for session auth, not API key auth)
if ($method !== 'GET' && $method !== 'OPTIONS' && !API_AUTH) {
    $csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $input['csrf_token'] ?? '';
    if (!iso_verify_csrf($csrf_token)) {
        sendResponse(['error' => 'CSRF token validation failed'], 403);
    }
}

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        // Check read permission
        api_require_permission('todos.read');
        if ($todo_id) {
            // Get single to-do
            $todo = R::load('todo', $todo_id);
            
            if (!$todo->id || $todo->user_id != $current_user_id) {
                sendResponse(['error' => 'To-Do not found'], 404);
            }
            
            sendResponse([
                'id' => $todo->id,
                'title' => $todo->title,
                'description' => $todo->description,
                'priority' => $todo->priority,
                'status' => $todo->status,
                'category' => $todo->category,
                'due_date' => $todo->due_date,
                'completed_at' => $todo->completed_at,
                'created_at' => $todo->created_at,
                'updated_at' => $todo->updated_at
            ]);
        } else {
            // List todos with filters
            $query_parts = ['user_id = ?'];
            $query_params = [$current_user_id];
            
            // Apply filters
            if (isset($_GET['status']) && $_GET['status'] !== 'all') {
                $query_parts[] = 'status = ?';
                $query_params[] = $_GET['status'];
            }
            
            if (isset($_GET['priority']) && $_GET['priority'] !== 'all') {
                $query_parts[] = 'priority = ?';
                $query_params[] = $_GET['priority'];
            }
            
            if (isset($_GET['category']) && $_GET['category'] !== 'all') {
                $query_parts[] = 'category = ?';
                $query_params[] = $_GET['category'];
            }
            
            if (!empty($_GET['search'])) {
                $query_parts[] = '(title LIKE ? OR description LIKE ?)';
                $query_params[] = '%' . $_GET['search'] . '%';
                $query_params[] = '%' . $_GET['search'] . '%';
            }
            
            // Date filters
            if (!empty($_GET['due_before'])) {
                $query_parts[] = 'due_date <= ?';
                $query_params[] = $_GET['due_before'];
            }
            
            if (!empty($_GET['due_after'])) {
                $query_parts[] = 'due_date >= ?';
                $query_params[] = $_GET['due_after'];
            }
            
            $query = implode(' AND ', $query_parts);
            
            // Get sort order
            $sort = $_GET['sort'] ?? 'priority';
            $order = $_GET['order'] ?? 'ASC';
            
            $order_by = match($sort) {
                'priority' => 'CASE priority WHEN "high" THEN 1 WHEN "medium" THEN 2 WHEN "low" THEN 3 ELSE 4 END ' . $order,
                'due_date' => 'due_date ' . $order,
                'created' => 'created_at ' . $order,
                'updated' => 'updated_at ' . $order,
                'title' => 'title ' . $order,
                default => 'created_at DESC'
            };
            
            $todos = R::find('todo', $query . ' ORDER BY ' . $order_by, $query_params);
            
            // Calculate statistics
            $stats = [
                'total' => count($todos),
                'pending' => 0,
                'in_progress' => 0,
                'completed' => 0,
                'overdue' => 0
            ];
            
            $today = date('Y-m-d');
            $todo_list = [];
            
            foreach ($todos as $todo) {
                // Update stats
                if ($todo->status === 'pending') $stats['pending']++;
                if ($todo->status === 'in_progress') $stats['in_progress']++;
                if ($todo->status === 'completed') $stats['completed']++;
                if ($todo->due_date && $todo->due_date < $today && $todo->status !== 'completed') {
                    $stats['overdue']++;
                }
                
                // Add to list
                $todo_list[] = [
                    'id' => $todo->id,
                    'title' => $todo->title,
                    'description' => $todo->description,
                    'priority' => $todo->priority,
                    'status' => $todo->status,
                    'category' => $todo->category,
                    'due_date' => $todo->due_date,
                    'is_overdue' => $todo->due_date && $todo->due_date < $today && $todo->status !== 'completed',
                    'completed_at' => $todo->completed_at,
                    'created_at' => $todo->created_at,
                    'updated_at' => $todo->updated_at
                ];
            }
            
            sendResponse([
                'todos' => $todo_list,
                'stats' => $stats
            ]);
        }
        break;
        
    case 'POST':
        // Check write permission
        api_require_permission('todos.write');
        
        // Create new to-do
        if (empty($input['title'])) {
            sendResponse(['error' => 'Title is required'], 400);
        }
        
        $todo = R::dispense('todo');
        $todo->user_id = $current_user_id;
        $todo->title = $input['title'];
        $todo->description = $input['description'] ?? '';
        $todo->priority = $input['priority'] ?? 'none';
        $todo->status = $input['status'] ?? 'pending';
        $todo->category = $input['category'] ?? 'other';
        $todo->due_date = !empty($input['due_date']) ? $input['due_date'] : null;
        $todo->created_at = date('Y-m-d H:i:s');
        $todo->updated_at = date('Y-m-d H:i:s');
        
        try {
            $id = R::store($todo);
            sendResponse([
                'success' => true,
                'id' => $id,
                'message' => 'To-Do created successfully'
            ], 201);
        } catch (Exception $e) {
            sendResponse(['error' => 'Failed to create to-do'], 500);
        }
        break;
        
    case 'PUT':
        // Check write permission
        api_require_permission('todos.write');
        
        // Update to-do
        if (!$todo_id) {
            sendResponse(['error' => 'To-Do ID is required'], 400);
        }
        
        $todo = R::load('todo', $todo_id);
        
        if (!$todo->id || $todo->user_id != $current_user_id) {
            sendResponse(['error' => 'To-Do not found'], 404);
        }
        
        // Update fields
        if (isset($input['title'])) $todo->title = $input['title'];
        if (isset($input['description'])) $todo->description = $input['description'];
        if (isset($input['priority'])) $todo->priority = $input['priority'];
        if (isset($input['status'])) {
            $todo->status = $input['status'];
            
            // Update completed_at based on status
            if ($todo->status === 'completed' && empty($todo->completed_at)) {
                $todo->completed_at = date('Y-m-d H:i:s');
            } elseif ($todo->status !== 'completed') {
                $todo->completed_at = null;
            }
        }
        if (isset($input['category'])) $todo->category = $input['category'];
        if (isset($input['due_date'])) $todo->due_date = $input['due_date'] ?: null;
        
        $todo->updated_at = date('Y-m-d H:i:s');
        
        try {
            R::store($todo);
            sendResponse([
                'success' => true,
                'message' => 'To-Do updated successfully'
            ]);
        } catch (Exception $e) {
            sendResponse(['error' => 'Failed to update to-do'], 500);
        }
        break;
        
    case 'PATCH':
        // Check write permission
        api_require_permission('todos.write');
        
        // Quick update (usually for status toggle)
        if (!$todo_id) {
            sendResponse(['error' => 'To-Do ID is required'], 400);
        }
        
        $todo = R::load('todo', $todo_id);
        
        if (!$todo->id || $todo->user_id != $current_user_id) {
            sendResponse(['error' => 'To-Do not found'], 404);
        }
        
        // Toggle status if requested
        if (isset($input['toggle_status']) && $input['toggle_status']) {
            if ($todo->status === 'completed') {
                $todo->status = 'pending';
                $todo->completed_at = null;
            } else {
                $todo->status = 'completed';
                $todo->completed_at = date('Y-m-d H:i:s');
            }
        } else {
            // Update specific field
            foreach ($input as $key => $value) {
                if (in_array($key, ['status', 'priority'])) {
                    $todo->$key = $value;
                }
            }
        }
        
        $todo->updated_at = date('Y-m-d H:i:s');
        
        try {
            R::store($todo);
            sendResponse([
                'success' => true,
                'status' => $todo->status,
                'message' => 'To-Do updated successfully'
            ]);
        } catch (Exception $e) {
            sendResponse(['error' => 'Failed to update to-do'], 500);
        }
        break;
        
    case 'DELETE':
        // Check delete permission
        api_require_permission('todos.delete');
        
        // Delete to-do
        if (!$todo_id) {
            sendResponse(['error' => 'To-Do ID is required'], 400);
        }
        
        $todo = R::load('todo', $todo_id);
        
        if (!$todo->id || $todo->user_id != $current_user_id) {
            sendResponse(['error' => 'To-Do not found'], 404);
        }
        
        try {
            R::trash($todo);
            sendResponse([
                'success' => true,
                'message' => 'To-Do deleted successfully'
            ]);
        } catch (Exception $e) {
            sendResponse(['error' => 'Failed to delete to-do'], 500);
        }
        break;
        
    default:
        sendResponse(['error' => 'Method not allowed'], 405);
}
?>
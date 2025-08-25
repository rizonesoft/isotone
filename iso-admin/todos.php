<?php
/**
 * To-Do List Management Page
 * Per-user development task tracking system
 * 
 * @package Isotone
 * @since 0.3.2
 */

// Required authentication
require_once 'auth.php';
requireRole('admin'); // Require admin role for todo management

// Required includes
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/iso-includes/database.php';
require_once dirname(__DIR__) . '/iso-includes/icon-functions.php';
require_once dirname(__DIR__) . '/iso-includes/class-security.php';

// Initialize database connection
isotone_db_connect();

use RedBeanPHP\R;

// Preload icons for this page
iso_preload_icons([
    // Page header icon
    ['name' => 'clipboard-document-list', 'style' => 'outline'],
    // Action icons
    ['name' => 'plus', 'style' => 'outline'],
    ['name' => 'trash', 'style' => 'outline'],
    ['name' => 'pencil', 'style' => 'outline'],
    ['name' => 'magnifying-glass', 'style' => 'outline'],
    ['name' => 'funnel', 'style' => 'outline'],
    // Status icons
    ['name' => 'calendar', 'style' => 'outline'],
    ['name' => 'check', 'style' => 'outline'],
    ['name' => 'clock', 'style' => 'outline'],
    ['name' => 'arrow-path', 'style' => 'outline'],
    // Message icons
    ['name' => 'check-circle', 'style' => 'outline'],
    ['name' => 'x-circle', 'style' => 'outline'],
    ['name' => 'x-mark', 'style' => 'outline'],
    ['name' => 'exclamation-triangle', 'style' => 'outline'],
    // Micro icons
    ['name' => 'plus', 'style' => 'micro'],
    ['name' => 'plus-circle', 'style' => 'micro'],
    ['name' => 'chevron-down', 'style' => 'micro'],
    ['name' => 'check', 'style' => 'micro'],
    ['name' => 'check-circle', 'style' => 'micro'],
    ['name' => 'x-mark', 'style' => 'micro'],
    ['name' => 'magnifying-glass', 'style' => 'micro'],
    ['name' => 'user', 'style' => 'micro'],
    ['name' => 'squares-2x2', 'style' => 'micro'],
    ['name' => 'clock', 'style' => 'micro'],
    ['name' => 'arrow-path', 'style' => 'micro'],
    ['name' => 'exclamation-triangle', 'style' => 'micro'],
]);

// Get current user ID from session
$current_user_id = $_SESSION['isotone_admin_user_id'] ?? 0;
$current_user = $_SESSION['isotone_admin_user'] ?? 'admin';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    // Verify CSRF token
    if (!iso_verify_csrf()) {
        $_SESSION['error_message'] = 'CSRF token validation failed. Please refresh and try again.';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $todo = R::dispense('todo');
            $todo->user_id = $current_user_id;
            $todo->title = $_POST['title'] ?? '';
            $todo->description = $_POST['description'] ?? '';
            $todo->priority = $_POST['priority'] ?? 'none';
            $todo->status = 'pending';
            $todo->category = $_POST['category'] ?? 'other';
            $todo->due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
            $todo->created_at = date('Y-m-d H:i:s');
            $todo->updated_at = date('Y-m-d H:i:s');
            
            try {
                R::store($todo);
                $_SESSION['success_message'] = 'To-Do item created successfully!';
            } catch (Exception $e) {
                $_SESSION['error_message'] = 'Error creating to-do: ' . $e->getMessage();
            }
            break;
            
        case 'update':
            $todo_id = $_POST['todo_id'] ?? 0;
            $todo = R::load('todo', $todo_id);
            
            if ($todo && $todo->user_id == $current_user_id) {
                $todo->title = $_POST['title'] ?? $todo->title;
                $todo->description = $_POST['description'] ?? $todo->description;
                $todo->priority = $_POST['priority'] ?? $todo->priority;
                $todo->status = $_POST['status'] ?? $todo->status;
                $todo->category = $_POST['category'] ?? $todo->category;
                $todo->due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : $todo->due_date;
                $todo->updated_at = date('Y-m-d H:i:s');
                
                if ($todo->status === 'completed' && empty($todo->completed_at)) {
                    $todo->completed_at = date('Y-m-d H:i:s');
                } elseif ($todo->status !== 'completed') {
                    $todo->completed_at = null;
                }
                
                try {
                    R::store($todo);
                    $_SESSION['success_message'] = 'To-Do item updated successfully!';
                } catch (Exception $e) {
                    $_SESSION['error_message'] = 'Error updating to-do: ' . $e->getMessage();
                }
            }
            break;
            
        case 'delete':
            $todo_id = $_POST['todo_id'] ?? 0;
            $todo = R::load('todo', $todo_id);
            
            if ($todo && $todo->user_id == $current_user_id) {
                try {
                    R::trash($todo);
                    $_SESSION['success_message'] = 'To-Do item deleted successfully!';
                } catch (Exception $e) {
                    $_SESSION['error_message'] = 'Error deleting to-do: ' . $e->getMessage();
                }
            }
            break;
            
        case 'toggle_status':
            $todo_id = $_POST['todo_id'] ?? 0;
            $todo = R::load('todo', $todo_id);
            
            if ($todo && $todo->user_id == $current_user_id) {
                if ($todo->status === 'completed') {
                    $todo->status = 'pending';
                    $todo->completed_at = null;
                } else {
                    $todo->status = 'completed';
                    $todo->completed_at = date('Y-m-d H:i:s');
                }
                $todo->updated_at = date('Y-m-d H:i:s');
                
                try {
                    R::store($todo);
                } catch (Exception $e) {
                    $_SESSION['error_message'] = 'Error updating status: ' . $e->getMessage();
                }
            }
            break;
    }
    
    // Redirect to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
    exit;
}

// Get filter parameters
$filter_status = $_GET['status'] ?? 'all';
$filter_priority = $_GET['priority'] ?? 'all';
$filter_category = $_GET['category'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$query_parts = ['user_id = ?'];
$query_params = [$current_user_id];

if ($filter_status !== 'all') {
    $query_parts[] = 'status = ?';
    $query_params[] = $filter_status;
}

if ($filter_priority !== 'all') {
    $query_parts[] = 'priority = ?';
    $query_params[] = $filter_priority;
}

if ($filter_category !== 'all') {
    $query_parts[] = 'category = ?';
    $query_params[] = $filter_category;
}

if (!empty($search)) {
    $query_parts[] = '(title LIKE ? OR description LIKE ?)';
    $query_params[] = '%' . $search . '%';
    $query_params[] = '%' . $search . '%';
}

$query = implode(' AND ', $query_parts);
$todos = R::find('todo', $query . ' ORDER BY 
    CASE priority 
        WHEN "high" THEN 1 
        WHEN "medium" THEN 2 
        WHEN "low" THEN 3 
        ELSE 4 
    END, 
    due_date ASC, 
    created_at DESC', $query_params);

// Calculate statistics
$stats = [
    'total' => count($todos),
    'pending' => 0,
    'in_progress' => 0,
    'completed' => 0,
    'overdue' => 0
];

$today = date('Y-m-d');
foreach ($todos as $todo) {
    if ($todo->status === 'pending') $stats['pending']++;
    if ($todo->status === 'in_progress') $stats['in_progress']++;
    if ($todo->status === 'completed') $stats['completed']++;
    if ($todo->due_date && $todo->due_date < $today && $todo->status !== 'completed') {
        $stats['overdue']++;
    }
}

// Start output buffering for content
ob_start();
?>

<!-- Alpine.js component for the page -->
<div x-data="todoApp()" x-init="init()">
    
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span class="shield-pulse flex-shrink-0">
                    <?php echo iso_get_icon('clipboard-document-list', 'outline', ['class' => 'w-10 h-10 text-cyan-500'], false); ?>
                </span>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Development To-Do List
                    </h1>
                    <p class="mt-1 text-gray-600 dark:text-gray-400">
                        Manage your development tasks and track progress
                    </p>
                </div>
            </div>
            <button @click="showAddModal = true" class="btn-primary">
                <span class="btn-icon-box">
                    <?php echo iso_get_icon('plus', 'micro', [], false); ?>
                </span>
                <span class="btn-primary-text">New To-Do</span>
            </button>
        </div>
    </div>
    
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6 animate-slideDown">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <?php echo iso_get_icon('check-circle', 'outline', ['class' => 'w-6 h-6 text-green-600 dark:text-green-400'], false); ?>
            </div>
            <div class="ml-3">
                <p class="text-green-800 dark:text-green-200"><?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
            </div>
            <button onclick="this.parentElement.parentElement.style.display='none'" class="ml-4 flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <?php echo iso_get_icon('x-mark', 'outline', ['class' => 'w-5 h-5'], false); ?>
            </button>
        </div>
    </div>
    <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6 animate-slideDown">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <?php echo iso_get_icon('x-circle', 'outline', ['class' => 'w-6 h-6 text-red-600 dark:text-red-400'], false); ?>
            </div>
            <div class="ml-3">
                <p class="text-red-800 dark:text-red-200"><?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
            </div>
            <button onclick="this.parentElement.parentElement.style.display='none'" class="ml-4 flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <?php echo iso_get_icon('x-mark', 'outline', ['class' => 'w-5 h-5'], false); ?>
            </button>
        </div>
    </div>
    <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    
    <!-- Statistics Cards (Not wrapped in content-card) -->
    <div class="flex flex-wrap lg:flex-nowrap gap-3 mb-8">
        <div class="info-card flex-1 min-w-0 group hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
            <div class="info-card-content">
                <div class="info-card-body">
                    <div class="flex items-center gap-2 mb-2">
                        <?php echo iso_get_icon('squares-2x2', 'micro', ['class' => 'w-4 h-4 text-gray-500'], false); ?>
                        <p class="info-card-label">Total Tasks</p>
                    </div>
                    <p class="info-card-value"><?php echo $stats['total']; ?></p>
                </div>
            </div>
        </div>
        
        <div class="info-card info-blue flex-1 min-w-0 group hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
            <div class="info-card-content">
                <div class="info-card-body">
                    <div class="flex items-center gap-2 mb-2">
                        <?php echo iso_get_icon('clock', 'micro', ['class' => 'w-4 h-4 text-blue-500'], false); ?>
                        <p class="info-card-label">Pending</p>
                    </div>
                    <p class="info-card-value"><?php echo $stats['pending']; ?></p>
                </div>
            </div>
        </div>
        
        <div class="info-card info-yellow flex-1 min-w-0 group hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
            <div class="info-card-content">
                <div class="info-card-body">
                    <div class="flex items-center gap-2 mb-2">
                        <?php echo iso_get_icon('arrow-path', 'micro', ['class' => 'w-4 h-4 text-yellow-500'], false); ?>
                        <p class="info-card-label">In Progress</p>
                    </div>
                    <p class="info-card-value"><?php echo $stats['in_progress']; ?></p>
                </div>
            </div>
        </div>
        
        <div class="info-card info-green flex-1 min-w-0 group hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
            <div class="info-card-content">
                <div class="info-card-body">
                    <div class="flex items-center gap-2 mb-2">
                        <?php echo iso_get_icon('check-circle', 'micro', ['class' => 'w-4 h-4 text-green-500'], false); ?>
                        <p class="info-card-label">Completed</p>
                    </div>
                    <p class="info-card-value"><?php echo $stats['completed']; ?></p>
                </div>
            </div>
        </div>
        
        <div class="info-card info-red flex-1 min-w-0 group hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 relative">
            <?php if ($stats['overdue'] > 0): ?>
            <div class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
            <?php endif; ?>
            <div class="info-card-content">
                <div class="info-card-body">
                    <div class="flex items-center gap-2 mb-2">
                        <?php echo iso_get_icon('exclamation-triangle', 'micro', ['class' => 'w-4 h-4 text-red-500'], false); ?>
                        <p class="info-card-label">Overdue</p>
                    </div>
                    <p class="info-card-value"><?php echo $stats['overdue']; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters and Search with enhanced styling -->
    <div class="content-card mb-6 group hover:shadow-lg transition-all duration-300">
        <div class="content-card-header">
            <div class="content-card-header-icon">
                <?php echo iso_get_icon('funnel', 'outline', ['class' => 'w-5 h-5'], false); ?>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filters & Search</h3>
            <?php if ($search || $filter_status !== 'all' || $filter_priority !== 'all' || $filter_category !== 'all'): ?>
            <span class="content-card-header-badge bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-400">
                Active
            </span>
            <?php endif; ?>
        </div>
        <div class="content-card-body">
            <form method="GET" class="flex flex-wrap gap-3">
                <!-- Search with icon -->
                <div class="flex-1 min-w-[250px] relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <?php echo iso_get_icon('magnifying-glass', 'micro', ['class' => 'w-5 h-5 text-gray-400 group-focus-within:text-cyan-500 transition-colors'], false); ?>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search by title or description..." 
                           class="w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                  placeholder-gray-400 dark:placeholder-gray-500
                                  focus:ring-2 focus:ring-cyan-500 focus:border-transparent
                                  hover:border-gray-400 dark:hover:border-gray-500 transition-colors">
                </div>
                
                <!-- Status Filter -->
                <select name="status" 
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                               focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                    <option value="all" class="bg-white dark:bg-gray-700">All Status</option>
                    <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?> class="bg-white dark:bg-gray-700">Pending</option>
                    <option value="in_progress" <?php echo $filter_status === 'in_progress' ? 'selected' : ''; ?> class="bg-white dark:bg-gray-700">In Progress</option>
                    <option value="completed" <?php echo $filter_status === 'completed' ? 'selected' : ''; ?> class="bg-white dark:bg-gray-700">Completed</option>
                    <option value="cancelled" <?php echo $filter_status === 'cancelled' ? 'selected' : ''; ?> class="bg-white dark:bg-gray-700">Cancelled</option>
                </select>
                
                <!-- Priority Filter -->
                <select name="priority" 
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                               focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                    <option value="all" class="bg-white dark:bg-gray-700">All Priority</option>
                    <option value="high" <?php echo $filter_priority === 'high' ? 'selected' : ''; ?> class="bg-white dark:bg-gray-700">High</option>
                    <option value="medium" <?php echo $filter_priority === 'medium' ? 'selected' : ''; ?> class="bg-white dark:bg-gray-700">Medium</option>
                    <option value="low" <?php echo $filter_priority === 'low' ? 'selected' : ''; ?> class="bg-white dark:bg-gray-700">Low</option>
                    <option value="none" <?php echo $filter_priority === 'none' ? 'selected' : ''; ?> class="bg-white dark:bg-gray-700">None</option>
                </select>
                
                <!-- Category Filter -->
                <select name="category" 
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                               focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                    <option value="all" class="bg-white dark:bg-gray-700">All Categories</option>
                    <option value="bug" <?php echo $filter_category === 'bug' ? 'selected' : ''; ?> class="bg-white dark:bg-gray-700">Bug Fix</option>
                    <option value="feature" <?php echo $filter_category === 'feature' ? 'selected' : ''; ?> class="bg-white dark:bg-gray-700">Feature</option>
                    <option value="documentation" <?php echo $filter_category === 'documentation' ? 'selected' : ''; ?> class="bg-white dark:bg-gray-700">Documentation</option>
                    <option value="testing" <?php echo $filter_category === 'testing' ? 'selected' : ''; ?> class="bg-white dark:bg-gray-700">Testing</option>
                    <option value="refactor" <?php echo $filter_category === 'refactor' ? 'selected' : ''; ?> class="bg-white dark:bg-gray-700">Refactor</option>
                    <option value="other" <?php echo $filter_category === 'other' ? 'selected' : ''; ?> class="bg-white dark:bg-gray-700">Other</option>
                </select>
                
                <button type="submit" class="btn-secondary group hover:shadow-md transition-all duration-200">
                    <?php echo iso_get_icon('funnel', 'outline', ['class' => 'w-4 h-4 group-hover:scale-110 transition-transform']); ?>
                    Apply Filters
                </button>
                
                <?php if ($search || $filter_status !== 'all' || $filter_priority !== 'all' || $filter_category !== 'all'): ?>
                    <a href="todos.php" class="btn-outline group hover:shadow-md transition-all duration-200">
                        <?php echo iso_get_icon('x-mark', 'micro', ['class' => 'w-4 h-4 group-hover:scale-110 transition-transform']); ?>
                        Clear All
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- To-Do List with enhanced cards -->
    <div class="space-y-3">
        <?php if (empty($todos)): ?>
            <div class="content-card bg-gradient-to-br from-gray-50 to-gray-100/50 dark:from-gray-800/50 dark:to-gray-900/50">
                <div class="content-card-body text-center py-16">
                    <div class="inline-flex p-4 bg-gray-100 dark:bg-gray-800 rounded-full mb-4">
                        <?php echo iso_get_icon('clipboard-document-list', 'outline', ['class' => 'w-16 h-16 text-gray-400 dark:text-gray-600'], false); ?>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">No tasks yet</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Start organizing your development workflow</p>
                    <button @click="showAddModal = true" class="btn-primary">
                        <?php echo iso_get_icon('plus', 'micro', ['class' => 'w-4 h-4'], false); ?>
                        Create First Task
                    </button>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($todos as $todo): 
                $is_overdue = $todo->due_date && $todo->due_date < $today && $todo->status !== 'completed';
                $is_completed = $todo->status === 'completed';
                
                // Determine border color class
                $border_class = match($todo->priority) {
                    'high' => 'border-l-4 border-l-red-500',
                    'medium' => 'border-l-4 border-l-yellow-500',
                    'low' => 'border-l-4 border-l-green-500',
                    default => 'border-l-4 border-l-gray-400'
                };
                
                // Add overdue background
                $bg_class = $is_overdue ? 'bg-gradient-to-r from-red-50 to-transparent dark:from-red-900/20 dark:to-transparent' : '';
                
                // Add completed opacity and styling
                $opacity_class = $is_completed ? 'opacity-75' : '';
                $completed_bg = $is_completed ? 'bg-gradient-to-r from-green-50/30 to-transparent dark:from-green-900/10 dark:to-transparent' : '';
            ?>
                <div class="content-card <?php echo $border_class; ?> <?php echo $bg_class; ?> <?php echo $completed_bg; ?> <?php echo $opacity_class; ?> group hover:shadow-lg transition-all duration-300 hover:scale-[1.01]">
                    <div class="content-card-body">
                        <div class="flex items-start gap-4">
                            <!-- Enhanced Checkbox with animation -->
                            <form method="POST" class="mt-1">
                                <?php echo iso_csrf_field(); ?>
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="todo_id" value="<?php echo $todo->id; ?>">
                                <label class="relative inline-block">
                                    <input type="checkbox" 
                                           class="w-5 h-5 text-cyan-600 border-2 border-gray-300 rounded focus:ring-2 focus:ring-cyan-500 focus:ring-offset-1
                                                  dark:bg-gray-700 dark:border-gray-600 cursor-pointer transition-all duration-200
                                                  hover:border-cyan-400 dark:hover:border-cyan-500"
                                           <?php echo $is_completed ? 'checked' : ''; ?>
                                           onchange="this.form.submit()">
                                    <?php if ($is_completed): ?>
                                    <span class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                        <?php echo iso_get_icon('check', 'micro', ['class' => 'w-3 h-3 text-white'], false); ?>
                                    </span>
                                    <?php endif; ?>
                                </label>
                            </form>
                            
                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors <?php echo $is_completed ? 'line-through text-gray-500 dark:text-gray-500' : ''; ?>">
                                    <?php echo htmlspecialchars($todo->title); ?>
                                </h3>
                                
                                <?php if ($todo->description): ?>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">
                                        <?php echo nl2br(htmlspecialchars($todo->description)); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <!-- Enhanced Meta Information with better badges -->
                                <div class="flex flex-wrap items-center gap-2 text-xs mt-3">
                                    <!-- Priority Badge -->
                                    <span class="badge badge-<?php 
                                        echo $todo->priority === 'high' ? 'red' : 
                                            ($todo->priority === 'medium' ? 'yellow' : 
                                            ($todo->priority === 'low' ? 'green' : 'gray')); 
                                    ?>">
                                        <?php echo ucfirst($todo->priority); ?> Priority
                                    </span>
                                    
                                    <!-- Status Badge -->
                                    <span class="badge badge-<?php 
                                        echo $todo->status === 'completed' ? 'green' : 
                                            ($todo->status === 'in_progress' ? 'blue' : 
                                            ($todo->status === 'cancelled' ? 'gray' : 'yellow')); 
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $todo->status)); ?>
                                    </span>
                                    
                                    <!-- Category -->
                                    <span class="badge badge-purple">
                                        <?php echo ucfirst($todo->category); ?>
                                    </span>
                                    
                                    <!-- Due Date -->
                                    <?php if ($todo->due_date): ?>
                                        <span class="text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                            <?php echo iso_get_icon('calendar', 'outline', ['class' => 'w-3 h-3 inline'], false); ?>
                                            <?php echo date('M d, Y', strtotime($todo->due_date)); ?>
                                            <?php if ($is_overdue): ?>
                                                <span class="text-red-500 font-semibold">(Overdue)</span>
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Enhanced Actions with tooltips -->
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <button @click="editTodo(<?php echo htmlspecialchars(json_encode([
                                    'id' => $todo->id,
                                    'title' => $todo->title,
                                    'description' => $todo->description,
                                    'priority' => $todo->priority,
                                    'status' => $todo->status,
                                    'category' => $todo->category,
                                    'due_date' => $todo->due_date
                                ])); ?>)" 
                                        class="p-2 text-cyan-500 hover:bg-cyan-50 dark:hover:bg-cyan-900/20 rounded-lg transition-all duration-200 hover:scale-110"
                                        title="Edit task">
                                    <?php echo iso_get_icon('pencil', 'outline', ['class' => 'w-4 h-4'], false); ?>
                                </button>
                                
                                <form method="POST" class="inline" onsubmit="return confirm('Delete this to-do?');">
                                    <?php echo iso_csrf_field(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="todo_id" value="<?php echo $todo->id; ?>">
                                    <button type="submit" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all duration-200 hover:scale-110"
                                            title="Delete task">
                                        <?php echo iso_get_icon('trash', 'outline', ['class' => 'w-4 h-4'], false); ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Enhanced Add/Edit Modal with glassmorphism -->
    <div x-show="showAddModal || showEditModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        
        <div class="flex items-center justify-center min-h-screen px-4">
            <!-- Enhanced Backdrop with blur -->
            <div class="fixed inset-0 bg-gradient-to-br from-black/60 via-black/50 to-black/60 backdrop-blur-md" @click="closeModals()"></div>
            
            <!-- Enhanced Modal with glassmorphism effect -->
            <div class="relative bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-2xl shadow-2xl max-w-2xl w-full p-8 border border-gray-200/50 dark:border-gray-700/50"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 transform scale-100 translate-y-0">
                
                <!-- Modal Header with icon -->
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2 bg-cyan-100 dark:bg-cyan-900/30 rounded-lg">
                        <span x-show="showAddModal">
                            <?php echo iso_get_icon('plus-circle', 'outline', ['class' => 'w-6 h-6 text-cyan-600 dark:text-cyan-400'], false); ?>
                        </span>
                        <span x-show="showEditModal">
                            <?php echo iso_get_icon('pencil', 'outline', ['class' => 'w-6 h-6 text-cyan-600 dark:text-cyan-400'], false); ?>
                        </span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white" x-text="showEditModal ? 'Edit To-Do' : 'Create New To-Do'"></h2>
                </div>
                
                <form method="POST">
                    <?php echo iso_csrf_field(); ?>
                    <input type="hidden" name="action" x-model="formAction">
                    <input type="hidden" name="todo_id" x-model="currentTodo.id">
                    
                    <div class="space-y-4">
                        <!-- Title -->
                        <div class="form-group">
                            <label class="form-label">
                                Title
                                <span class="form-label-required">*</span>
                            </label>
                            <input type="text" 
                                   name="title" 
                                   x-model="currentTodo.title"
                                   required 
                                   class="form-input">
                        </div>
                        
                        <!-- Description -->
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" 
                                      x-model="currentTodo.description"
                                      rows="3" 
                                      class="form-input"></textarea>
                        </div>
                        
                        <!-- Grid Layout for Options -->
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Priority -->
                            <div class="form-group">
                                <label class="form-label">Priority</label>
                                <select name="priority" x-model="currentTodo.priority" 
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                               bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                               focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                                    <option value="none" class="bg-white dark:bg-gray-700">None</option>
                                    <option value="low" class="bg-white dark:bg-gray-700">Low</option>
                                    <option value="medium" class="bg-white dark:bg-gray-700">Medium</option>
                                    <option value="high" class="bg-white dark:bg-gray-700">High</option>
                                </select>
                            </div>
                            
                            <!-- Category -->
                            <div class="form-group">
                                <label class="form-label">Category</label>
                                <select name="category" x-model="currentTodo.category" 
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                               bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                               focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                                    <option value="bug" class="bg-white dark:bg-gray-700">Bug Fix</option>
                                    <option value="feature" class="bg-white dark:bg-gray-700">Feature</option>
                                    <option value="documentation" class="bg-white dark:bg-gray-700">Documentation</option>
                                    <option value="testing" class="bg-white dark:bg-gray-700">Testing</option>
                                    <option value="refactor" class="bg-white dark:bg-gray-700">Refactor</option>
                                    <option value="other" class="bg-white dark:bg-gray-700">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Status (only for edit) -->
                        <div x-show="showEditModal" class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" x-model="currentTodo.status" 
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                           focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                                <option value="pending" class="bg-white dark:bg-gray-700">Pending</option>
                                <option value="in_progress" class="bg-white dark:bg-gray-700">In Progress</option>
                                <option value="completed" class="bg-white dark:bg-gray-700">Completed</option>
                                <option value="cancelled" class="bg-white dark:bg-gray-700">Cancelled</option>
                            </select>
                        </div>
                        
                        <!-- Due Date -->
                        <div class="form-group">
                            <label class="form-label">Due Date</label>
                            <input type="date" 
                                   name="due_date" 
                                   x-model="currentTodo.due_date"
                                   class="form-input">
                        </div>
                    </div>
                    
                    <!-- Enhanced Actions with animations -->
                    <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" @click="closeModals()" class="btn-secondary group hover:shadow-md transition-all duration-200">
                            <?php echo iso_get_icon('x-mark', 'micro', ['class' => 'w-4 h-4 group-hover:scale-110 transition-transform'], false); ?>
                            Cancel
                        </button>
                        <button type="submit" class="btn-primary group hover:shadow-lg transition-all duration-200">
                            <span class="btn-icon-box group-hover:scale-110 transition-transform">
                                <?php echo iso_get_icon('check', 'micro', [], false); ?>
                            </span>
                            <span class="btn-primary-text" x-text="showEditModal ? 'Update Task' : 'Create Task'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Custom styles for enhanced animations -->
<style>
@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin-slow {
    animation: spin-slow 3s linear infinite;
}

/* Enhanced todo card hover effects */
.content-card:hover .info-card-value {
    transform: scale(1.05);
    transition: transform 0.3s ease;
}

/* Smooth transitions for badges */
.badge {
    transition: all 0.2s ease;
}
.badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Glassmorphism enhancement for modal */
@supports (backdrop-filter: blur(20px)) {
    .backdrop-blur-xl {
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }
}

/* Progress bar animation for stats */
@keyframes slideIn {
    from { 
        transform: translateX(-100%);
        opacity: 0;
    }
    to { 
        transform: translateX(0);
        opacity: 1;
    }
}

.animate-slideIn {
    animation: slideIn 0.5s ease-out forwards;
}
</style>

<!-- Alpine.js Component Script -->
<script>
function todoApp() {
    return {
        showAddModal: false,
        showEditModal: false,
        formAction: 'create',
        currentTodo: {
            id: null,
            title: '',
            description: '',
            priority: 'none',
            status: 'pending',
            category: 'other',
            due_date: ''
        },
        
        init() {
            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                // 'n' for new todo
                if (e.key === 'n' && !this.showAddModal && !this.showEditModal) {
                    e.preventDefault();
                    this.showAddModal = true;
                }
                
                // Escape to close modals
                if (e.key === 'Escape') {
                    this.closeModals();
                }
            });
        },
        
        editTodo(todo) {
            this.currentTodo = {...todo};
            this.formAction = 'update';
            this.showEditModal = true;
        },
        
        closeModals() {
            this.showAddModal = false;
            this.showEditModal = false;
            this.formAction = 'create';
            this.currentTodo = {
                id: null,
                title: '',
                description: '',
                priority: 'none',
                status: 'pending',
                category: 'other',
                due_date: ''
            };
        }
    };
}
</script>

<?php
// Get the buffered content
$page_content = ob_get_clean();

// Set page configuration
$page_title = 'To-Do List';
$breadcrumbs = [
    ['title' => 'Development', 'url' => '/isotone/iso-admin/development.php'],
    ['title' => 'To-Do List', 'url' => '']
];

// Include the admin layout
require_once 'includes/admin-layout.php';
?>
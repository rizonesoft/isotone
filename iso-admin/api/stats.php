<?php
/**
 * Lightweight Stats API Endpoint
 * Returns dashboard statistics as JSON
 */

// Check auth
session_start();
if (empty($_SESSION['isotone_admin_logged_in'])) {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized']));
}

// Load database
require_once dirname(dirname(__DIR__)) . '/config.php';
require_once dirname(dirname(__DIR__)) . '/iso-includes/database.php';

// Connect
isotone_db_connect();

// Get all stats in one query
$query = "
    SELECT 
        (SELECT COUNT(*) FROM post) as posts,
        (SELECT COUNT(*) FROM page) as pages,
        (SELECT COUNT(*) FROM users) as users,
        (SELECT COUNT(*) FROM comment) as comments,
        (SELECT COUNT(*) FROM media) as media
";

try {
    $stats = \RedBeanPHP\R::getRow($query);
    
    // Return as JSON
    header('Content-Type: application/json');
    echo json_encode([
        'posts' => (int)($stats['posts'] ?? 0),
        'pages' => (int)($stats['pages'] ?? 0),
        'users' => (int)($stats['users'] ?? 0),
        'comments' => (int)($stats['comments'] ?? 0),
        'media' => (int)($stats['media'] ?? 0),
        'memory' => round(memory_get_usage() / 1024 / 1024, 2)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
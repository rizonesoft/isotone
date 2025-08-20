<?php
/**
 * Test AJAX Authentication
 */

// Load configuration and security class
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/iso-includes/class-security.php';

// Use secure session (same as auth.php)
IsotoneSecurity::secureSession();

header('Content-Type: application/json');

$debug = [
    'session_id' => session_id(),
    'session_status' => session_status(),
    'session_data' => $_SESSION,
    'cookies' => $_COOKIE,
    'auth_checks' => [
        'isotone_admin_logged_in' => isset($_SESSION['isotone_admin_logged_in']) ? $_SESSION['isotone_admin_logged_in'] : 'not set',
        'isotone_admin_user_id' => isset($_SESSION['isotone_admin_user_id']) ? $_SESSION['isotone_admin_user_id'] : 'not set',
        'user_role' => isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'not set'
    ]
];

echo json_encode($debug, JSON_PRETTY_PRINT);
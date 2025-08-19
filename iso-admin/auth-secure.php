<?php
/**
 * Secure Authentication Check for Admin Pages
 * Enhanced with security features
 * 
 * @package Isotone
 */

// Start timing for page load
if (!defined('ISOTONE_START')) {
    define('ISOTONE_START', microtime(true));
}

// Load security class
require_once dirname(__DIR__) . '/iso-includes/class-security.php';

// Configure secure session
IsotoneeSecurity::secureSession();

// Validate session fingerprint
if (!IsotoneeSecurity::validateFingerprint()) {
    // Possible session hijacking attempt
    IsotoneeSecurity::logSecurityEvent('session_hijack_attempt', [
        'session_id' => session_id()
    ]);
    
    // Destroy session and redirect
    session_unset();
    session_destroy();
    header('Location: /isotone/iso-admin/login.php?error=session_invalid');
    exit;
}

// Clean up any test data and optimize session
if (isset($_SESSION['memory_data'])) {
    unset($_SESSION['memory_data']);
}

// Clean up old/expired session data
$session_keys_to_keep = [
    'isotone_admin_logged_in',
    'isotone_admin_user',
    'isotone_admin_user_id', 
    'isotone_admin_user_data',
    'isotone_admin_last_activity',
    'fingerprint',
    'csrf_token',
    'last_regeneration'
];

// Remove any keys not in the whitelist
foreach ($_SESSION as $key => $value) {
    if (!in_array($key, $session_keys_to_keep) && strpos($key, 'login_attempts_') !== 0) {
        unset($_SESSION[$key]);
    }
}

// Load configuration and user class
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/iso-includes/class-user.php';

// Store original PHP memory limit before changing it
if (!defined('PHP_ORIGINAL_MEMORY_LIMIT')) {
    define('PHP_ORIGINAL_MEMORY_LIMIT', ini_get('memory_limit'));
}

// Ensure memory limit is enforced
if (defined('MEMORY_LIMIT')) {
    @ini_set('memory_limit', MEMORY_LIMIT);
}

// Check if user is logged in
if (!isset($_SESSION['isotone_admin_logged_in']) || $_SESSION['isotone_admin_logged_in'] !== true) {
    // Not logged in, redirect to login page
    $current_page = $_SERVER['REQUEST_URI'];
    header('Location: /isotone/iso-admin/login.php?redirect=' . urlencode($current_page));
    exit;
}

// Check session timeout (2 hours)
if (isset($_SESSION['isotone_admin_last_activity']) && (time() - $_SESSION['isotone_admin_last_activity'] > 7200)) {
    // Log timeout event
    IsotoneeSecurity::logSecurityEvent('session_timeout', [
        'user' => $_SESSION['isotone_admin_user'] ?? 'unknown'
    ]);
    
    // Session expired
    session_unset();
    session_destroy();
    header('Location: /isotone/iso-admin/login.php?expired=1');
    exit;
}

// Update last activity time
$_SESSION['isotone_admin_last_activity'] = time();

// Make current user data available
$current_user = $_SESSION['isotone_admin_user'] ?? 'Unknown';
$current_user_id = $_SESSION['isotone_admin_user_id'] ?? 0;
$current_user_data = $_SESSION['isotone_admin_user_data'] ?? [];

// Check user role for permission
function requireRole($role) {
    global $current_user_id;
    
    if (!$current_user_id) {
        header('Location: /isotone/iso-admin/login.php');
        exit;
    }
    
    // Check if user has required role
    $user = new IsotoneUser();
    if (!$user->hasRole($current_user_id, $role)) {
        // Log unauthorized access attempt
        IsotoneeSecurity::logSecurityEvent('unauthorized_access', [
            'user_id' => $current_user_id,
            'required_role' => $role,
            'page' => $_SERVER['REQUEST_URI']
        ]);
        
        header('Location: /isotone/iso-admin/403.php');
        exit;
    }
}

// CSRF Protection for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!iso_verify_csrf()) {
        // Log CSRF failure
        IsotoneeSecurity::logSecurityEvent('csrf_failure', [
            'user' => $current_user,
            'page' => $_SERVER['REQUEST_URI']
        ]);
        
        die('CSRF token validation failed. Please refresh and try again.');
    }
}
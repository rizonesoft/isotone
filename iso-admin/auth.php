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

// Load configuration FIRST - needed for SECURE_AUTH_KEY
require_once dirname(__DIR__) . '/config.php';

// Security check: Warn if authentication keys are not properly configured
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    $insecure_keys = [];
    
    // Check each key for default/insecure values
    $keys_to_check = ['AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 
                      'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT'];
    
    foreach ($keys_to_check as $key_name) {
        if (!defined($key_name) || 
            constant($key_name) === '' || 
            strpos(constant($key_name), 'put your unique phrase here') !== false ||
            strpos(constant($key_name), 'your unique phrase') !== false) {
            $insecure_keys[] = $key_name;
        }
    }
    
    if (!empty($insecure_keys)) {
        // Store warning in session to display in admin pages
        $_SESSION['security_warning'] = 'Security Warning: The following keys need to be configured in config.php: ' . implode(', ', $insecure_keys);
    }
}

// Load security class
require_once dirname(__DIR__) . '/iso-includes/class-security.php';

// Configure secure session
IsotoneSecurity::secureSession();

// Check if user is logged in FIRST
if (!isset($_SESSION['isotone_admin_logged_in']) || $_SESSION['isotone_admin_logged_in'] !== true) {
    // Not logged in, redirect to login page
    $current_page = $_SERVER['REQUEST_URI'];
    header('Location: /isotone/iso-admin/login.php?redirect=' . urlencode($current_page));
    exit;
}

// Only validate fingerprint for logged-in users
if (!IsotoneSecurity::validateFingerprint()) {
    // Possible session hijacking attempt
    IsotoneSecurity::logSecurityEvent('session_hijack_attempt', [
        'session_id' => session_id(),
        'user' => $_SESSION['isotone_admin_user'] ?? 'unknown'
    ]);
    
    // Destroy session
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
    if (!in_array($key, $session_keys_to_keep)) {
        unset($_SESSION[$key]);
    }
}

// Load user class (config already loaded above)
require_once dirname(__DIR__) . '/iso-includes/class-user.php';

// Store original PHP memory limit before changing it
if (!defined('PHP_ORIGINAL_MEMORY_LIMIT')) {
    define('PHP_ORIGINAL_MEMORY_LIMIT', ini_get('memory_limit'));
}

// Ensure memory limit is enforced
if (defined('MEMORY_LIMIT')) {
    @ini_set('memory_limit', MEMORY_LIMIT);
}

// Check session timeout (2 hours)
if (isset($_SESSION['isotone_admin_last_activity']) && (time() - $_SESSION['isotone_admin_last_activity'] > 7200)) {
    // Log timeout event
    IsotoneSecurity::logSecurityEvent('session_timeout', [
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
        IsotoneSecurity::logSecurityEvent('unauthorized_access', [
            'user_id' => $current_user_id,
            'required_role' => $role,
            'page' => $_SERVER['REQUEST_URI']
        ]);
        
        header('Location: /isotone/iso-admin/403.php');
        exit;
    }
}

// CSRF Protection for POST requests (skip for API endpoints)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if this is an API endpoint
    $is_api = strpos($_SERVER['REQUEST_URI'], '/iso-admin/api/') !== false;
    
    // Only validate CSRF for non-API POST requests
    if (!$is_api && !iso_verify_csrf()) {
        // Log CSRF failure
        IsotoneSecurity::logSecurityEvent('csrf_failure', [
            'user' => $current_user,
            'page' => $_SERVER['REQUEST_URI']
        ]);
        
        die('CSRF token validation failed. Please refresh and try again.');
    }
}
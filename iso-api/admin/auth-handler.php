<?php
/**
 * Unified Authentication Handler for Admin APIs
 * Supports both session-based and API key authentication
 * 
 * @package Isotone
 * @since 0.3.3
 */

// Load API authentication class
require_once dirname(dirname(__DIR__)) . '/iso-includes/class-apiauth.php';

// Try API key authentication first
$api_auth = IsotoneAPIAuth::authenticate();

if ($api_auth) {
    // API key authentication successful
    define('API_AUTH', true);
    define('API_USER_ID', $api_auth['user_id']);
    define('API_USER', $api_auth['user']);
    define('API_KEY_ID', $api_auth['api_key_id']);
    define('API_PERMISSIONS', $api_auth['permissions']);
    
    // Set rate limit headers
    $rate_headers = IsotoneAPIAuth::getRateLimitHeaders($api_auth['api_key_id']);
    foreach ($rate_headers as $header => $value) {
        header("$header: $value");
    }
    
    // Create session-like variables for compatibility
    $_SESSION['isotone_admin_user_id'] = $api_auth['user_id'];
    $_SESSION['isotone_admin_user'] = $api_auth['user'];
    $_SESSION['api_authenticated'] = true;
    
} else {
    // Fall back to session authentication
    define('API_AUTH', false);
    
    // Start session if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check session authentication
    require_once dirname(dirname(__DIR__)) . '/iso-admin/auth.php';
    
    // If we get here, session auth succeeded
    define('API_USER_ID', $_SESSION['isotone_admin_user_id'] ?? 0);
    define('API_USER', $_SESSION['isotone_admin_user'] ?? 'admin');
    define('API_KEY_ID', null);
    define('API_PERMISSIONS', ['*']); // Session users have all permissions
}

/**
 * Check if current user has permission
 * 
 * @param string $permission
 * @return bool
 */
function api_has_permission($permission) {
    if (!API_AUTH) {
        // Session users have all permissions
        return true;
    }
    
    return in_array($permission, API_PERMISSIONS) || 
           in_array('*', API_PERMISSIONS) ||
           in_array(explode('.', $permission)[0] . '.*', API_PERMISSIONS);
}

/**
 * Require specific permission or die
 * 
 * @param string $permission
 */
function api_require_permission($permission) {
    if (!api_has_permission($permission)) {
        http_response_code(403);
        die(json_encode([
            'error' => 'Forbidden',
            'message' => "Missing required permission: $permission"
        ]));
    }
}
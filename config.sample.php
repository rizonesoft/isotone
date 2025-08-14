<?php
/**
 * Isotone CMS Configuration File
 * 
 * This file contains the basic configuration for your Isotone installation.
 * You should edit this file with your database connection details and other settings.
 * 
 * @package Isotone
 * @version 0.1.2-alpha
 */

// ============================================================================
// DATABASE CONFIGURATION - EDIT THESE SETTINGS
// ============================================================================

/** Database hostname */
define('DB_HOST', 'localhost');

/** Database name */
define('DB_NAME', 'database_name_here');

/** Database username */
define('DB_USER', 'username_here');

/** Database password */
define('DB_PASSWORD', 'password_here');

/** Database port (optional - defaults to 3306) */
define('DB_PORT', 3306);

/** Database charset */
define('DB_CHARSET', 'utf8mb4');

/** Database collation */
define('DB_COLLATE', 'utf8mb4_unicode_ci');

/** Database table prefix (for multiple installations in one database) */
define('DB_PREFIX', 'iso_');

// ============================================================================
// APPLICATION SETTINGS - CUSTOMIZE YOUR INSTALLATION
// ============================================================================

/** Site URL (leave empty for auto-detection) */
define('SITE_URL', '');

/** Administrator email */
define('ADMIN_EMAIL', 'your-email@example.com');

/** Default timezone */
define('TIMEZONE', 'UTC');

/** Default language */
define('LANGUAGE', 'en');

// ============================================================================
// SECURITY SETTINGS
// ============================================================================

/** 
 * Authentication keys and salts.
 * Change these to unique phrases for better security.
 * You can generate these using: https://api.wordpress.org/secret-key/1.1/salt/
 */
define('AUTH_KEY',         'put your unique phrase here');
define('SECURE_AUTH_KEY',  'put your unique phrase here');
define('LOGGED_IN_KEY',    'put your unique phrase here');
define('NONCE_KEY',        'put your unique phrase here');
define('AUTH_SALT',        'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT',   'put your unique phrase here');
define('NONCE_SALT',       'put your unique phrase here');

// ============================================================================
// DEVELOPER SETTINGS
// ============================================================================

/** Enable debug mode (shows errors and warnings) */
define('DEBUG_MODE', true);

/** Enable query logging */
define('DEBUG_QUERIES', false);

/** Display errors (turn off in production) */
define('DISPLAY_ERRORS', true);

/** Error reporting level */
define('ERROR_LEVEL', E_ALL);

/** Enable maintenance mode */
define('MAINTENANCE_MODE', false);

// ============================================================================
// ADVANCED SETTINGS - ONLY CHANGE IF YOU KNOW WHAT YOU'RE DOING
// ============================================================================

/** Memory limit */
define('MEMORY_LIMIT', '128M');

/** Maximum execution time */
define('MAX_EXECUTION_TIME', 30);

/** Upload maximum filesize */
define('UPLOAD_MAX_SIZE', '10M');

/** Session lifetime in minutes */
define('SESSION_LIFETIME', 120);

/** Cache TTL in seconds */
define('CACHE_TTL', 3600);

/** Enable Redis caching (requires Redis server) */
define('REDIS_ENABLED', false);
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);

// ============================================================================
// ENVIRONMENT DETECTION
// ============================================================================

/** Current environment (development, staging, production) */
define('ENVIRONMENT', 'development');

/** Override settings based on environment */
if (ENVIRONMENT === 'production') {
    // Override for production
    if (!defined('DEBUG_MODE')) define('DEBUG_MODE', false);
    if (!defined('DISPLAY_ERRORS')) define('DISPLAY_ERRORS', false);
} elseif (ENVIRONMENT === 'staging') {
    // Override for staging
    if (!defined('DEBUG_MODE')) define('DEBUG_MODE', true);
    if (!defined('DISPLAY_ERRORS')) define('DISPLAY_ERRORS', false);
}

// ============================================================================
// STOP EDITING! Happy publishing with Isotone CMS
// ============================================================================

/** Absolute path to the Isotone directory */
if (!defined('ISOTONE_ROOT')) {
    define('ISOTONE_ROOT', dirname(__FILE__));
}

/** Load the bootstrap file */
if (file_exists(ISOTONE_ROOT . '/app/bootstrap.php')) {
    require_once ISOTONE_ROOT . '/app/bootstrap.php';
}
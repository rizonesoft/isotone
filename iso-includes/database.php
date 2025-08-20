<?php
/**
 * Centralized Database Connection Manager
 * 
 * This file provides a single point for database connections
 * to avoid multiple RedBeanPHP setups and reduce memory usage
 * 
 * @package Isotone
 */

// Load RedBeanPHP if not already loaded
if (!class_exists('RedBeanPHP\R')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

use RedBeanPHP\R;

/**
 * Initialize database connection if not already connected
 * 
 * @return bool True if connection successful
 */
function isotone_db_connect() {
    // Check if already connected
    if (R::testConnection()) {
        return true;
    }
    
    // Load config if not already loaded
    if (!defined('DB_HOST')) {
        require_once dirname(__DIR__) . '/config.php';
    }
    
    try {
        // Detect if running in WSL and adjust host accordingly
        $db_host = DB_HOST;
        if (DB_HOST === 'localhost' && file_exists('/proc/sys/fs/binfmt_misc/WSLInterop')) {
            // Running in WSL - use Windows host IP
            $windows_host = trim(shell_exec("ip route | grep default | awk '{print $3}'"));
            if ($windows_host) {
                $db_host = $windows_host;
            }
        }
        
        // Setup RedBeanPHP connection
        R::setup('mysql:host=' . $db_host . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
        
        // Freeze the schema in production for better performance (saves memory)
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            R::freeze(true);
        }
        
        // Additional optimizations
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            // Use less memory for bean caching
            R::useWriterCache(false);
        }
        
        return true;
    } catch (Exception $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get database connection
 * Ensures connection is established before returning
 */
function isotone_db() {
    isotone_db_connect();
    return R::class;
}
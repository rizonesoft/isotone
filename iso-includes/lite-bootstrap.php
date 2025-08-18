<?php
/**
 * Lightweight Bootstrap for Admin Pages
 * 
 * Loads only essential components to save memory
 * Use this instead of full vendor/autoload.php when possible
 * 
 * @package Isotone
 */

// Only load if not already loaded
if (!defined('ISOTONE_LITE_LOADED')) {
    define('ISOTONE_LITE_LOADED', true);
    
    // Load config first
    if (!defined('DB_HOST')) {
        require_once dirname(__DIR__) . '/config.php';
    }
    
    // Register a minimal autoloader for RedBeanPHP only
    spl_autoload_register(function ($class) {
        // Only handle RedBeanPHP classes
        if (strpos($class, 'RedBeanPHP\\') === 0) {
            $file = dirname(__DIR__) . '/vendor/gabordemooij/redbean/' . str_replace('\\', '/', $class) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }
        
        // Handle Isotone classes
        if (strpos($class, 'Isotone\\') === 0) {
            $file = dirname(__DIR__) . '/iso-core/' . str_replace(['Isotone\\', '\\'], ['', '/'], $class) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }
        
        return false;
    });
    
    // Load RedBeanPHP main file
    require_once dirname(__DIR__) . '/vendor/gabordemooij/redbean/RedBeanPHP/R.php';
    
    // Load essential helpers
    if (file_exists(dirname(__DIR__) . '/iso-core/helpers.php')) {
        require_once dirname(__DIR__) . '/iso-core/helpers.php';
    }
    
    // Load hooks if they exist
    if (file_exists(dirname(__DIR__) . '/iso-core/hooks.php')) {
        require_once dirname(__DIR__) . '/iso-core/hooks.php';
    }
}
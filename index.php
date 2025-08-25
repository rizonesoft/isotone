<?php
/**
 * Isotone - Lightweight CMS without Symfony
 * 
 * @copyright  2025 Rizonetech (Pty) Ltd
 * @license    MIT License
 */
declare(strict_types=1);

define('ISOTONE_START', microtime(true));
define('ISOTONE_ROOT', __DIR__);

// Store original PHP memory limit BEFORE loading config
if (!defined('PHP_ORIGINAL_MEMORY_LIMIT')) {
    define('PHP_ORIGINAL_MEMORY_LIMIT', @ini_get('memory_limit'));
}

// Load configuration
if (file_exists(ISOTONE_ROOT . '/config.php')) {
    require_once ISOTONE_ROOT . '/config.php';
    
    // Enforce Isotone memory limit
    if (defined('MEMORY_LIMIT')) {
        @ini_set('memory_limit', MEMORY_LIMIT);
    }
    
    if (defined('MAX_EXECUTION_TIME')) {
        @ini_set('max_execution_time', MAX_EXECUTION_TIME);
    }
}

// Load composer autoloader (for RedBean and other non-Symfony packages)
require_once ISOTONE_ROOT . '/vendor/autoload.php';

// Load core router
use Isotone\Core\Router;

// Create and run router
$router = new Router(ISOTONE_ROOT);
$router->handle();

// Debug: Show memory usage in development
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    echo "\n<!-- Memory: " . round(memory_get_usage() / 1024 / 1024, 2) . " MB -->";
    echo "\n<!-- Peak: " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " MB -->";
    echo "\n<!-- Time: " . round((microtime(true) - ISOTONE_START) * 1000, 2) . " ms -->";
}
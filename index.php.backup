<?php
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
    
    // Isotone memory limit enforcement
    // Enforce Isotone memory limit
    if (defined('MEMORY_LIMIT')) {
        // ALWAYS enforce the limit, even if PHP has unlimited (-1)
        // This provides resource protection and predictable memory usage
        @ini_set('memory_limit', MEMORY_LIMIT);
    }
    
    if (defined('MAX_EXECUTION_TIME')) {
        @ini_set('max_execution_time', MAX_EXECUTION_TIME);
    }
}

require_once ISOTONE_ROOT . '/vendor/autoload.php';

use Isotone\Core\Application;
use Symfony\Component\HttpFoundation\Request;

$app = new Application(ISOTONE_ROOT);

$request = Request::createFromGlobals();
$response = $app->handle($request);
$response->send();

$app->terminate($request, $response);
<?php
declare(strict_types=1);

define('ISOTONE_START', microtime(true));
define('ISOTONE_ROOT', __DIR__);

require_once ISOTONE_ROOT . '/vendor/autoload.php';

use Isotone\Core\Application;
use Symfony\Component\HttpFoundation\Request;

$app = new Application(ISOTONE_ROOT);

$request = Request::createFromGlobals();
$response = $app->handle($request);
$response->send();

$app->terminate($request, $response);
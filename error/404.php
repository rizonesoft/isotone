<?php
/**
 * 404 Error Handler
 * Displays the error page directly without redirect
 * 
 * @package Isotone
 * @since 0.3.2
 */

// Set the error code
$_GET['code'] = 404;

// Include the universal error page directly
require_once __DIR__ . '/error.php';

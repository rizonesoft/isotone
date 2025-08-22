<?php
/**
 * Isotone API Index
 * 
 * Shows available API endpoints
 * 
 * @package Isotone
 * @since 0.3.0
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$apiInfo = [
    'name' => 'Isotone API',
    'version' => '0.3.0',
    'description' => 'RESTful API for Isotone CMS',
    'endpoints' => [
        'icons' => [
            'url' => '/iso-api/icons.php',
            'method' => 'GET',
            'description' => 'Get SVG icons on-demand',
            'parameters' => [
                'name' => 'Icon name (required)',
                'style' => 'Icon style: outline, solid, micro (default: outline)',
                'size' => 'Icon size in pixels (default: 24, range: 8-1024)',
                'color' => 'Icon color (default: currentColor)',
                'class' => 'CSS class name (optional)'
            ],
            'example' => '/iso-api/icons.php?name=home&style=outline&size=32&color=blue'
        ]
    ],
    'future_endpoints' => [
        'themes' => 'Theme management API',
        'plugins' => 'Plugin management API', 
        'content' => 'Content management API',
        'media' => 'Media upload and management API'
    ],
    'documentation' => '/docs/api/',
    'support' => 'https://github.com/isotone/isotone/issues'
];

echo json_encode($apiInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
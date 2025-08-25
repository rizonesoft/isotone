<?php
/**
 * Isotone API Discovery Endpoint
 * 
 * Returns JSON documentation of all available API endpoints
 * Access at: /iso-api/ or /iso-api/index.php
 * 
 * This serves as the API discovery/documentation endpoint,
 * similar to Swagger or OpenAPI specification
 * 
 * @package Isotone
 * @since 0.3.0
 */

// Check if this is a 404 request for a non-existent endpoint
if (isset($_GET['404']) && $_GET['404'] === 'true') {
    // Set the error code and include the error page
    $_GET['code'] = 404;
    require_once dirname(__DIR__) . '/server/error.php';
    exit;
}

// Check if user wants to see full documentation (requires auth)
$showFull = isset($_GET['full']) && $_GET['full'] === 'true';

if ($showFull) {
    // Use admin authentication for full documentation
    require_once dirname(__DIR__) . '/iso-admin/auth.php';
    $isAdmin = true; // If we get here, user is authenticated
} else {
    // Public access - no auth required
    $isAdmin = false;
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Build API info - always show public endpoints
$apiInfo = [
    'name' => 'Isotone API',
    'version' => '0.3.2',
    'description' => 'RESTful API for Isotone CMS',
    'endpoints' => [
        'public' => [
            'description' => 'Public endpoints (no authentication required)',
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
            ]
        ]
    ],
    'documentation' => '/docs/api/',
    'support' => 'https://github.com/isotone/isotone/issues'
];

// Only show admin endpoints to authenticated admins
if ($isAdmin) {
    $apiInfo['endpoints']['admin'] = [
        'description' => 'Admin-only endpoints (requires authentication)',
        'authentication' => 'Session-based authentication required',
        'endpoints' => [
            'toni' => [
                'url' => '/iso-api/admin/toni.php',
                'method' => 'POST',
                'description' => 'AI assistant for admin tasks',
                'parameters' => [
                    'message' => 'User message for the assistant',
                    'context' => 'Optional context data'
                ]
            ],
            'stats' => [
                'url' => '/iso-api/admin/stats.php',
                'method' => 'GET',
                'description' => 'Get system statistics and metrics'
            ],
            'token-usage' => [
                'url' => '/iso-api/admin/token-usage.php',
                'method' => 'GET',
                'description' => 'Get AI token usage statistics'
            ],
            'openai-usage' => [
                'url' => '/iso-api/admin/openai-usage.php',
                'method' => 'GET',
                'description' => 'Get OpenAI API usage data'
            ],
            'docs-search' => [
                'url' => '/iso-api/admin/docs-search.php',
                'method' => 'GET',
                'description' => 'Search documentation',
                'parameters' => [
                    'q' => 'Search query'
                ]
            ]
        ]
    ];
    
    // Show future admin endpoints
    $apiInfo['future_endpoints'] = [
        'themes' => 'Theme management API',
        'plugins' => 'Plugin management API', 
        'content' => 'Content management API',
        'media' => 'Media upload and management API',
        'users' => 'User management API',
        'settings' => 'Settings management API'
    ];
} else {
    // For non-admins, just show that admin endpoints exist but require auth
    $apiInfo['endpoints']['admin'] = [
        'description' => 'Admin endpoints require authentication',
        'authentication' => 'Please authenticate as an admin to view available endpoints'
    ];
}

echo json_encode($apiInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
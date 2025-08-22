<?php
/**
 * Isotone Icons API Endpoint
 * 
 * RESTful endpoint for serving icons on-demand
 * 
 * Usage:
 * GET /api/icons.php?name=home&style=outline&size=24
 * GET /api/icons.php?name=user&style=solid&size=32&color=blue
 * 
 * @package Isotone
 * @since 0.3.0
 */

// Set proper headers
header('Content-Type: image/svg+xml');
header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *'); // Allow CORS for API usage
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET, OPTIONS');
    echo '<!-- Error: Method not allowed. Use GET. -->';
    exit;
}

// Get and validate request parameters
$iconName = isset($_GET['name']) ? preg_replace('/[^a-z0-9\-]/', '', strtolower($_GET['name'])) : '';
$style = isset($_GET['style']) ? $_GET['style'] : 'outline';
$size = isset($_GET['size']) ? intval($_GET['size']) : 24;
$class = isset($_GET['class']) ? htmlspecialchars($_GET['class']) : '';
$color = isset($_GET['color']) ? htmlspecialchars($_GET['color']) : 'currentColor';

// Validate parameters
if (empty($iconName)) {
    http_response_code(400);
    echo '<!-- Error: Icon name is required. Use ?name=icon-name -->';
    exit;
}

// Validate style
$validStyles = ['outline', 'solid', 'micro'];
if (!in_array($style, $validStyles)) {
    http_response_code(400);
    echo '<!-- Error: Invalid style. Use: outline, solid, or micro -->';
    exit;
}

// Validate size (prevent XSS and unreasonable sizes)
if ($size < 8 || $size > 1024) {
    http_response_code(400);
    echo '<!-- Error: Size must be between 8 and 1024 pixels -->';
    exit;
}

// Load the appropriate icon library
$iconLibraryPath = dirname(__DIR__) . '/iso-core/Core/';
$svgContent = '';
$viewBox = '';
$defaultAttributes = [];

try {
    switch ($style) {
        case 'outline':
            require_once $iconLibraryPath . 'IconLibrary.php';
            if (class_exists('IconLibrary') && IconLibrary::hasIcon($iconName)) {
                $svgContent = IconLibrary::getIconPath($iconName);
                $viewBox = '0 0 24 24';
                $defaultAttributes = [
                    'fill' => 'none',
                    'stroke' => $color,
                    'stroke-width' => '1.5'
                ];
            }
            break;
            
        case 'solid':
            require_once $iconLibraryPath . 'IconLibrarySolid.php';
            if (class_exists('IconLibrarySolid') && IconLibrarySolid::hasIcon($iconName)) {
                $svgContent = IconLibrarySolid::getIconPath($iconName);
                $viewBox = '0 0 24 24';
                $defaultAttributes = [
                    'fill' => $color
                ];
            }
            break;
            
        case 'micro':
            require_once $iconLibraryPath . 'IconLibraryMicro.php';
            if (class_exists('IconLibraryMicro') && IconLibraryMicro::hasIcon($iconName)) {
                $svgContent = IconLibraryMicro::getIconPath($iconName);
                $viewBox = '0 0 16 16';
                $defaultAttributes = [
                    'fill' => $color
                ];
            }
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo '<!-- Error: Internal server error -->';
    exit;
}

// Check if icon was found
if (empty($svgContent)) {
    http_response_code(404);
    echo '<!-- Error: Icon "' . htmlspecialchars($iconName) . '" not found in style "' . htmlspecialchars($style) . '" -->';
    exit;
}

// Build SVG attributes
$attributes = array_merge([
    'xmlns' => 'http://www.w3.org/2000/svg',
    'width' => $size,
    'height' => $size,
    'viewBox' => $viewBox,
    'aria-hidden' => 'true'
], $defaultAttributes);

// Add custom class if provided
if (!empty($class)) {
    $attributes['class'] = $class;
}

// Generate SVG
$svg = '<svg';
foreach ($attributes as $key => $value) {
    $svg .= ' ' . $key . '="' . $value . '"';
}
$svg .= '>' . $svgContent . '</svg>';

// Add ETag for better caching
$etag = md5($svg . $iconName . $style . $size . $color);
header('ETag: "' . $etag . '"');

// Check if client has cached version
if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $etag . '"') {
    http_response_code(304); // Not Modified
    exit;
}

// Output the SVG
echo $svg;
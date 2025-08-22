<?php
/**
 * AJAX endpoint for fetching icons
 * Returns SVG icons from the PHP icon libraries
 */

// No auth required for icons (they're public assets)
require_once dirname(__DIR__) . '/config.php';

// Get parameters
$icon = $_GET['icon'] ?? '';
$style = $_GET['style'] ?? 'outline'; // outline, solid, or micro
$attributes = $_GET['attributes'] ?? [];

// Validate style
if (!in_array($style, ['outline', 'solid', 'micro'])) {
    $style = 'outline';
}

// Load appropriate icon library
$iconLibraryPath = dirname(__DIR__) . '/iso-core/Core/';
switch ($style) {
    case 'solid':
        require_once $iconLibraryPath . 'IconLibrarySolid.php';
        $className = 'IconLibrarySolid';
        break;
    case 'micro':
        require_once $iconLibraryPath . 'IconLibraryMicro.php';
        $className = 'IconLibraryMicro';
        break;
    default:
        require_once $iconLibraryPath . 'IconLibrary.php';
        $className = 'IconLibrary';
        break;
}

// Set JSON header
header('Content-Type: application/json');

// Handle different request types
$action = $_GET['action'] ?? 'get';

switch ($action) {
    case 'get':
        // Get a single icon
        if (empty($icon)) {
            echo json_encode(['error' => 'Icon name required']);
            exit;
        }
        
        // Parse attributes if they're a JSON string
        if (is_string($attributes)) {
            $attributes = json_decode($attributes, true) ?? [];
        }
        
        // Get icon SVG
        $svg = $className::getIcon($icon, $attributes);
        echo json_encode(['svg' => $svg]);
        break;
        
    case 'path':
        // Get just the path
        if (empty($icon)) {
            echo json_encode(['error' => 'Icon name required']);
            exit;
        }
        
        $path = $className::getIconPath($icon);
        echo json_encode(['path' => $path]);
        break;
        
    case 'exists':
        // Check if icon exists
        if (empty($icon)) {
            echo json_encode(['exists' => false]);
            exit;
        }
        
        $exists = $className::hasIcon($icon);
        echo json_encode(['exists' => $exists]);
        break;
        
    case 'list':
        // Get all icon names
        $names = $className::getIconNames();
        echo json_encode(['icons' => $names]);
        break;
        
    case 'search':
        // Search icons
        $keyword = $_GET['keyword'] ?? '';
        if (empty($keyword)) {
            echo json_encode(['results' => []]);
            exit;
        }
        
        $results = $className::searchIcons($keyword);
        echo json_encode(['results' => $results]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
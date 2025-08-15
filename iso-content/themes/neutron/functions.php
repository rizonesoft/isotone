<?php
/**
 * Neutron Theme Functions
 * 
 * This file contains theme setup, hooks, and helper functions
 * for the Neutron theme.
 * 
 * @package Neutron
 * @version 1.0.0
 */

namespace NeutronTheme;

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/');
}

// Load WordPress compatibility functions
require_once __DIR__ . '/compat.php';

/**
 * Theme setup
 */
function theme_setup() {
    // Theme support features will be implemented when Isotone's hook system is ready
    // For now, these are placeholders showing intended functionality:
    
    // Future features:
    // - Post thumbnails support
    // - Automatic title tag generation
    // - HTML5 markup support
    // - Navigation menus: primary, footer, social
    
    return true;
}

// Register theme setup with Isotone hooks
if (function_exists('add_action')) {
    add_action('after_setup_theme', __NAMESPACE__ . '\\theme_setup');
}

/**
 * Enqueue theme styles and scripts
 */
function enqueue_assets() {
    // Asset enqueueing will be implemented when Isotone's asset system is ready
    // For now, assets are loaded directly in templates
    
    // Future implementation will enqueue:
    // - Tailwind CSS (CDN)
    // - Theme custom styles
    // - Alpine.js (CDN)
    // - Theme custom scripts
    
    return true;
}

// Register asset enqueuing with Isotone hooks
if (function_exists('add_action')) {
    add_action('iso_enqueue_scripts', __NAMESPACE__ . '\\enqueue_assets');
}

/**
 * Register theme widgets
 */
function register_widgets() {
    // Widget registration will be implemented when Isotone's widget system is ready
    // Planned widgets:
    // - Primary Sidebar
    // - Footer Widget Area
    
    return true;
}

// Register widgets with Isotone hooks
if (function_exists('add_action')) {
    add_action('widgets_init', __NAMESPACE__ . '\\register_widgets');
}

/**
 * Theme customizer options
 */
function customize_register($customizer = null) {
    // Customizer options will be implemented when Isotone's customizer system is ready
    // Planned options:
    // - Primary color picker
    // - Dark mode default (auto/light/dark)
    // - Typography settings
    // - Layout options
    
    return true;
}

// Register customizer options with Isotone hooks
if (function_exists('add_action')) {
    add_action('customize_register', __NAMESPACE__ . '\\customize_register');
}

/**
 * Sanitize dark mode setting
 */
function sanitize_dark_mode($input) {
    $valid = ['auto', 'light', 'dark'];
    return in_array($input, $valid) ? $input : 'auto';
}

/**
 * Get Heroicon SVG
 * 
 * @param string $icon Icon name (e.g., 'home', 'menu', 'x')
 * @param string $type Icon type ('outline' or 'solid')
 * @param string $class Additional CSS classes
 * @return string SVG HTML
 */
function get_heroicon($icon, $type = 'outline', $class = 'w-6 h-6') {
    $icons = [
        'outline' => [
            'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />',
            'menu' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />',
            'x' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />',
            'search' => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />',
            'user' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />',
            'cog' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />',
        ],
        'solid' => [
            'home' => '<path d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z" /><path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z" />',
        ]
    ];
    
    $fill = $type === 'solid' ? 'currentColor' : 'none';
    $stroke = $type === 'outline' ? 'currentColor' : 'none';
    $strokeWidth = $type === 'outline' ? '1.5' : '0';
    
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" fill="' . $fill . '" viewBox="0 0 24 24" stroke-width="' . $strokeWidth . '" stroke="' . $stroke . '" class="' . esc_attr($class) . '">';
    $svg .= $icons[$type][$icon] ?? '';
    $svg .= '</svg>';
    
    return $svg;
}

/**
 * Helper function to get theme option
 */
function get_theme_option($option, $default = null) {
    // Will integrate with Isotone's settings system when available
    // For now, return defaults
    $defaults = [
        'primary_color' => '#00D9FF',
        'dark_mode_default' => 'auto',
    ];
    
    return $defaults[$option] ?? $default;
}

/**
 * Output custom CSS based on theme options
 */
function custom_css() {
    $primary_color = get_theme_option('primary_color', '#00D9FF');
    ?>
    <style>
        :root {
            --neutron-primary: <?php echo htmlspecialchars($primary_color); ?>;
        }
    </style>
    <?php
}

// Hook registration will be enabled when Isotone implements its hook system
// add_action('wp_head', __NAMESPACE__ . '\\custom_css');
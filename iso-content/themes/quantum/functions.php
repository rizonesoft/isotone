<?php
/**
 * Quantum Theme Functions
 * 
 * @package Quantum
 * @version 1.0.0
 */

namespace QuantumTheme;

// Prevent direct access
if (!defined('ISOTONE_ROOT')) {
    die('Direct access not permitted');
}

/**
 * Theme setup
 */
function theme_setup() {
    // Register theme features (stored in theme config)
    $theme_features = [
        'custom-logo' => true,
        'custom-header' => true,
        'post-thumbnails' => true,
        'title-tag' => true,
        'html5' => ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption']
    ];
    
    // Store theme features in global for later use
    $GLOBALS['quantum_features'] = $theme_features;
    
    // Register navigation menus
    $GLOBALS['quantum_menus'] = [
        'primary' => 'Primary Menu',
        'footer' => 'Footer Menu',
        'social' => 'Social Menu'
    ];
}

// Use Isotone's hook system
if (function_exists('add_action')) {
    add_action('after_setup_theme', __NAMESPACE__ . '\\theme_setup');
}

/**
 * Enqueue theme styles and scripts
 */
function enqueue_assets() {
    // For now, we'll include styles and scripts directly in the template
    // since Isotone doesn't have wp_enqueue_style/script yet
    return;
}

// Hook into Isotone's script loading if available
if (function_exists('add_action')) {
    add_action('iso_enqueue_scripts', __NAMESPACE__ . '\\enqueue_assets');
}

/**
 * Get theme option
 */
function get_theme_option($key, $default = null) {
    $options = get_option('quantum_options', []);
    return isset($options[$key]) ? $options[$key] : $default;
}

/**
 * Get SVG icon
 */
function get_icon($name, $class = '') {
    $icons = [
        'menu' => '<svg class="' . $class . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>',
        'close' => '<svg class="' . $class . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
        'search' => '<svg class="' . $class . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>',
        'sun' => '<svg class="' . $class . '" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/></svg>',
        'moon' => '<svg class="' . $class . '" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg>',
        'arrow-right' => '<svg class="' . $class . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>',
        'sparkles' => '<svg class="' . $class . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>'
    ];
    
    return isset($icons[$name]) ? $icons[$name] : '';
}

/**
 * Get body classes
 */
function get_body_classes() {
    $classes = [];
    
    // Add dark mode class if enabled
    $dark_mode = get_theme_option('dark_mode_default', 'auto');
    if ($dark_mode === 'dark') {
        $classes[] = 'dark-mode';
    }
    
    // Add animation speed class
    $animation_speed = get_theme_option('animation_speed', 'normal');
    $classes[] = 'animation-' . $animation_speed;
    
    // Add glass morphism class
    $classes[] = 'quantum-theme';
    
    return $classes;
}

/**
 * Register widget areas (for future use)
 */
function register_widgets() {
    // Store widget areas in global for later use
    $GLOBALS['quantum_widgets'] = [
        'sidebar-main' => [
            'name' => 'Main Sidebar',
            'description' => 'Main sidebar widget area',
            'before_widget' => '<div class="widget glass-card %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h3 class="widget-title">',
            'after_title' => '</h3>'
        ],
        'footer-widgets' => [
            'name' => 'Footer Widgets',
            'description' => 'Footer widget area',
            'before_widget' => '<div class="footer-widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h4 class="footer-widget-title">',
            'after_title' => '</h4>'
        ]
    ];
}

// Initialize widgets if Isotone supports them
if (function_exists('add_action')) {
    add_action('widgets_init', __NAMESPACE__ . '\\register_widgets');
}

/**
 * Theme customizer options (for future implementation)
 */
function customize_register() {
    // Store customizer options for later use
    $GLOBALS['quantum_customizer'] = [
        'glass_blur' => [
            'default' => '20px',
            'type' => 'select',
            'label' => 'Glass Blur Intensity',
            'choices' => [
                '10px' => 'Light',
                '20px' => 'Medium',
                '30px' => 'Strong'
            ]
        ],
        'gradient_animated' => [
            'default' => true,
            'type' => 'checkbox',
            'label' => 'Animated Gradient Background'
        ]
    ];
}
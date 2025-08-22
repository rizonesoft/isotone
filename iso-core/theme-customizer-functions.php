<?php
/**
 * Theme Customizer Helper Functions
 * 
 * WordPress-compatible functions for theme customizer integration
 * 
 * @package Isotone
 * @since 1.0.0
 */

use Isotone\Core\Customizer;

/**
 * Check if we're in the customizer preview
 */
function is_customize_preview() {
    return isset($_GET['customize_preview']) && $_GET['customize_preview'] == '1';
}

/**
 * Enqueue customizer preview scripts
 */
function customize_preview_init() {
    if (is_customize_preview()) {
        // Add preview script to page
        add_action('wp_footer', function() {
            echo '<script src="/isotone/iso-admin/js/customize-preview.js"></script>';
        });
    }
}

/**
 * Register customizer section
 * 
 * @param WP_Customize_Manager $wp_customize
 */
function customize_register($wp_customize) {
    // This function is for theme developers to hook into
    // They would use: add_action('customize_register', 'my_theme_customize_register');
}

/**
 * Add customizer section helper
 */
function isotone_add_customizer_section($id, $args = []) {
    $customizer = Customizer::getInstance();
    return $customizer->addSection($id, $args);
}

/**
 * Add customizer setting helper
 */
function isotone_add_customizer_setting($id, $args = []) {
    $customizer = Customizer::getInstance();
    return $customizer->addSetting($id, $args);
}

/**
 * Add customizer control helper
 */
function isotone_add_customizer_control($id, $args = []) {
    $customizer = Customizer::getInstance();
    return $customizer->addControl($id, $args);
}

/**
 * Get customizer setting value
 */
function get_customizer_setting($setting_id, $default = '') {
    $customizer = Customizer::getInstance();
    $value = $customizer->getSettingValue($setting_id);
    return $value !== null ? $value : $default;
}

/**
 * Output customizer CSS
 */
function isotone_customizer_css() {
    if (!is_customize_preview()) {
        return;
    }
    
    $customizer = Customizer::getInstance();
    $css = '';
    
    // Get color settings
    $primary_color = get_theme_mod('primary_color', '#00D9FF');
    $background_color = get_theme_mod('background_color', '#0A0E27');
    $text_color = get_theme_mod('text_color', '#FFFFFF');
    $link_color = get_theme_mod('link_color', '#00D9FF');
    
    // Generate CSS
    $css .= ':root {';
    $css .= '--primary-color: ' . $primary_color . ';';
    $css .= '--background-color: ' . $background_color . ';';
    $css .= '--text-color: ' . $text_color . ';';
    $css .= '--link-color: ' . $link_color . ';';
    $css .= '}';
    
    // Typography settings
    $font_size = get_theme_mod('font_size', '16');
    $font_family = get_theme_mod('font_family', 'Inter, sans-serif');
    $line_height = get_theme_mod('line_height', '1.6');
    
    $css .= 'body {';
    $css .= 'font-size: ' . $font_size . 'px;';
    $css .= 'font-family: ' . $font_family . ';';
    $css .= 'line-height: ' . $line_height . ';';
    $css .= '}';
    
    // Layout settings
    $container_width = get_theme_mod('container_width', '1200');
    $css .= '.container, .site-container {';
    $css .= 'max-width: ' . $container_width . 'px;';
    $css .= '}';
    
    // Custom CSS
    $custom_css = get_theme_mod('custom_css', '');
    if (!empty($custom_css)) {
        $css .= $custom_css;
    }
    
    // Output CSS
    if (!empty($css)) {
        echo '<style id="isotone-customizer-css">' . $css . '</style>';
    }
}

/**
 * Register default customizer settings for themes
 */
function isotone_register_default_customizer_settings() {
    $customizer = Customizer::getInstance();
    
    // Allow themes to add their own sections
    do_action('isotone_customize_register', $customizer);
    
    // Typography Section
    $customizer->addSection('typography', [
        'title' => 'Typography',
        'priority' => 50,
        'description' => 'Customize fonts and text appearance'
    ]);
    
    // Font Size
    $customizer->addSetting('font_size', [
        'default' => '16',
        'type' => 'theme_mod',
        'transport' => 'postMessage',
        'sanitize_callback' => 'absint'
    ]);
    
    $customizer->addControl('font_size', [
        'label' => 'Base Font Size',
        'section' => 'typography',
        'type' => 'range',
        'input_attrs' => [
            'min' => 12,
            'max' => 24,
            'step' => 1
        ]
    ]);
    
    // Font Family
    $customizer->addSetting('font_family', [
        'default' => 'Inter, sans-serif',
        'type' => 'theme_mod',
        'transport' => 'postMessage',
        'sanitize_callback' => 'sanitize_text_field'
    ]);
    
    $customizer->addControl('font_family', [
        'label' => 'Font Family',
        'section' => 'typography',
        'type' => 'select',
        'choices' => [
            'Inter, sans-serif' => 'Inter',
            'system-ui, sans-serif' => 'System UI',
            'Georgia, serif' => 'Georgia',
            '"Times New Roman", serif' => 'Times New Roman',
            'Arial, sans-serif' => 'Arial',
            '"Courier New", monospace' => 'Courier New'
        ]
    ]);
    
    // Layout Section
    $customizer->addSection('layout', [
        'title' => 'Layout',
        'priority' => 70,
        'description' => 'Customize site layout options'
    ]);
    
    // Container Width
    $customizer->addSetting('container_width', [
        'default' => '1200',
        'type' => 'theme_mod',
        'transport' => 'postMessage',
        'sanitize_callback' => 'absint'
    ]);
    
    $customizer->addControl('container_width', [
        'label' => 'Container Width',
        'section' => 'layout',
        'type' => 'range',
        'input_attrs' => [
            'min' => 960,
            'max' => 1920,
            'step' => 10
        ]
    ]);
    
    // Sidebar Position
    $customizer->addSetting('sidebar_position', [
        'default' => 'right',
        'type' => 'theme_mod',
        'transport' => 'postMessage',
        'sanitize_callback' => 'sanitize_text_field'
    ]);
    
    $customizer->addControl('sidebar_position', [
        'label' => 'Sidebar Position',
        'section' => 'layout',
        'type' => 'radio',
        'choices' => [
            'left' => 'Left',
            'right' => 'Right',
            'none' => 'No Sidebar'
        ]
    ]);
    
    // Custom CSS Section
    $customizer->addSection('custom_css_section', [
        'title' => 'Additional CSS',
        'priority' => 200,
        'description' => 'Add custom CSS rules'
    ]);
    
    $customizer->addSetting('custom_css', [
        'default' => '',
        'type' => 'theme_mod',
        'transport' => 'postMessage',
        'sanitize_callback' => 'wp_strip_all_tags'
    ]);
    
    $customizer->addControl('custom_css', [
        'label' => 'Custom CSS',
        'section' => 'custom_css_section',
        'type' => 'textarea',
        'description' => 'Enter your custom CSS rules here'
    ]);
}

/**
 * Sanitization callbacks
 */
function sanitize_text_field($input) {
    return strip_tags($input);
}

function absint($input) {
    return abs(intval($input));
}

function wp_strip_all_tags($input) {
    return strip_tags($input);
}

function esc_url_raw($url) {
    return filter_var($url, FILTER_SANITIZE_URL);
}

function sanitize_email($email) {
    return filter_var($email, FILTER_SANITIZE_EMAIL);
}

/**
 * Initialize customizer on admin
 */
if (defined('ISOTONE_ADMIN')) {
    add_action('init', 'isotone_register_default_customizer_settings');
}

/**
 * Add preview init on front-end
 */
if (!defined('ISOTONE_ADMIN')) {
    add_action('init', 'customize_preview_init');
    add_action('wp_head', 'isotone_customizer_css');
}

/**
 * Helper to check if customizer is supported
 */
function isotone_customizer_supported() {
    return class_exists('Isotone\Core\Customizer');
}

/**
 * Get all registered customizer sections
 */
function get_customizer_sections() {
    if (!isotone_customizer_supported()) {
        return [];
    }
    
    $customizer = Customizer::getInstance();
    return $customizer->getSections();
}

/**
 * Get all registered customizer controls
 */
function get_customizer_controls() {
    if (!isotone_customizer_supported()) {
        return [];
    }
    
    $customizer = Customizer::getInstance();
    return $customizer->getControls();
}
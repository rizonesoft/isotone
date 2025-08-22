<?php
/**
 * Example: How Themes Add Customizer Settings in Isotone
 * 
 * This file demonstrates how themes can register their own
 * customizer sections, settings, and controls.
 * 
 * Place this code in your theme's functions.php file
 */

// Get the customizer instance
use Isotone\Core\Customizer;

/**
 * Register theme customizer settings
 * This function should be called during theme initialization
 */
function my_theme_customize_register() {
    $customizer = Customizer::getInstance();
    
    // 1. ADD A NEW SECTION
    $customizer->addSection('my_theme_options', [
        'title'       => 'Theme Options',
        'description' => 'Custom settings for My Theme',
        'priority'    => 120, // Order in the customizer
        'capability'  => 'edit_theme_options',
        'icon'        => 'cog' // Icon name (see below for available icons)
    ]);
    
    // 2. ADD SETTINGS
    
    // Text setting example
    $customizer->addSetting('my_theme_footer_text', [
        'default'           => 'Copyright © 2024',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage' // or 'refresh'
    ]);
    
    // Color setting example
    $customizer->addSetting('my_theme_accent_color', [
        'default'           => '#FF6B6B',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage'
    ]);
    
    // Checkbox setting example
    $customizer->addSetting('my_theme_show_sidebar', [
        'default'           => true,
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_checkbox',
        'transport'         => 'refresh'
    ]);
    
    // Select/dropdown setting example
    $customizer->addSetting('my_theme_layout', [
        'default'           => 'right-sidebar',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh'
    ]);
    
    // 3. ADD CONTROLS (UI elements for the settings)
    
    // Text control
    $customizer->addControl('my_theme_footer_text', [
        'label'       => 'Footer Copyright Text',
        'section'     => 'my_theme_options',
        'setting'     => 'my_theme_footer_text',
        'type'        => 'text',
        'description' => 'Enter the copyright text for your footer'
    ]);
    
    // Color picker control
    $customizer->addControl('my_theme_accent_color', [
        'label'       => 'Accent Color',
        'section'     => 'my_theme_options',
        'setting'     => 'my_theme_accent_color',
        'type'        => 'color',
        'description' => 'Choose the accent color for buttons and links'
    ]);
    
    // Checkbox control
    $customizer->addControl('my_theme_show_sidebar', [
        'label'       => 'Show Sidebar',
        'section'     => 'my_theme_options',
        'setting'     => 'my_theme_show_sidebar',
        'type'        => 'checkbox',
        'description' => 'Display sidebar on blog pages'
    ]);
    
    // Select/dropdown control
    $customizer->addControl('my_theme_layout', [
        'label'       => 'Site Layout',
        'section'     => 'my_theme_options',
        'setting'     => 'my_theme_layout',
        'type'        => 'select',
        'choices'     => [
            'no-sidebar'    => 'No Sidebar',
            'left-sidebar'  => 'Left Sidebar',
            'right-sidebar' => 'Right Sidebar',
            'dual-sidebar'  => 'Dual Sidebars'
        ],
        'description' => 'Choose your preferred layout'
    ]);
    
    // 4. ADD TO EXISTING SECTIONS
    // You can also add controls to built-in sections
    
    $customizer->addSetting('my_theme_logo_height', [
        'default'           => '60',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'absint',
        'transport'         => 'postMessage'
    ]);
    
    $customizer->addControl('my_theme_logo_height', [
        'label'       => 'Logo Height (px)',
        'section'     => 'site_identity', // Built-in section
        'setting'     => 'my_theme_logo_height',
        'type'        => 'number',
        'input_attrs' => [
            'min'  => 30,
            'max'  => 150,
            'step' => 5
        ]
    ]);
}

/**
 * Hook into Isotone's customizer registration
 * Call this in your theme's functions.php
 */
if (function_exists('add_action')) {
    // WordPress-style hook (if Isotone implements it)
    add_action('customize_register', 'my_theme_customize_register');
} else {
    // Direct call (current Isotone approach)
    my_theme_customize_register();
}

/**
 * Using customizer values in your theme templates
 */
function my_theme_example_usage() {
    // Get a customizer value
    $footer_text = get_theme_mod('my_theme_footer_text', 'Default Copyright');
    $accent_color = get_theme_mod('my_theme_accent_color', '#FF6B6B');
    $show_sidebar = get_theme_mod('my_theme_show_sidebar', true);
    $layout = get_theme_mod('my_theme_layout', 'right-sidebar');
    
    // Use in template
    ?>
    <footer style="background-color: <?php echo esc_attr($accent_color); ?>">
        <p><?php echo esc_html($footer_text); ?></p>
    </footer>
    
    <?php if ($show_sidebar): ?>
        <aside class="sidebar sidebar-<?php echo esc_attr($layout); ?>">
            <!-- Sidebar content -->
        </aside>
    <?php endif;
}

/**
 * Available Control Types in Isotone:
 * 
 * - text: Single line text input
 * - textarea: Multi-line text input
 * - email: Email input
 * - url: URL input
 * - number: Number input with min/max/step
 * - range: Slider control
 * - checkbox: Checkbox for boolean values
 * - select: Dropdown menu
 * - radio: Radio button group
 * - color: Color picker
 * - image: Image upload (when media library is implemented)
 * - dropdown-pages: Page selector (when pages are implemented)
 */

/**
 * Sanitization Callbacks:
 * 
 * - sanitize_text_field: For text inputs
 * - sanitize_textarea_field: For textareas
 * - sanitize_email: For email addresses
 * - esc_url_raw: For URLs
 * - absint: For positive integers
 * - sanitize_hex_color: For hex color codes
 * - wp_validate_boolean: For checkboxes
 * - sanitize_key: For select/radio options
 */

/**
 * Available Icons for Sections:
 * 
 * Default Icons:
 * - cog: Settings/configuration gear
 * - identification: User/identity card
 * - swatch: Color palette/swatches
 * - view-columns: Layout/columns
 * - bars-3-bottom-left: Footer/bottom bars
 * - sparkles: Special/featured content
 * - template: Page template/layout
 * - palette: Color palette
 * - photograph: Images/media
 * - document-text: Text/content
 * - globe: Global/international
 * 
 * Usage:
 * When adding a section, specify the icon parameter:
 * $customizer->addSection('my_section', [
 *     'title' => 'My Section',
 *     'icon' => 'sparkles'
 * ]);
 * 
 * If no icon is specified, 'cog' will be used as default.
 * Custom icons can be added by extending the icon library.
 */
?>
<?php
/**
 * Plugin Name: Hello Isotone
 * Description: A simple example plugin demonstrating the Isotone plugin API
 * Version: 1.0.0
 * Author: Isotone Team
 * License: MIT
 */

// Prevent direct access
if (!defined('ISOTONE_ROOT')) {
    die('Direct access not permitted');
}

/**
 * Plugin activation hook
 * Called when the plugin is activated
 */
function hello_isotone_activate() {
    // Create database tables, set default options, etc.
    add_option('hello_isotone_activated', time());
    add_option('hello_isotone_message', 'Hello from Isotone CMS!');
}

/**
 * Plugin deactivation hook
 * Called when the plugin is deactivated
 */
function hello_isotone_deactivate() {
    // Cleanup temporary data
    delete_option('hello_isotone_last_shown');
}

/**
 * Add a greeting message to the admin dashboard
 */
add_action('admin_dashboard_top', 'hello_isotone_dashboard_widget');
function hello_isotone_dashboard_widget() {
    $message = get_option('hello_isotone_message', 'Hello!');
    echo '<div class="iso-status iso-status-info">';
    echo '<h3>Hello Isotone Says:</h3>';
    echo '<p>' . esc_html($message) . '</p>';
    echo '<p><small>This message is from the Hello Isotone example plugin.</small></p>';
    echo '</div>';
}

/**
 * Add a menu item to the admin panel
 */
add_action('admin_menu', 'hello_isotone_add_menu');
function hello_isotone_add_menu() {
    add_menu_page(
        'Hello Isotone',           // Page title
        'Hello Isotone',           // Menu title
        'manage_options',          // Capability required
        'hello-isotone',           // Menu slug
        'hello_isotone_admin_page', // Callback function
        'dashicons-smiley',        // Icon
        100                        // Position
    );
}

/**
 * Admin page content
 */
function hello_isotone_admin_page() {
    // Handle form submission
    if (isset($_POST['hello_isotone_save'])) {
        update_option('hello_isotone_message', sanitize_text_field($_POST['hello_message']));
        echo '<div class="iso-status iso-status-success">Settings saved!</div>';
    }
    
    $current_message = get_option('hello_isotone_message', 'Hello from Isotone CMS!');
    ?>
    <div class="iso-container iso-container-md">
        <h1 class="iso-title">Hello Isotone Settings</h1>
        <p class="iso-subtitle">Configure your greeting message</p>
        
        <form method="post" action="">
            <div class="iso-form-group">
                <label for="hello_message" class="iso-label">Greeting Message</label>
                <input type="text" 
                       id="hello_message" 
                       name="hello_message" 
                       value="<?php echo esc_attr($current_message); ?>"
                       class="iso-input">
                <div class="iso-input-hint">
                    This message will be displayed in the admin dashboard.
                </div>
            </div>
            
            <button type="submit" name="hello_isotone_save" class="iso-btn">
                Save Settings
            </button>
        </form>
        
        <div class="iso-card-footer">
            <p>This is an example plugin demonstrating the Isotone plugin API.</p>
        </div>
    </div>
    <?php
}

/**
 * Add a shortcode for displaying the greeting
 */
add_shortcode('hello_isotone', 'hello_isotone_shortcode');
function hello_isotone_shortcode($atts) {
    $atts = shortcode_atts([
        'name' => 'World'
    ], $atts);
    
    $message = get_option('hello_isotone_message', 'Hello');
    return sprintf('<span class="hello-isotone-greeting">%s, %s!</span>', 
                   esc_html($message), 
                   esc_html($atts['name']));
}

/**
 * Add custom CSS for the plugin
 */
add_action('wp_head', 'hello_isotone_styles');
add_action('admin_head', 'hello_isotone_styles');
function hello_isotone_styles() {
    ?>
    <style>
        .hello-isotone-greeting {
            color: var(--accent);
            font-weight: bold;
            animation: pulse 2s ease-in-out infinite;
        }
    </style>
    <?php
}

/**
 * Register plugin hooks for future use
 */
do_action('hello_isotone_loaded');
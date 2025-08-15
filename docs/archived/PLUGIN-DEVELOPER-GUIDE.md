# Isotone Plugin Developer Guide

## Introduction

Isotone's plugin system is designed to be 100% compatible with WordPress plugin development patterns. If you've developed WordPress plugins, you already know how to develop for Isotone.

## Quick Start

### Plugin Structure

```
iso-content/plugins/my-plugin/
├── my-plugin.php           # Main plugin file (required)
├── readme.txt              # Plugin readme
├── uninstall.php           # Cleanup on uninstall
├── includes/
│   ├── class-plugin.php   # Main plugin class
│   ├── class-admin.php    # Admin functionality
│   └── class-public.php   # Public functionality
├── admin/
│   ├── css/
│   ├── js/
│   └── views/
├── public/
│   ├── css/
│   ├── js/
│   └── templates/
├── languages/
└── assets/
    └── images/
```

### Main Plugin File

```php
<?php
/**
 * Plugin Name: My Awesome Plugin
 * Plugin URI: https://example.com/plugins/awesome
 * Description: This plugin does awesome things
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: my-awesome-plugin
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MY_PLUGIN_VERSION', '1.0.0');
define('MY_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MY_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include main plugin class
require_once MY_PLUGIN_PATH . 'includes/class-plugin.php';

// Initialize plugin
function my_plugin_init() {
    $plugin = new My_Awesome_Plugin();
    $plugin->run();
}
add_action('plugins_loaded', 'my_plugin_init');

// Activation hook
register_activation_hook(__FILE__, 'my_plugin_activate');
function my_plugin_activate() {
    // Create database tables
    // Set default options
    // Schedule cron events
    flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'my_plugin_deactivate');
function my_plugin_deactivate() {
    // Clear cron events
    // Cleanup temporary data
    flush_rewrite_rules();
}
```

## Plugin Class Structure

### Main Plugin Class

```php
class My_Awesome_Plugin {
    
    protected $version;
    protected $loader;
    
    public function __construct() {
        $this->version = MY_PLUGIN_VERSION;
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    private function load_dependencies() {
        require_once MY_PLUGIN_PATH . 'includes/class-loader.php';
        require_once MY_PLUGIN_PATH . 'includes/class-i18n.php';
        require_once MY_PLUGIN_PATH . 'admin/class-admin.php';
        require_once MY_PLUGIN_PATH . 'public/class-public.php';
        
        $this->loader = new My_Plugin_Loader();
    }
    
    private function set_locale() {
        $plugin_i18n = new My_Plugin_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }
    
    private function define_admin_hooks() {
        $plugin_admin = new My_Plugin_Admin($this->version);
        
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
    }
    
    private function define_public_hooks() {
        $plugin_public = new My_Plugin_Public($this->version);
        
        $this->loader->add_action('iso_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('iso_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_filter('the_content', $plugin_public, 'filter_content');
        $this->loader->add_shortcode('my_shortcode', $plugin_public, 'render_shortcode');
    }
    
    public function run() {
        $this->loader->run();
    }
}
```

## Admin Menu and Pages

### Adding Admin Menu

```php
class My_Plugin_Admin {
    
    public function add_admin_menu() {
        // Add top-level menu
        add_menu_page(
            __('My Plugin', 'textdomain'),           // Page title
            __('My Plugin', 'textdomain'),           // Menu title
            'manage_options',                         // Capability
            'my-plugin',                             // Menu slug
            [$this, 'display_admin_page'],          // Callback
            'dashicons-admin-generic',              // Icon
            30                                       // Position
        );
        
        // Add submenu
        add_submenu_page(
            'my-plugin',                             // Parent slug
            __('Settings', 'textdomain'),           // Page title
            __('Settings', 'textdomain'),           // Menu title
            'manage_options',                        // Capability
            'my-plugin-settings',                   // Menu slug
            [$this, 'display_settings_page']        // Callback
        );
        
        // Add to existing menu
        add_options_page(
            __('My Plugin Settings', 'textdomain'),
            __('My Plugin', 'textdomain'),
            'manage_options',
            'my-plugin-options',
            [$this, 'display_options_page']
        );
    }
    
    public function display_admin_page() {
        include MY_PLUGIN_PATH . 'admin/views/admin-page.php';
    }
}
```

### Settings API

```php
public function register_settings() {
    // Register setting
    register_setting(
        'my_plugin_settings',                    // Option group
        'my_plugin_options',                     // Option name
        [$this, 'sanitize_options']             // Sanitize callback
    );
    
    // Add settings section
    add_settings_section(
        'my_plugin_general',                     // ID
        __('General Settings', 'textdomain'),    // Title
        [$this, 'render_section'],              // Callback
        'my-plugin-settings'                    // Page
    );
    
    // Add settings field
    add_settings_field(
        'my_plugin_field',                       // ID
        __('My Field', 'textdomain'),           // Title
        [$this, 'render_field'],                // Callback
        'my-plugin-settings',                   // Page
        'my_plugin_general'                     // Section
    );
}

public function render_field() {
    $options = get_option('my_plugin_options');
    $value = $options['field'] ?? '';
    ?>
    <input type="text" 
           name="my_plugin_options[field]" 
           value="<?php echo esc_attr($value); ?>" 
           class="regular-text" />
    <?php
}

public function sanitize_options($input) {
    $sanitized = [];
    
    if (isset($input['field'])) {
        $sanitized['field'] = sanitize_text_field($input['field']);
    }
    
    return $sanitized;
}
```

## Database Operations

### Creating Tables

```php
function my_plugin_create_tables() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'my_plugin_data';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        user_id int(11) NOT NULL,
        data text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    add_option('my_plugin_db_version', '1.0.0');
}

register_activation_hook(__FILE__, 'my_plugin_create_tables');
```

### Database Queries

```php
// Insert
$wpdb->insert(
    $wpdb->prefix . 'my_plugin_data',
    [
        'user_id' => get_current_user_id(),
        'data' => 'Some data'
    ],
    ['%d', '%s']
);

// Select
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}my_plugin_data WHERE user_id = %d",
        get_current_user_id()
    )
);

// Update
$wpdb->update(
    $wpdb->prefix . 'my_plugin_data',
    ['data' => 'Updated data'],
    ['id' => $id],
    ['%s'],
    ['%d']
);

// Delete
$wpdb->delete(
    $wpdb->prefix . 'my_plugin_data',
    ['id' => $id],
    ['%d']
);
```

## Shortcodes

### Creating Shortcodes

```php
// Simple shortcode
add_shortcode('my_shortcode', 'my_shortcode_handler');
function my_shortcode_handler($atts) {
    $atts = shortcode_atts([
        'title' => 'Default Title',
        'color' => '#000000'
    ], $atts);
    
    return sprintf(
        '<div style="color: %s;"><h3>%s</h3></div>',
        esc_attr($atts['color']),
        esc_html($atts['title'])
    );
}

// Enclosing shortcode
add_shortcode('my_box', 'my_box_shortcode');
function my_box_shortcode($atts, $content = null) {
    $atts = shortcode_atts([
        'type' => 'info'
    ], $atts);
    
    return sprintf(
        '<div class="box box-%s">%s</div>',
        esc_attr($atts['type']),
        do_shortcode($content)
    );
}

// Usage: [my_box type="warning"]Content here[/my_box]
```

## AJAX Handlers

### Admin AJAX

```php
// JavaScript
jQuery(document).ready(function($) {
    $('#my-button').click(function() {
        $.post(ajaxurl, {
            action: 'my_plugin_action',
            nonce: my_plugin_ajax.nonce,
            data: 'some data'
        }, function(response) {
            if (response.success) {
                alert(response.data.message);
            }
        });
    });
});

// PHP Handler
add_action('iso_ajax_my_plugin_action', 'handle_my_plugin_action');
function handle_my_plugin_action() {
    check_ajax_referer('my_plugin_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        iso_die('Unauthorized');
    }
    
    $data = sanitize_text_field($_POST['data']);
    
    // Process data...
    
    iso_send_json_success([
        'message' => 'Success!',
        'data' => $data
    ]);
}
```

### Public AJAX

```php
// For non-logged in users
add_action('iso_ajax_nopriv_public_action', 'handle_public_action');
add_action('iso_ajax_public_action', 'handle_public_action');

function handle_public_action() {
    check_ajax_referer('public_nonce', 'nonce');
    
    // Process public request...
    
    iso_send_json_success(['message' => 'Public action completed']);
}
```

## REST API Endpoints

```php
add_action('rest_api_init', function() {
    register_rest_route('my-plugin/v1', '/data', [
        'methods' => 'GET',
        'callback' => 'my_plugin_get_data',
        'permission_callback' => '__return_true'
    ]);
    
    register_rest_route('my-plugin/v1', '/data', [
        'methods' => 'POST',
        'callback' => 'my_plugin_create_data',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        },
        'args' => [
            'title' => [
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field'
            ]
        ]
    ]);
});

function my_plugin_get_data($request) {
    $data = get_option('my_plugin_data', []);
    return new WP_REST_Response($data, 200);
}

function my_plugin_create_data($request) {
    $title = $request->get_param('title');
    
    // Save data...
    
    return new WP_REST_Response(['success' => true], 201);
}
```

## Custom Post Types

```php
add_action('init', 'register_my_post_type');
function register_my_post_type() {
    $labels = [
        'name' => __('Products', 'textdomain'),
        'singular_name' => __('Product', 'textdomain'),
        'add_new' => __('Add New', 'textdomain'),
        'add_new_item' => __('Add New Product', 'textdomain'),
        'edit_item' => __('Edit Product', 'textdomain'),
        'new_item' => __('New Product', 'textdomain'),
        'view_item' => __('View Product', 'textdomain'),
        'search_items' => __('Search Products', 'textdomain'),
        'not_found' => __('No products found', 'textdomain'),
    ];
    
    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'taxonomies' => ['category', 'post_tag'],
        'menu_icon' => 'dashicons-products',
        'rewrite' => ['slug' => 'products'],
        'show_in_rest' => true,
    ];
    
    register_post_type('product', $args);
}
```

## Meta Boxes

```php
// Add meta box
add_action('add_meta_boxes', 'my_plugin_add_meta_box');
function my_plugin_add_meta_box() {
    add_meta_box(
        'my_plugin_meta',                        // ID
        __('Product Details', 'textdomain'),     // Title
        'my_plugin_meta_box_callback',          // Callback
        'product',                               // Post type
        'normal',                                // Context
        'high'                                   // Priority
    );
}

// Render meta box
function my_plugin_meta_box_callback($post) {
    wp_nonce_field('my_plugin_meta_box', 'my_plugin_meta_box_nonce');
    
    $price = get_post_meta($post->ID, '_product_price', true);
    ?>
    <label for="product_price">
        <?php _e('Price', 'textdomain'); ?>
    </label>
    <input type="text" 
           id="product_price" 
           name="product_price" 
           value="<?php echo esc_attr($price); ?>" />
    <?php
}

// Save meta box data
add_action('save_post', 'my_plugin_save_meta_box');
function my_plugin_save_meta_box($post_id) {
    // Verify nonce
    if (!isset($_POST['my_plugin_meta_box_nonce']) || 
        !wp_verify_nonce($_POST['my_plugin_meta_box_nonce'], 'my_plugin_meta_box')) {
        return;
    }
    
    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save data
    if (isset($_POST['product_price'])) {
        update_post_meta(
            $post_id,
            '_product_price',
            sanitize_text_field($_POST['product_price'])
        );
    }
}
```

## Widgets

```php
class My_Plugin_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'my_plugin_widget',
            __('My Plugin Widget', 'textdomain'),
            ['description' => __('A widget for my plugin', 'textdomain')]
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        echo '<div class="my-widget-content">';
        echo esc_html($instance['text'] ?? '');
        echo '</div>';
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = $instance['title'] ?? '';
        $text = $instance['text'] ?? '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
                <?php _e('Title:', 'textdomain'); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($title); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('text'); ?>">
                <?php _e('Text:', 'textdomain'); ?>
            </label>
            <textarea class="widefat" 
                      id="<?php echo $this->get_field_id('text'); ?>" 
                      name="<?php echo $this->get_field_name('text'); ?>"><?php echo esc_textarea($text); ?></textarea>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['text'] = sanitize_textarea_field($new_instance['text']);
        return $instance;
    }
}

// Register widget
add_action('widgets_init', function() {
    register_widget('My_Plugin_Widget');
});
```

## Cron Jobs

```php
// Schedule event on activation
register_activation_hook(__FILE__, 'my_plugin_schedule_cron');
function my_plugin_schedule_cron() {
    if (!wp_next_scheduled('my_plugin_daily_event')) {
        wp_schedule_event(time(), 'daily', 'my_plugin_daily_event');
    }
}

// Clear scheduled event on deactivation
register_deactivation_hook(__FILE__, 'my_plugin_clear_cron');
function my_plugin_clear_cron() {
    wp_clear_scheduled_hook('my_plugin_daily_event');
}

// Hook function to scheduled event
add_action('my_plugin_daily_event', 'my_plugin_do_daily_task');
function my_plugin_do_daily_task() {
    // Perform daily task
    // Clean up old data
    // Send email reports
    // etc.
}

// Custom cron schedule
add_filter('cron_schedules', 'my_plugin_cron_schedules');
function my_plugin_cron_schedules($schedules) {
    $schedules['every_five_minutes'] = [
        'interval' => 300,
        'display' => __('Every Five Minutes', 'textdomain')
    ];
    return $schedules;
}
```

## Security Best Practices

### Data Validation and Sanitization

```php
// Sanitize input
$text = sanitize_text_field($_POST['text']);
$email = sanitize_email($_POST['email']);
$url = esc_url_raw($_POST['url']);
$html = wp_kses_post($_POST['html']);

// Escape output
echo esc_html($text);
echo esc_attr($attribute);
echo esc_url($url);
echo esc_js($javascript);
echo esc_textarea($textarea);

// Nonces
// Create nonce
iso_nonce_field('my_action', 'my_nonce');

// Verify nonce
if (!iso_verify_nonce($_POST['my_nonce'], 'my_action')) {
    die('Security check failed');
}

// Capabilities
if (!current_user_can('manage_options')) {
    iso_die(__('You do not have sufficient permissions to access this page.'));
}

// SQL queries
global $wpdb;
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->posts} WHERE post_author = %d AND post_status = %s",
        $user_id,
        'publish'
    )
);
```

## Internationalization

```php
// Load text domain
add_action('plugins_loaded', 'my_plugin_load_textdomain');
function my_plugin_load_textdomain() {
    load_plugin_textdomain(
        'my-plugin',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages/'
    );
}

// Translatable strings
__('Hello World', 'my-plugin');              // Return translation
_e('Hello World', 'my-plugin');              // Echo translation
_n('One item', '%s items', $count, 'my-plugin'); // Plural forms
_x('Post', 'noun', 'my-plugin');             // Context
esc_html__('Hello World', 'my-plugin');      // Escaped
esc_attr__('Hello World', 'my-plugin');      // Escaped attribute

// JavaScript translations
iso_localize_script('my-script', 'my_plugin_i18n', [
    'hello' => __('Hello', 'my-plugin'),
    'world' => __('World', 'my-plugin')
]);
```

## Plugin Updates

```php
// Check for updates
add_filter('pre_set_site_transient_update_plugins', 'check_for_plugin_update');
function check_for_plugin_update($transient) {
    if (empty($transient->checked)) {
        return $transient;
    }
    
    // Check your server for updates
    $remote_version = wp_remote_get('https://example.com/api/plugin-version');
    
    if (!is_wp_error($remote_version)) {
        $version_data = json_decode(wp_remote_retrieve_body($remote_version), true);
        
        if (version_compare(MY_PLUGIN_VERSION, $version_data['version'], '<')) {
            $transient->response[MY_PLUGIN_BASENAME] = (object) [
                'slug' => 'my-plugin',
                'new_version' => $version_data['version'],
                'url' => $version_data['url'],
                'package' => $version_data['download_url']
            ];
        }
    }
    
    return $transient;
}
```

## Testing

### Unit Tests

```php
class My_Plugin_Test extends WP_UnitTestCase {
    
    public function setUp() {
        parent::setUp();
        // Initialize plugin
    }
    
    public function test_plugin_activation() {
        activate_plugin('my-plugin/my-plugin.php');
        $this->assertTrue(is_plugin_active('my-plugin/my-plugin.php'));
    }
    
    public function test_shortcode_output() {
        $output = do_shortcode('[my_shortcode title="Test"]');
        $this->assertContains('Test', $output);
    }
    
    public function test_ajax_handler() {
        $_POST['action'] = 'my_plugin_action';
        $_POST['nonce'] = wp_create_nonce('my_plugin_nonce');
        
        $this->_handleAjax('my_plugin_action');
        
        $response = json_decode($this->_last_response, true);
        $this->assertTrue($response['success']);
    }
}
```

## Debugging

```php
// Enable debug mode
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Log to debug.log
error_log('Debug message');
error_log(print_r($array, true));

// Conditional debugging
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Debug mode is on');
}

// Custom debug function
function my_plugin_debug($data, $label = '') {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log($label . ': ' . print_r($data, true));
    }
}
```

## Performance Optimization

```php
// Use transients for caching
$data = get_transient('my_plugin_data');
if (false === $data) {
    $data = expensive_operation();
    set_transient('my_plugin_data', $data, HOUR_IN_SECONDS);
}

// Batch database operations
global $wpdb;
$wpdb->query('START TRANSACTION');
// Multiple operations
$wpdb->query('COMMIT');

// Defer script loading
iso_enqueue_script('my-script', 'url', [], '1.0', true);

// Conditional asset loading
add_action('iso_enqueue_scripts', function() {
    if (is_page('contact')) {
        iso_enqueue_script('contact-form');
    }
});
```

## Plugin Submission Checklist

Before submitting your plugin:

1. ✅ Unique and descriptive plugin name
2. ✅ Valid plugin header
3. ✅ No PHP errors or warnings
4. ✅ Properly prefixed functions and classes
5. ✅ Escaped output
6. ✅ Sanitized input
7. ✅ Nonces for form submissions
8. ✅ Capability checks
9. ✅ Internationalization ready
10. ✅ Uninstall routine implemented
11. ✅ No hardcoded paths or URLs
12. ✅ GPL-compatible license
13. ✅ Documentation included
14. ✅ Tested on latest Isotone version

## Resources

- [Isotone Hooks Reference](../HOOKS.md)
- [WordPress Plugin Developer Handbook](https://developer.wordpress.org/plugins/)
- [Plugin API Reference](https://codex.wordpress.org/Plugin_API)
- [Isotone Community Forum](https://isotone.tech/forum)

---

Happy plugin development! 🚀
# Isotone Hooks System Documentation

## Overview

Isotone implements a WordPress-inspired hooks system with its own unique naming conventions. While the system works identically to WordPress hooks, we use the `iso_` prefix instead of `iso_` to establish Isotone's unique identity. This document tracks the implementation status of all hooks and serves as the authoritative reference for both LLMs and developers.

## 🎯 Naming Convention

**CRITICAL**: Isotone uses `iso_` prefix where WordPress uses `wp_`:
- ✅ `iso_head` (not `wp_head`)
- ✅ `iso_footer` (not `wp_footer`)
- ✅ `iso_loaded` (not `wp_loaded`)
- ✅ `iso_enqueue_scripts` (not `wp_enqueue_scripts`)

Other hooks remain the same (e.g., `init`, `the_content`, `save_post`) unless they have `wp_` prefix in WordPress.

## Quick Start

```php
// Action hook - performs an action
do_action('init');
add_action('init', 'my_init_function', 10, 1);

// Filter hook - modifies data
$content = apply_filters('the_content', $content);
add_filter('the_content', 'my_content_filter', 10, 1);
```

## Implementation Status

### Legend
- ✅ Fully implemented and tested
- 🚧 Partially implemented
- 📅 Planned for implementation
- ❌ Not yet started
- 🔄 In progress

## Core System Hooks

### Initialization Hooks

| Hook Name | Type | WordPress Equivalent | Status | Description | Priority |
|-----------|------|---------------------|---------|-------------|----------|
| `init` | Action | `init` | ❌ | Fires after Isotone has finished loading | ✅ |
| `after_setup_theme` | Action | `after_setup_theme` | ❌ | Fires after the theme is loaded | ✅ |
| `iso_loaded` | Action | `iso_loaded` | ❌ | Fires once Isotone, all plugins, and the theme are fully loaded | ✅ |
| `plugins_loaded` | Action | `plugins_loaded` | ❌ | Fires once activated plugins have loaded | HIGH |
| `setup_theme` | Action | `setup_theme` | ❌ | Fires before the theme is loaded | MEDIUM |
| `sanitize_comment_cookies` | Action | `sanitize_comment_cookies` | ❌ | Fires before comment cookies are sanitized | LOW |
| `muplugins_loaded` | Action | `muplugins_loaded` | ❌ | Fires once must-use plugins have loaded | LOW |

### Head and Footer Hooks

| Hook Name | Type | WordPress Equivalent | Status | Description | Priority |
|-----------|------|---------------------|---------|-------------|----------|
| `iso_head` | Action | `iso_head` | ❌ | Prints scripts or data in the head tag | ✅ |
| `iso_footer` | Action | `iso_footer` | ❌ | Prints scripts or data before closing body tag | ✅ |
| `iso_body_open` | Action | `iso_body_open` | ❌ | Fires after opening body tag | ✅ |
| `iso_print_styles` | Action | `iso_print_styles` | ❌ | Fires before styles are printed | MEDIUM |
| `iso_print_scripts` | Action | `iso_print_scripts` | ❌ | Fires before scripts are printed | MEDIUM |
| `iso_enqueue_scripts` | Action | `iso_enqueue_scripts` | ❌ | Fires when scripts and styles are enqueued | ✅ |
| `admin_enqueue_scripts` | Action | `admin_enqueue_scripts` | ❌ | Fires when admin scripts and styles are enqueued | HIGH |

### Content Hooks

| Hook Name | Type | WordPress Equivalent | Status | Description | Priority |
|-----------|------|---------------------|---------|-------------|----------|
| `the_content` | Filter | `the_content` | ❌ | Filters the post content | HIGH |
| `the_title` | Filter | `the_title` | ❌ | Filters the post title | HIGH |
| `the_excerpt` | Filter | `the_excerpt` | ❌ | Filters the post excerpt | HIGH |
| `the_tags` | Filter | `the_tags` | ❌ | Filters the tags list | MEDIUM |
| `the_category` | Filter | `the_category` | ❌ | Filters the category list | MEDIUM |
| `the_date` | Filter | `the_date` | ❌ | Filters the post date | LOW |
| `the_author` | Filter | `the_author` | ❌ | Filters the author name | LOW |
| `get_the_excerpt` | Filter | `get_the_excerpt` | ❌ | Filters the retrieved post excerpt | MEDIUM |
| `single_post_title` | Filter | `single_post_title` | ❌ | Filters the single post title | LOW |

### Database Hooks

| Hook Name | Type | WordPress Equivalent | Status | Description | Priority |
|-----------|------|---------------------|---------|-------------|----------|
| `pre_get_posts` | Action | `pre_get_posts` | ❌ | Fires before posts are retrieved | HIGH |
| `save_post` | Action | `save_post` | ❌ | Fires after a post is saved | HIGH |
| `delete_post` | Action | `delete_post` | ❌ | Fires before a post is deleted | HIGH |
| `iso_insert_post` | Action | `iso_insert_post` | ❌ | Fires after a post is inserted | HIGH |
| `edit_post` | Action | `edit_post` | ❌ | Fires after a post is updated | MEDIUM |
| `publish_post` | Action | `publish_post` | ❌ | Fires when a post is published | MEDIUM |
| `transition_post_status` | Action | `transition_post_status` | ❌ | Fires when post status changes | MEDIUM |

### User and Authentication Hooks

| Hook Name | Type | WordPress Equivalent | Status | Description | Priority |
|-----------|------|---------------------|---------|-------------|----------|
| `iso_login` | Action | `iso_login` | ❌ | Fires after user logs in | HIGH |
| `iso_logout` | Action | `iso_logout` | ❌ | Fires after user logs out | HIGH |
| `user_register` | Action | `user_register` | ❌ | Fires after user registration | HIGH |
| `profile_update` | Action | `profile_update` | ❌ | Fires after user profile update | MEDIUM |
| `delete_user` | Action | `delete_user` | ❌ | Fires before user deletion | MEDIUM |
| `authenticate` | Filter | `authenticate` | ❌ | Filters user authentication | HIGH |
| `login_redirect` | Filter | `login_redirect` | ❌ | Filters login redirect URL | MEDIUM |

### Admin Hooks

| Hook Name | Type | WordPress Equivalent | Status | Description | Priority |
|-----------|------|---------------------|---------|-------------|----------|
| `admin_menu` | Action | `admin_menu` | ❌ | Fires before admin menu rendering | HIGH |
| `admin_init` | Action | `admin_init` | ❌ | Fires as admin screen initializes | HIGH |
| `admin_head` | Action | `admin_head` | ❌ | Fires in admin page header | MEDIUM |
| `admin_footer` | Action | `admin_footer` | ❌ | Fires in admin page footer | MEDIUM |
| `admin_bar_menu` | Action | `admin_bar_menu` | ❌ | Add items to admin bar | LOW |
| `current_screen` | Action | `current_screen` | ❌ | Fires after current screen is set | MEDIUM |
| `admin_notices` | Action | `admin_notices` | ❌ | Displays admin notices | HIGH |

### Theme Hooks

| Hook Name | Type | WordPress Equivalent | Status | Description | Priority |
|-----------|------|---------------------|---------|-------------|----------|
| `switch_theme` | Action | `switch_theme` | ❌ | Fires when theme is switched | HIGH |
| `activate_theme` | Action | `activated_theme` | ❌ | Fires after theme activation | HIGH |
| `theme_loaded` | Action | N/A | ❌ | Fires after theme functions.php loads | HIGH |
| `customize_register` | Action | `customize_register` | ❌ | Fires when customizer is initialized | MEDIUM |
| `widgets_init` | Action | `widgets_init` | ❌ | Fires when widgets are initialized | MEDIUM |
| `customize_preview_init` | Action | `customize_preview_init` | ❌ | Fires when customizer preview loads | LOW |

### Plugin Hooks

| Hook Name | Type | WordPress Equivalent | Status | Description | Priority |
|-----------|------|---------------------|---------|-------------|----------|
| `activate_plugin` | Action | `activate_plugin` | ❌ | Fires when plugin is activated | HIGH |
| `deactivate_plugin` | Action | `deactivate_plugin` | ❌ | Fires when plugin is deactivated | HIGH |
| `uninstall_plugin` | Action | N/A | ❌ | Fires when plugin is uninstalled | MEDIUM |
| `upgrader_process_complete` | Action | `upgrader_process_complete` | ❌ | Fires when plugin update completes | LOW |

### AJAX Hooks

| Hook Name | Type | WordPress Equivalent | Status | Description | Priority |
|-----------|------|---------------------|---------|-------------|----------|
| `iso_ajax_{action}` | Action | `iso_ajax_{action}` | ❌ | Handles authenticated AJAX requests | HIGH |
| `iso_ajax_nopriv_{action}` | Action | `iso_ajax_nopriv_{action}` | ❌ | Handles non-authenticated AJAX requests | HIGH |
| `admin_ajax` | Action | N/A | ❌ | General AJAX handler | MEDIUM |

### REST API Hooks

| Hook Name | Type | WordPress Equivalent | Status | Description | Priority |
|-----------|------|---------------------|---------|-------------|----------|
| `rest_api_init` | Action | `rest_api_init` | ❌ | Fires when REST API is initialized | HIGH |
| `rest_pre_dispatch` | Filter | `rest_pre_dispatch` | ❌ | Filters REST response before dispatch | MEDIUM |
| `rest_post_dispatch` | Filter | `rest_post_dispatch` | ❌ | Filters REST response after dispatch | MEDIUM |
| `rest_authentication_errors` | Filter | `rest_authentication_errors` | ❌ | Filters REST authentication | HIGH |

### Media Hooks

| Hook Name | Type | WordPress Equivalent | Status | Description | Priority |
|-----------|------|---------------------|---------|-------------|----------|
| `add_attachment` | Action | `add_attachment` | ❌ | Fires after attachment is added | MEDIUM |
| `delete_attachment` | Action | `delete_attachment` | ❌ | Fires before attachment is deleted | MEDIUM |
| `iso_handle_upload` | Filter | `iso_handle_upload` | ❌ | Filters file upload handling | HIGH |
| `upload_mimes` | Filter | `upload_mimes` | ❌ | Filters allowed mime types | MEDIUM |

### Comment Hooks

| Hook Name | Type | WordPress Equivalent | Status | Description | Priority |
|-----------|------|---------------------|---------|-------------|----------|
| `comment_post` | Action | `comment_post` | ❌ | Fires after comment is posted | MEDIUM |
| `edit_comment` | Action | `edit_comment` | ❌ | Fires after comment is edited | LOW |
| `delete_comment` | Action | `delete_comment` | ❌ | Fires before comment is deleted | LOW |
| `pre_comment_approved` | Filter | `pre_comment_approved` | ❌ | Filters comment approval status | MEDIUM |
| `comment_text` | Filter | `comment_text` | ❌ | Filters comment text | MEDIUM |

### Rewrite and Routing Hooks

| Hook Name | Type | WordPress Equivalent | Status | Description | Priority |
|-----------|------|---------------------|---------|-------------|----------|
| `rewrite_rules_array` | Filter | `rewrite_rules_array` | ❌ | Filters rewrite rules | MEDIUM |
| `template_redirect` | Action | `template_redirect` | ❌ | Fires before template loads | ✅ |
| `template_include` | Filter | `template_include` | ❌ | Filters template file path | HIGH |
| `request` | Filter | `request` | ❌ | Filters request query variables | MEDIUM |

### Cron Hooks

| Hook Name | Type | WordPress Equivalent | Status | Description | Priority |
|-----------|------|---------------------|---------|-------------|----------|
| `iso_cron` | Action | Custom events | ❌ | Scheduled cron events | MEDIUM |
| `cron_schedules` | Filter | `cron_schedules` | ❌ | Filters cron schedules | LOW |

## Hook Implementation Classes

### Core Classes Required

```php
namespace Isotone\Core;

class Hook {
    private static $actions = [];
    private static $filters = [];
    
    public static function addAction($tag, $callback, $priority = 10, $accepted_args = 1) {}
    public static function addFilter($tag, $callback, $priority = 10, $accepted_args = 1) {}
    public static function doAction($tag, ...$args) {}
    public static function applyFilters($tag, $value, ...$args) {}
    public static function removeAction($tag, $callback, $priority = 10) {}
    public static function removeFilter($tag, $callback, $priority = 10) {}
    public static function hasAction($tag, $callback = false) {}
    public static function hasFilter($tag, $callback = false) {}
    public static function currentAction() {}
    public static function currentFilter() {}
    public static function doingAction($action = null) {}
    public static function doingFilter($filter = null) {}
    public static function didAction($tag) {}
    public static function didFilter($tag) {}
}
```

## Global Functions (WordPress Compatibility)

```php
// Action functions
function add_action($tag, $callback, $priority = 10, $accepted_args = 1) {
    return Hook::addAction($tag, $callback, $priority, $accepted_args);
}

function do_action($tag, ...$args) {
    return Hook::doAction($tag, ...$args);
}

function remove_action($tag, $callback, $priority = 10) {
    return Hook::removeAction($tag, $callback, $priority);
}

function has_action($tag, $callback = false) {
    return Hook::hasAction($tag, $callback);
}

// Filter functions
function add_filter($tag, $callback, $priority = 10, $accepted_args = 1) {
    return Hook::addFilter($tag, $callback, $priority, $accepted_args);
}

function apply_filters($tag, $value, ...$args) {
    return Hook::applyFilters($tag, $value, ...$args);
}

function remove_filter($tag, $callback, $priority = 10) {
    return Hook::removeFilter($tag, $callback, $priority);
}

function has_filter($tag, $callback = false) {
    return Hook::hasFilter($tag, $callback);
}
```

## Usage Examples

### Theme Developer Example

```php
// In theme's functions.php
namespace MyTheme;

// Add custom styles
add_action('iso_head', function() {
    echo '<link rel="stylesheet" href="/path/to/custom.css">';
}, 20);

// Modify content
add_filter('the_content', function($content) {
    return $content . '<p>Added by theme</p>';
}, 10);

// Add admin menu item
add_action('admin_menu', function() {
    add_menu_page('Theme Options', 'Theme Options', 'manage_options', 'theme-options', 'theme_options_page');
});

// Enqueue scripts
add_action('iso_enqueue_scripts', function() {
    iso_enqueue_script('theme-script', get_template_directory_uri() . '/js/script.js', ['jquery'], '1.0.0', true);
});
```

### Plugin Developer Example

```php
// In plugin file
namespace MyPlugin;

// Initialize plugin
add_action('init', function() {
    // Register custom post type
    register_post_type('product', [
        'public' => true,
        'label' => 'Products'
    ]);
});

// Save post hook
add_action('save_post', function($post_id) {
    // Custom save logic
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    // Update meta
    update_post_meta($post_id, '_custom_field', $_POST['custom_field']);
}, 10, 1);

// Add settings page
add_action('admin_menu', function() {
    add_options_page('My Plugin', 'My Plugin', 'manage_options', 'my-plugin', 'render_settings');
});

// AJAX handler
add_action('iso_ajax_my_action', function() {
    // Handle AJAX request
    iso_send_json_success(['message' => 'Success']);
});
```

## Hook Firing Order

### Page Load Sequence

1. `muplugins_loaded`
2. `plugins_loaded`
3. `sanitize_comment_cookies`
4. `setup_theme`
5. `after_setup_theme`
6. `init`
7. `iso_loaded`
8. `parse_request`
9. `send_headers`
10. `parse_query`
11. `pre_get_posts`
12. `wp`
13. `template_redirect`
14. `get_header`
15. `iso_head`
16. `iso_enqueue_scripts`
17. `iso_print_styles`
18. `iso_print_scripts`
19. `get_sidebar`
20. `dynamic_sidebar`
21. `get_footer`
22. `iso_footer`
23. `iso_print_footer_scripts`
24. `shutdown`

### Admin Page Load Sequence

1. `muplugins_loaded`
2. `plugins_loaded`
3. `setup_theme`
4. `after_setup_theme`
5. `init`
6. `iso_loaded`
7. `auth_redirect`
8. `admin_init`
9. `admin_menu`
10. `current_screen`
11. `admin_enqueue_scripts`
12. `admin_head`
13. `in_admin_header`
14. `admin_notices`
15. `in_admin_footer`
16. `admin_footer`
17. `admin_print_footer_scripts`
18. `shutdown`

## Testing Hooks

### Unit Test Example

```php
class HookTest extends TestCase {
    public function testActionFires() {
        $called = false;
        
        add_action('test_action', function() use (&$called) {
            $called = true;
        });
        
        do_action('test_action');
        
        $this->assertTrue($called);
    }
    
    public function testFilterModifiesValue() {
        add_filter('test_filter', function($value) {
            return $value . ' modified';
        });
        
        $result = apply_filters('test_filter', 'original');
        
        $this->assertEquals('original modified', $result);
    }
}
```

## Automated Documentation

### Hook Scanner

The system automatically scans for hook usage and updates documentation:

```php
// Scans codebase for do_action() and apply_filters() calls
php isotone hooks:scan

// Generates hook documentation
php isotone hooks:docs

// Validates hook implementation
php isotone hooks:validate
```

## Implementation Checklist

### Phase 1: Core System (Priority: CRITICAL)
- [ ] Create Hook class with basic functionality
- [ ] Implement global helper functions
- [ ] Add init hook
- [ ] Add iso_head and iso_footer hooks
- [ ] Create unit tests for hook system

### Phase 2: Content Hooks (Priority: HIGH)
- [ ] Implement the_content filter
- [ ] Implement the_title filter
- [ ] Implement the_excerpt filter
- [ ] Add save_post action
- [ ] Add pre_get_posts action

### Phase 3: Admin Hooks (Priority: HIGH)
- [ ] Implement admin_menu action
- [ ] Implement admin_init action
- [ ] Add admin_notices action
- [ ] Add admin_enqueue_scripts action

### Phase 4: Theme Support (Priority: MEDIUM)
- [ ] Implement after_setup_theme action
- [ ] Add widgets_init action
- [ ] Add switch_theme action
- [ ] Implement customize_register action

### Phase 5: User/Auth Hooks (Priority: MEDIUM)
- [ ] Implement iso_login action
- [ ] Implement iso_logout action
- [ ] Add user_register action
- [ ] Add authenticate filter

### Phase 6: Advanced Features (Priority: LOW)
- [ ] AJAX hook system
- [ ] REST API hooks
- [ ] Cron hook system
- [ ] Media upload hooks

## Contributing

When implementing new hooks:

1. Update this document with implementation status
2. Follow WordPress naming conventions exactly
3. Add unit tests for each hook
4. Update automated documentation
5. Add usage examples

## References

- [WordPress Plugin API](https://codex.wordpress.org/Plugin_API)
- [WordPress Action Reference](https://codex.wordpress.org/Plugin_API/Action_Reference)
- [WordPress Filter Reference](https://codex.wordpress.org/Plugin_API/Filter_Reference)

---



# Isotone Hook Naming Conventions

## Overview

Isotone uses a WordPress-compatible hooks system with its own unique naming conventions. This document defines the official naming rules for all hooks in Isotone.

## 🎯 Core Principle

**Isotone establishes its unique identity while maintaining WordPress familiarity.**

## Naming Rules

### 1. WordPress-Equivalent Hooks

When Isotone implements functionality equivalent to WordPress hooks, use the `iso_` prefix instead of `wp_`:

| WordPress Hook | Isotone Hook | Purpose |
|---------------|--------------|---------|
| `wp_head` | `iso_head` | Add elements to `<head>` |
| `wp_footer` | `iso_footer` | Add elements before `</body>` |
| `wp_body_open` | `iso_body_open` | Add elements after `<body>` |
| `wp_loaded` | `iso_loaded` | Fires when Isotone is fully loaded |
| `wp_enqueue_scripts` | `iso_enqueue_scripts` | Enqueue scripts and styles |
| `wp_ajax_{action}` | `iso_ajax_{action}` | Handle AJAX requests |
| `wp_ajax_nopriv_{action}` | `iso_ajax_nopriv_{action}` | Handle unauthenticated AJAX |
| `wp_login` | `iso_login` | User login event |
| `wp_logout` | `iso_logout` | User logout event |
| `wp_die` | `iso_die` | Terminate execution |
| `wp_mail` | `iso_mail` | Email sending |
| `wp_redirect` | `iso_redirect` | URL redirection |
| `wp_handle_upload` | `iso_handle_upload` | File upload handling |
| `wp_insert_post` | `iso_insert_post` | Post insertion |
| `wp_update_post` | `iso_update_post` | Post update |
| `wp_delete_post` | `iso_delete_post` | Post deletion |
| `wp_insert_user` | `iso_insert_user` | User creation |
| `wp_update_user` | `iso_update_user` | User update |
| `wp_delete_user` | `iso_delete_user` | User deletion |

### 2. Generic WordPress Hooks

Hooks that don't have the `wp_` prefix in WordPress remain the same in Isotone:

| Hook Name | Usage | Notes |
|-----------|-------|-------|
| `init` | System initialization | Same as WordPress |
| `admin_init` | Admin initialization | Same as WordPress |
| `admin_menu` | Admin menu setup | Same as WordPress |
| `admin_notices` | Admin notifications | Same as WordPress |
| `the_content` | Filter post content | Same as WordPress |
| `the_title` | Filter post title | Same as WordPress |
| `save_post` | Post save action | Same as WordPress |
| `delete_post` | Post deletion action | Same as WordPress |
| `user_register` | User registration | Same as WordPress |
| `template_redirect` | Template redirection | Same as WordPress |
| `after_setup_theme` | Theme setup | Same as WordPress |
| `widgets_init` | Widget initialization | Same as WordPress |
| `rest_api_init` | REST API setup | Same as WordPress |

### 3. Isotone-Specific Hooks

New hooks unique to Isotone should follow these patterns:

- **System hooks**: `isotone_{event}` (e.g., `isotone_core_loaded`)
- **Module hooks**: `isotone_{module}_{event}` (e.g., `isotone_plugin_activated`)
- **Admin hooks**: `isotone_admin_{event}` (e.g., `isotone_admin_dashboard_widgets`)
- **API hooks**: `isotone_api_{event}` (e.g., `isotone_api_request`)

## Function Naming

### Helper Functions

Functions that directly correspond to WordPress functions use the `iso_` prefix:

| WordPress Function | Isotone Function |
|-------------------|------------------|
| `wp_enqueue_script()` | `iso_enqueue_script()` |
| `wp_enqueue_style()` | `iso_enqueue_style()` |
| `wp_localize_script()` | `iso_localize_script()` |
| `wp_create_nonce()` | `iso_create_nonce()` |
| `wp_verify_nonce()` | `iso_verify_nonce()` |
| `wp_nonce_field()` | `iso_nonce_field()` |
| `wp_send_json()` | `iso_send_json()` |
| `wp_send_json_success()` | `iso_send_json_success()` |
| `wp_send_json_error()` | `iso_send_json_error()` |
| `wp_die()` | `iso_die()` |
| `wp_redirect()` | `iso_redirect()` |
| `wp_safe_redirect()` | `iso_safe_redirect()` |
| `wp_script_is()` | `iso_script_is()` |
| `wp_style_is()` | `iso_style_is()` |

### Generic Functions

Functions without `wp_` prefix remain the same:

- `add_action()`
- `do_action()`
- `add_filter()`
- `apply_filters()`
- `remove_action()`
- `remove_filter()`
- `has_action()`
- `has_filter()`
- `current_action()`
- `current_filter()`
- `doing_action()`
- `doing_filter()`
- `did_action()`
- `did_filter()`

## Implementation Guidelines

### For Developers

1. **Always use `iso_` prefix** when implementing WordPress-equivalent functionality
2. **Keep generic hook names** for non-prefixed WordPress hooks
3. **Document new hooks** in `/HOOKS.md`
4. **Maintain backward compatibility** where possible

### For Theme Developers

```php
// Correct - Using Isotone conventions
add_action('iso_head', 'my_head_scripts');
add_action('iso_footer', 'my_footer_scripts');
add_action('iso_enqueue_scripts', 'my_theme_scripts');

// Also correct - Generic hooks
add_action('init', 'my_init_function');
add_action('admin_menu', 'my_admin_menu');
```

### For Plugin Developers

```php
// AJAX handlers
add_action('iso_ajax_my_action', 'handle_my_ajax');
add_action('iso_ajax_nopriv_my_action', 'handle_public_ajax');

// Enqueue scripts
add_action('iso_enqueue_scripts', function() {
    iso_enqueue_script('my-script', plugin_dir_url(__FILE__) . 'script.js');
});
```

### Backward Compatibility

To support existing WordPress code, Isotone may provide compatibility aliases:

```php
// Isotone provides backward compatibility
if (!function_exists('wp_head')) {
    function wp_head() {
        iso_head(); // Calls the Isotone equivalent
    }
}
```

## Migration Guide

### Converting WordPress Code to Isotone

1. **Replace `wp_` prefixed hooks with `iso_`**:
   ```php
   // WordPress
   add_action('wp_head', 'my_function');
   
   // Isotone
   add_action('iso_head', 'my_function');
   ```

2. **Replace `wp_` prefixed functions with `iso_`**:
   ```php
   // WordPress
   wp_enqueue_script('my-script', $url);
   
   // Isotone
   iso_enqueue_script('my-script', $url);
   ```

3. **Keep generic hooks unchanged**:
   ```php
   // Both WordPress and Isotone
   add_action('init', 'my_function');
   add_filter('the_content', 'my_filter');
   ```

## Rationale

This naming convention:

1. **Establishes Isotone's unique identity** - The `iso_` prefix clearly identifies Isotone-specific functionality
2. **Maintains familiarity** - WordPress developers can easily understand and adapt
3. **Avoids conflicts** - Clear separation from WordPress prevents naming collisions
4. **Supports migration** - Easy to convert WordPress code to Isotone
5. **Enables compatibility** - Allows for backward compatibility layers

## Enforcement

### Automated Checks

The CLI tool validates hook naming:

```bash
php isotone hooks:validate
```

### Code Review

All new hooks must:
1. Follow these naming conventions
2. Be documented in `/HOOKS.md`
3. Include PHPDoc comments
4. Have unit tests

## Updates

This document is authoritative. Any changes to hook naming conventions must:
1. Be discussed with the core team
2. Update this document
3. Update all affected code
4. Maintain backward compatibility

---

**Last Updated**: 2024-12-14  
**Version**: 1.0.0  
**Status**: Official
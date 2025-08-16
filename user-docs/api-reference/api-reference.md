# Isotone Hooks API Reference

> Auto-generated: 2025-08-16 16:43:55

## Overview

This document lists all hooks that are actually implemented in the Isotone codebase.

## Actions

### `test_action`

**Usage:**
```php
add_action('test_action', 'your_callback_function', 10, 1);
```

**Fired in:**
- `app/Commands/HooksCommand.php:283`

### `priority_test`

**Usage:**
```php
add_action('priority_test', 'your_callback_function', 10, 1);
```

**Fired in:**
- `app/Commands/HooksCommand.php:319`

### `remove_test`

**Usage:**
```php
add_action('remove_test', 'your_callback_function', 10, 1);
```

**Fired in:**
- `app/Commands/HooksCommand.php:335`

### `current_test`

**Usage:**
```php
add_action('current_test', 'your_callback_function', 10, 1);
```

**Fired in:**
- `app/Commands/HooksCommand.php:349`

### `after_setup_theme`

Fires after the theme is loaded

**Since:** 1.0.0

**Usage:**
```php
add_action('after_setup_theme', 'your_callback_function', 10, 1);
```

**Fired in:**
- `app/Core/Application.php:150`
- `iso-admin/hooks-explorer.php:52`

### `init`

Fires after Isotone has finished loading but before any headers are sent

**Since:** 1.0.0

**Usage:**
```php
add_action('init', 'your_callback_function', 10, 1);
```

**Fired in:**
- `app/Core/Application.php:151`
- `iso-admin/hooks-explorer.php:53`

### `iso_loaded`

Fires when Isotone core has been fully loaded

**Since:** 1.0.0

**Usage:**
```php
add_action('iso_loaded', 'your_callback_function', 10, 1);
```

**Fired in:**
- `app/Core/Application.php:152`

### `template_redirect`

Fires before determining which template to load

**Since:** 1.0.0

**Usage:**
```php
add_action('template_redirect', 'your_callback_function', 10, 1);
```

**Fired in:**
- `app/Core/Application.php:179`

### `shutdown`

Fires just before PHP shuts down execution

**Since:** 1.0.0

**Usage:**
```php
add_action('shutdown', 'your_callback_function', 10, 1);
```

**Fired in:**
- `app/Core/Application.php:194`

### `iso_head`

Fires in the <head> section of the site

**Since:** 1.0.0

**Usage:**
```php
add_action('iso_head', 'your_callback_function', 10, 1);
```

**Fired in:**
- `app/hooks.php:356`

### `iso_footer`

Fires before the closing </body> tag

**Since:** 1.0.0

**Usage:**
```php
add_action('iso_footer', 'your_callback_function', 10, 1);
```

**Fired in:**
- `app/hooks.php:369`

### `iso_body_open`

Fires immediately after the opening <body> tag

**Since:** 1.0.0

**Usage:**
```php
add_action('iso_body_open', 'your_callback_function', 10, 1);
```

**Fired in:**
- `app/hooks.php:382`
- `iso-content/themes/neutron/compat.php:24`

### `iso_enqueue_scripts`

Fires when scripts and styles should be enqueued

**Since:** 1.0.0

**Usage:**
```php
add_action('iso_enqueue_scripts', 'your_callback_function', 10, 1);
```

**Fired in:**
- `app/hooks.php:395`
- `iso-admin/hooks-explorer.php:55`

### `widgets_init`

Fires after all default widgets have been registered

**Since:** 1.0.0

**Usage:**
```php
add_action('widgets_init', 'your_callback_function', 10, 1);
```

**Fired in:**
- `iso-admin/hooks-explorer.php:54`

### `isotone_head`

**Usage:**
```php
add_action('isotone_head', 'your_callback_function', 10, 1);
```

**Fired in:**
- `iso-content/themes/isotone-default/index.php:20`

### `isotone_before_content`

**Usage:**
```php
add_action('isotone_before_content', 'your_callback_function', 10, 1);
```

**Fired in:**
- `iso-content/themes/isotone-default/index.php:39`

### `isotone_after_content`

**Usage:**
```php
add_action('isotone_after_content', 'your_callback_function', 10, 1);
```

**Fired in:**
- `iso-content/themes/isotone-default/index.php:57`

### `isotone_footer`

**Usage:**
```php
add_action('isotone_footer', 'your_callback_function', 10, 1);
```

**Fired in:**
- `iso-content/themes/isotone-default/index.php:69`

### `hello_isotone_loaded`

**Usage:**
```php
add_action('hello_isotone_loaded', 'your_callback_function', 10, 1);
```

**Fired in:**
- `iso-content/plugins/hello-isotone/hello-isotone.php:139`

## Filters

### `test_filter`

**Usage:**
```php
add_filter('test_filter', 'your_filter_function', 10, 1);
```

**Applied in:**
- `app/Commands/HooksCommand.php:295`

### `iso_enqueue_style`

Filters style enqueue data before adding

**Since:** 1.0.0

**Usage:**
```php
add_filter('iso_enqueue_style', 'your_filter_function', 10, 1);
```

**Applied in:**
- `app/hooks.php:413`

### `iso_enqueue_script`

Filters script enqueue data before adding

**Since:** 1.0.0

**Usage:**
```php
add_filter('iso_enqueue_script', 'your_filter_function', 10, 1);
```

**Applied in:**
- `app/hooks.php:444`


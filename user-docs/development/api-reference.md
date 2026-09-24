# Isotone Hooks API Reference

> Auto-generated: 2025-08-26 04:48:43

## Overview

This document lists all hooks that are actually implemented in the Isotone codebase.

## Actions

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

### `iso_body_open`

Fires immediately after the opening <body> tag

**Since:** 1.0.0

**Usage:**
```php
add_action('iso_body_open', 'your_callback_function', 10, 1);
```

**Fired in:**
- `iso-content/themes/neutron/compat.php:24`

### `hello_isotone_loaded`

**Usage:**
```php
add_action('hello_isotone_loaded', 'your_callback_function', 10, 1);
```

**Fired in:**
- `iso-content/plugins/hello-isotone/hello-isotone.php:139`


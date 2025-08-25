# Custom Error Pages for Themes

Theme developers can create custom error pages in several ways:

## Method 1: Theme Error Files

Place error page files in your theme directory. The system checks for these patterns in order:

1. `{error_code}.php` (e.g., `404.php`)
2. `error-{error_code}.php` (e.g., `error-404.php`)
3. `errors/{error_code}.php` (e.g., `errors/404.php`)
4. `error.php` (generic handler for all errors)

### Example Theme Structure:
```
iso-themes/
└── your-theme/
    ├── 404.php          # Custom 404 page
    ├── 403.php          # Custom 403 page
    └── error.php        # Generic error handler
```

### In Your Theme Error File:
```php
<?php
// The error code is available as:
$error_code = $_GET['error_code'] ?? $GLOBALS['error_code'] ?? 404;

// Your custom error page HTML
?>
<!DOCTYPE html>
<html>
<head>
    <title>Error <?php echo $error_code; ?></title>
</head>
<body>
    <h1>Oops! Error <?php echo $error_code; ?></h1>
    <!-- Your custom design -->
</body>
</html>
```

## Method 2: Using Hooks

Add to your theme's functions.php:

### Modify Error Configuration:
```php
add_filter('iso_error_config', function($config, $error_code) {
    if ($error_code === 404) {
        $config['title'] = 'Page Not Found - My Theme';
        $config['messages'] = [
            'Sorry, we couldn\'t find that page.',
            'It might have been moved or deleted.',
            'Try searching or go back home.'
        ];
    }
    return $config;
}, 10, 2);
```

### Completely Handle Error Display:
```php
add_filter('iso_handle_error', function($handled, $error_code, $config) {
    if ($error_code === 404) {
        // Display your custom error page
        include get_theme_path('my-custom-404.php');
        return true; // Tells system we handled it
    }
    return $handled;
}, 10, 3);
```

## Method 3: Frontend vs Admin

- **Frontend errors**: Check for theme overrides first
- **Admin errors**: Always use the system glitch-style page
- Detection is based on URL containing `/iso-admin/`

## Available Error Codes

The system handles these HTTP error codes:
- **400** - Bad Request
- **401** - Unauthorized
- **403** - Forbidden
- **404** - Not Found
- **405** - Method Not Allowed
- **408** - Request Timeout
- **500** - Internal Server Error
- **502** - Bad Gateway
- **503** - Service Unavailable
- **504** - Gateway Timeout

## Priority Order

1. Theme error file (if not in admin area)
2. Hook: `iso_handle_error` filter
3. Default system error page (glitch style)

## Helper Functions

Use these in your PHP code:

```php
// Redirect to error page
iso_error(404);

// Include error page directly
iso_abort(500);
```

## Notes for Theme Developers

- Theme error pages only work for frontend (not admin area)
- Set `ACTIVE_THEME` constant in config.php
- Error pages have access to session data
- The default error page includes memory optimization
- Consider mobile responsiveness in custom error pages
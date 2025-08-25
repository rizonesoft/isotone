# Isotone Routing System

## Overview

Isotone uses a lightweight, custom-built routing system that provides powerful URL handling without external dependencies. The Router class (`iso-core/Core/Router.php`) serves as the central request dispatcher, handling all URLs, API endpoints, error pages, and supporting WordPress-style permalinks.

## Key Features

- **Zero Dependencies**: No Symfony, Laravel, or other framework required
- **Pattern Matching**: Dynamic URL segments with type constraints
- **Error Handling**: Integrated HTTP error management (400-504)
- **API Routing**: Centralized API endpoint handling with versioning support
- **Permalink Support**: WordPress-compatible permalink structures
- **Platform Agnostic**: Works identically on Apache, Nginx, and LiteSpeed

## Architecture

### Request Flow

```
Request → .htaccess/nginx → index.php → Router → Handler
                                            ↓
                                        Response
```

All requests that don't match existing files or directories are routed through `index.php`, which initializes the Router to handle the request.

## Basic Routing

### Defining Routes

Routes are defined in the Router's `initializeDefaultRoutes()` method:

```php
// Exact route
$router->addRoute('GET', '/', [$this, 'handleHome']);

// Route with parameters
$router->addRoute('GET', '/post/{id}', [$this, 'handlePost']);

// Wildcard method (accepts any HTTP method)
$router->addRoute('*', '/api/{endpoint:any}', [$this, 'handleApiEndpoint']);
```

### Route Parameters

The Router supports dynamic URL segments with type constraints:

#### Available Parameter Types

| Type | Pattern | Example | Matches |
|------|---------|---------|---------|
| `any` | `{param:any}` | `/user/{username:any}` | Any non-slash character |
| `id` | `{param:id}` | `/post/{id:id}` | Numeric IDs only |
| `slug` | `{param:slug}` | `/page/{slug:slug}` | Lowercase letters, numbers, hyphens |
| `year` | `{param:year}` | `/{year:year}` | 4-digit year (e.g., 2025) |
| `month` | `{param:month}` | `/{month:month}` | 01-12 |
| `day` | `{param:day}` | `/{day:day}` | 01-31 |
| `alpha` | `{param:alpha}` | `/tag/{name:alpha}` | Letters only |
| `alphanum` | `{param:alphanum}` | `/ref/{code:alphanum}` | Letters and numbers |

#### Default Type

If no type is specified, `any` is used:

```php
// These are equivalent:
$router->addRoute('GET', '/user/{username}', $handler);
$router->addRoute('GET', '/user/{username:any}', $handler);
```

## WordPress-Style Permalinks

The Router supports WordPress-compatible permalink structures:

```php
// Set permalink structure
$router->setPermalinkStructure('/%year%/%monthnum%/%postname%/');

// This creates a route pattern: /{year:year}/{month:month}/{slug:slug}/
// Matches URLs like: /2025/01/hello-world/
```

### Common Permalink Patterns

```php
// Date-based archives
'/%year%/%monthnum%/%day%/%postname%/' // /2025/01/15/my-post/
'/%year%/%monthnum%/%postname%/'       // /2025/01/my-post/

// Category-based
'/blog/%category%/%postname%/'         // /blog/tech/my-post/
'/%category%/%postname%/'              // /tech/my-post/

// Simple
'/%postname%/'                         // /my-post/
'/articles/%postname%/'                // /articles/my-post/
```

## API Routing

The Router handles all API endpoints through a centralized system:

```php
// Built-in API routes
GET  /api/version     // Version information
GET  /api/system      // System information
GET  /api            // API discovery endpoint

// Dynamic API routing
*    /api/{endpoint:any}  // Routes to /iso-api/{endpoint}.php
```

### API Versioning

You can implement API versioning by adding version-specific routes:

```php
$router->addRoute('*', '/api/v1/{endpoint:any}', [$this, 'handleApiV1']);
$router->addRoute('*', '/api/v2/{endpoint:any}', [$this, 'handleApiV2']);
```

## Error Handling

The Router includes integrated error handling for all HTTP status codes:

```php
// Set custom error handler
$router->setErrorHandler(404, function($code, $message) {
    return view('errors.404', ['message' => $message]);
});

// Trigger error from route handler
$router->handleHttpError(404, 'Page not found');
```

### Supported Error Codes

- 400 Bad Request
- 401 Unauthorized
- 403 Forbidden
- 404 Not Found
- 405 Method Not Allowed
- 408 Request Timeout
- 500 Internal Server Error
- 502 Bad Gateway
- 503 Service Unavailable
- 504 Gateway Timeout

## Route Handlers

### Accessing Route Parameters

In your route handler, access parameters via the Router:

```php
private function handlePost()
{
    $postId = $this->getRouteParam('id');
    $allParams = $this->getRouteParams();
    
    // Load post by ID
    $post = Post::find($postId);
    
    return view('post', ['post' => $post]);
}
```

### Global Router Access

The Router instance is available globally in themes:

```php
global $isotone_router;

// Get current route info
$currentRoute = $isotone_router->getCurrentRoute();
$params = $isotone_router->getRouteParams();
```

## Server Configuration

### Apache (.htaccess)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

### Nginx

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### LiteSpeed

LiteSpeed servers use Apache-compatible .htaccess files, so no additional configuration is needed.

## Custom Routes in Themes/Plugins

Themes and plugins can register custom routes using hooks:

```php
// In theme's functions.php or plugin file
add_action('init', function() {
    global $isotone_router;
    
    // Add custom route
    $isotone_router->addRoute('GET', '/my-custom-page', function() {
        return '<h1>Custom Page</h1>';
    });
    
    // Add route with parameters
    $isotone_router->addRoute('GET', '/product/{id:id}', function() use ($isotone_router) {
        $productId = $isotone_router->getRouteParam('id');
        // Handle product display
    });
});
```

## Advanced Routing

### Route Groups (Future Enhancement)

```php
// Group routes with common prefix (planned feature)
$router->group('/admin', function($router) {
    $router->addRoute('GET', '/dashboard', 'AdminController@dashboard');
    $router->addRoute('GET', '/users', 'AdminController@users');
    $router->addRoute('GET', '/settings', 'AdminController@settings');
});
```

### Middleware (Future Enhancement)

```php
// Add middleware to routes (planned feature)
$router->addRoute('GET', '/admin/*', 'AdminController@handle')
       ->middleware(['auth', 'admin']);
```

## Performance Considerations

1. **Route Order**: Exact routes are checked before pattern routes for optimal performance
2. **Caching**: Route compilation can be cached in production (future enhancement)
3. **Memory Usage**: The Router maintains a minimal memory footprint (~200KB)

## Migration Guide

### From Direct File Access

If migrating from direct PHP file access:

**Before:**
```
/api/users.php
/api/posts.php
/pages/about.php
```

**After:**
```php
$router->addRoute('GET', '/api/users', [$this, 'handleApiUsers']);
$router->addRoute('GET', '/api/posts', [$this, 'handleApiPosts']);
$router->addRoute('GET', '/pages/about', [$this, 'handlePageAbout']);
```

### From WordPress

The Router supports WordPress-style permalinks natively:

```php
// WordPress permalink
update_option('permalink_structure', '/%year%/%monthnum%/%postname%/');

// Isotone equivalent
$router->setPermalinkStructure('/%year%/%monthnum%/%postname%/');
```

## Debugging Routes

Enable debug mode to see route matching information:

```php
// In config.php
define('DEBUG_MODE', true);

// This will show route matching details in HTML comments
```

## Security Considerations

1. **Input Validation**: Always validate route parameters in handlers
2. **Authentication**: Check user permissions in route handlers
3. **Rate Limiting**: Implement rate limiting for API endpoints
4. **CORS**: Configure CORS headers for API routes as needed

## Examples

### Blog with Categories

```php
// Category archive
$router->addRoute('GET', '/category/{slug:slug}', function() use ($router) {
    $category = $router->getRouteParam('slug');
    $posts = Post::byCategory($category);
    return view('archive', ['posts' => $posts]);
});

// Single post
$router->addRoute('GET', '/{year:year}/{month:month}/{slug:slug}', function() use ($router) {
    $slug = $router->getRouteParam('slug');
    $post = Post::bySlug($slug);
    return view('single', ['post' => $post]);
});
```

### RESTful API

```php
// RESTful resource routes
$router->addRoute('GET',    '/api/posts',     'PostApi@index');   // List
$router->addRoute('POST',   '/api/posts',     'PostApi@store');   // Create
$router->addRoute('GET',    '/api/posts/{id:id}', 'PostApi@show'); // Read
$router->addRoute('PUT',    '/api/posts/{id:id}', 'PostApi@update'); // Update
$router->addRoute('DELETE', '/api/posts/{id:id}', 'PostApi@destroy'); // Delete
```

### Custom Admin Routes

```php
// Admin dashboard
$router->addRoute('GET', '/admin', function() {
    if (!is_admin()) {
        return $router->handleHttpError(403, 'Access denied');
    }
    return view('admin.dashboard');
});

// Admin settings
$router->addRoute('GET', '/admin/settings/{section:slug}', function() use ($router) {
    $section = $router->getRouteParam('section');
    return view('admin.settings', ['section' => $section]);
});
```

## Troubleshooting

### Routes Not Matching

1. Check route order - exact routes before patterns
2. Verify parameter types match expected input
3. Ensure .htaccess/nginx config is correct
4. Check for conflicting routes

### 404 Errors

1. Verify file doesn't exist (files take precedence)
2. Check route is registered
3. Ensure request method matches route method
4. Verify URL prefix (/isotone) if in subdirectory

### API Routes Not Working

1. Ensure /iso-api/.htaccess is removed
2. Check API files exist in /iso-api/
3. Verify CORS headers if cross-origin

## Related Documentation

- [Project Structure](project-structure.md) - Understanding Isotone's directory layout
- [API Reference](api-reference.md) - Detailed API endpoint documentation
- [Hooks System](hooks.md) - Extending routing with hooks
- [Theme Development](themes.md) - Creating routes in themes
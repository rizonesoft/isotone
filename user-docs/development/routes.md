# Routes

## System Routes

Isotone's routing system handles all requests through a centralized Router class. For comprehensive documentation on the routing system, see [Routing System Documentation](routing-system.md).

## Available Routes

> Last updated: 2025-01-24

### Core Routes

| Method | Pattern | Handler | Description |
|--------|---------|---------|-------------|
| GET | `/` | handleHome | Homepage/theme display |
| GET | `/api` | handleApiDiscovery | API discovery endpoint |
| GET | `/api/version` | handleApiVersion | System version information |
| GET | `/api/system` | handleApiSystem | System status and info |
| * | `/api/{endpoint:any}` | handleApiEndpoint | Dynamic API routing |

### Admin Routes

Admin routes are currently handled directly through Apache/Nginx but will be integrated into the Router in future versions.

- `/iso-admin/` - Admin dashboard
- `/iso-admin/login.php` - Admin login
- `/iso-admin/settings.php` - System settings

### Error Routes

All HTTP errors (400-504) are handled internally by the Router's error handling system.

## Dynamic Routing

The Router supports dynamic URL patterns with type constraints:

```php
// Examples of dynamic routes
'/post/{id:id}'           // Matches: /post/123
'/category/{slug:slug}'    // Matches: /category/tech-news
'/{year:year}/{month:month}/{slug:slug}' // Matches: /2025/01/hello-world
```

## Adding Custom Routes

### In Themes

```php
// In theme's functions.php
add_action('init', function() {
    global $isotone_router;
    
    $isotone_router->addRoute('GET', '/custom-page', function() {
        return '<h1>Custom Page</h1>';
    });
});
```

### In Plugins

```php
// In plugin file
class MyPlugin {
    public function __construct() {
        add_action('init', [$this, 'registerRoutes']);
    }
    
    public function registerRoutes() {
        global $isotone_router;
        
        $isotone_router->addRoute('GET', '/my-plugin', [$this, 'handleRequest']);
    }
    
    public function handleRequest() {
        return 'Plugin response';
    }
}
```

## Permalink Support

Isotone supports WordPress-style permalinks:

```php
// Set permalink structure
$router->setPermalinkStructure('/%year%/%monthnum%/%postname%/');

// This automatically creates routes for posts following that pattern
```

## API Endpoints

API endpoints in `/iso-api/` are automatically routed:

- `/api/icons` → `/iso-api/icons.php`
- `/api/admin/stats` → `/iso-api/admin/stats.php`

## Route Caching

Route caching is planned for future versions to improve performance in production environments.

## See Also

- [Routing System Documentation](routing-system.md) - Complete routing guide
- [API Reference](api-reference.md) - API endpoint details
- [Project Structure](project-structure.md) - Directory organization
- [Hooks System](hooks.md) - Extending with hooks
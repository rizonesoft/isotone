---
title: Isotone Architecture
description: Detailed overview of Isotone's service-oriented architecture, design patterns, and system components
tags: [architecture, design-patterns, services, core-components, system-design]
category: development
priority: 90
last_updated: 2025-01-20
---

# Isotone Architecture

## Overview

Isotone uses a **Service-Oriented Architecture with Direct Routing** pattern, optimized for lightweight deployment on shared hosting environments. This pragmatic approach prioritizes simplicity, performance, and ease of maintenance over complex architectural patterns.

## Architecture Pattern

### Not MVC
Isotone deliberately **does not use** a traditional Model-View-Controller (MVC) pattern. Instead, it employs a simpler, more direct architecture similar to WordPress, making it easier to understand and maintain.

### Core Components

```
/isotone/
├── iso-core/                # Core application logic
│   ├── Commands/            # CLI command handlers
│   ├── Core/               # Core CMS functionality
│   ├── Services/           # Business logic services
│   ├── helpers.php         # Global helper functions
│   ├── hooks.php           # WordPress-style hooks system
│   └── theme-functions.php # Theme support functions
├── iso-admin/              # Admin interface
│   ├── api/               # API endpoints
│   ├── css/               # Admin styles
│   ├── includes/          # Shared admin components
│   └── *.php              # Individual admin pages
├── iso-automation/         # Automation & documentation system
├── iso-content/           # User-generated content
│   ├── plugins/          # Installed plugins
│   ├── themes/           # Installed themes
│   └── uploads/          # Media files
└── iso-includes/          # Shared resources & libraries
```

## Architectural Decisions

### 1. Service-Oriented Business Logic

All business logic is encapsulated in service classes located in `/iso-core/Services/`:

```php
// Example: ToniService for AI Assistant
class ToniService {
    public function sendMessage($userId, $message) {
        // Business logic here
    }
}
```

**Benefits:**
- Clear separation of concerns
- Easy to test and maintain
- Services can be reused across different interfaces

### 2. Direct Page Routing

Admin pages are individual PHP files that handle their own request/response cycle:

```php
// /iso-admin/settings.php
require_once 'auth.php';  // Authentication
// Handle GET/POST directly
// Include layout template
```

**Benefits:**
- Simple to understand
- No complex routing configuration
- Each page is self-contained
- Works perfectly on shared hosting

### 3. RedBeanPHP Active Record

Database operations use RedBeanPHP's Active Record pattern:

```php
// No model classes needed
$user = R::dispense('users');
$user->name = 'John';
R::store($user);
```

**Benefits:**
- No schema migrations needed
- Zero configuration
- Automatic table creation
- Simple and intuitive

### 4. WordPress-Style Hooks System

Extensibility through action and filter hooks:

```php
// Add functionality without modifying core
add_action('init', 'my_custom_function');
add_filter('the_content', 'modify_content');
```

**Benefits:**
- Plugin system compatibility
- Non-invasive extensions
- Familiar to WordPress developers
- Maintains upgrade safety

### 5. Template-Based UI

Admin interface uses a simple template inclusion system:

```php
// Page content
$page_content = ob_get_clean();
// Include layout
require_once 'includes/admin-layout.php';
```

**Benefits:**
- Consistent UI across admin
- No complex templating engine
- PHP as the template language
- Fast and efficient

## Data Flow

### Request Lifecycle

1. **Entry Point** → Apache routes to PHP file
2. **Authentication** → `auth.php` verifies session
3. **Processing** → Page handles its own logic
4. **Service Layer** → Business logic in Services
5. **Database** → RedBeanPHP handles persistence
6. **Response** → Template renders output

### Example Flow: Settings Page

```
User Request → /iso-admin/settings.php
    ↓
Auth Check (auth.php)
    ↓
Load Configuration (config.php)
    ↓
Initialize Database (RedBeanPHP)
    ↓
Process Form (if POST)
    ↓
Query Database (R::find())
    ↓
Render Template (admin-layout.php)
    ↓
Response to User
```

## Design Principles

### 1. Simplicity First
- No unnecessary abstractions
- Direct, readable code
- Minimal dependencies

### 2. Shared Hosting Compatible
- No special server requirements
- Works with standard PHP/MySQL
- No command-line deployment needed

### 3. Progressive Enhancement
- Core functionality works everywhere
- Modern features when available
- Graceful degradation

### 4. Extensible but Stable
- Hooks for extensions
- Core remains untouched
- Plugins don't break updates

## File Organization

### Application Code (`/iso-core/`)

- **Commands/** - CLI command handlers
- **Core/** - Core CMS functionality (Application, ThemeAPI, etc.)
- **Services/** - Business logic services (DatabaseService, ToniService, etc.)

### Admin Interface (`/iso-admin/`)

- **Individual PHP files** - Each admin page (dashboard.php, settings.php, etc.)
- **includes/** - Shared components (admin-layout.php, admin-auth.php)
- **api/** - AJAX/REST endpoints
- **css/** - Admin styles (Tailwind-based)

### Content & Extensions (`/iso-content/`)

- **plugins/** - WordPress-compatible plugins
- **themes/** - Theme files
- **uploads/** - User media

### Shared Resources (`/iso-includes/`)

- **assets/** - Images, logos
- **css/** - Global styles (modular CSS)
- **js/** - JavaScript libraries
- **classes/** - Utility classes

## Comparison with MVC

| Aspect | Traditional MVC | Isotone Architecture |
|--------|----------------|---------------------|
| **Models** | Model classes with ORM | RedBeanPHP beans (no classes) |
| **Views** | Template engine (Blade, Twig) | PHP includes with output buffering |
| **Controllers** | Controller classes with routing | Direct PHP files handle requests |
| **Routing** | Complex route definitions | File-based routing via Apache |
| **Business Logic** | In controllers or models | In Service classes |
| **Database** | Migrations & schemas | Auto-generated by RedBeanPHP |

## Benefits of This Architecture

### For Developers
- **Easy to understand** - No complex patterns to learn
- **Quick to develop** - Direct approach, less boilerplate
- **Familiar** - Similar to WordPress architecture
- **Debuggable** - Clear execution path

### For Hosting
- **Lightweight** - Minimal overhead
- **Compatible** - Works on any PHP host
- **No build step** - Upload and run
- **Standard stack** - PHP/MySQL only

### For Maintenance
- **Self-contained pages** - Easy to modify
- **Clear dependencies** - Simple require statements
- **Version control friendly** - No generated files
- **Update safe** - Hooks preserve customizations

## Security Considerations

### Authentication
- Session-based authentication
- Required on every admin page
- Centralized in `auth.php`

### Database
- Prepared statements via RedBeanPHP
- No raw SQL queries
- Input sanitization built-in

### File Access
- `.htaccess` protection
- Direct file access blocked
- Upload validation

## Performance Optimizations

### Caching Strategy
- Output caching for expensive operations
- Database query caching via RedBeanPHP
- Asset caching with versioning

### Lazy Loading
- Services loaded on demand
- Database connection only when needed
- Autoloading via Composer

## Future Considerations

While maintaining the current architecture, future enhancements might include:

- PSR-4 autoloading for Services
- API-first approach for admin
- Progressive Web App capabilities
- Optional Redis/Memcached support

## Conclusion

Isotone's architecture is intentionally simple and pragmatic. It avoids over-engineering while providing a solid foundation for a lightweight CMS. This approach ensures that Isotone remains true to its goal of being an accessible, easy-to-deploy content management system that works everywhere.
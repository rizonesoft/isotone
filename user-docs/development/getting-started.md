# Getting Started with Isotone CMS

Welcome to Isotone CMS! This guide will help you understand the basics and start building with Isotone.

## Quick Start

If you haven't set up your development environment yet, see the [Development Setup Guide](DEVELOPMENT-SETUP.md) first.

## Understanding Isotone

### What is Isotone?

Isotone is a lightweight, modern PHP CMS designed for:
- **Shared hosting compatibility** - Runs anywhere PHP runs
- **Developer-friendly** - Clean code, modern patterns
- **Performance** - Fast and efficient
- **Extensibility** - Plugin and theme system

### Core Concepts

1. **MVC Architecture** - Separation of concerns
2. **Hook System** - WordPress-like actions and filters
3. **ORM-based** - RedBeanPHP for database abstraction
4. **PSR Standards** - Modern PHP practices
5. **Environment-based Config** - Using .env files

## Project Structure

```
isotone/
├── app/                # Application code
│   ├── Core/           # Core CMS classes
│   ├── Commands/       # CLI commands
│   ├── Models/         # Data models
│   ├── Services/       # Business logic
│   └── helpers.php     # Helper functions
├── iso-admin/          # Admin panel (coming soon)
├── iso-includes/       # Shared resources
│   ├── assets/         # Images, logos
│   ├── css/            # Global styles
│   ├── js/             # Global scripts
│   └── scripts/        # PHP includes
├── iso-content/        # User content
│   ├── plugins/        # Installed plugins
│   ├── themes/         # Installed themes
│   ├── uploads/        # Media files
│   └── cache/          # Cache files
├── config/             # Configuration files
├── docs/               # Documentation
├── install/            # Installation wizard
├── scripts/            # Build scripts
├── storage/            # Logs and temp
├── vendor/             # Composer dependencies
├── index.php           # Entry point
├── .htaccess           # URL rewriting & security
├── .env                # Environment config
└── composer.json       # Dependencies
```

## Creating Your First Page

### 1. Understanding Routing

Routes are defined in `app/Core/Application.php`:

```php
// Example route
$this->routes->add('about', new Route('/about', [
    '_controller' => [$this, 'handleAbout']
]));
```

### 2. Creating a Controller Method

Add a new method to handle the route:

```php
private function handleAbout(Request $request): Response
{
    $html = '<h1>About Us</h1><p>Welcome to our site!</p>';
    return new Response($html);
}
```

### 3. Testing Your Page

Visit: http://localhost/isotone/about

## Installation Process

### Initial Setup

1. **Clone or download** the Isotone repository
2. **Install dependencies** with `composer install`
3. **Configure database** in `.env` file
4. **Run installation wizard** at http://localhost/isotone/install/
   - Choose your Super Admin username
   - Set your email and password
   - No default credentials - you control everything
5. **Delete or rename** the `/install` directory after setup for security

## Working with the Database

### Setting Up RedBeanPHP

Isotone uses RedBeanPHP for database operations:

```php
use Isotone\Services\DatabaseService;
use RedBeanPHP\R;

// Database is automatically initialized via DatabaseService
DatabaseService::initialize();

// Create a new record
$post = R::dispense('post');
$post->title = 'Hello World';
$post->content = 'This is my first post';
R::store($post);

// Find records
$posts = R::findAll('post', 'ORDER BY created_at DESC');
```

### Working with Settings

Settings are stored in the `isotonesetting` table:

```php
// Get a setting
$setting = R::findOne('isotonesetting', 'setting_key = ?', ['site_title']);
$siteTitle = $setting ? $setting->setting_value : 'Default Title';

// Update a setting
$setting = R::findOne('isotonesetting', 'setting_key = ?', ['site_title']);
if (!$setting) {
    $setting = R::dispense('isotonesetting');
    $setting->setting_key = 'site_title';
}
$setting->setting_value = 'My New Site Title';
$setting->setting_type = 'string';
$setting->updated_at = date('Y-m-d H:i:s');
R::store($setting);
```

Note: Column names use `setting_key`, `setting_value`, and `setting_type` to avoid MySQL reserved words.

### Creating Models

Create a model in `app/Models/Post.php`:

```php
namespace Isotone\Models;

use RedBeanPHP\SimpleModel;

class Post extends SimpleModel
{
    public function getFormattedDate()
    {
        return date('F j, Y', strtotime($this->created_at));
    }
}
```

## Creating a Plugin

### Basic Plugin Structure

Create a new directory in `iso-content/plugins/`:

```
iso-content/plugins/
└── my-plugin/
    ├── my-plugin.php    # Main plugin file
    └── README.md        # Documentation
```

### Plugin File Template

```php
<?php
/**
 * Plugin Name: My Plugin
 * Description: A sample plugin
 * Version: 1.0.0
 * Author: Your Name
 */

namespace MyPlugin;

// Hook into Isotone
add_action('isotone_init', function() {
    // Plugin initialization code
});

// Add a filter
add_filter('isotone_content', function($content) {
    return $content . '<p>Added by plugin!</p>';
});
```

## Creating a Theme

### Basic Theme Structure

Create a new directory in `iso-content/themes/`:

```
iso-content/themes/
└── my-theme/
    ├── index.php       # Main template
    ├── style.css       # Theme styles
    ├── functions.php   # Theme functions
    └── templates/      # Template parts
```

### Theme Files

**style.css** - Theme information:
```css
/*
Theme Name: My Theme
Description: A custom theme
Version: 1.0.0
Author: Your Name
*/
```

**index.php** - Main template:
```php
<!DOCTYPE html>
<html>
<head>
    <title><?php echo site_title(); ?></title>
    <?php theme_head(); ?>
</head>
<body>
    <?php theme_content(); ?>
    <?php theme_footer(); ?>
</body>
</html>
```

## Using the CLI (Coming Soon)

Isotone will include a CLI tool for common tasks:

```bash
# Create a new plugin
php isotone make:plugin my-plugin

# Create a new theme
php isotone make:theme my-theme

# Clear cache
php isotone cache:clear

# Run migrations
php isotone migrate
```

## Environment Configuration

### Development vs Production

Configure your environment in `.env`:

**Development:**
```env
APP_ENV=development
APP_DEBUG=true
CACHE_DRIVER=file
```

**Production:**
```env
APP_ENV=production
APP_DEBUG=false
CACHE_DRIVER=redis
```

### Security Keys

Generate secure keys for your application:

```bash
# Generate a random key
php -r "echo base64_encode(random_bytes(32));"
```

Add to `.env`:
```env
APP_KEY=your_generated_key_here
JWT_SECRET=another_generated_key
```

## Best Practices

### 1. Follow PSR Standards
- PSR-4 for autoloading
- PSR-12 for code style
- Use namespaces properly

### 2. Security First
- Always escape output
- Use prepared statements
- Validate all input
- Keep dependencies updated

### 3. Performance
- Enable caching in production
- Optimize images
- Minify CSS/JS
- Use CDN for assets

### 4. Version Control
- Commit `.env.example`, not `.env`
- Ignore `vendor/` directory
- Use semantic versioning

## Common Tasks

### Adding a Menu Item

```php
add_action('admin_menu', function() {
    add_menu_item([
        'title' => 'My Page',
        'url' => '/my-page',
        'icon' => 'dashboard'
    ]);
});
```

### Creating a Widget

```php
register_widget('sidebar', [
    'name' => 'Main Sidebar',
    'description' => 'Widgets in this area appear in the sidebar'
]);
```

### Handling Forms

```php
if ($request->isMethod('POST')) {
    $data = $request->request->all();
    
    // Validate
    if (empty($data['email'])) {
        throw new ValidationException('Email required');
    }
    
    // Process
    $user = R::dispense('user');
    $user->email = $data['email'];
    R::store($user);
    
    // Redirect
    return new RedirectResponse('/success');
}
```

## Debugging

### Enable Debug Mode

In `.env`:
```env
APP_DEBUG=true
```

### Using var_dump

```php
use Symfony\Component\VarDumper\VarDumper;

VarDumper::dump($variable);
// or
dd($variable); // Dump and die
```

### Logging

```php
use Monolog\Logger;

$logger = new Logger('isotone');
$logger->info('User logged in', ['user_id' => 123]);
$logger->error('Database connection failed');
```

### Check Logs

View logs in `storage/logs/`:
```bash
tail -f storage/logs/isotone.log
```

## Resources

### Documentation
- [Development Setup](DEVELOPMENT-SETUP.md)
- [Technology Stack](ISOTONE-TECH-STACK.md)
- [API Reference](api-reference.md) (Coming Soon)

### Community
- GitHub Issues - Report bugs
- Discord - Get help
- Forums - Discuss features

### Learning PHP
- [PHP.net](https://php.net) - Official documentation
- [PSR Standards](https://www.php-fig.org/psr/) - PHP standards
- [Composer](https://getcomposer.org/doc/) - Dependency management

## Next Steps

1. **Build a Simple Plugin** - Start with a hello world plugin
2. **Create a Custom Theme** - Design your own theme
3. **Explore the Core** - Read through `app/Core` classes
4. **Contribute** - Submit PRs for improvements

## Need Help?

- Check the [Troubleshooting Guide](DEVELOPMENT-SETUP.md#troubleshooting)
- Search existing GitHub issues
- Ask in the community Discord
- Read the source code - it's well documented!

---

*Happy coding with Isotone CMS!*
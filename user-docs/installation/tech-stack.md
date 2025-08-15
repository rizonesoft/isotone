# Isotone Technology Stack Specification

> **Document Status:** Planning Phase - This is a technical specification for the planned Isotone implementation.

## Overview

Isotone will be a modern, lightweight Content Management System designed for simplicity, performance, and compatibility with standard shared hosting environments. This document outlines the planned technology stack and architectural decisions.

## Core Backend Stack

### PHP 8.3+

**Runtime Requirements:**
- PHP 8.3 or higher
- Memory limit: 128MB minimum, 256MB recommended
- Max execution time: 30 seconds minimum

**Key Extensions Required:**
- `pdo_mysql` - Database connectivity
- `gd` or `imagick` - Image processing
- `mbstring` - Multi-byte string support
- `json` - JSON encoding/decoding
- `zip` - Plugin/theme installation
- `curl` - External API communications
- `openssl` - Security features

**Planned Development Standards:**
- **PSR-3**: Logger Interface for consistent logging
- **PSR-4**: Autoloading standard for class loading
- **PSR-12**: Extended coding style guide
- **PSR-7**: HTTP message interfaces (for API development)
- **PSR-15**: HTTP handlers and middleware

**Dependency Management:**
```json
{
  "require": {
    "php": ">=8.3",
    "gabordemooij/redbean": "^5.7",
    "monolog/monolog": "^3.0",
    "symfony/http-foundation": "^7.0",
    "league/commonmark": "^2.4",
    "intervention/image": "^3.0"
  }
}
```

### Composer Dependencies (Planned)

**Planned Core Dependencies:**
- `gabordemooij/redbean` - ORM and database abstraction
- `monolog/monolog` - PSR-3 compliant logging
- `symfony/http-foundation` - Request/Response handling
- `league/commonmark` - Markdown parsing
- `intervention/image` - Image manipulation wrapper

**Planned Development Dependencies:**
- `phpunit/phpunit` - Unit testing
- `squizlabs/php_codesniffer` - Code style enforcement
- `phpstan/phpstan` - Static analysis

## Database Layer

### MariaDB 10.6+ / MySQL 8.0+

**Database Configuration:**
- Character set: `utf8mb4`
- Collation: `utf8mb4_unicode_ci`
- Storage engine: InnoDB
- Strict mode enabled

**Database Access:**
- PDO with prepared statements for all queries
- Connection pooling where available
- Query caching enabled

### RedBeanPHP ORM

**Planned Implementation Strategy:**
- Fluid mode during development
- Frozen mode in production for performance
- Custom model classes for complex entities
- Automatic schema generation for plugins

**Planned Core Tables Structure:**
```sql
-- Users
users (id, username, email, password_hash, role, created_at, updated_at)

-- Content
posts (id, title, slug, content, status, author_id, created_at, updated_at, published_at)
pages (id, title, slug, content, status, author_id, parent_id, order, created_at, updated_at)

-- Taxonomy
categories (id, name, slug, description, parent_id)
tags (id, name, slug)

-- Media
media (id, filename, path, mime_type, size, metadata, uploaded_by, created_at)

-- Settings
options (id, option_name, option_value, autoload)

-- Plugins/Themes
plugins (id, name, version, active, settings)
themes (id, name, version, active, settings)
```

## Frontend Technologies

### CSS Framework Strategy

**Core Admin Interface:**
- **Tailwind CSS 3.4+** - Utility-first approach
- PostCSS for processing
- PurgeCSS for production builds
- Custom Isotone component library built on Tailwind

**Theme Development:**
- Agnostic approach - themes choose their framework
- Bundled starter themes:
  - Bootstrap 5 theme (familiar for developers)
  - Tailwind theme (modern approach)
  - Vanilla CSS theme (no framework)

### JavaScript Stack

**Alpine.js 3.x**
- Declarative, reactive framework
- ~15KB minified
- Perfect for shared hosting limitations
- Use for:
  - Admin UI interactions
  - Form validations
  - Dynamic content updates
  - Modal/dropdown management

**HTMX 2.x**
- HTML-driven Ajax functionality
- ~14KB minified
- Use for:
  - Inline editing
  - Infinite scroll
  - Form submissions
  - Live search
  - Content polling

**Optional Enhancements:**
- `Sortable.js` - Drag and drop functionality
- `Choices.js` - Enhanced select boxes
- `Flatpickr` - Date/time picker

### Icon System

**Lucide Icons**
- Open source, consistent design
- Tree-shakeable
- SVG-based for crisp rendering
- 1000+ icons available
- Implementation via PHP helper functions

## Planned Architecture Patterns

### Plugin Architecture

**Planned Hook/Filter System:**
```php
// Action hooks
do_action('isotone_init');
add_action('isotone_init', 'my_plugin_init', 10, 1);

// Filter hooks
$content = apply_filters('isotone_content', $content);
add_filter('isotone_content', 'my_content_filter', 10, 1);
```

**Plugin Structure:**
```
plugins/
├── my-plugin/
│   ├── my-plugin.php       # Main plugin file
│   ├── includes/           # PHP classes
│   ├── assets/            # CSS, JS, images
│   ├── templates/         # View templates
│   └── languages/         # i18n files
```

### Theme System

**Template Hierarchy:**
1. Child theme template
2. Parent theme template
3. Plugin template override
4. Core default template

**Theme Structure:**
```
themes/
├── my-theme/
│   ├── style.css          # Theme metadata
│   ├── functions.php      # Theme setup
│   ├── index.php         # Main template
│   ├── single.php        # Single post
│   ├── page.php          # Page template
│   ├── archive.php       # Archive pages
│   ├── partials/         # Reusable components
│   └── assets/           # Theme resources
```

### REST API

**Endpoints Structure:**
```
/api/v1/posts
/api/v1/pages
/api/v1/media
/api/v1/users
/api/v1/categories
/api/v1/tags
/api/v1/settings
```

**Authentication:**
- JWT tokens for stateless auth
- API keys for server-to-server
- WordPress-compatible endpoints for migration tools (with `iso_` prefix for hooks)

### Content Management

**Markdown Support:**
- CommonMark specification
- GFM (GitHub Flavored Markdown) extensions
- Syntax highlighting with Prism.js
- WYSIWYG editor with markdown shortcuts

**Media Handling:**
- Automatic image optimization
- Responsive image generation
- WebP conversion when supported
- Lazy loading implementation

## Planned Performance Optimization

### Caching Strategy

**Page Caching:**
- Full-page HTML caching for anonymous users
- Fragment caching for dynamic sections
- Database query caching

**Asset Optimization:**
- CSS/JS minification and concatenation
- Browser caching headers
- CDN support for static assets

### Image Optimization

**Processing Libraries:**
- Primary: GD Library (widely available)
- Fallback: ImageMagick (when available)
- Features:
  - Automatic resizing
  - Format conversion
  - Quality optimization
  - EXIF data handling

## Planned Security Features

### Core Security

- CSRF protection on all forms
- XSS prevention via output escaping
- SQL injection prevention via prepared statements
- File upload validation and sanitization
- Password hashing with PASSWORD_DEFAULT
- Two-factor authentication support
- Rate limiting for login attempts
- Security headers (CSP, X-Frame-Options, etc.)

### User Permissions

**Role-Based Access Control:**
- Super Admin
- Administrator
- Editor
- Author
- Contributor
- Subscriber

**Capability System:**
- Granular permissions
- Custom capabilities for plugins
- Role management interface

## Planned Development Tools

### Local Development

**Planned Docker Configuration:**
```yaml
version: '3.8'
services:
  web:
    image: php:8.3-apache
    volumes:
      - ./:/var/www/html
  database:
    image: mariadb:10.6
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: isotone
```

### Planned Testing Framework

- **PHPUnit** - Unit and integration testing
- **Behat** - Behavioral testing
- **Cypress** - E2E testing for admin interface

### Planned Code Quality Tools

- **PHP CodeSniffer** - Enforce PSR-12
- **PHPStan** - Static analysis (Level 6+)
- **PHP Mess Detector** - Code complexity analysis

## Deployment Considerations

### Minimum Requirements

**Shared Hosting Compatibility:**
- PHP 8.3+
- MySQL 5.7+ or MariaDB 10.3+
- 50MB disk space (core)
- 128MB PHP memory limit
- mod_rewrite enabled

### Recommended Environment

- PHP 8.3+ with OPcache
- MariaDB 10.6+
- Redis/Memcached for object caching
- 256MB+ PHP memory limit
- HTTP/2 support
- SSL certificate

### Planned Installation Process

1. Upload files via FTP/SFTP
2. Create database
3. Run web-based installer
4. Configure permalinks
5. Install starter content (optional)

## Additional Components

### Search Functionality

**Built-in Search:**
- MySQL FULLTEXT indexing
- Relevance scoring
- Search filters and facets

**Optional Integrations:**
- Elasticsearch adapter
- Algolia integration
- Meilisearch support

### Multi-language Support

- GNU gettext for translations
- RTL language support
- Language pack management
- Content translation interface

### Email System

- PHPMailer for email sending
- SMTP configuration support
- Email templating system
- Queue support for bulk emails

### Backup System

- Database export functionality
- File system backup
- Scheduled backups via cron
- One-click restore

## Future Considerations

### Potential Progressive Enhancements

- WebSocket support for real-time features
- GraphQL API endpoint
- Static site generation capability
- Edge caching integration

### Scalability Path

1. Single server (shared hosting)
2. VPS with caching
3. Load-balanced setup
4. Microservices architecture (future)

## Version Control

### Git Workflow

- Main branch for stable releases
- Develop branch for ongoing work
- Feature branches for new features
- Semantic versioning (MAJOR.MINOR.PATCH)

## Documentation

### Developer Documentation

- PHPDoc for all classes and methods
- API documentation generation
- Plugin development guide
- Theme development guide

### User Documentation

- Installation guide
- User manual
- Video tutorials
- Community forum

---

*Document Status: Planning Phase*
*Last Updated: 2025*
*Specification Version: 0.1.0*
# Isotone

> **Project Status:** 🚧 Early Development - Core foundation implemented

## Why Isotone?

**Isotone** (*/ˈaɪsətoʊn/*) takes its name from the mathematical concept of an isotone (order-preserving) function—a transformation that maintains structure and relationships while allowing for growth and change. Just like its mathematical namesake, Isotone will preserve what matters (simplicity, reliability, compatibility) while transforming how modern content management should work.

In physics, isotonic means "equal tension"—and that's exactly what Isotone aims to bring to web development: perfect balance between power and simplicity, features and performance, flexibility and structure.

## 🚀 Features

### ✅ Implemented
- **Modern UI Design** - Dark theme with glassmorphism and electric accents
- **Custom SVG Logo** - Unique brand identity with gradient effects
- **Favicon System** - PNG-based favicons with web manifest for PWA support
- **SEO Optimization** - Meta tags, Open Graph, and Twitter Card support
- **Routing System** - Symfony-based routing with clean URLs
- **Environment Configuration** - config.php file support for configuration (no .env)
- **Composer Integration** - Modern PHP dependency management
- **PSR-12 Compliant** - Clean, standardized code structure
- **Database Integration** - RedBeanPHP ORM (no migrations needed)
- **Plugin System** - WordPress-compatible hooks with `iso_` prefix (add_action, add_filter)
- **Native Theme API** - Complete theming system with WordPress-compatible functions
  - Template functions (bloginfo, home_url, get_template_directory_uri)
  - Content loop system (have_posts, the_post, the_title, the_content)
  - Conditional tags (is_home, is_single, is_page, is_archive)
  - Template hierarchy and part loading
  - Theme customization API (theme mods)
- **Authentication System** - User login with role-based permissions
- **Modern Admin Interface** - Responsive dashboard with collapsible sidebar
  - Collapsible sidebar with submenus for all sections
  - Top admin bar with quick actions and search
  - Dashboard widgets (stats, analytics, quick draft, system health)
  - User management with CRUD operations
  - Breadcrumb navigation and toast notifications
  - Dark/light mode toggle (UI ready)
  - Mobile responsive with Alpine.js interactivity
  - Chart.js integration for analytics visualization

### 🚧 In Development
- **Content Management** - Full posts and pages system with database storage
- **Media Library** - File upload and management
- **Admin Post Editor** - Create and edit content from admin panel

### 📋 Planned Features

#### Built for Real-World Hosting
- **Shared hosting compatible** - Works on any standard PHP hosting
- **Lightweight core** - Under 10MB base installation
- **Resource efficient** - Runs smoothly on 128MB RAM
- **No Node.js required** - Pure PHP for maximum compatibility

#### Modern Developer Experience
- **RESTful API** - Headless CMS capabilities built-in
- **Markdown native** - Write content the way developers love
- **Git-friendly** - Version control your content and configuration

#### Performance First
- **Built-in caching** - Page, object, and query caching
- **Image optimization** - Automatic resizing and WebP conversion
- **Lazy loading** - Efficient resource loading
- **CDN ready** - Static asset optimization built-in

## 📋 Target Requirements

- PHP 8.3 or higher
- MariaDB 10.6+ or MySQL 8.0+
- Apache or Nginx web server
- 50MB disk space (minimum)
- 128MB RAM (minimum)

For detailed requirements and technology stack, see [Technology Stack Documentation](docs/ISOTONE-TECH-STACK.md).

## 🎯 Quick Start

### Prerequisites
- PHP 8.3 or higher
- MySQL/MariaDB
- Apache with mod_rewrite
- Composer

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/rizonesoft/isotone.git
   cd isotone
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure database**
   ```bash
   cp config.sample.php config.php
   # Edit config.php with your database credentials
   ```
   - Create database in phpMyAdmin (e.g., `isotone_db`)
   - Update `DB_NAME`, `DB_USER`, `DB_PASSWORD` in config.php
   - Configuration follows standard PHP CMS pattern

4. **Run installation wizard**
   ```
   http://localhost/isotone/install/
   ```
   - Choose your Super Admin username
   - Set your email and password
   - No default credentials - you choose everything

5. **Access the site**
   ```
   http://localhost/isotone/
   ```
   
6. **Security** (Important!)
   - Delete `/install` directory after setup
   - Or rename: `mv install install.backup`

For detailed setup instructions, see the [Development Setup Guide](docs/DEVELOPMENT-SETUP.md).

## 🏗️ Project Structure

```
isotone/
├── app/                  # Core application code
│   ├── Core/            # Core CMS functionality
│   ├── Commands/        # CLI commands
│   ├── Models/          # Data models
│   └── Services/        # Business logic
├── iso-admin/           # Admin panel (coming soon)
├── iso-includes/        # Shared resources
│   ├── assets/          # Static assets (images, logos)
│   ├── css/             # Modular CSS architecture
│   │   ├── base.css     # Core variables & typography
│   │   ├── layout.css   # Containers, grids & structure
│   │   ├── components.css # Buttons, forms, badges
│   │   ├── effects.css  # Animations & glassmorphism
│   │   └── isotone.css  # Main import file
│   ├── js/              # Global JavaScript files
│   └── scripts/         # PHP include scripts
├── iso-content/         # User-generated content
│   ├── plugins/         # Installed plugins
│   ├── themes/          # Installed themes
│   └── uploads/         # Media uploads
├── iso-runtime/         # System-generated files
│   ├── cache/           # Page cache, compiled templates
│   ├── logs/            # Application logs
│   └── temp/            # Temporary files
├── config/              # Configuration files
├── docs/                # LLM/Technical documentation (don't reorganize)
├── user-docs/           # User-facing documentation (well organized)
├── install/             # Installation wizard (delete after setup)
│   └── index.php        # Web-based installer
├── scripts/             # Automated/IDE scripts
├── vendor/              # Composer dependencies
├── config.php           # Main configuration (create from config.sample.php)
├── config.sample.php    # Configuration template
├── index.php            # Main entry point
├── .htaccess            # Security & routing
├── composer.json        # PHP dependencies
└── isotone              # CLI tool
```

## 💻 Development

### Current Implementation Status

✅ **Completed:**
- Project structure and organization
- Composer configuration with dependencies
- Basic routing system (Symfony Routing)
- Environment configuration (.env support)
- Front controller pattern
- PSR-4 autoloading
- Development environment setup
- Modular CSS architecture with glassmorphism effects
- Installation wizard with database setup

🚧 **In Progress:**
- Database integration with RedBeanPHP
- Plugin/hook system
- Basic admin interface

📋 **Planned:**
- Complete admin dashboard
- Theme system
- REST API endpoints
- Media management
- Caching system
- CLI tools

### Getting Started with Development

See our comprehensive guides:
- [Development Environment Setup](docs/DEVELOPMENT-SETUP.md) - Set up XAMPP, Composer, and tools
- [Getting Started Guide](docs/GETTING-STARTED.md) - Learn Isotone basics and create your first plugin

### Running Tests

```bash
# Run all tests
composer test

# Check code style
composer check-style

# Static analysis
composer analyse
```

## 📚 Documentation

### 📖 User Documentation
**Well-organized guides for humans:** See [`user-docs/README.md`](user-docs/README.md)
- Installation & Setup Guides
- Configuration Documentation  
- Developer Tutorials
- API Reference
- User Guides

### 🤖 Technical Documentation
**For LLMs and automated tools:** [`docs/`](docs/) (do not reorganize)
- [Configuration Guide](docs/CONFIGURATION.md) - Complete config.php documentation
- [Development Environment Setup](docs/DEVELOPMENT-SETUP.md) - Complete setup guide for Windows/Mac/Linux
- [Getting Started Guide](docs/GETTING-STARTED.md) - Learn the basics and build your first plugin
- [Technology Stack Specification](docs/ISOTONE-TECH-STACK.md) - Detailed technical architecture
- [CSS Architecture Guide](#css-architecture) - Modular CSS system documentation

### Coming Soon
- Installation Guide for Production
- User Manual
- API Reference
- Advanced Plugin Development
- Theme Development Guide
- Database Migration Guide

## 🤝 Contributing

We welcome contributions to Isotone!

### How You Can Help

- **Code Contributions** - Help implement core features
- **Documentation** - Improve guides and API docs
- **Testing** - Report bugs and write tests
- **Plugins/Themes** - Create extensions for the ecosystem
- **Ideas** - Suggest features and improvements

### Development Workflow

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and code style checks
5. Submit a pull request

See [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines.

## 🔒 Security

### Reporting Security Issues

**Please do not report security vulnerabilities through public GitHub issues.**

Email: security@rizonetech.com

We'll respond within 48 hours and work with you to understand and resolve the issue.

### Planned Security Features

- Prepared statements for all database queries
- CSRF protection on all forms
- XSS prevention via automatic output escaping
- File upload validation and sanitization
- Built-in rate limiting
- Two-factor authentication support

## 📊 Performance Targets

Target benchmarks for Isotone on a $5/month shared hosting plan:

| Metric | Target | vs WordPress (estimated) |
|--------|---------|-----------|
| First Paint | < 1s | ~60% faster |
| Time to Interactive | < 1.5s | ~65% faster |
| Memory Usage | < 15MB | ~70% less |
| Database Queries | < 10 | ~60% fewer |

## 🗺️ Development Roadmap

### Phase 1: Foundation (Current)
- 📋 Project planning and documentation
- 📋 Technical architecture design
- 📋 Development environment setup

### Phase 2: Core Implementation
- 📋 Basic CMS functionality
- 📋 Plugin/theme system
- 📋 Admin interface
- 📋 Content management

### Phase 3: Version 1.0 Features
- 📋 REST API
- 📋 Markdown support
- 📋 Media management
- 📋 Caching system

### Future Versions
- GraphQL API
- Advanced custom fields
- Multilingual support
- E-commerce capabilities

## 🎨 CSS Architecture

Isotone uses a **modular and reusable CSS system** that eliminates duplication between pages. The architecture is organized into specialized files:

### CSS File Structure
- **`base.css`** - Core variables, typography, and resets
- **`layout.css`** - Containers, grids, backgrounds, and structural elements
- **`components.css`** - Buttons, forms, badges, cards, and UI components
- **`effects.css`** - Animations, transitions, and glassmorphism effects
- **`isotone.css`** - Main file that imports all modules

### Key Features
- **CSS Custom Properties** - Consistent theming with CSS variables
- **Glassmorphism Effects** - Modern frosted glass aesthetic
- **Responsive Design** - Mobile-first approach with breakpoints
- **BEM-like Naming** - Prefixed classes (`iso-`) for namespace isolation
- **Zero Duplication** - Shared styles across all pages
- **Page-Specific Overrides** - Minimal inline styles only when necessary

### Usage Example
```html
<!-- Include the main CSS file -->
<link rel="stylesheet" href="/iso-includes/css/isotone.css">

<!-- Use modular classes -->
<div class="iso-container iso-glass">
    <h1 class="iso-title">Isotone</h1>
    <button class="iso-btn iso-btn-arrow">Get Started</button>
</div>
```

### 🚫 IMPORTANT: No Inline CSS Rule
**For developers and LLM IDEs:** ALWAYS search for existing styles in the modular CSS system before creating new styles. If a style doesn't exist, evaluate if it can be made modular and reusable before resorting to inline CSS. We maintain a strict **NO INLINE CSS** policy to ensure consistency and maintainability.

## 🙏 Planned Technologies

Isotone will leverage these excellent open-source projects:

- [RedBeanPHP](https://redbeanphp.com) - Elegant ORM
- [Alpine.js](https://alpinejs.dev) - Lightweight reactivity
- [HTMX](https://htmx.org) - HTML-first interactions
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS
- [Lucide Icons](https://lucide.dev) - Beautiful icons

## 📜 License

Isotone is open-source software licensed under the MIT License.

Copyright © 2025 Rizonetech (Pty) Ltd. All rights reserved.

## 💬 Get Involved

While the project is in planning phase, you can:
- Watch this repository for updates
- Share your ideas and feedback
- Help shape the future of Isotone

---

<div align="center">
  
**A vision for modern content management**

Designed by developers, for developers who ship to production.

© 2025 [Rizonetech (Pty) Ltd](https://rizonetech.com). All rights reserved.

</div>
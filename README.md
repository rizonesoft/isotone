# Isotone CMS

> **Project Status:** 🚧 Early Development - Core foundation implemented

## Why Isotone?

**Isotone** (*/ˈaɪsətoʊn/*) takes its name from the mathematical concept of an isotone (order-preserving) function—a transformation that maintains structure and relationships while allowing for growth and change. Just like its mathematical namesake, Isotone CMS will preserve what matters (simplicity, reliability, compatibility) while transforming how modern content management should work.

In physics, isotonic means "equal tension"—and that's exactly what Isotone aims to bring to web development: perfect balance between power and simplicity, features and performance, flexibility and structure.

## 🚀 Planned Features

### Built for Real-World Hosting
- **Shared hosting compatible** - Works on any standard PHP hosting
- **Lightweight core** - Under 10MB base installation
- **Resource efficient** - Runs smoothly on 128MB RAM
- **No Node.js required** - Pure PHP for maximum compatibility

### Modern Developer Experience
- **PSR standards compliant** - Clean, maintainable code
- **Composer-based** - Modern dependency management
- **RESTful API** - Headless CMS capabilities built-in
- **Markdown native** - Write content the way developers love
- **Git-friendly** - Version control your content and configuration

### Thoughtful Architecture
- **Hook-based plugin system** - Familiar to WordPress developers
- **Template inheritance** - DRY theme development
- **Alpine.js + HTMX** - Modern reactivity without the bloat
- **Tailwind CSS** - Utility-first styling for the admin interface

### Performance First
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

For detailed requirements and technology stack, see [Technology Stack Documentation](docs/isotone-tech-stack.md).

## 🎯 Quick Start

### Prerequisites
- PHP 8.3 or higher
- MySQL/MariaDB
- Apache with mod_rewrite
- Composer

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/isotone/isotone.git
   cd isotone
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

4. **Access the site**
   ```
   http://localhost/isotone/
   ```

For detailed setup instructions, see the [Development Setup Guide](docs/development-setup.md).

## 🏗️ Project Structure

```
isotone/
├── app/                  # Core application code
│   ├── Core/            # Core CMS functionality
│   ├── Http/            # Controllers and middleware
│   ├── Models/          # Data models
│   └── Services/        # Business logic
├── content/             # User content (gitignored)
│   ├── uploads/         # Media files
│   ├── cache/           # Cache files
│   └── backups/         # Backup files
├── plugins/             # Installed plugins
├── themes/              # Installed themes
├── public/              # Web root
│   ├── index.php        # Front controller
│   ├── admin/           # Admin assets
│   └── assets/          # Public assets
├── config/              # Configuration files
├── docs/                # Documentation
│   └── isotone-tech-stack.md
├── storage/             # Logs and temporary files
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
- [Development Environment Setup](docs/development-setup.md) - Set up XAMPP, Composer, and tools
- [Getting Started Guide](docs/getting-started.md) - Learn Isotone basics and create your first plugin

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

### Available Now
- [Development Environment Setup](docs/development-setup.md) - Complete setup guide for Windows/Mac/Linux
- [Getting Started Guide](docs/getting-started.md) - Learn the basics and build your first plugin
- [Technology Stack Specification](docs/isotone-tech-stack.md) - Detailed technical architecture

### Coming Soon
- Installation Guide for Production
- User Manual
- API Reference
- Advanced Plugin Development
- Theme Development Guide
- Database Migration Guide

## 🤝 Contributing

We welcome contributions to Isotone CMS!

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

Email: security@isotone.tech

We'll respond within 48 hours and work with you to understand and resolve the issue.

### Planned Security Features

- Prepared statements for all database queries
- CSRF protection on all forms
- XSS prevention via automatic output escaping
- File upload validation and sanitization
- Built-in rate limiting
- Two-factor authentication support

## 📊 Performance Targets

Target benchmarks for Isotone CMS on a $5/month shared hosting plan:

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

## 🙏 Planned Technologies

Isotone will leverage these excellent open-source projects:

- [RedBeanPHP](https://redbeanphp.com) - Elegant ORM
- [Alpine.js](https://alpinejs.dev) - Lightweight reactivity
- [HTMX](https://htmx.org) - HTML-first interactions
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS
- [Lucide Icons](https://lucide.dev) - Beautiful icons

## 📜 License

Isotone CMS will be open-source software licensed under the MIT License.

## 💬 Get Involved

While the project is in planning phase, you can:
- Watch this repository for updates
- Share your ideas and feedback
- Help shape the future of Isotone CMS

---

<div align="center">
  
**A vision for modern content management**

Designed by developers, for developers who ship to production.

</div>
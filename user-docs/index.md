# Isotone Documentation

> Modern PHP CMS with WordPress-compatible hooks and native theming API

## What is Isotone?

Isotone is a lightweight, modern content management system built with PHP that provides:

- 🎨 **Native Theme API** - WordPress-compatible functions without WordPress
- 🔌 **Plugin System** - Familiar hooks with `iso_` prefix
- 📦 **No Build Steps** - Works on any PHP hosting
- 🚀 **Modern Architecture** - PSR-12, Composer, RedBeanPHP ORM

## Quick Start

```bash
# Clone the repository
git clone https://github.com/rizonesoft/isotone.git

# Install dependencies
composer install

# Configure database
cp config.sample.php config.php

# Run installation
Visit http://localhost/isotone/install/
```

## Documentation Sections

### Getting Started
- [Installation Guide](./getting-started/installation.md)
- [Configuration](./getting-started/configuration.md)
- [First Steps](./getting-started/first-steps.md)

### For Developers
- [Theme Development](./developers/themes.md)
- [Plugin Development](./developers/plugins.md)
- [Hooks Reference](./developers/hooks.md)
- [Template Functions](./developers/template-functions.md)

### API Reference
- [Theme API](./api/theme-api.md)
- [Content API](./api/content-api.md)
- [Database Models](./api/models.md)
- [REST Endpoints](./api/rest.md)

### User Guide
- [Admin Dashboard](./guide/admin.md)
- [Managing Content](./guide/content.md)
- [Themes & Appearance](./guide/themes.md)
- [Plugins](./guide/plugins.md)

## Version

Current Version: **0.1.2-alpha**

## License

Isotone is open source software licensed under the MIT License.
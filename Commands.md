# Isotone Commands Reference

This document lists all available commands in Isotone, including Composer scripts and CLI commands.

## Table of Contents
- [Composer Commands](#composer-commands)
- [Isotone CLI Commands](#isotone-cli-commands)
- [Development Workflow](#development-workflow)

---

## Composer Commands

### Installation & Dependencies
```bash
composer install              # Install all dependencies (including dev)
composer install --no-dev     # Install production dependencies only
composer update               # Update all dependencies
composer update --no-dev      # Update production dependencies only
composer dump-autoload        # Regenerate autoloader
composer dump-autoload -o     # Regenerate optimized autoloader
```

### Testing
```bash
composer test                 # Run all PHPUnit tests
composer test:unit            # Run unit tests only
composer test:integration     # Run integration tests only
```

### Code Analysis
```bash
composer analyse              # Run PHPStan analysis
composer analyse:core         # Analyze iso-core directory
composer analyse:admin        # Analyze iso-admin directory
composer analyse:all          # Analyze entire codebase
composer analyse:strict       # Run analysis at level 8 (strictest)
```

### Code Style
```bash
composer check-style          # Check PSR-12 compliance
composer fix-style            # Automatically fix PSR-12 issues
composer pre-commit           # Run pre-commit checks (runs check-style)
```

### Documentation
```bash
composer docs:check           # Check documentation status
composer docs:update          # Update documentation
composer docs:index           # Generate documentation index
composer docs:lint            # Lint documentation files
composer docs:build           # Build HTML documentation
composer docs:html            # Alias for docs:build
composer docs:hooks           # Generate hooks documentation
composer docs:all             # Run all documentation tasks
```

### Hooks System
```bash
composer hooks:docs           # Generate hooks documentation
composer hooks:scan           # Scan codebase for hook usage
```

### IDE & Development
```bash
composer ide:sync             # Sync IDE configuration
composer validate:rules       # Validate automation rules
```

### Version Management
```bash
composer version:patch        # Bump patch version (0.1.0 -> 0.1.1)
composer version:minor        # Bump minor version (0.1.0 -> 0.2.0)
composer version:major        # Bump major version (0.1.0 -> 1.0.0)
```

### Tailwind CSS
```bash
composer tailwind:build       # Build Tailwind CSS
composer tailwind:watch       # Watch and rebuild on changes
composer tailwind:minify      # Minify CSS output
composer tailwind:install     # Install Tailwind dependencies
composer tailwind:update      # Update Tailwind version
composer tailwind:status      # Check Tailwind status
```

---

## Isotone CLI Commands

All Isotone CLI commands are run using: `php isotone <command> [options]`

### Version Commands
```bash
php isotone version                    # Show current version information
php isotone version:check              # Check system compatibility
php isotone version:history            # Show version history
php isotone version:bump [type]        # Bump version number
php isotone version:set <version>      # Set version directly
```

#### Version Bump Examples
```bash
php isotone version:bump patch                    # 0.1.0 -> 0.1.1
php isotone version:bump minor beta               # 0.1.0 -> 0.2.0-beta
php isotone version:bump major stable "Phoenix"   # 0.1.0 -> 1.0.0 (Phoenix)
```

#### Version Set Example
```bash
php isotone version:set 1.0.0-rc1 "Phoenix"       # Set to specific version
```

### Changelog
```bash
php isotone changelog          # Generate and save CHANGELOG.md
```

### Database Commands
```bash
php isotone db:test            # Test database connection
php isotone db:status          # Show database status
php isotone db:init            # Initialize database schema
```

### Migration Commands
```bash
php isotone migrate            # Run database migrations
php isotone migrate:status     # Check migration status
php isotone migrate:rollback   # Rollback last migration batch
```

### Hooks Commands
```bash
php isotone hooks:scan         # Scan codebase for hook usage
php isotone hooks:docs         # Generate hook documentation
php isotone hooks:validate     # Validate hook implementation
php isotone hooks:list         # List all registered hooks
php isotone hooks:test         # Test hook system
```

### Icon Commands
```bash
php isotone icons:gallery      # Generate HTML icon gallery documentation
```

### Help
```bash
php isotone help               # Show help message with all commands
```

---

## Development Workflow

### Setting Up Development Environment
```bash
# Clone repository
git clone https://github.com/rizonesoft/isotone.git
cd isotone

# Install all dependencies (including dev tools)
composer install

# Copy configuration
cp config.sample.php config.php
# Edit config.php with your database credentials

# Initialize database
php isotone db:init
```

### Before Committing Code
```bash
# Check code style
composer check-style

# Run static analysis
composer analyse

# Run tests
composer test

# Or run all pre-commit checks
composer pre-commit
```

### Building for Production
```bash
# Install production dependencies only
composer install --no-dev --optimize-autoloader

# Build and minify CSS
composer tailwind:minify

# Generate optimized autoloader
composer dump-autoload -o
```

### Documentation Tasks
```bash
# Update all documentation
composer docs:all

# Generate hooks documentation after adding new hooks
composer hooks:scan
composer hooks:docs

# Build HTML documentation
composer docs:build
```

### Version Release Process
```bash
# Bump version (example: patch release)
composer version:patch

# Generate changelog
php isotone changelog

# Commit changes
git add .
git commit -m "Release v0.1.1"
git tag v0.1.1
git push origin main --tags
```

---

## Notes

### Production Deployment
When deploying to production, always use:
```bash
composer install --no-dev --optimize-autoloader
```
This excludes development dependencies (PHPStan, PHPUnit, CodeSniffer) and creates an optimized autoloader.

### Configuration Files
- **composer.json** - Located in project root
- **phpstan.neon** - Located in `iso-development/`
- **config.php** - Located in project root (create from config.sample.php)

### Directory Structure
- **vendor/** - All Composer dependencies
- **iso-development/** - Development tools and configurations
- **iso-core/** - Core application code
- **iso-admin/** - Admin panel
- **iso-api/** - API endpoints
- **iso-includes/** - Shared includes and utilities

### Running Commands from Different Directories
Most commands should be run from the project root. If you're in a subdirectory, navigate back to the root:
```bash
cd /path/to/isotone
composer [command]
```

---

*Last updated: 2025-08-25*
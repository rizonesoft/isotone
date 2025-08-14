# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) and other LLMs when working with code in this repository.

## ⚠️ CRITICAL: This is an LLM-Driven Project

**Isotone CMS is primarily developed by AI assistants. Read the LLM-specific guides FIRST:**
- 📖 [`docs/LLM-DEVELOPMENT-GUIDE.md`](docs/LLM-DEVELOPMENT-GUIDE.md) - Essential rules for LLM developers
- 📏 [`docs/AI-CODING-STANDARDS.md`](docs/AI-CODING-STANDARDS.md) - Coding standards for AI
- 💬 [`docs/PROMPT-ENGINEERING-GUIDE.md`](docs/PROMPT-ENGINEERING-GUIDE.md) - How to write prompts for this project

## ⚠️ AUTO-COMMIT ON SATISFACTION
**CRITICAL**: When user says "perfect", "thanks", "good", "excellent", "happy with" - IMMEDIATELY:
1. Run: `git add -A && git commit -m "description" && git push`
2. Don't wait for explicit commit command
3. Common phrases: "That's perfect thanks", "Looks good", "Happy with that"
4. See `docs/AI-SATISFACTION-TRIGGERS.md` for full list

## Project Overview

Isotone CMS is a lightweight PHP content management system in early development. It features a WordPress-like plugin system, RedBeanPHP ORM, and is designed for shared hosting compatibility.

### Key Constraints for LLMs
- **NO Node.js/npm** - Pure PHP project
- **NO build steps** - Direct file editing only  
- **NO migrations** - RedBeanPHP handles schema
- **XAMPP/shared hosting** - Must work on basic hosting
- **PSR-12 standards** - Follow PHP-FIG standards
- **Security first** - Use .htaccess to protect sensitive files/directories

## Current Project State

**Early Development Phase** - Core foundation implemented:
- ✅ Basic routing system with Symfony components
- ✅ Environment configuration (.env support)
- ✅ PSR-4 autoloading and project structure
- ✅ Composer dependencies installed
- ✅ Modern UI design system (dark theme with glassmorphism)
- ✅ Database layer (RedBeanPHP) - connected and initialized
- ✅ Installation wizard for initial setup
- 🚧 Plugin system - in progress
- 🚧 Admin panel - in progress

### Database Notes
- Tables: `isotonesetting`, `isotoneuser`, `isotonecontent`
- Settings columns: `setting_key`, `setting_value`, `setting_type` (avoid MySQL reserved words)
- User columns: `username`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`

### Design System
- **Theme**: Modern dark with electric cyan (#00D9FF) and neon green (#00FF88) accents
- **Typography**: Inter font with refined letter spacing (0.01em - 0.08em)
- **Effects**: Static gradients, glassmorphism, subtle animations
- **Logo**: Custom SVG with gradient effects, left-aligned header
- **Favicon**: 512px PNG with web manifest for PWA support
- **SEO**: Full meta tags, Open Graph, Twitter Cards
- **Config**: See `config/theme.php` for color palette and design tokens

### Documentation
- `README.md` - Project overview and quick start
- `docs/development-setup.md` - Complete setup guide for XAMPP and other environments
- `docs/getting-started.md` - Tutorial for developers new to Isotone
- `docs/isotone-tech-stack.md` - Technical architecture specification

## Development Environment

- **XAMPP for Windows 11** (WSL environment)
- Web root: `/mnt/c/xampp/htdocs/isotone`
- Access via: `http://localhost/isotone`
- Database: `isotone_db` (created in phpMyAdmin)
- Installation: `http://localhost/isotone/install/`

## Planned Architecture

### Technology Stack (Planned)
- **PHP 8.3+** with PSR standards
- **MariaDB/MySQL** via XAMPP
- **RedBeanPHP** for ORM
- **Alpine.js + HTMX** for frontend interactivity
- **Tailwind CSS** for admin styling

### Core Concepts (Planned)
- WordPress-like plugin system using hooks/filters
- Theme system with template hierarchy
- REST API at `/api/v1/`
- Markdown-native content editing

### Project Structure (Current)
```
isotone/
├── app/             # Core application
│   ├── Core/        # CMS functionality
│   ├── Commands/    # CLI commands
│   ├── Models/      # Data models
│   └── Services/    # Business logic
├── iso-admin/       # Admin panel (coming soon)
├── iso-includes/    # Shared resources
│   ├── assets/      # Images, logos, icons
│   ├── css/         # Global CSS
│   ├── js/          # Global JavaScript
│   └── scripts/     # PHP includes
├── iso-content/     # User content
│   ├── plugins/     # Installed plugins
│   ├── themes/      # Installed themes
│   ├── uploads/     # Media uploads
│   └── cache/       # Cache files
├── config/          # Configuration
├── docs/            # Documentation
├── install/         # Installation wizard
├── scripts/         # Build/IDE scripts
├── storage/         # Logs and temp files
├── vendor/          # Composer dependencies
├── index.php        # Main entry point
├── .htaccess        # Security & routing
└── .env             # Environment config
```

Note: The `/public` folder was removed to simplify routing. Everything now runs from the root directory.

## Key Development Commands

```bash
# Install/update dependencies
composer install
composer update

# Run tests
composer test

# Check code style
composer check-style
composer fix-style

# Static analysis
composer analyse

# Documentation maintenance (CRITICAL for LLMs!)
composer docs:check    # Check if docs match code
composer docs:update   # Auto-generate some docs

# Start development (visit http://localhost/isotone/)
# No PHP built-in server needed - uses XAMPP Apache
```

## 📚 Documentation Maintenance (REQUIRED!)

**Isotone uses automated documentation checking.** As an LLM, you MUST:

### When Adding Features:
1. Update README.md status (change 🚧 to ✅ when done)
2. Update relevant docs in `/docs`
3. Add new env vars to `.env.example`
4. Document new routes/endpoints
5. Run `composer docs:check` to verify

### When Changing Code:
1. Update file references in docs if files moved/renamed
2. Update code examples if APIs changed
3. Update CLAUDE.md if new patterns introduced
4. Check for outdated information

### Before Completing Task:
```bash
# ALWAYS run this before saying you're done:
composer docs:check

# If it shows errors, fix them!
# If it shows warnings, evaluate if they need fixing
```

### Documentation Files That Often Need Updates:
- `README.md` - Feature status, installation steps
- `CLAUDE.md` - This file, new patterns/rules
- `.env.example` - New environment variables
- `docs/getting-started.md` - New features, examples
- `docs/LLM-DEVELOPMENT-GUIDE.md` - New patterns for AI
- `composer.json` - New commands/dependencies

## Current Implementation Files

### Core System
- `app/Core/Application.php` - Main application class with routing
- `app/Services/DatabaseService.php` - Database connection management
- `app/helpers.php` - Global helper functions
- `index.php` - Front controller entry point (in root)
- `install/index.php` - Installation wizard

### Configuration
- `.env` - Environment variables (copy from .env.example)
- `composer.json` - PHP dependencies and autoloading

## Next Implementation Steps

1. ~~Complete database integration with RedBeanPHP~~ ✅
2. ~~Create installation wizard~~ ✅
3. Implement hook/filter system for plugins
4. Create basic admin authentication
5. Build admin dashboard UI
6. Develop theme system with template hierarchy
7. Add REST API endpoints
8. Create CLI tool for common tasks

## 🤖 Quick LLM Task Reference

### Before Starting ANY Task:
1. Read this file completely
2. Check `docs/LLM-DEVELOPMENT-GUIDE.md`
3. Review existing code patterns in `app/Core/`
4. Verify changes work with XAMPP
5. **Run `composer docs:check`** to ensure docs are current

### Common Tasks for LLMs:

**Add a new page/route:**
- Edit `app/Core/Application.php::initializeRoutes()`
- Add handler method in same class
- Return `Response` object with HTML

**Create a model:**
- Add to `app/Models/`
- Extend `\RedBeanPHP\SimpleModel`
- Name as `Model_[tablename]`

**Add a plugin:**
- Create in `iso-content/plugins/[plugin-name]/`
- Use WordPress-style hooks
- No npm/build required

**Fix a bug:**
- Read error carefully
- Check `.env` configuration
- Test with XAMPP paths
- Escape all output

### ⛔ NEVER Do These:
- Run `npm install` or any npm command
- Create database migrations
- Use Laravel/Symfony patterns
- Add complex build processes
- Assume root URL (always `/isotone/`)
- Commit `.env` file
- Modify `vendor/` directory

### ✅ ALWAYS Do These:
- Follow PSR-12 standards
- Add PHPDoc comments
- Escape HTML output
- Use RedBeanPHP for database
- Test on `/isotone/` URL
- Keep shared hosting compatible
- **Update ALL affected documentation**
- **Run `composer docs:check` before finishing**
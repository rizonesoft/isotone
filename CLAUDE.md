# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) and other LLMs when working with code in this repository.

## ⚠️ CRITICAL: This is an LLM-Driven Project

**Isotone is primarily developed by AI assistants. Read the LLM-specific guides FIRST:**
- 📖 [`docs/LLM-DEVELOPMENT-GUIDE.md`](docs/LLM-DEVELOPMENT-GUIDE.md) - Essential rules for LLM developers
- 📏 [`docs/AI-CODING-STANDARDS.md`](docs/AI-CODING-STANDARDS.md) - Coding standards for AI
- 💬 [`docs/PROMPT-ENGINEERING-GUIDE.md`](docs/PROMPT-ENGINEERING-GUIDE.md) - How to write prompts for this project
- 🔧 [`docs/LLM-CONFIG-RULES.md`](docs/LLM-CONFIG-RULES.md) - **CRITICAL: Config.php rules (NO .env files!)**

## 🔍 SEARCH BEFORE CREATING - MANDATORY

**CRITICAL**: Before creating ANY new code, styles, or files, you MUST:

1. **SEARCH for existing implementations**:
   - Use `Grep` to search for similar functionality
   - Use `Glob` to find related files
   - Check `/iso-includes/css/` for existing styles
   - Review similar pages/components that might have reusable code

2. **REUSE over CREATE**:
   - Found a similar class/function? Extend or modify it
   - Found similar CSS? Use existing classes
   - Found a pattern? Follow it consistently

3. **NEVER duplicate**:
   - Don't create new CSS if it exists in modular files
   - Don't write new functions if similar ones exist
   - Don't create new files if existing ones can be extended

**VIOLATION**: Creating duplicate code without searching first is a critical error

## 🗄️ DATABASE OPERATIONS - USE REDBEAN ONLY

**MANDATORY**: ALL database operations MUST use RedBeanPHP. NEVER use PDO, mysqli, or raw SQL directly.

### ✅ ALWAYS Use RedBeanPHP:
```php
// CORRECT - Using RedBeanPHP
use RedBeanPHP\R;
$user = R::findOne('isotoneuser', 'username = ?', [$username]);
$post = R::dispense('post');
R::store($post);
```

### ❌ NEVER Use Direct Database Access:
```php
// WRONG - Direct PDO
$pdo = new PDO(...);
$stmt = $pdo->prepare("SELECT * FROM users");

// WRONG - Direct MySQL
$conn = new mysqli(...);
$result = $conn->query("SELECT * FROM users");

// WRONG - Creating custom database classes
class Database { ... }
```

### RedBeanPHP Naming Rules:
1. **Table names**: Lowercase, NO underscores (✅ `isotoneuser` ❌ `isotone_user`)
2. **Column names**: Can use underscores (✅ `created_at`, `last_login`)
3. **Foreign keys**: Use `tablename_id` pattern
4. **Link tables**: RedBean creates these automatically for many-to-many

### Why RedBeanPHP:
- Zero configuration ORM
- Automatic schema evolution
- No migrations needed
- Perfect for shared hosting
- Consistent with project architecture

**VIOLATION**: Using any database method other than RedBeanPHP is a critical error

## 📝 NOTES & REMINDERS

**The user has a `NOTES.md` file for saving notes, reminders, and ideas.**
- When asked to "save a note" or "remember this", add it to NOTES.md
- Organize notes under the appropriate section
- Update the "Last updated" date when adding notes
- The file is tracked in git by default (can be ignored if needed)

## ⚠️ AUTO-COMMIT ON SATISFACTION
**CRITICAL**: When user says "perfect", "thanks", "good", "excellent", "happy with" - IMMEDIATELY:
1. Run: `git add -A && git commit -m "description" && git push`
2. Don't wait for explicit commit command
3. Common phrases: "That's perfect thanks", "Looks good", "Happy with that"
4. See `docs/AI-SATISFACTION-TRIGGERS.md` for full list

## Project Overview

Isotone is a lightweight PHP content management system in early development. It features a WordPress-like plugin system, RedBeanPHP ORM, and is designed for shared hosting compatibility.

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
- ✅ Configuration system using `config.php` (WordPress-style)
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

### Design System - CRITICAL SEPARATION

#### 🎨 STYLING ARCHITECTURE - NEVER CONFUSE THESE:

1. **ADMIN PAGES** (`/iso-admin/*`):
   - **Framework**: Tailwind CSS via CDN
   - **Style**: Clean, modern, utility-first
   - **Colors**: Gray-900 background, cyan/green accents
   - **DO**: Use Tailwind classes (bg-gray-900, text-cyan-400, etc.)
   - **DON'T**: Use glassmorphism or custom CSS

2. **AUTHENTICATION & FRONTEND** (login, install, public):
   - **Framework**: Custom modular CSS (`/iso-includes/css/`)
   - **Style**: Glassmorphism with backdrop-filter effects
   - **Classes**: `iso-` prefixed (iso-container, iso-btn, iso-title)
   - **DO**: Use existing modular CSS, search before creating
   - **DON'T**: Use Tailwind classes or create duplicate CSS

3. **CRITICAL RULES**:
   - **NEVER** mix Tailwind and custom CSS systems
   - **NEVER** duplicate existing CSS functionality
   - **ALWAYS** check which context you're in (admin vs frontend)
   - **ALWAYS** search `/iso-includes/css/` before creating styles
   - **ALWAYS** use the appropriate system for the context

- **Theme**: Modern dark with electric cyan (#00D9FF) and neon green (#00FF88) accents
- **Typography**: Inter font with refined letter spacing (0.01em - 0.08em)
- **Effects**: Static gradients, glassmorphism, subtle animations
- **Logo**: Custom SVG with gradient effects
- **Config**: See `config/theme.php` for color palette and design tokens

### 🎨 CSS Architecture - CRITICAL RULES

#### 🚫 NO INLINE CSS POLICY
**IMPORTANT**: Isotone maintains a strict **NO INLINE CSS** policy. As an LLM/IDE, you MUST:

1. **ALWAYS** search for existing styles in `/iso-includes/css/` before creating new styles
2. **CHECK** the modular CSS files in this order:
   - `base.css` - Variables, typography, resets
   - `layout.css` - Containers, grids, structural elements  
   - `components.css` - Buttons, forms, badges, UI components
   - `effects.css` - Animations, glassmorphism, transitions
3. **EVALUATE** if a new style can be made modular/reusable before creating it
4. **USE** existing classes with `iso-` prefix (e.g., `iso-container`, `iso-btn`, `iso-title`)
5. **ONLY** use inline styles for truly page-specific, one-off requirements
6. **PREFER** creating new classes in the appropriate CSS module over inline styles

#### CSS File Structure
```
iso-includes/css/
├── isotone.css      # Main import file - include this in pages
├── base.css         # CSS variables, fonts, resets
├── layout.css       # Page structure, containers, grids
├── components.css   # Reusable UI components
└── effects.css      # Animations and visual effects
```

#### Example Usage
```html
<!-- GOOD: Using modular CSS classes -->
<div class="iso-container iso-glass">
    <h1 class="iso-title">Welcome</h1>
    <button class="iso-btn iso-btn-arrow">Continue</button>
</div>

<!-- BAD: Inline styles (avoid!) -->
<div style="background: rgba(255,255,255,0.1); padding: 2rem;">
    <h1 style="color: #00D9FF;">Welcome</h1>
</div>
```

### Configuration System - CRITICAL FOR LLMs

#### 🔧 NO .ENV FILES - USE config.php
**IMPORTANT**: Isotone uses a traditional `config.php` file, NOT .env files.

1. **Configuration location**: `/config.php` in root directory
2. **Template file**: `/config.sample.php` (tracked in git)
3. **Actual config**: `/config.php` (ignored by git, contains credentials)

#### When setting up Isotone:
```bash
cp config.sample.php config.php
# Then edit config.php with database credentials
```

#### Key configuration constants:
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` - Database settings
- `DEBUG_MODE`, `DISPLAY_ERRORS` - Development settings
- `SITE_URL`, `ADMIN_EMAIL` - Application settings
- `ENVIRONMENT` - Current environment (development/staging/production)

#### NEVER:
- Create or reference .env files
- Use Dotenv package (it's been removed)
- Suggest environment variables for configuration

#### ALWAYS:
- Use `config.php` for all configuration
- Reference configuration via PHP constants (e.g., `DB_NAME`)
- Keep sensitive data in `config.php` (not tracked in git)

### Documentation
- `README.md` - Project overview and quick start
- `docs/DEVELOPMENT-SETUP.md` - Complete setup guide for XAMPP and other environments
- `docs/GETTING-STARTED.md` - Tutorial for developers new to Isotone
- `docs/ISOTONE-TECH-STACK.md` - Technical architecture specification

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
│   ├── css/         # Modular CSS architecture (NO INLINE CSS!)
│   ├── js/          # Global JavaScript
│   └── scripts/     # PHP includes
├── iso-content/     # User content (preserve during updates)
│   ├── plugins/     # Installed plugins
│   ├── themes/      # Installed themes
│   └── uploads/     # Media uploads
├── iso-runtime/     # System generated (safe to delete)
│   ├── cache/       # Page cache, compiled templates
│   ├── logs/        # Application logs
│   └── temp/        # Temporary files
├── config/          # Configuration
├── docs/            # LLM/Technical documentation (don't reorganize)
├── user-docs/       # User-facing documentation (well organized)
├── install/         # Installation wizard
├── scripts/         # Build/IDE scripts
├── vendor/          # Composer dependencies
├── config.php       # Main configuration (DO NOT COMMIT)
├── config.sample.php # Configuration template
├── NOTES.md         # User's notes and reminders
├── index.php        # Main entry point
└── .htaccess        # Security & routing
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
composer docs:sync     # Sync user-docs
composer docs:all      # Complete update + check

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
- `docs/GETTING-STARTED.md` - New features, examples
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

**Save a note or reminder:**
- Add to `NOTES.md` under appropriate section
- Update the "Last updated" date
- Keep notes organized and clear

### ⛔ NEVER Do These:
- **Never** run `npm install` or any npm command (pure PHP project)
- **Never** create database migrations (RedBeanPHP handles schema automatically)
- **Never** use Laravel/Symfony full framework patterns (this is lightweight)
- **Never** add complex build processes (must work on shared hosting)
- **Never** assume root URL (always use `/isotone/` path)
- **Never** commit `config.php` to git (it contains credentials)
- **Never** modify `vendor/` directory directly
- **Never** create or reference `.env` files (use config.php instead)

### ✅ ALWAYS Do These:
- Follow PSR-12 coding standards
- Add PHPDoc comments to functions/classes
- Escape HTML output for security
- Use RedBeanPHP for all database operations
- Test on `/isotone/` URL path
- Keep shared hosting compatible
- Use `config.php` for all configuration
- **Update ALL affected documentation when changing code**
- **Run `composer docs:check` before completing any task**

## IDE Integration
- `.windsurf-rules.md` - Windsurf IDE rules
- `.cursorrules` - Cursor IDE rules
- `.github/copilot-instructions.md` - GitHub Copilot

## Git Workflow
- **Pre-commit**: Runs `docs:check` only
- **Pre-push**: Runs `docs:update` for version changes
- **Manual**: Use `composer docs:all` for complete update

## Remember
- Keep code simple and maintainable
- Follow existing patterns
- Document everything
- Test on XAMPP
- No Node.js/npm dependencies
- Use `NOTES.md` for user's notes and reminders
# CLAUDE.md - Complete Isotone Development Rules & Guidelines

**THIS IS THE SINGLE SOURCE OF TRUTH** - Read ENTIRE file before ANY task

## 🎯 INTRODUCTION

Welcome to **Isotone** - a lightweight, secure, and developer-friendly CMS built for shared hosting environments. As an AI assistant working on this project, your mission is to maintain and enhance Isotone while strictly adhering to its core philosophy: **simplicity, security, and compatibility**.

Isotone is designed to be:
- **ACCESSIBLE** - Works on basic XAMPP/shared hosting without complex requirements
- **MAINTAINABLE** - Direct file editing, no build processes, clear code structure
- **SECURE** - Defense in depth with authentication, CSRF protection, and input validation
- **EFFICIENT** - Minimal dependencies, optimized queries, fast page loads
- **DEVELOPER-FRIENDLY** - WordPress-like hooks, familiar patterns, extensive documentation

## 🚀 YOUR MISSION

As an AI developer on the Isotone project, you are responsible for:

1. **READ THIS ENTIRE FILE(CLAUDE.md)** before starting any task - no exceptions
2. **SEARCH BEFORE CREATING** - Always look for existing solutions first
3. **FOLLOW ALL APPLICABLE RULES** - They ensure consistency and stability
4. **VALIDATE YOUR WORK** - Test changes, check syntax, verify functionality
5. **DOCUMENT AS YOU GO** - Update docs when changing features
6. **THINK SECURITY FIRST** - Every line of code must be secure
7. **PRESERVE SIMPLICITY** - Don't add complexity without clear benefit

Remember: You're not just writing code, you're crafting a system that needs to work reliably on thousands of different hosting environments, from high-end servers to basic shared hosting plans.

## 📁 PROJECT STRUCTURE

### File Inclusion Patterns
**Admin Files Pattern:**
```php
<?php
// REQUIRED includes for admin pages
require_once 'auth.php';  // ALWAYS FIRST (relative path)
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Database init if needed
if (!R::testConnection()) {
    isotone_db_connect();  // From /iso-includes/database.php
}
```

**NEVER USE:**
- `bootstrap.php` - Does not exist in Isotone
- Namespaces - Isotone does not use PHP namespaces
- `use` statements for Isotone classes

**Service Classes:**
```php
// Including a service class (no namespace)
require_once dirname(__DIR__) . '/iso-core/Services/ToniService.php';
$service = new ToniService();  // No namespace
```

### Directory Structure
```
/isotone/
├── iso-admin/          # Admin panel files
├── iso-content/        # User content (uploads, themes, plugins)
├── iso-includes/       # Core includes
│   ├── css/           # Modular CSS files
│   ├── js/            # JavaScript files
│   └── class-*.php    # Class files
├── iso-automation/     # Automation system
└── vendor/            # Composer dependencies
```

### File Naming
- PHP classes: `class-{name}.php` (e.g., `class-login-security.php`)
- CSS modules: `{feature}.css` (e.g., `dashboard-widgets.css`)
- JavaScript: `{feature}.js` (e.g., `chart-loader.js`)
- Templates: `{name}-template.php`

### File Inclusion Patterns
**PHP File Inclusion:**
```php
// CORRECT
require_once dirname(__DIR__) . '/config.php';
// WRONG
require_once '../config.php';
```

## 🗄️ WSL DATABASE CONNECTION GUIDE

### When to Connect Directly to MySQL
Connect directly to analyze structure, check data, or debug issues.

**IMPORTANT:** Due to Bash tool limitations, do this in TWO STEPS:
```bash
# STEP 1: Get the Windows host IP
ip route | grep default | awk '{print $3}'
# This returns something like: 182.190.245.1
# STEP 2: Use that IP to connect to MySQL
mysql -h [IP_FROM_STEP_1] -u root isotone_db
```

**For SQL queries via Bash:**
```bash
# STEP 1: Get the IP
ip route | grep default | awk '{print $3}'
# STEP 2: Run query using the IP
echo "SHOW TABLES;" | mysql -h [IP_FROM_STEP_1] -u root isotone_db
```

**WRONG - These will FAIL from WSL:**
```bash
mysql -h localhost -u root isotone_db        # FAILS - tries WSL's MySQL
mysql -h 127.0.0.1 -u root isotone_db        # FAILS - points to WSL
```

**Note:** The Windows host IP changes rarely but can vary between WSL restarts. Always get the current IP first.

### Safe Database Analysis Commands
```sql
-- SAFE: Read-only commands for analysis
SHOW TABLES;                               -- List all tables
DESCRIBE tablename;                        -- Show table structure
SELECT COUNT(*) FROM tablename;            -- Count records
SELECT * FROM tablename LIMIT 10;          -- Preview data
SHOW CREATE TABLE tablename;               -- See table creation SQL

-- DANGEROUS: NEVER run without asking first
DROP TABLE tablename;                      -- Deletes entire table
TRUNCATE tablename;                        -- Deletes all data
ALTER TABLE tablename ...;                 -- Changes structure
DELETE FROM tablename ...;                 -- Removes records
UPDATE tablename SET ...;                  -- Modifies data
```

### Database Connection and Usage Rules - ONLY APPLICABLE WHEN DEVELOPING AND TESTING BY LLM AI
- **ALWAYS** ask before DELETE, DROP, TRUNCATE, or ALTER operations
- **ALWAYS** use RedBeanPHP in PHP code (never raw SQL)
- **READ-ONLY** analysis is safe and encouraged
- **BACKUP** before any structural changes

## 🚨 CRITICAL RULES (NEVER VIOLATE)

- **NEVER** write "Isotone CMS" - just "Isotone" 
- **NEVER** skip authentication on admin pages
- **NEVER** use raw SQL - always RedBeanPHP -> [See Database Operations (RedBeanPHP Rules)](#database-operations-redbeanphp-rules)
- **NEVER** create database migrations - RedBeanPHP handles schema
- **NEVER** use namespaces - Isotone uses plain PHP classes
- **NEVER** skip auth.php on admin pages
- **NEVER** use inline CSS or style tags `<style>` tags
- **NEVER** add npm dependencies - Keep it pure PHP (except Tailwind)
- **NEVER** forget dark mode and light modestyles - Every UI element needs both variants
- **NEVER** use localhost for MySQL in WSL - Use Windows host IP instead
- **NEVER** use .env files - only config.php
- **NEVER** commit without user confirmation
- **NEVER** document unverified features
- **NEVER** load Alpine.js in `<head>`
- **NEVER** commit without user confirmation - **ALWAYS** ask first

- **ALWAYS** search before creating - **REUSE EXISTING CODE IF APPLICABLE** -> [See Search Before Create](#search-before-create)
- **ALWAYS** write "Isotone" and NOT "Isotone CMS" - It's just "Isotone" since v0.1.5 (This applies to ALL documentation, code, and UI)
- **ALWAYS** use Context7 for API docs, **NEVER** guess API endpoints -> [See API Documentation Consultation](#api-documentation-consultation)
- **ALWAYS** test in both light/dark modes
- **ALWAYS** use modular CSS files **NEVER** inline styles
- **ALWAYS** verify documentation accuracy
- **ALWAYS** populate version features array
- **ALWAYS** use `iso_` prefix for WP hooks
- **ALWAYS** escape HTML output
- **ALWAYS** follow PSR-12 standards

### Isotone Project Rules
- **Security first** - Escape all output, validate input, use CSRF tokens
- **Performance second** - Optimize for speed, minimize queries, cache when possible
- **NO build steps** - Direct file editing only, no webpack/gulp/compilation
- **NO Node.js/npm** - Pure PHP project (exception: Tailwind CSS build only)
- **NO migrations** - RedBeanPHP handles schema automatically
- **NO .env files** - Use config.php for all configuration
- **NO MVC pattern** - Direct page routing in /iso-admin/
- **NO Laravel/Symfony patterns** - Lightweight architecture only
- **NO complex dependencies** - Minimal vendor requirements
- **NO custom autoloaders** - Composer autoload only
- **NO namespaces** - Isotone uses plain PHP classes
- **XAMPP/shared hosting** - Must work on basic hosting
- **PSR-12 standards** - Follow PHP-FIG standards
- **WordPress-compatible hooks** - Use iso_ prefix for WP equivalents
- **Direct file access** - Each admin page is self-contained
- **URL path aware** - Must work in /isotone/ subdirectory
- **Mobile responsive** - All UI must work on mobile devices
- **Dark mode support** - Every UI element needs dark variant

### GPT-5 Models
- **NEVER** assume GPT-5 doesn't exist - it was released and is production-ready
- **NEVER** revert GPT-5 code to GPT-4 or older models
- GPT-5, GPT-5-mini, and GPT-5-nano are real, available models from OpenAI
- The /v1/responses endpoint is the correct API endpoint for GPT-5
- GPT-5 supports vision with input_text and input_image content types

### Database Operations (RedBeanPHP Rules)
- **MUST use RedBeanPHP for ALL database operations:**
- Tables are created automatically by RedBeanPHP
- **NO underscores or special characters** in table names
- Bean types(table names) **MUST** be lowercase letters only (a-z)
- Example: `R::dispense('userlist')` **NOT** `R::dispense('user_list')`
- Use snake_case in PHP: `$bean->user_name` not `$bean->userName`
- Database columns should be snake_case
- Columns can contain letters, numbers, and underscores
- Use `R::dispense()` to create beans
- Use `R::store()` to save
- Use `R::find()` and `R::findOne()` to query
- Use `R::trash()` to delete

- **ALWAYS** use `isotone_db_connect()` from `/iso-includes/database.php`
- **ALWAYS** use SINGULAR table names (user, post, comment)

- **NEVER** modify the connection logic
- **NEVER** use plural table names - ALWAYS singular (user, not users)
- **NEVER** use underscores in table names via R::dispense()
- **NEVER** use uppercase or special characters in bean types
- **NEVER** write raw SQL - ALWAYS use RedBeanPHP methods
- **NEVER** use PDO or mysqli directly

## 🔐 AUTHENTICATION & SECURITY

### Admin Authentication
**Every PHP file in `/iso-admin/` MUST:**
```php
<?php
# Admin page template
// Check authentication - ALWAYS FIRST
require_once 'auth.php';
requireRole('admin'); // or appropriate role
// Rest of the code...
```
**Exception:** Only `login.php` and `logout.php` don't require auth.php

**AJAX Handlers:**
- Must check session authentication
- Verify CSRF tokens with `iso_verify_csrf()`
- Return JSON responses with proper headers

### Security Implementation
- Use `LoginSecurity` class for login attempts
- Use `IsotoneSecurity` class for general security
- Record all login attempts (success and failure)
- Implement rate limiting and lockouts
- **NEVER** store passwords in plain text
- **ALWAYS** use password_hash() and password_verify()

## 📋 MANDATORY PRACTICES

### Search Before Create
- **ALWAYS** search for existing code before creating new - **REUSE over CREATE**
- Use `Grep` to search for similar functionality
- Use `Glob` to find related files
- Check `/iso-includes/css/` for existing styles

### API Documentation Consultation
- **ALWAYS** consult Context7 documentation for ANY external API
- Use `mcp__context7__resolve-library-id` first to get library ID
- Then use `mcp__context7__get-library-docs` with that ID
- **NEVER** guess API methods or parameters
- **NEVER** use outdated documentation from memory


## 🎨 CSS & STYLING RULES

### CSS Architecture
**MUST follow modular CSS system:**
- Styles go in `/iso-includes/css/modules/`
- One file per component/feature
- Use semantic naming (e.g., `dashboard-widgets.css`)
- **NEVER** use inline styles
- **NEVER** use `<style>` tags in PHP files

**Loading CSS:**
```php
// CORRECT
<link rel="stylesheet" href="../iso-includes/css/modules/component.css">

// WRONG
<style>.my-class { color: red; }</style>
```

### Tailwind CSS (v4.0.0-beta.8)
- Use Tailwind utility classes for styling; prefer composition over custom CSS.
- Version: Tailwind CSS v4.0.0-beta.8 with @tailwindcss/cli v4.0.0-beta.8.
- Content scanning uses Tailwind v4 `@source` directives defined in `tailwind-build/src/input.css`. If you add UI files in new locations, update those `@source` globs accordingly.
- Build via Composer scripts:
  - `composer tailwind:build` (production build)
  - `composer tailwind:minify` (minified build)
  - `composer tailwind:status` (status)
- Production: commit built CSS (`iso-admin/css/tailwind.css` or `iso-admin/css/tailwind.min.css`). The CDN fallback is for **DEVELOPMENT ONLY** and must not be relied on in production.
- Write custom CSS only when Tailwind cannot achieve the design. Place custom styles in modular files under `/iso-includes/css/` (never inline styles).

### Dark Mode (class strategy)
- Dark mode uses the class strategy. Apply the `dark` class on the root element (e.g., `<html class="dark">`) and use `dark:` variants for styles.
- Every component must provide dark variants for backgrounds, borders, text, and states.
- Prefer palette tokens and opacity utilities over hard-coded colors, e.g., `bg-white/80 dark:bg-gray-900/60`.
- Always test in both light and dark modes.
- Example patterns:
  - Container: `bg-white text-gray-900 dark:bg-gray-900 dark:text-gray-100`
  - Border: `border-gray-200 dark:border-gray-700`
  - Muted text: `text-gray-600 dark:text-gray-300`
  - Inputs: `bg-white dark:bg-gray-800 focus:ring-cyan-500 dark:focus:ring-cyan-400`
- Dev toggle example:
  ```js
  // Toggle dark mode on the root element (development/testing)
  document.documentElement.classList.toggle('dark')
  ```

## 🚀 JAVASCRIPT & ALPINE.JS

### Alpine.js Loading Rules
**MUST load Alpine.js in correct order:**
1. Load Alpine.js core FIRST
2. Load Alpine plugins
3. Initialize with `Alpine.start()`
4. All Alpine code must be inside `x-data` components

**Script Loading Order:**
```html
<!-- 1. Alpine Core -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<!-- 2. Your Alpine Components -->
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('componentName', () => ({
        // component logic
    }));
});
</script>
```

### Chart.js Integration
- Load Chart.js dynamically when needed
- Check if already loaded before adding script
- Disable animations to prevent DOM errors
- Use proper canvas sizing with containers
- Clean up charts on destroy

## 🔌 HOOKS & FILTERS

### Plugin Development
**Structure:** `/iso-content/plugins/[plugin-name]/`
**Main File:** `[plugin-name].php` with header:
```php
/**
 * Plugin Name: My Plugin
 * Description: Plugin description
 * Version: 1.0.0
 * Author: Author Name
 * License: GPL v2 or later
 */
```

**Best Practices:**
- Prefix all functions with plugin slug
- Use proper nonce verification
- Escape all output
- Validate and sanitize input

### Theme Development  
**Structure:** `/iso-content/themes/[theme-name]/`
**Required Files:**
- `style.css` - Theme stylesheet with header
- `index.php` - Main template file
- `functions.php` - Theme functions

**Template Hierarchy:**
- `single.php` - Single post
- `page.php` - Single page
- `archive.php` - Archive pages
- `search.php` - Search results
- `404.php` - Not found
- `index.php` - Fallback template

### Hook System
**WordPress-Compatible Hook System:**
- Use `iso_` prefix for WordPress equivalents
- Examples: `iso_init`, `iso_admin_menu`, `iso_enqueue_scripts`
- Register hooks with `add_action()` and `add_filter()`
- Document all hooks in `/docs/hooks/`

**Hook Naming Convention:**
```php
// CORRECT
do_action('iso_before_header');
apply_filters('iso_menu_items', $items);

// WRONG
do_action('before_header');  // Missing iso_ prefix
```

### Custom Hooks
- Plugin-specific hooks use `plugin_name_` prefix
- Theme hooks use `theme_` prefix
- Core hooks always use `iso_` prefix


## 🔧 CONFIGURATION

### Configuration Management
- Main config: `/config.php`
- **NEVER** use .env files
- **NEVER** commit sensitive data
- Use constants for configuration
- Database credentials in config.php only

**Config Structure:**
```php
// CORRECT
define('DB_HOST', 'localhost');
define('DB_NAME', 'isotone_db');

// WRONG
$db_host = 'localhost';  // Don't use variables
```

## 📝 GIT & VERSION MANAGEMENT

### Version Management Workflow
**MANDATORY steps for version bump:**
1. Determine version type (patch/minor/major)
2. Collect features and changes from git log
3. **POPULATE features array in version.json** (NEVER leave empty!)
4. Execute: `php isotone version:bump [type] [stage] [codename]`
5. Generate changelog: `php isotone changelog`
6. Update relevant documentation manually
7. Report completion with version number

**Intent Mapping:**
- Bug fixes → `patch`
- New features → `minor`  
- Breaking changes → `major`
- Beta release → `minor beta`
- Production → `major stable`

**Stage Progression:**
alpha → beta → rc → stable

**Codenames:** Genesis → Phoenix → Aurora → Titan → Nebula

### Git Standards
**Commit Messages:**
```
feat: Add user authentication system
fix: Resolve database connection issue
docs: Update API documentation
style: Format code according to standards
refactor: Restructure authentication logic
test: Add unit tests for login
chore: Update dependencies
```

**Auto-commit triggers:**
- User says "looks good", "perfect", "that works"
- Task completion confirmed
- User expresses satisfaction

### Version Management
**Version Format:** `MAJOR.MINOR.PATCH-STAGE`
- Example: `1.0.0-alpha`, `1.0.0-beta`, `1.0.0`
- Stage progression: alpha → beta → rc → stable
- Update version in `composer.json` and `config.php`

**Version Bump Process:**
1. Update version in `/composer.json`
2. Update version in `/config.php`
3. Update changelog
4. Commit with message: `chore: Bump version to X.Y.Z`

## 💻 LLM DEVELOPMENT GUIDELINES



### Task Patterns
**Add Route:**
1. Edit `app/Core/Application.php::initializeRoutes()`
2. Add handler method in same class
3. Return Response object with HTML

**Create Model:**
1. Add to `app/Models/`
2. Extend `\RedBeanPHP\SimpleModel`
3. Name as `Model_[tablename]`

**Add Plugin:**
1. Create in `iso-content/plugins/[plugin-name]/`
2. Use WordPress-style hooks with `iso_` prefix
3. No npm/build required

### NEVER Do These
- Never run npm install or any npm command
- Never create database migrations
- Never use Laravel/Symfony full framework patterns
- Never add complex build processes
- Never assume root URL (use /isotone/ path)
- Never commit config.php to git
- Never modify vendor/ directory
- Never create or reference .env files

## 🛠️ DEVELOPMENT WORKFLOW

### Task Workflow
1. **IDENTIFY** task type from user request
2. **SEARCH** for existing similar code
3. **CHECK** relevant rules in this file
4. **IMPLEMENT** following all applicable rules
5. **VALIDATE** using automation tools
6. **COMMIT** when user confirms satisfaction

### Validation Commands
```bash
# Validate all rules
php iso-automation/cli.php validate:all

# Check documentation
composer docs:check

# Analyze code
php iso-automation/cli.php analyze:code

# Generate documentation
php iso-automation/cli.php generate:docs
```

## 📚 DOCUMENTATION SYSTEM

### Documentation Structure
- **User Docs**: `/user-docs/` - All user and developer documentation
- **Key Files**: `CLAUDE.md`, `README.md`, `NOTES.md`, `CHANGELOG.md`
- **Note**: `/docs/` folder removed - all docs now in `/user-docs/`

### Documentation Accuracy Rules
**BEFORE writing ANY documentation:**
1. **VERIFY** with actual code using Read tool
2. **TEST** commands to confirm they work
3. **CHECK** file paths and directories exist
4. **VALIDATE** config options against actual files
5. **CONFIRM** external URLs are valid

**NEVER document without verification:**
- Features that don't exist
- Commands that don't work
- Incorrect file paths
- Untested installation steps

### Automated Documentation
- **Hooks Docs**: `composer docs:hooks` or `php isotone hooks:docs`
- **IDE Sync**: `composer ide:sync`

## 🧠 RULE MANAGEMENT META-RULES

### Before Modifying ANY Rules
1. **SEARCH** for existing related rules
2. **CHECK** for duplicates or overlaps
3. **VERIFY** no other rules reference the one being changed
4. **ENSURE** rule names are unique

### Common Mistakes to Avoid
- Adding duplicate rules without searching
- Leaving orphaned references after removing features
- Creating overlapping rules in different sections
- Not checking indirect references in nested structures

## 🏁 QUICK START CHECKLIST

### For ANY Task:
1. ✅ Read task type from user request
2. ✅ Search this file (Ctrl+F) for relevant section
3. ✅ Follow all applicable rules
4. ✅ Validate with automation tools if available

### Task-Specific Shortcuts:
- **Database Work**: Jump to "DATABASE RULES"
- **Admin Pages**: Jump to "AUTHENTICATION"
- **CSS Changes**: Jump to "CSS & STYLING"
- **API Work**: Jump to "API Documentation"
- **Git/Commits**: Jump to "GIT & VERSION"

## 🚫 NEVER FORGET THESE RULES

## 📚 DEVELOPMENT COMMANDS

### Composer Scripts
```bash
# Testing & Analysis
composer test           # Run all tests
composer analyse        # Static analysis with PHPStan
composer check-style    # Check PSR-12 compliance
composer fix-style      # Auto-fix code style

# Documentation
composer docs:hooks     # Generate hooks documentation
composer ide:sync       # Sync IDE rules

# Version Management
composer version:patch  # Bump patch version
composer version:minor  # Bump minor version
composer version:major  # Bump major version
```

### Isotone CLI
```bash
# Version Commands
php isotone version          # Show version info
php isotone version:bump     # Bump version
php isotone version:history  # Show history
php isotone changelog        # Generate CHANGELOG.md

# Database Commands
php isotone db:test          # Test connection
php isotone db:status        # Show status
php isotone db:init          # Initialize schema

# System Commands
php isotone system           # Check compatibility
php isotone hooks:docs       # Generate hook docs
```

## ⚡ MEMORY AIDS & PATTERNS


### Development Setup (XAMPP)
1. Install XAMPP for Windows (PHP 8.3+)
2. Clone to `C:\xampp\htdocs\isotone`
3. Create database `isotone_db` in phpMyAdmin
4. Copy `config.sample.php` to `config.php`
5. Edit `config.php` with credentials
6. Run `composer install`
7. Visit `http://localhost/isotone/install/`

**WSL Setup:**
- XAMPP runs on Windows
- Access via `/mnt/c/xampp/htdocs/isotone`
- Database auto-detects Windows host IP
- Use Windows browser for testing

### Common Workflows
1. **Version Bump**: `php isotone version:bump [type] [stage]` → Generate changelog → Update docs
2. **Add Route**: Edit `app/Core/Application.php::initializeRoutes()` → Add handler → Return Response
3. **Database Op**: Use `R::dispense()` → Set properties → `R::store()`
4. **Documentation**: Verify with Read → Test commands → Check paths → Write docs
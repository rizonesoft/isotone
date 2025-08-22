# CLAUDE.md - Isotone Development Rules & Guidelines
**⚠️ MANDATORY: READ ENTIRE FILE BEFORE ANY TASK - NO EXCEPTIONS ⚠️**

## 🚨 CRITICAL RULES - NEVER VIOLATE

### ❌ NEVER DO THIS (INSTANT FAILURES)
1.  **NEVER** write "Isotone CMS" - just "Isotone"
2.  **NEVER** skip `auth.php` on admin pages (except login.php/logout.php)
3.  **NEVER** use raw SQL - ALWAYS use RedBeanPHP
4.  **NEVER** create database migrations - RedBeanPHP handles schema
5.  **NEVER** use namespaces - Isotone uses plain PHP classes
6.  **NEVER** use inline CSS or `<style>` tags - use modular CSS files
7.  **NEVER** add npm dependencies - pure PHP (except Tailwind build)
8.  **NEVER** forget dark mode styles - every UI needs both light/dark
9.  **NEVER** use localhost for MySQL in WSL - use Windows host IP
10. **NEVER** use .env files - only config.php
11. **NEVER** commit without user confirmation
12. **NEVER** document unverified features
13. **NEVER** load Alpine.js in `<head>` - always defer
14. **NEVER** use Laravel/Symfony patterns - lightweight only
15. **NEVER** add complex build processes
16. **NEVER** modify vendor/ directory
17. **NEVER** use plural table names - ALWAYS singular
18. **NEVER** hardcode SVG icons - use Icon API (`iso_icon()`)
19. **NEVER** create API endpoints outside `/iso-api/` directory
20. **NEVER** create non-RESTful API endpoints - follow standards
21. **NEVER** skip CORS headers on API endpoints
22. **NEVER** create APIs without proper error handling

### ✅ ALWAYS DO THIS (MANDATORY)
1.  **ALWAYS** search before creating - REUSE existing code
2.  **ALWAYS** test in both light/dark modes
3.  **ALWAYS** use modular CSS files in `/iso-includes/css/`
4.  **ALWAYS** verify documentation accuracy
5.  **ALWAYS** use `iso_` prefix for WordPress-compatible hooks
6.  **ALWAYS** escape HTML output with `esc_html()`, `esc_attr()`, `esc_url()`
7.  **ALWAYS** follow PSR-12 standards
8.  **ALWAYS** use Icon API for icons (`iso_icon()`, `iso_get_icon()`)
9.  **ALWAYS** include auth.php FIRST in admin files
10. **ALWAYS** use RedBeanPHP for ALL database operations
11. **ALWAYS** use Context7 for external API documentation
12. **ALWAYS** update NOTES.md for reminders/todos
13. **ALWAYS** populate version features array
14. **ALWAYS** validate and sanitize all input
15. **ALWAYS** create API endpoints in `/iso-api/` directory only
16. **ALWAYS** include CORS headers on API endpoints
17. **ALWAYS** validate API parameters and return proper HTTP status codes
18. **ALWAYS** use RESTful conventions for API design
19. **ALWAYS** add caching headers to API responses

## 🎯 PRIMARY WORKFLOW - FOLLOW EVERY TIME

### For ANY Task - The 6-Step Process:
```
1. ✅ IDENTIFY — Clarify intent and scope
2. ✅ SEARCH — Use Grep to find existing code (REUSE > CREATE)
3. ✅ CHECK — Verify all rules apply (especially NEVER/ALWAYS lists)
4. ✅ IMPLEMENT — Make minimal changes with proper patterns
5. ✅ VALIDATE — Run `composer analyse` and `composer check-style`
6. ✅ DOCUMENT — Update docs as specified below
```

### Documentation Updates - WHERE & WHEN
**When code changes, update these files:**
- `README.md` — feature changes, install steps
- `CHANGELOG.md` — user-visible changes
- `HOOKS.md` — if hooks changed
- `NOTES.md` — for reminders, todos, plans
- `user-docs/development/project-structure.md` — directory changes
- `user-docs/development/commands.md` — new CLI commands
- `user-docs/configuration/config-guide.md` — config variables
- `user-docs/icons/` — icon library changes

## 📁 REQUIRED FILE PATTERNS

### Admin Page Template (MEMORIZE THIS)
```php
<?php
// THIS PATTERN IS MANDATORY FOR ALL ADMIN PAGES
require_once 'auth.php';  // ALWAYS FIRST - relative path
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Database init if needed
if (!R::testConnection()) {
    isotone_db_connect();  // From /iso-includes/database.php
}

// Your code here...
```

### Service Class Inclusion (NO NAMESPACES)
```php
// Isotone doesn't use namespaces - include directly
require_once dirname(__DIR__) . '/iso-core/Services/ToniService.php';
$service = new ToniService();  // No namespace prefix
```

### Icon Usage Pattern (NEVER HARDCODE)
```php
// CORRECT - Using icon library
require_once dirname(__DIR__) . '/iso-core/Core/IconLibrary.php';
echo IconLibrary::getIcon('home', ['class' => 'w-6 h-6']);

// WRONG - Never hardcode SVG
echo '<svg>...</svg>';  // NEVER DO THIS
```

## 🗄️ DATABASE RULES (REDBEANPHP ONLY)

### Critical RedBeanPHP Rules
```php
// CORRECT Table Names (singular, lowercase, no underscores)
R::dispense('user');        // ✅ Correct
R::dispense('post');        // ✅ Correct
R::dispense('userprofile'); // ✅ Correct (no underscore)

// WRONG Table Names
R::dispense('users');       // ❌ Never plural
R::dispense('user_profile');// ❌ Never underscores
R::dispense('UserProfile'); // ❌ Never uppercase
```

### Database Operations
```php
// ALWAYS use these patterns
isotone_db_connect();        // Initialize connection
R::dispense('bean');        // Create new record
R::store($bean);            // Save record
R::find('bean', 'id = ?', [$id]); // Query records
R::trash($bean);            // Delete record

// NEVER use these
mysql_query();              // ❌ Raw SQL
PDO::prepare();            // ❌ Direct PDO
mysqli_connect();          // ❌ Direct mysqli
```

### WSL MySQL Connection
```bash
# CORRECT - Two-step process for WSL
# Step 1: Get Windows host IP
ip route | grep default | awk '{print $3}'
# Step 2: Connect using that IP
mysql -h [IP_FROM_STEP_1] -u root isotone_db

# WRONG - These FAIL in WSL
mysql -h localhost         # ❌ Tries WSL's MySQL
mysql -h 127.0.0.1        # ❌ Points to WSL
```

## 🎨 CSS & STYLING MANDATORY RULES

### Tailwind CSS v4.1.12
- **Version**: v4.1.12 (NEVER change without approval)
- **Build**: `composer tailwind:build` or `composer tailwind:minify`
- **Source**: `iso-automation/tailwind/src/input.css`
- **Output**: `iso-admin/css/tailwind.css`
- **Config**: Uses `@source` directives (Tailwind v4 style)

### Dark Mode Pattern (MANDATORY)
```html
<!-- EVERY element needs both variants -->
<div class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
  <p class="text-gray-600 dark:text-gray-400">Content</p>
  <button class="border-gray-200 dark:border-gray-700">Button</button>
</div>
```

## 🔌 ICON LIBRARY SYSTEM

### Three Icon Styles Available
1. **IconLibrary** - Outline (24x24, stroke) - DEFAULT
2. **IconLibrarySolid** - Solid (24x24, fill)
3. **IconLibraryMicro** - Micro (16x16, fill)

### Usage Pattern
```php
// Include the appropriate library
require_once '/iso-core/Core/IconLibrary.php';        // Outline
require_once '/iso-core/Core/IconLibrarySolid.php';   // Solid
require_once '/iso-core/Core/IconLibraryMicro.php';   // Micro

// Use icons
IconLibrary::getIcon('home');        // Default outline
IconLibrarySolid::getIcon('home');   // Solid variant
IconLibraryMicro::getIcon('home');   // Micro variant
```

### JavaScript Icons
```javascript
// Available global objects
IsotoneIcons.getIcon('home');        // Outline
IsotoneIconsSolid.getIcon('home');   // Solid
IsotoneIconsMicro.getIcon('home');   // Micro
```

## 📝 NOTES.MD HANDLING

### When User Says: "add note", "remember", "remind me", "todo"
```markdown
1. IMMEDIATELY open/read /mnt/c/xampp/htdocs/isotone/NOTES.md
2. Add to correct section (newest first):
   - Quick Notes: `- [YYYY-MM-DDTHH:MM±TZ] text #tags`
   - Reminders: `- [OPEN] text — Due: ISO8601 — Added: ISO8601`
   - TODO List: `- [ ] Task (added: YYYY-MM-DD)`
3. Update "Last updated:" line at bottom
```

## 🚀 JAVASCRIPT & ALPINE.JS

### Alpine.js Loading Order (CRITICAL)
```html
<!-- CORRECT ORDER -->
<script src="//unpkg.com/alpinejs" defer></script>
<script>
document.addEventListener('alpine:init', () => {
    // Alpine components here
});
</script>

<!-- WRONG - Never in <head> without defer -->
<head>
  <script src="//unpkg.com/alpinejs"></script> <!-- ❌ -->
</head>
```

## 🔐 SECURITY PATTERNS

### Output Escaping (MANDATORY)
```php
// ALWAYS escape output
echo esc_html($text);           // HTML text
echo esc_attr($attribute);      // HTML attributes
echo esc_url($url);             // URLs
echo esc_js($javascript);       // JavaScript

// NEVER output raw
echo $user_input;               // ❌ XSS vulnerability
```

### CSRF Protection
```php
// Generate token
iso_csrf_field();

// Verify token
if (!iso_verify_csrf()) {
    die('CSRF validation failed');
}
```

## 📂 PROJECT STRUCTURE

### Directory Layout (MEMORIZE)
```
/isotone/
├── iso-admin/          # Admin panel (each file = one page)
├── iso-automation/     # CLI tools & automation
├── iso-content/        # User content
│   ├── plugins/       # Plugins directory
│   ├── themes/        # Themes directory
│   └── uploads/       # Media uploads
├── iso-core/          # Core classes
│   ├── Commands/      # CLI commands
│   ├── Core/          # Core functionality
│   └── Services/      # Service classes
├── iso-includes/      # Shared includes
│   ├── css/          # CSS modules
│   ├── js/           # JavaScript
│   └── functions.php # Global functions
├── config.php         # Main configuration
├── vendor/           # Composer packages (NEVER modify)
└── CLAUDE.md         # THIS FILE - YOUR BIBLE
```

## 🗂️ THEME DEVELOPMENT

### Theme Structure
```
/iso-content/themes/[theme-name]/
├── style.css         # REQUIRED - theme metadata
├── index.php        # REQUIRED - main template
├── functions.php    # Optional - theme setup
└── template-parts/  # Optional - components
```

### Theme Isolation Rules
```php
// ✅ ALLOWED in themes
get_posts();          // Template functions
the_title();         // Display functions
do_action();         // Hooks

// ❌ NEVER in themes
R::find('post');     // Direct database
$_POST['data'];      // Direct superglobals
include '../../../'; // Path traversal
```

## 🔄 HOOKS SYSTEM

### Hook Naming Convention
```php
// Core hooks - ALWAYS use iso_ prefix
do_action('iso_init');
apply_filters('iso_menu_items', $items);

// Plugin hooks - use plugin_ prefix
do_action('myplugin_loaded');

// Theme hooks - use theme_ prefix
do_action('theme_header');
```

## 📦 VERSION MANAGEMENT

### Version Bump Process
```bash
# 1. Determine type
patch = bug fixes
minor = new features
major = breaking changes

# 2. Update version
php isotone version:bump [type] [stage] [codename]

# 3. Generate changelog
php isotone changelog

# 4. Commit
git commit -m "chore: Bump version to X.Y.Z"
```

### Stages & Codenames
- **Stages**: alpha → beta → rc → stable
- **Codenames**: Genesis → Phoenix → Aurora → Titan → Nebula

## 🛠️ COMPOSER SCRIPTS

### Essential Commands
```bash
composer analyse         # PHPStan analysis
composer check-style    # PHPCS check
composer fix-style      # PHPCBF fix
composer test           # Run tests
composer docs:html      # Generate documentation
composer tailwind:build # Build Tailwind CSS
```

## 🎯 QUICK DECISION TREE

### "Should I create a new file?"
1. Did you search for existing code? → NO? **STOP, SEARCH FIRST**
2. Can you reuse existing code? → YES? **REUSE IT**
3. Is it absolutely necessary? → NO? **DON'T CREATE**
4. Will it follow all patterns? → NO? **FIX PATTERNS FIRST**
5. OK to create, but UPDATE DOCS

### "How should I handle this database operation?"
1. Is it a query? → Use `R::find()` or `R::findOne()`
2. Is it create? → Use `R::dispense()` then `R::store()`
3. Is it update? → Load with `R::load()`, modify, then `R::store()`
4. Is it delete? → Use `R::trash()`
5. **NEVER write raw SQL**

### "Where does this CSS go?"
1. Is it component-specific? → `/iso-includes/css/modules/[component].css`
2. Is it Tailwind utilities? → Use classes directly in HTML
3. Is it inline style? → **STOP** - NEVER use inline styles
4. Is it in a `<style>` tag? → **STOP** - NEVER use style tags

## 🚀 PHILOSOPHY & MISSION

**Isotone Core Values:**
- **SIMPLICITY** - No unnecessary complexity
- **COMPATIBILITY** - Works on basic shared hosting
- **SECURITY** - Defense in depth
- **PERFORMANCE** - Fast and efficient
- **MAINTAINABILITY** - Clear, readable code

**Your Mission:**
You're building a CMS that must work reliably on thousands of different hosting environments. Every decision should prioritize simplicity, security, and compatibility over cleverness or advanced features.

## ⚡ PERFORMANCE NOTES

- Cache expensive operations
- Minimize database queries
- Use CDN for external libraries
- Optimize images before upload
- Lazy load when appropriate

## 🔍 VALIDATION CHECKLIST

Before completing ANY task, verify:
- [ ] All NEVER rules followed?
- [ ] All ALWAYS rules followed?
- [ ] Code follows file patterns?
- [ ] Dark mode styles included?
- [ ] Documentation updated?
- [ ] Icons use Icon API (`iso_icon()`)?
- [ ] Database uses RedBeanPHP?
- [ ] Security patterns applied?
- [ ] PSR-12 standards met?
- [ ] Tests pass?

## 🌐 API RULES & PATTERNS (CRITICAL)

### 📍 API Directory Structure - MANDATORY LOCATIONS
```
/iso-api/                # ✅ ALL API endpoints go here
├── index.php           # ✅ API explorer and endpoint listing
├── icons.php           # ✅ Icon API endpoint
├── themes.php          # ✅ Future: Theme management API
├── plugins.php         # ✅ Future: Plugin management API
└── content.php         # ✅ Future: Content management API

❌ NEVER CREATE APIs ANYWHERE ELSE:
- NOT in /iso-includes/
- NOT in /iso-admin/
- NOT in root directory
- NOT in subdirectories of /iso-api/
```

### 🔧 Icon API Usage - MEMORIZE THIS
```php
// ✅ CORRECT - Always use these functions
iso_icon('home');                           // Basic icon (lazy loaded)
iso_icon('user', 'solid');                 // Solid style
iso_icon('cog', 'micro', ['size' => 16]);  // Micro with attributes
iso_icon_outline('star');                  // Outline shortcut
iso_icon_solid('heart');                   // Solid shortcut
iso_icon_micro('x');                       // Micro shortcut

// ✅ CORRECT - Get without displaying
$icon = iso_get_icon('home', 'outline', ['class' => 'w-6 h-6']);

// ✅ CORRECT - URL for custom implementations
$url = iso_get_icon_url('user', 'solid', ['size' => 32]);

// ✅ CORRECT - Helper components
echo iso_icon_button('plus', 'Add Item');
echo iso_icon_link('external-link', 'Visit', 'https://example.com');

// ❌ NEVER DO THESE:
require_once 'IconLibrary.php';            // Old method
IconLibrary::getIcon('home');              // Direct library usage
echo '<svg>...</svg>';                     // Hardcoded SVG
```

### 🛠️ API Development Template
```php
<?php
/**
 * Isotone [Name] API Endpoint
 * 
 * Description of what this API does
 * 
 * @package Isotone
 * @since 0.3.0
 */

// ✅ MANDATORY HEADERS - ALWAYS INCLUDE ALL
header('Content-Type: application/json');
header('Cache-Control: public, max-age=3600');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('X-Content-Type-Options: nosniff');

// ✅ MANDATORY - Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ MANDATORY - Method validation
$allowedMethods = ['GET', 'POST']; // Adjust as needed
if (!in_array($_SERVER['REQUEST_METHOD'], $allowedMethods)) {
    http_response_code(405);
    header('Allow: ' . implode(', ', $allowedMethods));
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// ✅ MANDATORY - Parameter validation
$param = isset($_GET['param']) ? sanitize($_GET['param']) : '';
if (empty($param)) {
    http_response_code(400);
    echo json_encode(['error' => 'Parameter required']);
    exit;
}

// ✅ MANDATORY - Try-catch for errors
try {
    // Your API logic here
    $result = processRequest($param);
    
    // ✅ MANDATORY - Success response format
    echo json_encode([
        'success' => true,
        'data' => $result,
        'message' => 'Operation completed'
    ]);
    
} catch (Exception $e) {
    // ✅ MANDATORY - Error response format
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'code' => 'INTERNAL_ERROR'
    ]);
}
```

### 📋 API Validation Checklist - EVERY API MUST HAVE:
- [ ] Located in `/iso-api/` directory only
- [ ] CORS headers included
- [ ] OPTIONS method handling
- [ ] Method validation
- [ ] Parameter validation and sanitization
- [ ] Proper HTTP status codes
- [ ] Try-catch error handling
- [ ] Consistent JSON response format
- [ ] Cache headers appropriate for content
- [ ] Security headers (X-Content-Type-Options)

### 🔄 RESTful URL Patterns - FOLLOW EXACTLY:
```
GET    /api/icons.php?name=home&style=outline     # Get icon
GET    /api/themes.php?action=list                # List themes
POST   /api/themes.php?action=activate            # Activate theme
PUT    /api/content.php?id=123                    # Update content
DELETE /api/media.php?id=456                      # Delete media
```

### ⚡ Performance Rules for APIs:
- **ALWAYS** add appropriate cache headers
- **ALWAYS** use ETags for conditional requests
- **ALWAYS** validate early and return quickly on errors
- **NEVER** load entire libraries unless absolutely necessary
- **NEVER** process without parameter validation

## 📌 FINAL REMINDERS

1. **THIS FILE IS YOUR BIBLE** - Re-read sections before tasks
2. **SEARCH BEFORE CREATE** - Always look for existing solutions
3. **WHEN IN DOUBT** - Check the NEVER/ALWAYS lists
4. **PATTERNS ARE MANDATORY** - No exceptions
5. **DOCUMENTATION IS CODE** - Keep it updated

---

**Remember: You're not just writing code, you're maintaining a system that needs to work everywhere, for everyone, reliably and securely.**

**LAST RULE: If something isn't clear, ASK THE USER before proceeding.**
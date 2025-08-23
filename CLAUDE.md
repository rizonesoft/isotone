
**YOUR MISSION:**
You're building a CMS that must work reliably on thousands of different hosting environments. Every decision should prioritize simplicity, security, and compatibility over cleverness or advanced features. **REMEMBER:** You're not just writing code; you're maintaining a system that must work reliably and securely everywhere.

Search before creating - **REUSE** existing code, stylesheets, APIs, hooks, services, includes, etc, where possible.
Note similar implementations, patterns to follow.
Follow **PSR-12 standards**.

Do notuse Laravel/Symfony patterns.
Step 1: Get Windows host IP: `ip route | grep default | awk '{print $3}'` and then Step 2: Connect using that IP: `mysql -h [IP_FROM_STEP_1] -u root isotone_db` to MySQL database from WSL.
Use RedBeanPHP ORM for ALL database operations (no raw SQL, no migration files).
ALWAYS use singular, lowercase, no underscores table names.
**NEVER** use inline CSS or style tags.
CSS should be in the `/iso-includes/css/` directory.
Always look for existing CSS before creating new classes or styles.
Always look for existing solutions before creating new code.
Isotone admin pages use Tailwind CSS v4.1.12. Never change this version without approval.
`require_once 'auth.php';` is always first in admin pages.
`require_once dirname(__DIR__) . '/config.php';` is always second in admin pages.
`require_once dirname(__DIR__) . '/vendor/autoload.php';` is always third in admin pages.
Use `iso_` prefix for WordPress-compatible hooks.
All API endpoints go in the `/iso-api/` directory, **NEVER** outside.
All API endpoints should include CORS headers.
All API endpoints should validate input and return proper HTTP status codes.
Create RESTful API endpoints: follow RESTful API standards.
Use Icon API (`iso_icon()`), **NEVER** hardcode SVG icons.
Isotone uses plain PHP classes, not namespaces.
Every UI needs both light/dark styles.
**NEVER** load Alpine.js in `<head>` - always defer.
**NEVER** modify vendor/ directory.
For core hooks, always use `iso_` prefix: apply_filters('iso_menu_items', $items);
For plugin hooks always use `plugin_` prefix, for theme hooks always use `theme_` prefix, for admin hooks always use `admin_` prefix, for user hooks always use `user_` prefix, etc.
Find existing iso_* hooks to extend vs creating new.
Use Context7 for external API documentation.
Keep documentation updated after each change.
Update NOTES.md for notes/reminders/todos.
**NEVER** write "Isotone CMS" - just "Isotone".

**PRESERVE** the glitch-style animated 404 page at `/iso-admin/404.php` - it's a feature.

**ASK FIRST** when requirements are unclear - clarify before implementing.

**SEARCH** - Discover & Reuse Existing Code, Javascripts, Stylesheets, APIs, Hooks, Services, Includes
**ISOTONE DIRECTORY STRUCTURE**:
- iso-core/Services/*  - Core service classes
- iso-core/Core/*      - Core functionality
- iso-admin/*          - Admin components
- iso-includes/*       - Shared includes

### 3. ✅ **CHECK** - Validate Standards & Architecture
- **PSR-12 Compliance**: Verify formatting, naming conventions
- **Security Layer**: Confirm `iso-admin/auth.php` on admin pages (except login.php/logout.php).
- **Hook Naming**: All hooks prefixed with `iso_`.
- **Early Conflict Detection**: Flag any standard violations BEFORE coding.

### 4. ✅ **IMPLEMENT** - Code with Minimal Impact
- **Diff Minimization**: Change only what's necessary
- **Extension First**: Extend existing classes/services before creating new
- **Import Organization**: All use/require statements at file top
- **No New Dependencies**: Work within existing framework
- **Escape HTML output**: Use `esc_html()`, `esc_attr()`, `esc_url()`.
- **ICON API**: Use the Icon API for icons.
- **Error Handling**: Implement try-catch blocks, log errors appropriately
- **Code Comments**: Document complex logic, not obvious code

### 5. ✅ **VALIDATE** - Multi-Layer Testing
- **Static Analysis**: Run PHPStan if configured - `composer analyse`
- **Style Check**: `phpcs --standard=PSR12 [files]` - The vendor JS files (alpine.js, chart.js) should probably be excluded.
- **Unit Tests**: Run existing test suite, add tests for new methods
- **Integration Test**: Verify with connected services
- **Hook Verification**: Confirm all hooks fire correctly
- **Manual Smoke Test**: Click through affected UI paths
- **Performance Check**: Verify no degradation (query count, load time)

### 6. ✅ **DOCUMENT** - Complete the Cycle
- **Code Documentation**: PHPDoc blocks for new methods/classes
- **Changelog Update**: Add entry with version, date, changes
  **Update these documentation files:**
- `README.md` — if new feature changes or install steps.
- `CHANGELOG.md` — user-visible changes
- `HOOKS.md` — Document new `iso_*` hooks with parameters
- `user-docs/development/project-structure.md` — directory changes
- `user-docs/development/commands.md` — new CLI commands
- `user-docs/configuration/config-guide.md` — config variables
- `user-docs/icons/` — icon library changes
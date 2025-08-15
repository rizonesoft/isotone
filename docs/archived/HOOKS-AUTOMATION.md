# Hooks Documentation Automation

## Overview

Isotone now includes an automated system that scans the codebase for hook implementations and generates up-to-date documentation automatically.

## How It Works

### 1. Automatic Scanning

The system scans all PHP files in:
- `/app` - Core application code
- `/iso-admin` - Admin panel  
- `/iso-content/themes` - Installed themes
- `/iso-content/plugins` - Installed plugins

It looks for:
- `do_action()` calls (action hooks)
- `apply_filters()` calls (filter hooks)
- Hook documentation in PHPDoc comments
- Deprecated hooks

### 2. Documentation Generation

The scanner generates two main documents:

#### `/HOOKS.md`
- Updates implementation status (✅ for implemented hooks)
- Adds a "Discovered Hooks" section with all found hooks
- Updates statistics (total hooks, progress percentage)
- Shows file locations where each hook is fired

#### `/user-docs/development/api-reference.md`
- Complete API reference for developers
- Categorized by hook type (Actions, Filters)
- Usage examples for each hook
- Helper function documentation
- Isotone-specific functions (`iso_*` functions)

### 3. Integration with Documentation Workflow

The hooks documentation is integrated into the existing documentation system:

```bash
# Generate hooks documentation only
composer docs:hooks

# Update all documentation including hooks
composer docs:all

# The hooks are also updated when running
php scripts/update-docs.php
```

## File Structure

```
scripts/
├── generate-hooks-docs.php    # Main hooks documentation generator
├── update-docs.php            # Overall documentation updater (includes hooks)
├── check-docs.php             # Documentation integrity checker
└── sync-user-docs.php         # Syncs to user-facing docs

storage/
└── hooks-status.json          # Stores scan statistics and discovered hooks

user-docs/development/
└── api-reference.md           # User-facing API documentation (auto-generated)
```

## Features

### Smart Hook Detection
- Extracts hook names from `do_action()` and `apply_filters()` calls
- Parses hook arguments
- Finds PHPDoc descriptions
- Tracks line numbers and file locations

### Hook Categorization
Automatically categorizes hooks by prefix:
- `iso_*` - Isotone Core hooks
- `admin_*` - Admin hooks
- `init*` - Initialization hooks
- `save_*` - Data saving hooks
- `the_*` - Content display hooks
- And more...

### Implementation Status Tracking
- Marks hooks as ✅ when implementation is found
- Shows which hooks are planned but not yet implemented
- Calculates overall implementation progress

### Developer-Friendly Output
- Usage examples for each hook
- File locations with line numbers
- Parameter documentation
- Since version tracking

## Usage

### Manual Generation

Run the hooks documentation generator:

```bash
php scripts/generate-hooks-docs.php
```

Or via Composer:

```bash
composer docs:hooks
```

### Automatic Updates

Hooks documentation is automatically updated when:

1. Running `composer docs:all`
2. Running `composer docs:update`
3. During version bumps (`composer version:patch`, etc.)
4. In pre-commit hooks (if configured)

### Output Example

The generator provides statistics:

```
🔍 Scanning for hooks implementation...
📝 Updated HOOKS.md
📝 Generated user-docs/development/api-reference.md
📊 Updated hooks status
✅ Hooks documentation generated successfully!

📊 Statistics:
  Files scanned: 33
  Actions found: 13
  Filters found: 3
  Deprecated hooks: 0

📁 Categories:
  Other: 10 hooks
  Isotone Core: 6 hooks
```

## Benefits

1. **Always Up-to-Date**: Documentation reflects actual code implementation
2. **No Manual Maintenance**: Automatically discovers new hooks
3. **Developer-Friendly**: Provides usage examples and locations
4. **Progress Tracking**: Shows implementation status at a glance
5. **Integration**: Works seamlessly with existing documentation workflow

## Hook Naming Convention

The system respects Isotone's hook naming convention:
- WordPress-equivalent hooks use `iso_` prefix
- Example: `iso_head` instead of `wp_head`
- Generic hooks remain unchanged (e.g., `init`, `the_content`)

See [HOOK-NAMING-CONVENTIONS.md](HOOK-NAMING-CONVENTIONS.md) for details.

## Extending the System

To add new scanning directories:

1. Edit `scripts/generate-hooks-docs.php`
2. Add to the `$scanDirectories` array
3. Run `composer docs:hooks` to regenerate

To customize categorization:

1. Edit the `categorizeHooks()` method
2. Add new prefix patterns and categories
3. Regenerate documentation

## Troubleshooting

### No hooks found
- Check that PHP files use standard `do_action()` and `apply_filters()` syntax
- Verify scanning directories exist and contain PHP files

### Documentation not updating
- Ensure write permissions on `/HOOKS.md` and `/user-docs/development/`
- Check for PHP errors: `php scripts/generate-hooks-docs.php`

### Missing hook descriptions
- Add PHPDoc comments above hook calls
- Use `@since` tag for version tracking

---

**Last Updated**: 2024-12-14  
**Status**: Active and integrated into documentation workflow
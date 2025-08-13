# Version Management Guide

This guide explains how to manage versions in Isotone CMS.

## Overview

Isotone uses semantic versioning (SemVer) with the format: `MAJOR.MINOR.PATCH[-STAGE]`

- **MAJOR**: Incompatible API changes
- **MINOR**: Backwards-compatible functionality additions
- **PATCH**: Backwards-compatible bug fixes
- **STAGE**: Optional pre-release identifier (alpha, beta, rc)

## Version Configuration

Version information is stored in `config/version.json`:

```json
{
    "current": "0.1.0-alpha",
    "schema": "1.0.0",
    "codename": "Genesis",
    "release_date": "2025-01-13",
    "history": [...]
}
```

## Updating Versions

### Method 1: Using CLI Commands

#### Bump Version (Recommended)

The `version:bump` command automatically increments the version number:

```bash
# Patch release (0.1.0 -> 0.1.1)
php isotone version:bump patch

# Minor release (0.1.0 -> 0.2.0)
php isotone version:bump minor

# Major release (0.1.0 -> 1.0.0)
php isotone version:bump major

# With stage identifier
php isotone version:bump minor beta        # -> 0.2.0-beta
php isotone version:bump major rc          # -> 1.0.0-rc

# With stage and codename
php isotone version:bump major stable "Phoenix"  # -> 1.0.0 (Phoenix)
```

#### Set Version Directly

For special cases, you can set the version directly:

```bash
# Set specific version
php isotone version:set 1.0.0-rc1

# Set version with codename
php isotone version:set 1.0.0 "Phoenix"
```

### Method 2: Manual Update

1. Edit `config/version.json`
2. Update the `current` field
3. Update the `release_date` to today's date
4. Optionally update the `codename`
5. Add an entry to the `history` array

## Version Stages

Isotone supports four development stages:

1. **alpha** - Early development, major changes expected
2. **beta** - Feature complete, testing phase
3. **rc** (Release Candidate) - Final testing before stable
4. **stable** - Production ready (no stage suffix)

## Codenames

Each major or significant release can have a codename. Suggested theme: **Mythological/Celestial**

Examples:
- 0.1.0 - Genesis
- 1.0.0 - Phoenix
- 2.0.0 - Aurora
- 3.0.0 - Titan

## Version History

The version history is automatically maintained in `config/version.json`. Each entry includes:

```json
{
    "version": "0.1.0-alpha",
    "date": "2025-01-13",
    "codename": "Genesis",
    "features": ["List of new features"],
    "breaking_changes": ["List of breaking changes"],
    "fixed": ["List of bug fixes"],
    "security": ["Security updates"]
}
```

## Changelog Generation

Generate a CHANGELOG.md from version history:

```bash
php isotone changelog:generate
```

## API Access

Version information is available via API:

- **Current Version**: `GET /api/version`
- **System Info**: `GET /api/system`

Example response:
```json
{
    "version": "0.1.0-alpha",
    "schema": "1.0.0",
    "codename": "Genesis",
    "stage": "alpha",
    "release_date": "2025-01-13",
    "php_version": "8.3.6",
    "php_required": "8.3.0"
}
```

## PHP Access

Access version information in your code:

```php
use Isotone\Core\Version;

// Get current version
$version = Version::current();

// Format for display
echo Version::format();  // v0.1.0-alpha (Genesis)

// Check version requirements
if (Version::meets('1.0.0')) {
    // Feature available in 1.0.0+
}

// Get next version number
$next = Version::getNextVersion('minor');  // 0.2.0

// Bump version programmatically
$newVersion = Version::bump('patch', 'beta', 'Nebula');

// Add features to current version
Version::addFeatures([
    'Added user authentication',
    'Implemented plugin system'
]);
```

## Best Practices

### When to Bump Versions

- **Patch**: Bug fixes, security updates, minor improvements
- **Minor**: New features, non-breaking changes
- **Major**: Breaking changes, major rewrites, API changes

### Pre-release Workflow

1. Development phase: `x.y.z-alpha`
2. Testing phase: `x.y.z-beta`
3. Release candidate: `x.y.z-rc1`, `x.y.z-rc2`, etc.
4. Stable release: `x.y.z`

### Version Commit Workflow

```bash
# 1. Make your changes
git add .
git commit -m "feat: Add new feature"

# 2. Bump version
php isotone version:bump minor

# 3. Stage version file
git add config/version.json

# 4. Commit version bump
git commit -m "chore: Bump version to $(php isotone version --short)"

# 5. Tag the release
git tag v$(php isotone version --short)

# 6. Push with tags
git push origin main --tags
```

## Database Schema Versioning

The `schema` field in `version.json` tracks database structure changes:

- Increment when database structure changes
- Used by migration system to determine required updates
- Independent from application version

## Compatibility Checking

Check system compatibility:

```bash
php isotone version:check
```

This verifies:
- PHP version requirements
- Required PHP extensions
- Database schema compatibility

## Version Display

The version is displayed in:

1. **Landing Page**: Badge next to title
2. **CLI**: `php isotone version`
3. **API**: `/api/version` endpoint
4. **Admin Panel**: Footer (when implemented)

## Troubleshooting

### Version Not Updating

If the version doesn't update after using CLI commands:

1. Check file permissions on `config/version.json`
2. Ensure the file is writable
3. Clear any caches

### Version Mismatch

If you see different versions in different places:

1. Check `config/version.json` is the single source of truth
2. Clear opcache if enabled: `opcache_reset()`
3. Restart web server

## Future Enhancements

Planned improvements to the versioning system:

- [ ] Automatic update checking against GitHub releases
- [ ] Version-specific migration files
- [ ] Plugin compatibility versioning
- [ ] Automated release notes generation
- [ ] Version rollback capability
- [ ] Update notifications in admin panel

---

For more information, see the [Development Guide](development-setup.md) or run `php isotone help`.
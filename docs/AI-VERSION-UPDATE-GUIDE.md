# AI Assistant Version Update Guide

This guide helps AI assistants (like Claude) understand how to handle version updates for Isotone CMS.

## Understanding User Intent

### Common Version Update Requests

| User Says | AI Should Execute | Reasoning |
|-----------|------------------|-----------|
| "Fix that bug and update version" | `php isotone version:bump patch` | Bug fix = patch |
| "We added user authentication" | `php isotone version:bump minor` | New feature = minor |
| "Ready for beta testing" | `php isotone version:bump minor beta` | Testing phase = beta stage |
| "Let's go to beta 2" | `php isotone version:set 0.2.0-beta2` | Specific beta version |
| "Ship it! Release 1.0" | `php isotone version:bump major stable "Phoenix"` | Production = major + stable |
| "The API changed completely" | `php isotone version:bump major` | Breaking change = major |
| "Time for version 2" | `php isotone version:bump major stable "Aurora"` | New major version |

## Version Progression Path

```
0.1.0-alpha → 0.2.0-beta → 0.3.0-rc1 → 1.0.0 (stable)
```

### Stage Transitions

1. **Alpha → Beta**: When core features are complete
2. **Beta → RC**: When testing is mostly done
3. **RC → Stable**: When ready for production
4. **Stable → Next Alpha**: When starting next major version

## Codename Selection

### Theme: Mythological/Celestial

Suggested progression:
- v0.x - Genesis (beginning)
- v1.0 - Phoenix (rebirth/launch)
- v2.0 - Aurora (dawn/light)
- v3.0 - Titan (strength)
- v4.0 - Nebula (expansion)
- v5.0 - Olympus (peak)
- v6.0 - Cosmos (universe)
- v7.0 - Chronos (time)
- v8.0 - Atlas (support)
- v9.0 - Helios (sun)
- v10.0 - Zenith (pinnacle)

## Complete Update Workflow

When a user requests a version update, the AI should:

### 1. Determine Version Type

```php
// Check what changed
- Bug fixes only → patch
- New features (backward compatible) → minor
- Breaking changes or major milestone → major
```

### 2. Execute Version Bump

```bash
# Examples based on context
php isotone version:bump patch                    # Bug fix
php isotone version:bump minor beta              # New feature, beta stage
php isotone version:bump major stable "Phoenix"  # Major release
```

### 3. Update Feature List

Edit `config/version.json` to add features for the new version:

```json
{
    "version": "0.2.0-beta",
    "features": [
        "Added user authentication system",
        "Implemented role-based permissions",
        "Created admin dashboard"
    ]
}
```

### 4. Confirm Changes

```bash
# Show new version
php isotone version

# Check what changed
cat config/version.json
```

### 5. Stage for Commit

```bash
git add config/version.json
git commit -m "chore: Bump version to $(php isotone version --short)"
```

## UI Version Display

### Current Implementation

The UI **automatically** pulls version from the system:

1. **Landing Page** (`Application.php`):
   ```php
   $isotonerVersion = Version::format();     // "v0.1.0-alpha (Genesis)"
   $versionBadge = Version::getBadge();      // Colored badge HTML
   $versionInfo = Version::current();        // Full version array
   ```

2. **API Endpoints**:
   - `/api/version` - Returns current version JSON
   - `/api/system` - Returns full system info with version

3. **CLI Display**:
   - `php isotone version` - Shows formatted version

### No UI Code Changes Needed!

When version is bumped:
- ✅ Landing page automatically shows new version
- ✅ API returns updated version
- ✅ CLI reflects changes immediately
- ✅ Version badge color changes based on stage

The system is **fully dynamic** - just update `config/version.json` and everything updates!

## Decision Tree for AI

```
User mentions version update?
├─ Yes
│  ├─ Specific version mentioned?
│  │  ├─ Yes → Use version:set
│  │  └─ No → Continue
│  │
│  ├─ What changed?
│  │  ├─ Bug fix → version:bump patch
│  │  ├─ New feature → version:bump minor
│  │  ├─ Breaking change → version:bump major
│  │  └─ Unclear → Ask user
│  │
│  ├─ Stage mentioned?
│  │  ├─ "beta" → Add beta stage
│  │  ├─ "RC" → Add rc stage
│  │  ├─ "release/production" → stable (no stage)
│  │  └─ No → Keep current stage
│  │
│  └─ Major version?
│     ├─ Yes → Suggest new codename
│     └─ No → Keep current codename
│
└─ No → Don't update version
```

## Example AI Responses

### User: "We're ready for beta"
```bash
I'll update the version to beta stage:

php isotone version:bump minor beta

This updates from 0.1.0-alpha to 0.2.0-beta.
```

### User: "Ship version 1.0!"
```bash
Excellent! Let's release version 1.0:

php isotone version:bump major stable "Phoenix"

This creates version 1.0.0 with codename "Phoenix".
The landing page and API will automatically show the new version.
```

### User: "Fixed the login bug"
```bash
I'll bump the patch version for this bug fix:

php isotone version:bump patch

This updates to 0.1.1-alpha.
```

## Important Notes

1. **Version is the Single Source of Truth**: `config/version.json`
2. **UI Updates Automatically**: No code changes needed
3. **Always Document Changes**: Update features array in version.json
4. **Follow SemVer**: Major.Minor.Patch
5. **Stage Progression**: alpha → beta → rc → stable

## Automation Capabilities

The AI should be able to:
- ✅ Understand version update intent
- ✅ Execute appropriate bump command
- ✅ Suggest codenames for major versions
- ✅ Update feature lists in version.json
- ✅ Understand stage transitions
- ✅ Know that UI updates automatically

The AI should NOT:
- ❌ Manually edit version strings in code
- ❌ Update UI components (they're dynamic)
- ❌ Skip version numbers
- ❌ Use non-standard version formats
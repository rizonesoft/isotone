# Documentation Sync Note

The following files in `/user-docs/` are copies from `/docs/` for better organization:

| User-Docs Location | Source in /docs/ | Why Duplicated |
|-------------------|------------------|----------------|
| `installation/development-setup.md` | `docs/DEVELOPMENT-SETUP.md` | Human-friendly organization |
| `development/getting-started.md` | `docs/GETTING-STARTED.md` | Human-friendly organization |
| `installation/tech-stack.md` | `docs/ISOTONE-TECH-STACK.md` | Human-friendly organization |
| `configuration/config-guide.md` | `docs/CONFIGURATION.md` | Human-friendly organization |
| `configuration/database.md` | `docs/DATABASE-CONNECTION.md` | Human-friendly organization |
| `development/api-reference.md` | `docs/API-REFERENCE.md` | Human-friendly organization |

## Important Notes

1. **Primary Source**: The `/docs/` versions are the authoritative source
2. **Updates**: When updating these files, update the `/docs/` version first
3. **Sync**: Periodically copy updates from `/docs/` to `/user-docs/`
4. **References**: Many automated tools and LLMs reference the `/docs/` paths directly

## Why This Structure?

- `/docs/` must remain flat for LLM compatibility (95+ hardcoded references)
- `/user-docs/` provides logical organization for human readers
- Duplication ensures both audiences are served without breaking systems

## Sync Commands

### Automated Sync (Recommended)

```bash
# Sync user-docs with source files from /docs/
composer docs:sync

# Update docs AND sync to user-docs automatically
composer docs:update
```

### Manual Sync

```bash
# If you need to manually sync specific files
cp docs/DEVELOPMENT-SETUP.md user-docs/installation/development-setup.md
cp docs/GETTING-STARTED.md user-docs/development/getting-started.md
cp docs/ISOTONE-TECH-STACK.md user-docs/installation/tech-stack.md
cp docs/CONFIGURATION.md user-docs/configuration/config-guide.md
cp docs/DATABASE-CONNECTION.md user-docs/configuration/database.md
cp docs/API-REFERENCE.md user-docs/development/api-reference.md
```

## Automation Integration

The sync happens automatically when:
1. Running `composer docs:update` - Updates /docs/ then syncs to /user-docs/
2. Can be run separately with `composer docs:sync`


# Isotone Runtime Directory

This directory contains all system-generated files that are created during runtime.

## Directory Structure

```
iso-runtime/
├── cache/    # Page cache, compiled templates, processed assets
├── logs/     # Error logs, debug logs, access logs
└── temp/     # Temporary files, sessions, upload processing
```

## Important Notes

### Security
- **No direct access** - The .htaccess file blocks all HTTP access
- **Not web accessible** - These files should never be served directly

### Maintenance
- **Safe to delete** - All contents can be safely deleted (will be regenerated)
- **Not in version control** - Contents are ignored by git (except .gitkeep files)
- **Regular cleanup** - Old files should be cleaned up periodically

### Permissions
The web server needs write permissions to these directories:
```bash
chmod 755 iso-runtime
chmod 775 iso-runtime/cache
chmod 775 iso-runtime/logs  
chmod 775 iso-runtime/temp
```

### Backup Strategy
- **Do not backup** - These files are temporary and regeneratable
- Focus backups on `iso-content/` which contains user data

## Difference from iso-content

| iso-content/ | iso-runtime/ |
|-------------|-------------|
| User-created content | System-generated files |
| Plugins, themes, uploads | Cache, logs, temp files |
| Preserve during updates | Safe to delete anytime |
| Include in backups | Exclude from backups |
| Version control: Mixed | Version control: Ignored |
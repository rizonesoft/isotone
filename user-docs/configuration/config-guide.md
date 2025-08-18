# Isotone Configuration Guide

## Overview

Isotone uses a single PHP configuration file (`config.php`) in the root directory for all settings. This approach is familiar to PHP developers, works reliably on all hosting environments, and follows the pattern established by WordPress and other popular PHP CMS platforms.

## ⚠️ IMPORTANT: No .env Files

**Isotone does NOT use .env files or environment variables.** All configuration is done through `config.php` in the root directory. This ensures maximum compatibility with shared hosting environments.

## Initial Setup

### 1. Configuration File Location

The configuration file must be located at:
```
/isotone/config.php
```

If it doesn't exist, copy from the sample:
```bash
cp config.sample.php config.php
```

### 2. Required Database Settings

At minimum, you must configure these database settings:

```php
/** Database hostname */
define('DB_HOST', 'localhost');

/** Database name */
define('DB_NAME', 'isotone_db');

/** Database username */
define('DB_USER', 'your_username');

/** Database password */
define('DB_PASSWORD', 'your_password');
```

## Configuration Sections

### Database Configuration

```php
// Required settings
DB_HOST      - Database server hostname (default: 'localhost')
DB_NAME      - Name of your database (required)
DB_USER      - Database username (required)
DB_PASSWORD  - Database password (required)

// Optional settings
DB_PORT      - Database port (default: 3306)
DB_CHARSET   - Character set (default: 'utf8mb4')
DB_COLLATE   - Database collation (default: 'utf8mb4_unicode_ci')
DB_PREFIX    - Table prefix for multiple installations (default: 'iso_')
```

#### Special Note for WSL Users

If you're running Isotone in WSL (Windows Subsystem for Linux), the DatabaseService automatically detects and handles the connection:

- **Automatic detection**: The system automatically finds the Windows host IP
- **No manual configuration needed**: Just use 'localhost' in config.php
- **Manual override**: If needed, you can use the Windows host IP directly:
  ```bash
  # Find Windows host IP from WSL
  ip route | grep default | awk '{print $3}'
  # Usually returns something like: 172.19.240.1
  ```

### Application Settings

```php
SITE_URL     - Your site URL (leave empty for auto-detection)
ADMIN_EMAIL  - Administrator email address (default: 'admin@example.com')
TIMEZONE     - Default timezone (default: 'UTC')
              Examples: 'America/New_York', 'Europe/London', 'Asia/Tokyo'
LANGUAGE     - Default language code (default: 'en')
```

### Security Settings

```php
AUTH_KEY         - Authentication key for cookies
SECURE_AUTH_KEY  - Secure authentication key
LOGGED_IN_KEY    - Key for logged-in cookies
NONCE_KEY        - Nonce generation key
AUTH_SALT        - Authentication salt
SECURE_AUTH_SALT - Secure authentication salt
LOGGED_IN_SALT   - Logged-in cookie salt
NONCE_SALT       - Nonce salt
```

**Important:** Change all security keys to unique phrases. You can generate them at:
https://api.wordpress.org/secret-key/1.1/salt/

### Developer Settings

```php
DEBUG_MODE       - Enable/disable debug mode (default: true)
                  Shows detailed error messages and warnings
DEBUG_QUERIES    - Log database queries (default: false)
                  Useful for optimizing database performance
DISPLAY_ERRORS   - Show PHP errors on screen (default: true)
                  Should be false in production
ERROR_LEVEL      - PHP error reporting level (default: E_ALL)
                  E_ALL | E_ERROR | E_WARNING | E_NOTICE
MAINTENANCE_MODE - Enable maintenance mode (default: false)
                  Shows maintenance page to visitors
```

### Memory Limit - Simple & Effective

Isotone uses a single memory limit that's enforced across the entire system, providing predictable resource usage and protection against runaway scripts.

```php
MEMORY_LIMIT - System-wide memory limit (default: '256M', yours: '64M')
```

#### How It Works

1. **Always Enforced**: The limit is always applied, even if PHP has unlimited memory
2. **Bidirectional**: 
   - Increases if PHP limit is lower (64M → 256M)
   - Decreases if PHP limit is higher (512M → 256M)
   - Enforces even on unlimited (-1) → 256M
3. **Simple**: One setting controls everything - no confusion

#### Why Enforce Limits?

- **Resource Protection**: Prevents excessive memory consumption
- **Predictable Usage**: Know exactly how much memory your site can use
- **Shared Hosting Safety**: Critical for multi-site environments
- **Runaway Script Prevention**: Stops bad code from consuming all memory

#### Configuration Examples

**Standard Setup:**
```php
define('MEMORY_LIMIT', '256M');
```

**Shared Hosting (Conservative):**
```php
define('MEMORY_LIMIT', '128M');
```

**Dedicated Server (Generous):**
```php
define('MEMORY_LIMIT', '512M');
```

**Development Environment:**
```php
define('MEMORY_LIMIT', '1G');
```

#### What the Dashboard Shows

The System Health widget displays memory configuration:

1. **Memory Configuration**: Shows the enforced limits
   - **Per-Request Limit**: The maximum memory any single request can use (e.g., 64M)
   - **PHP Default**: What PHP was originally configured with (often Unlimited)
   - **Protection Status**: Confirms memory limits are enforced

2. **Why Not Current Usage?**: 
   - PHP memory is per-request (each page load is separate)
   - Dashboard can only show its own memory usage (always low ~3-4MB)
   - Showing this would be misleading as it doesn't represent total site usage
   - Focus is on the configuration that protects your server

#### Checking Applied Memory Limit

You can verify the memory limit in several ways:

1. **In Admin Dashboard**: The System Health widget shows:
   - **Per-Request Limit**: The enforced memory limit (e.g., 64M)
   - **PHP Default**: What PHP originally had configured
   - **Protection Status**: Confirmation that limits are active
   
2. **Via PHP**: Use `ini_get('memory_limit')` to see the enforced limit

3. **Debug method**: Add `var_dump(ini_get('memory_limit'))` to verify enforcement

### Advanced Settings

```php
MAX_EXECUTION_TIME - Maximum script execution time (default: 30 seconds)
UPLOAD_MAX_SIZE    - Maximum upload file size (default: '10M')
SESSION_LIFETIME   - Session lifetime in minutes (default: 120)
CACHE_TTL         - Cache time-to-live in seconds (default: 3600)
```

### Redis Configuration (Optional)

```php
REDIS_ENABLED - Enable Redis caching (default: false)
REDIS_HOST    - Redis server hostname (default: '127.0.0.1')
REDIS_PORT    - Redis server port (default: 6379)
```

### Environment-Specific Configuration

The config file includes automatic environment detection:

```php
/** Current environment */
define('ENVIRONMENT', 'development');  // 'development' | 'staging' | 'production'

// Automatic overrides based on environment
if (ENVIRONMENT === 'production') {
    // Production overrides (only if not already defined)
    if (!defined('DEBUG_MODE')) define('DEBUG_MODE', false);
    if (!defined('DISPLAY_ERRORS')) define('DISPLAY_ERRORS', false);
} elseif (ENVIRONMENT === 'staging') {
    // Staging overrides
    if (!defined('DEBUG_MODE')) define('DEBUG_MODE', true);
    if (!defined('DISPLAY_ERRORS')) define('DISPLAY_ERRORS', false);
}
```

## Example Configurations

### Local Development

```php
define('ENVIRONMENT', 'development');
define('DEBUG_MODE', true);
define('DISPLAY_ERRORS', true);
define('ERROR_LEVEL', E_ALL);

define('DB_HOST', 'localhost');
define('DB_NAME', 'isotone_dev');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

define('MEMORY_LIMIT', '512M');
```

### Shared Hosting

```php
define('ENVIRONMENT', 'production');
define('DEBUG_MODE', false);
define('DISPLAY_ERRORS', false);

define('DB_HOST', 'mysql.yourhost.com');
define('DB_NAME', 'user123_isotone');
define('DB_USER', 'user123_dbuser');
define('DB_PASSWORD', 'strong_password_here');

// Conservative memory limit for shared hosting
define('MEMORY_LIMIT', '128M');
```

### Production Server

```php
define('ENVIRONMENT', 'production');
define('DEBUG_MODE', false);
define('DISPLAY_ERRORS', false);
define('ERROR_LEVEL', E_ERROR | E_WARNING);

define('DB_HOST', 'localhost');
define('DB_NAME', 'isotone_prod');
define('DB_USER', 'isotone_user');
define('DB_PASSWORD', 'secure_password_here');

define('MEMORY_LIMIT', '256M');
define('MAX_EXECUTION_TIME', 60);
```

## Security Best Practices

### 1. File Permissions

```bash
# Set secure permissions for config.php
chmod 600 config.php  # Read/write for owner only

# Or slightly less restrictive if needed
chmod 640 config.php  # Read/write for owner, read for group
```

### 2. Version Control

The `config.php` file is automatically excluded from Git via `.gitignore` to prevent accidental commits of sensitive data.

### 3. Backup Strategy

- Keep secure backups of your `config.php` file
- Store backups separately from your code
- Include database credentials and security keys
- Test restore procedures regularly

### 4. Security Keys

- Generate unique keys for each installation
- Never reuse keys between sites
- Regenerate if you suspect compromise
- Use the WordPress salt generator for strong keys

## Troubleshooting

### Database Connection Issues

If you get database connection errors:

1. **Verify credentials**: Check DB_HOST, DB_NAME, DB_USER, DB_PASSWORD
2. **Check database exists**: Ensure the database has been created
3. **Verify permissions**: User must have ALL privileges on the database
4. **WSL users**: The system auto-detects WSL, but you can manually specify the Windows host IP if needed

### Memory Limit Issues

If you encounter memory exhaustion:

1. **Check current limit**: Look at System Health widget in admin dashboard
2. **Increase limit**: Adjust MEMORY_LIMIT in config.php
3. **Verify application**: Check if new limit is applied (may require PHP restart)
4. **Server restrictions**: Some hosts limit maximum memory regardless of settings

**Important Note:**
Isotone enforces its memory limit even if PHP has unlimited memory. This provides:
- Predictable resource usage
- Protection against runaway scripts
- Consistent behavior across environments

### Configuration Not Loading

If settings aren't being applied:

1. **File location**: Ensure `config.php` is in the root directory
2. **Syntax check**: Run `php -l config.php` to check for errors
3. **Permissions**: Verify PHP can read the file
4. **Cache clearing**: Some settings may be cached - clear cache after changes

## Configuration Variables Reference

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| **Database Settings** ||||
| `DB_HOST` | string | `'localhost'` | Database server hostname |
| `DB_NAME` | string | (required) | Database name |
| `DB_USER` | string | (required) | Database username |
| `DB_PASSWORD` | string | (required) | Database password |
| `DB_PORT` | int | `3306` | Database server port |
| `DB_CHARSET` | string | `'utf8mb4'` | Database character set |
| `DB_COLLATE` | string | `'utf8mb4_unicode_ci'` | Database collation |
| `DB_PREFIX` | string | `'iso_'` | Table prefix |
| **Application Settings** ||||
| `SITE_URL` | string | `''` (auto-detect) | Site URL |
| `ADMIN_EMAIL` | string | `'admin@example.com'` | Administrator email |
| `TIMEZONE` | string | `'UTC'` | Default timezone |
| `LANGUAGE` | string | `'en'` | Default language |
| **Security Keys** ||||
| `AUTH_KEY` | string | (required) | Authentication key |
| `SECURE_AUTH_KEY` | string | (required) | Secure auth key |
| `LOGGED_IN_KEY` | string | (required) | Logged-in key |
| `NONCE_KEY` | string | (required) | Nonce key |
| `AUTH_SALT` | string | (required) | Auth salt |
| `SECURE_AUTH_SALT` | string | (required) | Secure auth salt |
| `LOGGED_IN_SALT` | string | (required) | Logged-in salt |
| `NONCE_SALT` | string | (required) | Nonce salt |
| **Developer Settings** ||||
| `DEBUG_MODE` | bool | `true` | Enable debug mode |
| `DEBUG_QUERIES` | bool | `false` | Log queries |
| `DISPLAY_ERRORS` | bool | `true` | Display errors |
| `ERROR_LEVEL` | int | `E_ALL` | Error reporting level |
| `MAINTENANCE_MODE` | bool | `false` | Maintenance mode |
| **Performance Settings** ||||
| `MEMORY_LIMIT` | string | `'256M'` | System-wide memory limit |
| `MAX_EXECUTION_TIME` | int | `30` | Max execution seconds |
| `UPLOAD_MAX_SIZE` | string | `'10M'` | Max upload size |
| `SESSION_LIFETIME` | int | `120` | Session lifetime (minutes) |
| `CACHE_TTL` | int | `3600` | Cache TTL (seconds) |
| **Redis Settings** ||||
| `REDIS_ENABLED` | bool | `false` | Enable Redis |
| `REDIS_HOST` | string | `'127.0.0.1'` | Redis host |
| `REDIS_PORT` | int | `6379` | Redis port |
| **Environment** ||||
| `ENVIRONMENT` | string | `'development'` | Current environment |

## For Developers and LLMs

### ⚠️ CRITICAL RULES

1. **NEVER** create or reference `.env` files
2. **NEVER** suggest using environment variables for configuration
3. **ALWAYS** use `config.php` for all settings
4. **ALWAYS** access configuration via PHP constants (e.g., `DB_NAME`, not `$_ENV['DB_NAME']`)
5. **NEVER** commit `config.php` to version control
6. **ALWAYS** define constants with `define()` function
7. **CHECK** if constant is defined before accessing: `defined('CONSTANT_NAME')`

### Loading Configuration

Configuration is automatically loaded by `index.php`:

```php
// Configuration is loaded early in index.php
if (file_exists(ISOTONE_ROOT . '/config.php')) {
    require_once ISOTONE_ROOT . '/config.php';
}
```

### Accessing Configuration Values

```php
// Check if defined first
if (defined('DB_NAME')) {
    $database = DB_NAME;
}

// With default value
$memory = defined('MEMORY_LIMIT') ? MEMORY_LIMIT : '128M';

// Admin area detection
$is_admin = strpos($_SERVER['REQUEST_URI'] ?? '', '/iso-admin/') !== false;
```

## Support

If you encounter configuration issues:

1. Check this documentation first
2. Review error logs in `/iso-runtime/logs/`
3. Search existing [GitHub Issues](https://github.com/rizonesoft/isotone/issues)
4. Ask in [GitHub Discussions](https://github.com/rizonesoft/isotone/discussions)
5. Report bugs with configuration details (excluding passwords)

---

*Last updated: January 2025*
*Isotone Version: 0.1.2-alpha*
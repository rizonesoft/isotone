# Isotone Configuration Guide

## Overview

Isotone uses a traditional PHP configuration file (`config.php`) following the pattern established by WordPress and other popular PHP CMS platforms. This approach is familiar to PHP developers and works reliably on all hosting environments.

## ⚠️ IMPORTANT: No .env Files

**Isotone does NOT use .env files or environment variables.** All configuration is done through `config.php` in the root directory.

## Initial Setup

### 1. Create Your Configuration File

```bash
# Copy the sample configuration
cp config.sample.php config.php

# Edit with your settings
nano config.php  # or use your preferred editor
```

### 2. Required Settings

At minimum, you must configure these database settings:

```php
/** Database hostname */
define('DB_HOST', 'localhost');

/** Database name */
define('DB_NAME', 'your_database_name');

/** Database username */
define('DB_USER', 'your_username');

/** Database password */
define('DB_PASSWORD', 'your_password');
```

## Configuration Sections

### Database Configuration

```php
DB_HOST         - Database server hostname (usually 'localhost')
DB_NAME         - Name of your database
DB_USER         - Database username
DB_PASSWORD     - Database password
DB_PORT         - Database port (default: 3306)
DB_CHARSET      - Character set (default: 'utf8mb4')
DB_COLLATE      - Database collation (default: 'utf8mb4_unicode_ci')
DB_PREFIX       - Table prefix for multiple installations (default: 'iso_')
```

### Application Settings

```php
SITE_URL        - Your site URL (leave empty for auto-detection)
ADMIN_EMAIL     - Administrator email address
TIMEZONE        - Default timezone (e.g., 'UTC', 'America/New_York')
LANGUAGE        - Default language code (e.g., 'en')
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
DEBUG_MODE         - Enable/disable debug mode (true/false)
DEBUG_QUERIES      - Log database queries (true/false)
DISPLAY_ERRORS     - Show PHP errors (true/false)
ERROR_LEVEL        - PHP error reporting level (e.g., E_ALL)
MAINTENANCE_MODE   - Enable maintenance mode (true/false)
```

### Environment-Specific Configuration

The config file includes automatic environment detection:

```php
/** Current environment */
define('ENVIRONMENT', 'development');  // or 'staging' or 'production'

// Automatic overrides based on environment
if (ENVIRONMENT === 'production') {
    define('DEBUG_MODE', false);
    define('DISPLAY_ERRORS', false);
}
```

## Security Best Practices

### 1. File Permissions

```bash
# Set proper permissions for config.php
chmod 600 config.php  # Read/write for owner only
```

### 2. Git Ignore

The `config.php` file is automatically ignored by Git to prevent accidental commits of sensitive data.

### 3. Backup Your Configuration

Keep a secure backup of your `config.php` file, especially the database credentials and security keys.

## Troubleshooting

### Database Connection Issues

If you get database connection errors:

1. Verify database credentials in `config.php`
2. Ensure the database exists
3. Check that the database user has proper permissions
4. For WSL users, use `127.0.0.1` instead of `localhost`

### Configuration Not Loading

If settings aren't being applied:

1. Ensure `config.php` exists in the root directory
2. Check for PHP syntax errors: `php -l config.php`
3. Verify file permissions allow PHP to read the file

### Missing Configuration File

If `config.php` doesn't exist:

```bash
# The installation wizard will prompt you to create it
# Or manually create from the sample:
cp config.sample.php config.php
```

## Migration from .env

If you're migrating from an older version that used .env files:

1. Copy your database settings from `.env` to `config.php`
2. Delete the `.env` file
3. Remove `vlucas/phpdotenv` dependency: `composer update`

## For Developers and LLMs

### ⚠️ CRITICAL RULES

1. **NEVER** create or reference `.env` files
2. **NEVER** suggest using environment variables for configuration
3. **ALWAYS** use `config.php` for all settings
4. **ALWAYS** access configuration via PHP constants (e.g., `DB_NAME`, not `$_ENV['DB_NAME']`)
5. **NEVER** commit `config.php` to version control

### Helper Function

The `env()` function still exists for backward compatibility but maps to config constants:

```php
// This works but is deprecated:
$dbName = env('DB_NAME', 'default');

// Preferred approach:
$dbName = defined('DB_NAME') ? DB_NAME : 'default';
```

## Example Configurations

### Local Development

```php
define('ENVIRONMENT', 'development');
define('DEBUG_MODE', true);
define('DISPLAY_ERRORS', true);
define('DB_HOST', 'localhost');
define('DB_NAME', 'isotone_dev');
```

### Production Server

```php
define('ENVIRONMENT', 'production');
define('DEBUG_MODE', false);
define('DISPLAY_ERRORS', false);
define('DB_HOST', 'localhost');
define('DB_NAME', 'isotone_prod');
```

### Shared Hosting

```php
define('DB_HOST', 'mysql.yourhost.com');
define('DB_NAME', 'user123_isotone');
define('DB_USER', 'user123_dbuser');
define('DB_PASSWORD', 'strong_password_here');
```

## Support

If you encounter configuration issues:

1. Check this documentation first
2. Review the error logs in `/iso-runtime/logs/`
3. Ask in the [GitHub Discussions](https://github.com/rizonesoft/isotone/discussions)
4. Report bugs in [GitHub Issues](https://github.com/rizonesoft/isotone/issues)

## Configuration Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `DB_HOST` | `localhost` | Database host |
| `DB_NAME` | `database_name_here` | Database name |
| `DB_USER` | `username_here` | Database username |
| `DB_PASSWORD` | `password_here` | Database password |
| `DB_PORT` | `3306` | Configuration setting |
| `DB_CHARSET` | `utf8mb4` | Configuration setting |
| `DB_COLLATE` | `utf8mb4_unicode_ci` | Configuration setting |
| `DB_PREFIX` | `iso_` | Configuration setting |
| `SITE_URL` | `(empty)` | Configuration setting |
| `ADMIN_EMAIL` | `your-email@example.com` | Administrator email |
| `TIMEZONE` | `UTC` | Default timezone |
| `LANGUAGE` | `en` | Configuration setting |
| `AUTH_KEY` | `put your unique phrase here` | Configuration setting |
| `SECURE_AUTH_KEY` | `put your unique phrase here` | Configuration setting |
| `LOGGED_IN_KEY` | `put your unique phrase here` | Configuration setting |
| `NONCE_KEY` | `put your unique phrase here` | Configuration setting |
| `AUTH_SALT` | `put your unique phrase here` | Configuration setting |
| `SECURE_AUTH_SALT` | `put your unique phrase here` | Configuration setting |
| `LOGGED_IN_SALT` | `put your unique phrase here` | Configuration setting |
| `NONCE_SALT` | `put your unique phrase here` | Configuration setting |
| `DEBUG_MODE` | `true` | Configuration setting |
| `DEBUG_QUERIES` | `false` | Configuration setting |
| `DISPLAY_ERRORS` | `true` | Configuration setting |
| `ERROR_LEVEL` | `E_ALL` | Configuration setting |
| `MAINTENANCE_MODE` | `false` | Configuration setting |
| `MEMORY_LIMIT` | `128M` | Configuration setting |
| `MAX_EXECUTION_TIME` | `30` | Configuration setting |
| `UPLOAD_MAX_SIZE` | `10M` | Maximum upload size |
| `SESSION_LIFETIME` | `120` | Session lifetime in minutes |
| `CACHE_TTL` | `3600` | Configuration setting |
| `REDIS_ENABLED` | `false` | Configuration setting |
| `REDIS_HOST` | `127.0.0.1` | Configuration setting |
| `REDIS_PORT` | `6379` | Configuration setting |
| `ENVIRONMENT` | `development` | Configuration setting |
| `DEBUG_MODE` | `false` | Configuration setting |
| `DISPLAY_ERRORS` | `false` | Configuration setting |
| `DEBUG_MODE` | `true` | Configuration setting |
| `DISPLAY_ERRORS` | `false` | Configuration setting |

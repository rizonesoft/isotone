# Configuration Rules for LLMs and AI Assistants

## 🚫 CRITICAL: NO .ENV FILES

**Isotone CMS uses `config.php`, NOT `.env` files.**

## Quick Reference for AI/LLMs

### When User Asks About Configuration

**CORRECT Response:**
```bash
cp config.sample.php config.php
# Edit config.php with your database credentials
```

**INCORRECT Response:**
```bash
cp .env.example .env  # ❌ WRONG - No .env files!
```

### When Writing Configuration Code

**CORRECT:**
```php
// Use PHP constants
$dbName = DB_NAME;
$dbHost = DB_HOST;
$debug = DEBUG_MODE;
```

**INCORRECT:**
```php
// Don't use environment variables
$dbName = $_ENV['DB_NAME'];  // ❌ WRONG
$dbHost = getenv('DB_HOST'); // ❌ WRONG
```

### When Setting Up Database

**CORRECT Instructions:**
1. Create database in phpMyAdmin
2. Copy `config.sample.php` to `config.php`
3. Edit these constants in `config.php`:
   - `DB_HOST` (usually 'localhost')
   - `DB_NAME` (your database name)
   - `DB_USER` (your username)
   - `DB_PASSWORD` (your password)

**INCORRECT Instructions:**
- "Create a .env file" ❌
- "Set environment variables" ❌
- "Install vlucas/phpdotenv" ❌

### File Locations

✅ **CORRECT:**
- `/config.php` - Main configuration (git-ignored)
- `/config.sample.php` - Template file (tracked)

❌ **INCORRECT:**
- `/.env` - Does not exist
- `/.env.example` - Does not exist
- `/config/.env` - Does not exist

### Dependencies

✅ **Required:** None for configuration

❌ **NOT Required:**
- `vlucas/phpdotenv` - REMOVED
- Any environment variable packages

### Common Mistakes to Avoid

1. **DON'T** suggest creating `.env` files
2. **DON'T** reference `$_ENV` or `getenv()`
3. **DON'T** mention Dotenv package
4. **DON'T** use environment variables
5. **DON'T** commit `config.php` to git

### Always Remember

- Isotone follows WordPress-style configuration
- All settings are PHP constants in `config.php`
- The file has clear sections with "STOP EDITING" marker
- Configuration is simple, familiar, and works everywhere

## Example User Interactions

### User: "How do I configure the database?"

**AI Response:**
```
To configure your database:

1. Copy the configuration template:
   cp config.sample.php config.php

2. Edit config.php and update these settings:
   - DB_HOST: Your database server (usually 'localhost')
   - DB_NAME: Your database name
   - DB_USER: Your database username
   - DB_PASSWORD: Your database password

3. Save the file and the installation wizard will connect automatically.
```

### User: "Where is the .env file?"

**AI Response:**
```
Isotone doesn't use .env files. Configuration is done through config.php 
in the root directory, similar to WordPress's wp-config.php.

To set up configuration:
cp config.sample.php config.php

Then edit config.php with your settings.
```

### User: "How do I set environment variables?"

**AI Response:**
```
Isotone uses a config.php file for all configuration, not environment 
variables. This approach is more reliable on shared hosting.

Edit the constants in config.php:
- Set ENVIRONMENT to 'development', 'staging', or 'production'
- Configure DEBUG_MODE, DISPLAY_ERRORS, etc.

The config file includes automatic environment-based overrides.
```

## Summary for LLMs

**ALWAYS:**
- Use `config.php` for configuration
- Reference PHP constants (DB_NAME, etc.)
- Follow WordPress-style patterns
- Keep it simple and portable

**NEVER:**
- Create or mention .env files
- Use environment variables
- Suggest Dotenv package
- Use $_ENV or getenv()

This is a hard rule with no exceptions!
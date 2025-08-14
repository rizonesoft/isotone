# Quick Start Guide

Get Isotone up and running in 5 minutes!

## Prerequisites

- PHP 8.3 or higher
- MySQL/MariaDB database
- Apache with mod_rewrite enabled
- Composer (for dependency management)

## Installation Steps

### 1. Download Isotone

```bash
# Clone from GitHub
git clone https://github.com/rizonesoft/isotone.git
cd isotone

# Or download the latest release
wget https://github.com/rizonesoft/isotone/archive/main.zip
unzip main.zip
cd isotone-main
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Database

Create your configuration file:

```bash
cp config.sample.php config.php
```

Edit `config.php` with your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database');
define('DB_USER', 'your_username');
define('DB_PASSWORD', 'your_password');
```

### 4. Create Database

Create a new database in phpMyAdmin or via command line:

```sql
CREATE DATABASE isotone_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Run Installation Wizard

Navigate to your site in a browser:

```
http://localhost/isotone/install/
```

Follow the installation wizard to:
- Set up your admin account
- Configure site settings
- Initialize the database

### 6. Secure Your Installation

After installation completes:

```bash
# Remove the install directory
rm -rf install/

# Or rename it as backup
mv install/ install.backup/
```

## What's Next?

- **Explore the Admin Panel:** `http://localhost/isotone/admin/`
- **Create Your First Page:** See [Content Management Guide](../guides/content-management.md)
- **Install Plugins:** Check [Plugin Development](../development/plugins.md)
- **Customize Your Theme:** Read [Theme Development](../development/themes.md)

## Troubleshooting

### Database Connection Failed

- Verify your database credentials in `config.php`
- Ensure MySQL/MariaDB is running
- Check that the database exists
- For WSL users: use `127.0.0.1` instead of `localhost`

### 404 Errors on Pages

- Ensure Apache mod_rewrite is enabled
- Check that `.htaccess` file exists in root
- Verify Apache allows `.htaccess` overrides

### White Screen / PHP Errors

- Check PHP version: `php -v` (must be 8.3+)
- Enable error reporting in `config.php`:
  ```php
  define('DEBUG_MODE', true);
  define('DISPLAY_ERRORS', true);
  ```
- Check error logs in `/iso-runtime/logs/`

## Need Help?

- 📖 [Full Documentation](../README.md)
- 💬 [GitHub Discussions](https://github.com/rizonesoft/isotone/discussions)
- 🐛 [Report Issues](https://github.com/rizonesoft/isotone/issues)
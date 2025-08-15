# Installation Guide

## System Requirements

- PHP 8.3 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache with mod_rewrite or Nginx
- Composer 2.0+

## Quick Installation

### 1. Download Isotone

```bash
# Via Git
git clone https://github.com/rizonesoft/isotone.git
cd isotone

# Or download the ZIP from GitHub
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Database

```bash
# Copy the sample configuration
cp config.sample.php config.php

# Edit config.php with your database credentials
```

### 4. Create Database

Create a new database in MySQL/MariaDB:

```sql
CREATE DATABASE isotone_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Run Installation Wizard

Visit `http://your-site.com/isotone/install/` in your browser and follow the setup wizard.

## XAMPP Installation

For local development with XAMPP:

1. Place Isotone in `C:\xampp\htdocs\isotone`
2. Start Apache and MySQL in XAMPP Control Panel
3. Create database via phpMyAdmin
4. Visit `http://localhost/isotone/install/`

## Shared Hosting Installation

1. Upload files via FTP to your web directory
2. Create database via hosting control panel
3. Update `config.php` with database credentials
4. Visit `http://your-domain.com/install/`

## Post-Installation

After installation:

1. Delete the `/install` directory for security
2. Set appropriate file permissions:
   ```bash
   chmod 755 iso-content/
   chmod 755 iso-content/uploads/
   ```
3. Configure your admin account
4. Select and activate a theme

## Troubleshooting

### Common Issues

**Error: 500 Internal Server Error**
- Check `.htaccess` file exists
- Ensure mod_rewrite is enabled
- Verify PHP version is 8.3+

**Database Connection Failed**
- Verify database credentials in `config.php`
- Ensure database exists
- Check database user permissions

**Blank Page**
- Enable PHP error reporting
- Check PHP error logs
- Verify all dependencies installed

## Next Steps

- [Configure Isotone](./configuration.md)
- [Install a Theme](../guide/themes.md)
- [Create Your First Post](./first-steps.md)
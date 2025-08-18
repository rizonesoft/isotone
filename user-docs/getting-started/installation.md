# Installation Guide

## System Requirements

### Minimum Requirements
- **PHP**: 8.3 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Web Server**: Apache 2.4+ with mod_rewrite enabled, or Nginx
- **Composer**: 2.0 or higher
- **Node.js**: 18.0+ (for Tailwind CSS compilation)
- **NPM**: 8.0+ (comes with Node.js)

### Required PHP Extensions
- PDO and PDO_MySQL
- mbstring
- json (usually built-in)
- fileinfo
- curl
- gd or imagick (for image processing)
- openssl
- zip

### Recommended Server Settings
- Memory Limit: 128MB minimum (256MB recommended)
- Max Execution Time: 30 seconds (60 recommended)
- Upload Max Filesize: 10MB (32MB recommended)
- Post Max Size: 10MB (32MB recommended)

## Installation Methods

### Method 1: Standard Installation

#### 1. Download Isotone

```bash
# Clone via Git (recommended)
git clone https://github.com/rizonesoft/isotone.git
cd isotone

# Or download the latest release ZIP from GitHub
# Extract to your web directory
```

#### 2. Install PHP Dependencies

```bash
# Install all PHP dependencies via Composer
composer install --no-dev --optimize-autoloader

# For development environments, include dev dependencies
composer install
```

#### 3. Database Setup

Create a new MySQL database:

```sql
CREATE DATABASE isotone_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create a database user (optional but recommended)
CREATE USER 'isotone_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON isotone_db.* TO 'isotone_user'@'localhost';
FLUSH PRIVILEGES;
```

#### 4. Configure Isotone

```bash
# Copy the sample configuration file
cp config.sample.php config.php

# Edit config.php with your database credentials
nano config.php
```

Update the following in `config.php`:

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'isotone_db');
define('DB_USER', 'isotone_user');
define('DB_PASSWORD', 'your_password');

// Site URL (leave empty for auto-detection or specify)
define('SITE_URL', '');  // Or 'https://your-domain.com'

// Security Keys and Salts (replace with unique values)
define('AUTH_KEY',         'put your unique phrase here');
define('SECURE_AUTH_KEY',  'put your unique phrase here');
define('LOGGED_IN_KEY',    'put your unique phrase here');
define('NONCE_KEY',        'put your unique phrase here');
define('AUTH_SALT',        'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT',   'put your unique phrase here');
define('NONCE_SALT',       'put your unique phrase here');
```

> **Tip**: Generate secure keys at https://api.wordpress.org/secret-key/1.1/salt/

#### 5. Build CSS Assets

Isotone uses Tailwind CSS v4. The CSS needs to be built after installation:

```bash
# Install Tailwind CSS dependencies (downloads Node.js if needed)
composer tailwind:install

# Build the CSS files
composer tailwind:build

# For production (minified CSS)
composer tailwind:minify

# For development (watch mode)
composer tailwind:watch
```

#### 6. Run Installation Wizard

1. Navigate to `http://your-domain.com/install/` in your browser
2. Follow the installation wizard:
   - System requirements check
   - Database connection verification
   - Admin account creation
   - Initial site configuration

#### 7. Post-Installation Security

```bash
# Remove installation directory (IMPORTANT!)
rm -rf install/

# Set proper file permissions
chmod 755 iso-content/
chmod 755 iso-content/uploads/
chmod 755 iso-content/themes/
chmod 755 iso-content/plugins/
chmod 755 storage/
chmod 644 config.php

# Protect sensitive directories with .htaccess (Apache)
echo "Deny from all" > iso-automation/.htaccess
echo "Deny from all" > storage/.htaccess
```

### Method 2: Shared Hosting Installation

#### 1. Prepare Files Locally

```bash
# Download Isotone
git clone https://github.com/rizonesoft/isotone.git

# Install dependencies
composer install --no-dev --optimize-autoloader

# Build CSS (requires Node.js locally)
composer tailwind:install
composer tailwind:minify

# Create config from sample
cp config.sample.php config.php
```

#### 2. Upload to Hosting

1. Use FTP/SFTP to upload all files to your web root
2. Ensure `.htaccess` file is uploaded (may be hidden)
3. Create database via hosting control panel (cPanel, Plesk, etc.)

#### 3. Configure via Hosting Panel

1. Edit `config.php` with your hosting database details
2. Set file permissions through file manager:
   - Folders: 755
   - Files: 644
   - `iso-content/uploads/`: 775 (if needed for uploads)

#### 4. Complete Installation

Visit `http://your-domain.com/install/` and complete the wizard.

### Method 3: Docker Installation (Development)

```bash
# Clone repository
git clone https://github.com/rizonesoft/isotone.git
cd isotone

# Copy config file
cp config.sample.php config.php

# Start containers (if Docker configuration exists)
docker-compose up -d

# Install dependencies
docker-compose exec app composer install
docker-compose exec app composer tailwind:install
docker-compose exec app composer tailwind:build

# Visit {your-domain}/install/
```

## Advanced Configuration

### Enable Pretty URLs

For Apache (already included in `.htaccess`):
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
```

For Nginx:
```nginx
location / {
    try_files $uri $uri/ /index.php?url=$uri&$args;
}
```

### Performance Optimization

```bash
# Enable OPcache (php.ini)
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000

# Build optimized autoloader
composer dump-autoload --optimize

# Minify CSS for production
composer tailwind:minify
```

### Environment Configuration

Edit `config.php` to set your environment:

```php
/** Current environment (development, staging, production) */
define('ENVIRONMENT', 'production');

// For development
define('DEBUG_MODE', true);
define('DISPLAY_ERRORS', true);

// For production
define('DEBUG_MODE', false);
define('DISPLAY_ERRORS', false);
```

## Automation & Development Tools

After installation, Isotone includes powerful automation tools:

```bash
# Documentation Management
composer docs:check         # Check documentation
composer docs:update        # Update documentation
composer docs:hooks         # Generate hooks documentation

# Validation & Testing
composer validate:rules     # Validate automation rules
composer test               # Run tests
composer check-style        # Check code style
composer analyse            # Run static analysis

# IDE & Development
composer ide:sync           # Sync IDE configuration

# CSS Management
composer tailwind:build     # Build CSS
composer tailwind:watch     # Watch CSS changes
composer tailwind:minify    # Minify CSS for production
composer tailwind:status    # Check Tailwind build status
```

## Troubleshooting

### Common Issues

**Blank White Page**
- Enable error display in `config.php`: `define('DISPLAY_ERRORS', true);`
- Check PHP error logs
- Verify PHP version: `php -v` (must be 8.3+)
- Ensure all Composer dependencies installed

**CSS Not Loading / Styling Broken**
```bash
# Rebuild Tailwind CSS
composer tailwind:build

# Check if CSS file exists
ls -la iso-admin/css/tailwind.css

# Check Tailwind status
composer tailwind:status
```

**Database Connection Error**
- Verify credentials in `config.php`
- Check database exists and is accessible
- For WSL/Docker, use host machine IP instead of localhost (e.g., 172.19.240.1)
- Test connection: `mysql -h localhost -u username -p database_name`

**500 Internal Server Error**
- Check `.htaccess` file exists
- Verify mod_rewrite is enabled: `a2enmod rewrite`
- Check file permissions (folders: 755, files: 644)
- Review Apache/Nginx error logs

**Composer Memory Errors**
```bash
# Increase memory limit
COMPOSER_MEMORY_LIMIT=-1 composer install
```

**Missing Tailwind Styles**
```bash
# Reinstall and rebuild
composer tailwind:install
composer tailwind:build

# Check Node.js version
node --version  # Should be 18+
```

### Getting Help

1. Check the [Documentation](../index.md)
2. Review the [CLAUDE.md](../../CLAUDE.md) file for development guidelines
3. Visit [GitHub Issues](https://github.com/rizonesoft/isotone/issues)

## Next Steps

After successful installation:

1. **Configure Your Site**: Update site settings in Admin → Settings
2. **Install a Theme**: Browse and activate themes in Admin → Appearance
3. **Set Up Plugins**: Extend functionality via Admin → Plugins
4. **Create Content**: Start with your first post or page
5. **Explore Development Tools**: Check Admin → Development → Automation

## Version Management

Isotone includes built-in version management commands:

```bash
# Bump version (creates new version and updates changelog)
php isotone version:bump patch alpha  # For patch releases
php isotone version:bump minor alpha  # For minor releases
php isotone version:bump major alpha  # For major releases

# Or use Composer scripts
composer version:patch
composer version:minor
composer version:major
```

## Security Recommendations

1. **Always remove the `/install` directory after installation**
2. **Use strong, unique security keys in config.php**
3. **Set proper file permissions (755 for directories, 644 for files)**
4. **Keep PHP and all dependencies updated**
5. **Use HTTPS in production**
6. **Regularly backup your database and files**
7. **Monitor error logs for suspicious activity**

## System Health Check

After installation, regularly check your system health:

1. Review PHP configuration with `phpinfo()`
2. Check available PHP extensions: `php -m`
3. Monitor error logs in `/storage/logs/`
4. Use debugging tools in development mode
5. Test database connectivity periodically
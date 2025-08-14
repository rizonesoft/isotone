# Development Environment Setup

This guide will help you set up a local development environment for Isotone CMS.

## Prerequisites

### Required Software

- **PHP 8.3+** - The minimum PHP version required
- **MySQL 5.7+** or **MariaDB 10.3+** - Database server
- **Apache 2.4+** - Web server with mod_rewrite enabled
- **Composer 2.0+** - PHP dependency manager

### Recommended Development Stacks

#### Windows
- **XAMPP** (Recommended) - Includes Apache, MySQL/MariaDB, PHP
- **WAMP** - Alternative to XAMPP
- **Laragon** - Modern, lightweight alternative

#### macOS
- **MAMP** - Simple Apache, MySQL, PHP stack
- **Laravel Valet** - Lightweight development environment
- **Homebrew** - Install components individually

#### Linux
- **LAMP Stack** - Install via package manager
- **Docker** - Containerized environment (coming soon)

## Installation Steps

### 1. Setting Up XAMPP (Windows)

#### Install XAMPP
1. Download XAMPP from [apachefriends.org](https://www.apachefriends.org/)
2. Install to `C:\xampp` (default location)
3. Start Apache and MySQL from XAMPP Control Panel

#### Enable Required Modules
1. Open XAMPP Control Panel
2. Click "Config" for Apache → "httpd.conf"
3. Ensure these lines are uncommented:
   ```apache
   LoadModule rewrite_module modules/mod_rewrite.so
   ```
4. Find the `<Directory>` block for htdocs and ensure:
   ```apache
   AllowOverride All
   ```
5. Save and restart Apache

### 2. Install Composer

#### Windows
1. Download from [getcomposer.org](https://getcomposer.org/download/)
2. Run the installer
3. Select your PHP executable (e.g., `C:\xampp\php\php.exe`)
4. Verify installation:
   ```bash
   composer --version
   ```

#### macOS/Linux
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

### 3. Clone or Download Isotone

#### Option A: Git Clone (Recommended)
```bash
cd C:\xampp\htdocs  # Or your web root
git clone https://github.com/rizonesoft/isotone.git
cd isotone
```

#### Option B: Download ZIP
1. Download the latest release
2. Extract to your web root (e.g., `C:\xampp\htdocs\isotone`)

### 4. Install Dependencies

Navigate to the project directory and run:

```bash
composer install
```

This will install all PHP dependencies including:
- RedBeanPHP (ORM)
- Symfony Components
- Monolog (Logging)
- PHPUnit (Testing)

### 5. Environment Configuration

1. Copy the environment template:
   ```bash
   cp .env.example .env
   ```
   
   On Windows:
   ```cmd
   copy .env.example .env
   ```

2. Edit `.env` file with your settings:
   ```env
   # Application
   APP_NAME="Isotone CMS"
   APP_ENV=development
   APP_DEBUG=true
   APP_URL=http://localhost/isotone
   
   # Database (XAMPP defaults)
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=isotone_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

### 6. Database Setup

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create a new database named `isotone_db`
3. Set collation to `utf8mb4_unicode_ci`

### 7. Run Installation Wizard

1. Visit http://localhost/isotone/install/ in your browser
2. The wizard will check database connection
3. Choose your Super Admin credentials:
   - Username (minimum 3 characters)
   - Email address
   - Password (minimum 8 characters)
4. Complete the installation
5. **Important:** Delete or rename the `/install` directory for security

### 8. Verify Installation

Visit http://localhost/isotone/ in your browser. You should see:
- ✅ Welcome page with Isotone logo
- ✅ PHP version check
- ✅ Composer status
- ✅ Database connection status
- ✅ Environment status

## Troubleshooting

### 404 Error on Homepage

1. **Check mod_rewrite is enabled:**
   - Restart Apache after enabling
   
2. **Verify .htaccess file:**
   - Ensure `.htaccess` exists in root directory
   - Check Apache allows .htaccess overrides

3. **Try direct access:**
   - Visit http://localhost/isotone/
   - If you see directory listing, check if index.php exists
   - If this doesn't work, check Apache configuration

### Composer Not Found

- **Windows:** Ensure Composer is in your PATH
- **Use full path:** `C:\ProgramData\ComposerSetup\bin\composer`
- **Alternative:** Download composer.phar and use:
  ```bash
  php composer.phar install
  ```

### Permission Issues (Linux/macOS)

```bash
# Set proper permissions
chmod -R 755 isotone
chmod -R 777 storage
chmod -R 777 content/cache
chmod -R 777 content/uploads
```

### Database Connection Failed

1. Verify MySQL/MariaDB is running
2. Check credentials in `.env`
3. Ensure database `isotone_db` exists
4. Test connection:
   ```bash
   mysql -u root -p -e "SHOW DATABASES;"
   ```
5. Try the test script:
   ```
   http://localhost/isotone/install/test-db.php
   ```

## Development Tools

### VS Code Extensions

Recommended extensions for Isotone development:
- **PHP Intelephense** - PHP intelligence
- **PHP Debug** - Debugging support
- **DotENV** - .env file syntax
- **GitLens** - Git integration
- **Prettier** - Code formatting

### PHPStorm Configuration

1. Set PHP interpreter to XAMPP's PHP
2. Configure Composer executable
3. Enable WordPress code style (similar to Isotone)
4. Set up database connection for SQL support

### Running Tests

```bash
# Run all tests
composer test

# Run with coverage
composer test -- --coverage-html coverage

# Run specific test
./vendor/bin/phpunit tests/Unit/ApplicationTest.php
```

### Code Quality

```bash
# Check code style
composer check-style

# Fix code style
composer fix-style

# Run static analysis
composer analyse
```

## Next Steps

- Read the [Getting Started Guide](GETTING-STARTED.md)
- Explore the [Project Structure](project-structure.md)
- Learn about [Plugin Development](plugin-development.md)
- Review [Coding Standards](coding-standards.md)

## Getting Help

- **Documentation:** Check the `/docs` directory
- **Issues:** Report bugs on GitHub
- **Community:** Join our Discord server
- **FAQ:** See [Troubleshooting](#troubleshooting) above

---

*Last updated: 2025*
# Database Connection Guide

This guide explains how the database connection works and how to configure external tools (IDEs, database clients) to connect.

## Application Database Connection

The application uses **intelligent environment detection** to connect to MySQL:

### Web Context (Browser)
- **Host**: `127.0.0.1` (automatically converted from `localhost`)
- **Port**: `3306`
- **Database**: `isotone_db`
- **Username**: `root`
- **Password**: (empty)

### CLI Context (WSL)
- **Host**: Windows host IP (auto-detected, usually `10.255.255.254` or `172.19.240.1`)
- **Port**: `3306`
- **Database**: `isotone_db`
- **Username**: `root`
- **Password**: (empty)

## IDE/Database Client Configuration

### For IDEs and Database Clients (PhpStorm, VSCode, DBeaver, etc.)

Since you're running XAMPP on Windows, use these settings:

```yaml
Host: localhost  # or 127.0.0.1
Port: 3306
Database: isotone_db
Username: root
Password: (leave empty)
```

### Connection Methods by Tool

#### PhpStorm / IntelliJ IDEA
1. Open Database tool window (View → Tool Windows → Database)
2. Click + → Data Source → MySQL
3. Configuration:
   ```
   Host: localhost
   Port: 3306
   Database: isotone_db
   User: root
   Password: (empty)
   URL: jdbc:mysql://localhost:3306/isotone_db
   ```

#### Visual Studio Code (MySQL Extension)
1. Install MySQL extension by Jun Han
2. Add connection:
   ```json
   {
     "host": "localhost",
     "port": 3306,
     "user": "root",
     "password": "",
     "database": "isotone_db"
   }
   ```

#### DBeaver
1. New Database Connection → MySQL
2. Settings:
   ```
   Server Host: localhost
   Port: 3306
   Database: isotone_db
   Username: root
   Password: (empty)
   ```

#### MySQL Workbench
1. New Connection
2. Connection settings:
   ```
   Connection Name: Isotone
   Hostname: 127.0.0.1
   Port: 3306
   Username: root
   Password: (empty)
   Default Schema: isotone_db
   ```

#### TablePlus
1. Create new connection → MySQL
2. Settings:
   ```
   Host: 127.0.0.1
   Port: 3306
   User: root
   Password: (empty)
   Database: isotone_db
   ```

#### HeidiSQL (comes with XAMPP)
1. Session manager → New
2. Settings:
   ```
   Network type: MySQL (TCP/IP)
   Hostname / IP: 127.0.0.1
   User: root
   Password: (empty)
   Port: 3306
   ```

### Command Line Access

#### From Windows (XAMPP)
```bash
C:\xampp\mysql\bin\mysql -u root -p isotone_db
# Press Enter when prompted for password (empty)
```

#### From WSL
```bash
# If mysql client is installed in WSL
mysql -h 127.0.0.1 -u root -p isotone_db
# Press Enter when prompted for password
```

## How It Works (Technical Details)

The application's `DatabaseService.php` uses smart detection:

1. **Detects Environment**: Checks if running in WSL and whether it's CLI or web
2. **Selects Appropriate Host**:
   - Web context: Uses `127.0.0.1` (TCP connection)
   - WSL CLI: Attempts to find Windows host IP
   - Regular environment: Uses configured host

This **DOES NOT** interfere with IDE connections because:
- The smart detection only applies to the PHP application
- IDEs connect directly to MySQL on Windows
- The `.env` file remains simple (`localhost`)
- External tools bypass the PHP layer entirely

## Environment Variables (.env)

The `.env` file keeps simple, standard configuration:

```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=isotone_db
DB_USERNAME=root
DB_PASSWORD=
```

## Troubleshooting

### IDE Can't Connect
1. **Check XAMPP MySQL is running**: Green light in XAMPP Control Panel
2. **Try 127.0.0.1 instead of localhost**: Some tools prefer IP
3. **Check Windows Firewall**: Port 3306 should be accessible
4. **Verify MySQL bind-address**: Should be `0.0.0.0` or `127.0.0.1` in `my.ini`

### Application Can't Connect
1. **Web access works?**: Check http://localhost/isotone/
2. **Database exists?**: Verify `isotone_db` exists in phpMyAdmin
3. **Check .env file**: Ensure it's not `.env.example`
4. **Run installation wizard**: Visit http://localhost/isotone/install/
5. **Test connection**: Visit http://localhost/isotone/install/test-db.php

### WSL CLI Can't Connect
This is expected behavior. The application will auto-detect and handle WSL connections.
- http://localhost/isotone/ (main application)
- Use phpMyAdmin for database management: http://localhost/phpmyadmin

## Database Schema

After running the installation wizard, these tables are created:

```sql
setting    -- System configuration
user       -- User accounts  
content    -- Pages and content (when created)
```

### Table Structure

**setting table:**
- `setting_key` - Configuration key name (e.g., 'site_title')
- `setting_value` - Configuration value
- `setting_type` - Data type (string, int, boolean, etc.)
- `updated_at` - Last update timestamp

**user table:**
- `username` - User login name
- `email` - User email address
- `password` - Hashed password
- `role` - User role (superadmin, admin, editor, etc.)
- `status` - Account status (active, inactive, suspended)
- `created_at` - Account creation timestamp
- `updated_at` - Last update timestamp

Note: RedBeanPHP uses singular table names without underscores by convention. Column names avoid MySQL reserved words like 'key', 'value', and 'type'.

## RedBeanPHP ORM

The application uses RedBeanPHP, which:
- Creates tables automatically when needed
- Doesn't require migrations for development
- Uses convention: lowercase singular table names
- Table naming: `user`, `setting`, `content`, etc. (simple names, no prefixes)

## Best Practices

1. **Development**: Let RedBeanPHP create/modify tables (freeze = false)
2. **Production**: Freeze the schema (freeze = true)
3. **Backups**: Regular exports via phpMyAdmin or mysqldump
4. **Version Control**: Don't commit `.env` file (use `.env.example`)

## Quick Reference Card

```
Application (Web):     127.0.0.1:3306/isotone_db
Application (WSL CLI): Auto-detected Windows IP
Your IDE:             localhost:3306/isotone_db
phpMyAdmin:           http://localhost/phpmyadmin
```

---

This configuration ensures all tools can connect without interfering with each other!
# Isotone CMS Installation

This directory contains the web-based installation wizard for Isotone CMS.

## 🚀 How to Install

1. **Ensure database is configured**
   - Edit `.env` file with your database credentials
   - Database should be created (e.g., `isotone_db`)

2. **Access the installer**
   - Navigate to: `http://localhost/isotone/install/`
   - Or: `https://yourdomain.com/install/`

3. **Set up Super Admin**
   - Choose your username (minimum 3 characters)
   - Enter valid email address
   - Create strong password (minimum 8 characters)
   - Confirm your password

4. **Complete installation**
   - Click "Install Isotone CMS"
   - Installation creates:
     - Super Admin user account
     - Initial system settings
     - `.isotone-installed` marker file

## 🔒 Security

### After Installation
- **DELETE this directory** for production sites
- Or rename it: `mv install install.backup`
- Or protect with .htaccess authentication

### Installation Lock
- Installation creates `.isotone-installed` file in root
- Delete this file to allow reinstallation
- Presence of this file prevents accidental reinstalls

## 🔧 Troubleshooting

### Database Connection Failed
- Check `.env` file has correct credentials
- Verify MySQL/MariaDB is running
- Ensure database exists
- Test connection with the button in installer

### Can't Access Installer
- Check Apache/Nginx is running
- Verify `.htaccess` is not blocking access
- Ensure `/install/` directory has proper permissions

### Already Installed Message
- Delete `.isotone-installed` file in root directory
- This safety feature prevents accidental data loss

## 📝 Default Settings

The installer creates these default settings:
- Site Title: "Isotone CMS"
- Site Description: "Lightweight. Powerful. Everywhere."
- Timezone: UTC
- Date Format: Y-m-d
- Time Format: H:i:s

These can be changed in the Admin Panel after installation.

## ⚠️ Important Notes

1. **Never commit credentials** - The `.isotone-installed` file contains no passwords
2. **Use strong passwords** - Minimum 8 characters, mix of letters, numbers, symbols
3. **Unique usernames** - Don't use common names like "admin" or "root"
4. **Valid email** - Used for password recovery and notifications

## 🎨 Themed Interface

The installer uses Isotone's signature design:
- Dark theme with glassmorphism
- Electric cyan (#00D9FF) and neon green (#00FF88) accents
- Responsive design for mobile installation
- Real-time validation feedback

## Manual Installation (CLI)

If you prefer command-line installation:

```php
php isotone install:admin --username=yourusername --email=you@example.com
```

You'll be prompted for the password securely.
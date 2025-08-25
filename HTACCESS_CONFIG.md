# .htaccess Configuration Guide for Isotone

## Overview
Isotone uses a hybrid approach for error handling to maximize compatibility across different installation directories.

## How It Works

### 1. Primary Method: mod_rewrite (Relative Paths)
The `.htaccess` file uses mod_rewrite rules that work with **relative paths** from the .htaccess location:

```apache
# This works regardless of installation directory
RewriteRule .* server/error.php?code=404 [L]
```

This means if Isotone is installed in:
- `/` → Loads `/server/error.php`
- `/isotone/` → Loads `/isotone/server/error.php`
- `/sites/mysite/` → Loads `/sites/mysite/server/error.php`

**No configuration needed!** These rules work automatically.

### 2. Fallback Method: ErrorDocument (Absolute Paths)
For errors that can't be caught by mod_rewrite (like 500 Internal Server Error), we use ErrorDocument directives as a fallback:

```apache
ErrorDocument 404 /isotone/server/error.php?code=404
```

⚠️ **These need adjustment based on your installation directory!**

## Installation Directory Configuration

### If Isotone is installed in the root directory (`/`)
Update the ErrorDocument lines in `.htaccess`:
```apache
ErrorDocument 400 /server/error.php?code=400
ErrorDocument 401 /server/error.php?code=401
ErrorDocument 403 /server/error.php?code=403
ErrorDocument 404 /server/error.php?code=404
ErrorDocument 405 /server/error.php?code=405
ErrorDocument 408 /server/error.php?code=408
ErrorDocument 500 /server/error.php?code=500
ErrorDocument 502 /server/error.php?code=502
ErrorDocument 503 /server/error.php?code=503
ErrorDocument 504 /server/error.php?code=504
```

### If Isotone is installed in `/isotone/` (default)
No changes needed! The .htaccess comes pre-configured for this.

### If Isotone is installed in a custom directory (e.g., `/myapp/`)
Update the ErrorDocument lines in `.htaccess`:
```apache
ErrorDocument 400 /myapp/server/error.php?code=400
ErrorDocument 401 /myapp/server/error.php?code=401
ErrorDocument 403 /myapp/server/error.php?code=403
ErrorDocument 404 /myapp/server/error.php?code=404
ErrorDocument 405 /myapp/server/error.php?code=405
ErrorDocument 408 /myapp/server/error.php?code=408
ErrorDocument 500 /myapp/server/error.php?code=500
ErrorDocument 502 /myapp/server/error.php?code=502
ErrorDocument 503 /myapp/server/error.php?code=503
ErrorDocument 504 /myapp/server/error.php?code=504
```

## Why This Hybrid Approach?

1. **mod_rewrite handles most errors** - Works with relative paths, no configuration needed
2. **ErrorDocument is a safety net** - Catches server-level errors that mod_rewrite can't intercept
3. **Maximum compatibility** - Works on all Apache servers with mod_rewrite enabled

## Testing Your Configuration

After installation, test that error pages work correctly:

1. Visit a non-existent page: `http://yoursite.com/isotone/does-not-exist`
2. You should see the styled 404 error page with the quantum/multiverse theme
3. Check browser console for the friendly easter egg messages

## Troubleshooting

### Error pages show plain text instead of styled page
- Make sure mod_rewrite is enabled on your server
- Check that the `/server/error.php` file exists and is readable

### Error pages not showing at all
- Verify the ErrorDocument paths match your installation directory
- Ensure `.htaccess` files are being processed (AllowOverride must be enabled)

### Getting 500 Internal Server Error
- Check Apache error logs for specific issues
- Verify file permissions (error.php should be readable by web server)
- Make sure PHP is properly configured

## Notes

- The universal error handler (`server/error.php`) handles all error codes
- Theme overrides are supported - themes can provide custom error pages
- The error page includes performance-optimized animations and glitch effects
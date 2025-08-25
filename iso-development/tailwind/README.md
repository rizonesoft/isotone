# Tailwind CSS Build System

This directory contains the Tailwind CSS v4 build system for Isotone.

## Setup

### First-time Installation
```bash
# Install dependencies
composer tailwind:install

# Build CSS
composer tailwind:build
```

## Usage

### Build Commands
All commands are available through Composer:

- `composer tailwind:build` - Build CSS for production
- `composer tailwind:watch` - Watch files and rebuild on changes
- `composer tailwind:minify` - Build minified production CSS
- `composer tailwind:update` - Update Tailwind to latest version
- `composer tailwind:status` - Check build status

### Direct NPM Commands
You can also run commands directly in this directory:

```bash
cd iso-automation/tailwind
npm run build    # Build CSS
npm run watch    # Watch mode
npm run minify   # Minified build
```

## Configuration

### Content Scanning
Tailwind v4 automatically scans for classes using the `@source` directives in `src/input.css`:

- `iso-admin/**/*.{php,html,js}` - Admin interface files
- `iso-core/**/*.php` - Core PHP files  
- `iso-themes/**/*.php` - Theme files
- `iso-content/**/*.php` - Content files

### Output Files
- **Regular CSS**: `iso-admin/css/tailwind.css` (64KB)
- **Minified CSS**: `iso-admin/css/tailwind.min.css` (47KB)

## Production Deployment

⚠️ **Important**: The Tailwind CDN should NOT be used in production.

Before deploying to production:

1. Build the CSS file:
   ```bash
   composer tailwind:build
   # or for minified version:
   composer tailwind:minify
   ```

2. Commit the built CSS files:
   ```bash
   git add iso-admin/css/tailwind.css
   git add iso-admin/css/tailwind.min.css
   git commit -m "chore: Build Tailwind CSS for production"
   ```

3. The admin interface will automatically use the built CSS file when it exists.

## Fallback Behavior

If the built CSS file doesn't exist, the system will fall back to the Tailwind CDN with a console warning. This is only intended for development environments.

## Dependencies

- **Tailwind CSS**: v4.0.0-beta.8
- **@tailwindcss/cli**: v4.0.0-beta.8
- **Alpine.js**: v3.14.9 (included for future bundling)
- **Chart.js**: v4.5.0 (included for future bundling)

## Notes

- Dark mode support is pending full implementation in Tailwind v4 beta
- The build system uses Tailwind's new `@source` directive for content detection
- No PostCSS configuration is required with Tailwind v4
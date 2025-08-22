# Icon API

The Isotone Icon API provides efficient, on-demand loading of icons for better performance. Instead of loading entire icon libraries, you can load only the icons you need.

## Overview

The Icon API offers three ways to use icons:

1. **Lazy Loading** (Recommended) - Icons load as needed
2. **Inline SVG** - Immediate rendering, cached in memory
3. **Direct URL** - For custom implementations

## Quick Start

### Basic Usage

```php
// Display an icon (lazy loaded by default)
iso_icon('home'); // Outline style

// Specific style
iso_icon('user', 'solid');
iso_icon('cog', 'micro');

// With attributes
iso_icon('search', 'outline', [
    'class' => 'w-6 h-6 text-blue-500',
    'width' => '24',
    'height' => '24'
]);
```

### Style Shortcuts

```php
// These are equivalent
iso_icon('home', 'outline');
iso_icon_outline('home');

iso_icon('user', 'solid');
iso_icon_solid('user');

iso_icon('cog', 'micro');
iso_icon_micro('cog');
```

## Icon Styles

| Style | Size | Description |
|-------|------|-------------|
| `outline` | 24x24 | Outlined icons with stroke |
| `solid` | 24x24 | Filled solid icons |
| `micro` | 16x16 | Small icons for tight spaces |

## API Reference

### Global Functions

#### `iso_icon($name, $style, $attributes, $lazy)`

Display an icon (echoes output).

**Parameters:**
- `$name` (string) - Icon name (e.g., 'home', 'user', 'cog')
- `$style` (string) - Icon style: 'outline', 'solid', 'micro' (default: 'outline')
- `$attributes` (array) - HTML/SVG attributes (default: [])
- `$lazy` (bool) - Use lazy loading (default: true)

**Example:**
```php
iso_icon('home', 'outline', ['class' => 'icon'], true);
```

#### `iso_get_icon($name, $style, $attributes, $lazy)`

Get an icon (returns output without echoing).

**Returns:** string - HTML for the icon

**Example:**
```php
$homeIcon = iso_get_icon('home', 'solid', ['width' => '32']);
echo $homeIcon;
```

#### `iso_get_icon_url($name, $style, $params)`

Get icon URL for custom implementations.

**Parameters:**
- `$params` (array) - URL parameters: size, class, color

**Example:**
```php
$url = iso_get_icon_url('home', 'outline', [
    'size' => 32,
    'color' => '#3B82F6'
]);
// Returns: /iso-api/icons.php?name=home&style=outline&size=32&color=%233B82F6
```

### Helper Functions

#### `iso_icon_button($icon, $text, $button_attrs, $icon_attrs, $icon_position)`

Create a button with an icon.

**Example:**
```php
echo iso_icon_button('plus', 'Add Item', [
    'class' => 'btn btn-primary',
    'onclick' => 'addItem()'
]);
```

#### `iso_icon_link($icon, $text, $url, $link_attrs, $icon_attrs)`

Create a link with an icon.

**Example:**
```php
echo iso_icon_link('external-link', 'Visit Site', 'https://example.com', [
    'target' => '_blank',
    'class' => 'external-link'
]);
```

## Performance Optimization

### Preloading Icons

For icons used frequently, preload them to improve performance:

```php
// In your theme's functions.php or plugin init
iso_preload_icons([
    ['name' => 'home', 'style' => 'outline'],
    ['name' => 'user', 'style' => 'solid'],
    ['name' => 'cog', 'style' => 'outline'],
    ['name' => 'search', 'style' => 'micro']
]);
```

### Register Icons for Auto-Preload

```php
// Register icons that should always be preloaded
iso_register_icons([
    ['name' => 'menu', 'style' => 'outline'],
    ['name' => 'close', 'style' => 'outline'],
    ['name' => 'user', 'style' => 'solid']
]);
```

## Direct API Usage

### HTTP API Endpoint

**URL:** `/iso-api/icons.php`

**Method:** `GET`

**Parameters:**
- `name` (required) - Icon name
- `style` (optional) - Icon style: `outline`, `solid`, `micro` (default: `outline`)
- `size` (optional) - Icon size in pixels, range 8-1024 (default: `24`)
- `class` (optional) - CSS class name
- `color` (optional) - Icon color (default: `currentColor`)

**Examples:**
```
GET /iso-api/icons.php?name=home&style=outline&size=32&color=blue
GET /iso-api/icons.php?name=user&style=solid&size=24
GET /iso-api/icons.php?name=cog&style=micro&size=16&class=spin
```

**Response:**
- **200** - SVG icon content
- **304** - Not Modified (cached)
- **400** - Bad request (invalid parameters)
- **404** - Icon not found
- **405** - Method not allowed (use GET)

### HTML Usage

```html
<!-- Lazy loaded image -->
<img src="/iso-api/icons.php?name=home&style=outline&size=24" 
     alt="Home icon" 
     loading="lazy">

<!-- CSS background -->
<div style="background-image: url('/iso-api/icons.php?name=star&style=solid&size=16')"></div>
```

### JavaScript Usage

```javascript
// Fetch icon URL
const iconUrl = `/iso-api/icons.php?name=heart&style=solid&size=20`;

// Use in JavaScript
const img = document.createElement('img');
img.src = iconUrl;
img.alt = 'Heart icon';
document.body.appendChild(img);

// Fetch as text for inline SVG
fetch('/iso-api/icons.php?name=home&style=outline')
    .then(response => response.text())
    .then(svg => {
        document.getElementById('icon-container').innerHTML = svg;
    });
```

## CSS Styling

### Default Classes

The helper functions add default CSS classes:

```css
/* Icon buttons */
.iso-icon-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.iso-icon-button__icon {
    flex-shrink: 0;
}

/* Icon links */
.iso-icon-link {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    text-decoration: none;
}

.iso-icon-link__icon {
    flex-shrink: 0;
}
```

### Custom Styling

```php
// Add custom classes
iso_icon('home', 'outline', [
    'class' => 'w-6 h-6 text-blue-500 hover:text-blue-700'
]);

// Inline styles
iso_icon('user', 'solid', [
    'style' => 'width: 20px; height: 20px; color: #ef4444;'
]);
```

## Advanced Usage

### IconAPI Class Methods

For advanced usage, you can use the `IconAPI` class directly:

```php
// Load IconAPI
require_once 'iso-core/Core/IconAPI.php';

// Get icon URL
$url = IconAPI::getIconUrl('home', 'outline', ['size' => 32]);

// Get as image tag
$img = IconAPI::getIconImg('user', 'solid', ['width' => 24]);

// Get inline SVG
$svg = IconAPI::getIconSvg('cog', 'outline', ['class' => 'spin']);

// Preload icons
IconAPI::preloadIcons([
    ['name' => 'home', 'style' => 'outline'],
    ['name' => 'user', 'style' => 'solid']
]);

// Clear cache
IconAPI::clearCache();

// Debug cached icons
$cached = IconAPI::getCachedIcons();
```

## Error Handling

### Missing Icons

If an icon doesn't exist:

```php
// Returns empty string for invalid icons
$icon = iso_get_icon('nonexistent-icon');
echo empty($icon) ? 'Icon not found' : $icon;
```

### HTTP API Errors

The HTTP API returns proper status codes:

- `400` - Bad request (missing icon name)
- `404` - Icon not found
- `304` - Not modified (cached)
- `200` - Success

## Browser Caching

Icons are cached for 1 year with proper ETags. The API automatically handles:

- Cache headers for optimal performance
- ETag generation for cache validation
- 304 Not Modified responses

## Best Practices

1. **Use lazy loading** for icons below the fold
2. **Preload** frequently used icons
3. **Use appropriate styles** (micro for small spaces)
4. **Add alt text** for accessibility when using img tags
5. **Set proper dimensions** to prevent layout shift
6. **Use currentColor** for theme-adaptable icons

## Migration from Full Libraries

### Before (Full Library)

```php
require_once 'iso-core/Core/IconLibrary.php';
echo IconLibrary::getIcon('home', ['class' => 'w-6 h-6']);
```

### After (Icon API)

```php
iso_icon('home', 'outline', ['class' => 'w-6 h-6']);
```

The Icon API provides the same functionality with better performance and easier usage.